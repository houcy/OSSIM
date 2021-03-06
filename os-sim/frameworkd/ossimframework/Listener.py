#!/usr/bin/python
#
# License:
#
#    Copyright (c) 2003-2006 ossim.net
#    Copyright (c) 2007-2010 AlienVault
#    All rights reserved.
#
#    This package is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; version 2 dated June, 1991.
#    You may not use, modify or distribute this program under any other version
#    of the GNU General Public License.
#
#    This package is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this package; if not, write to the Free Software
#    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,
#    MA  02110-1301  USA
#
#
# On Debian GNU/Linux systems, the complete text of the GNU General
# Public License can be found in `/usr/share/common-licenses/GPL-2'.
#
# Otherwise you can read it here: http://www.gnu.org/licenses/gpl-2.0.txt
#

#
# GLOBAL IMPORTS
#
import os, re, socket, SocketServer, sys, threading

#
# LOCAL IMPORTS
#
import Action
from AlarmGroup import AlarmGroup
import Const
from DoControl import ControlManager
from DoNessus import NessusManager
from DoNagios import NagiosManager
from ApacheNtopProxyManager import ApacheNtopProxyManager
from Logger import Logger
from OssimConf import OssimConf
import Util

#
# GLOBAL VARIABLES
#
logger = Logger.logger
controlmanager = None

class FrameworkBaseRequestHandler(SocketServer.StreamRequestHandler):

    __nessusmanager = None
    __nagiosmanager = None
    __conf = None



    def handle(self):
        global controlmanager

        self.__id = None

        logger.debug("Request from: %s:%i" % (self.client_address))

        while 1:
            try:
                line = self.rfile.readline().rstrip('\n')
                if len(line) > 0:
                    command = line.split()[0]

                    # set sane default response
                    response = ""

                    # check if we are a "control" request message
                    if command == "control":
                        # spawn our control timer
                        if controlmanager == None:
                            controlmanager = ControlManager(OssimConf(Const.CONFIG_FILE))

                        response = controlmanager.process(self, command, line)

                    # otherwise we are some form of standard control message
                    elif command == "nessus":
                        if self.__nessusmanager == None:
                            self.__nessusmanager = NessusManager

                        response = self.__nessusmanager.process(line)

                    elif command == "nagios":
                        if self.__nagiosmanager == None:
                            self.__nagiosmanager = NagiosManager(OssimConf(Const.CONFIG_FILE))

                        response = self.__nagiosmanager.process(line)

                    elif command == "ping":
                        response = "pong\n"

                    elif command == "add_asset" or command == "remove_asset" or command == "refresh_asset_list":
                        linebk = ""                        
                        if controlmanager == None:
                            controlmanager = ControlManager(OssimConf(Const.CONFIG_FILE))
                        linebk = "action=\"refresh_asset_list\"\n"
                        response = controlmanager.process(self, command, linebk)
                           
                    elif command == "refresh_sensor_list":
                        logger.info("Check ntop proxy configuration ...")
                        ap = ApacheNtopProxyManager(OssimConf(Const.CONFIG_FILE))
                        ap.refreshConfiguration()
                        
                    else:
                        a = Action.Action(line)
                        a.start()

                        # Group Alarms
                        #ag = AlarmGroup.AlarmGroup()
                        #ag.start()

                    # return the response as appropriate
                    if len(response) > 0:
                        self.wfile.write(response)

                    line = ""

                else:
                    return
            except socket.error, e:
                logger.warning("Client disconnected..." )

            except IndexError:
                logger.error("IndexError")

            except Exception, e:
                logger.error("Unexpected exception in control: %s" % str(e))
                return


    def finish(self):
        global controlmanager
        if controlmanager != None:
            controlmanager.finish(self)

        return SocketServer.StreamRequestHandler.finish(self)


    def set_id(self, id):
        self.__id = id


    def get_id(self):
        return self.__id


class FrameworkBaseServer(SocketServer.ThreadingTCPServer):
    allow_reuse_address = True

    def __init__(self, server_address, handler_class=FrameworkBaseRequestHandler):
        SocketServer.ThreadingTCPServer.__init__(self, server_address, handler_class)
        return


    def serve_forever(self):
        while True:
            self.handle_request()
   
        return



class Listener(threading.Thread):

    def __init__(self):

        self.__server = None
        threading.Thread.__init__(self)


    def run(self):


        try:
            serverAddress = (str(Const.LISTENER_ADDRESS), int(Const.LISTENER_PORT))
            self.__server = FrameworkBaseServer(serverAddress, FrameworkBaseRequestHandler)

        except socket.error, e:
            logger.critical(e)
            sys.exit()


        self.__server.serve_forever()


if __name__ == "__main__":

    listener = Listener()
    listener.start()

# vim:ts=4 sts=4 tw=79 expandtab:

<?php
/*****************************************************************************
*
*    License:
*
*   Copyright (c) 2003-2006 ossim.net
*   Copyright (c) 2007-2009 AlienVault
*   All rights reserved.
*
*   This package is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; version 2 dated June, 1991.
*   You may not use, modify or distribute this program under any other version
*   of the GNU General Public License.
*
*   This package is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this package; if not, write to the Free Software
*   Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,
*   MA  02110-1301  USA
*
*
* On Debian GNU/Linux systems, the complete text of the GNU General
* Public License can be found in `/usr/share/common-licenses/GPL-2'.
*
* Otherwise you can read it here: http://www.gnu.org/licenses/gpl-2.0.txt
****************************************************************************/
/**
* Class and Function List:
* Function list:
* Classes list:
*/

ob_implicit_flush();
ini_set('include_path', '/usr/share/ossim/include');

require_once ('classes/Scan.inc');
include_once ('classes/Session.inc');

$net             = $argv[1];
$remote_sensor   = $argv[2];
$timing_template = ($argv[3] != "") ? $argv[3] : "-T3";

$full = ($argv[4] == "full" || $argv[4] == "fast") ? $argv[4] : "";

$nmap_completed_scan = $argv[5];

if (!preg_match("/\d+\.\d+\.\d+\.\d+/",$net)) 
	die("Incorrect net/host format $net\n");

if ($remote_sensor != "" && $remote_sensor != "null") 
{
    $rscan = new RemoteScan($net,"ping",$remote_sensor, $nmap_completed_scan);
    $quiet = ($timing_template != "") ? FALSE : TRUE;
    echo "Scanning remote network: $net\n";
    $rscan->do_scan($quiet);
    if ($rscan->err()=="") 
	{
        $ips=$rscan->get_scan();
    } 
	else 
	{    
        $ips = array();
        echo "Unable to run remote scan: ".$rscan->err()."\n";
    }
} 
else 
{
    echo "Scanning local network: $net\n";
    $scan = new Scan($net, null, $nmap_completed_scan);
    $scan->append_option($timing_template);
    if ($full == "fast") 
	{
        $scan->append_option("-F");
    	$scan->do_scan(TRUE);
    } 
	elseif ($full == "full") 
	{
    	$scan->do_scan(TRUE);
    } 
	else 
	{
    	$scan->do_scan(FALSE);
    }
    echo "\n";
    
	$ips=$scan->get_scan();
}

foreach ($ips as $ip => $val) {
    echo "Host $ip appears to be up\n";
}
?>

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
* - Incident_metric()
* - get_id()
* - get_incident_id()
* - get_target()
* - get_metric_type()
* - get_metric_value()
* - get_list()
* Classes list:
* - Incident_metric
*/
require_once ("ossim_db.inc");
require_once 'classes/Util.inc';

class Incident_metric {
    var $id;
    var $incident_id;
    var $target;
    var $metric_type;
    var $metric_value;
    function Incident_metric($id, $incident_id, $target, $metric_type, $metric_value) {
        $this->id = intval($id);
        $this->incident_id = intval($incident_id);
        $this->target = Util::htmlentities($target, ENT_COMPAT, "UTF-8");
        $this->metric_type = Util::htmlentities($metric_type, ENT_COMPAT, "UTF-8");
        $this->metric_value = intval($metric_value);
    }
    function get_id() {
        return $this->id;
    }
    function get_incident_id() {
        return $this->incident_id;
    }
    function get_target() {
        return $this->target;
    }
    function get_metric_type() {
        return $this->metric_type;
    }
    function get_metric_value() {
        return $this->metric_value;
    }
    function get_list($conn, $args) {
        $list = array();
        $query = OssimQuery("SELECT * FROM incident_metric $args");
        if (!$rs = & $conn->Execute($query)) {
            print $conn->ErrorMsg();
        } else {
            while (!$rs->EOF) {
                $list[] = new Incident_metric($rs->fields["id"], $rs->fields["incident_id"], $rs->fields["target"], $rs->fields["metric_type"], $rs->fields["metric_value"]);
                $rs->MoveNext();
            }
        }
        return $list;
    }
}
?>

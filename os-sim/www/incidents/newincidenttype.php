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
require_once ('classes/Session.inc');
require_once 'classes/Security.inc';
require_once ('ossim_db.inc');
require_once ('classes/Incident_type.inc');
require_once ("classes/Incident_type.inc");

Session::logcheck("MenuIncidents", "IncidentsTypes");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title> <?php echo gettext("OSSIM Framework"); ?> </title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
	<meta http-equiv="Pragma" CONTENT="no-cache"/>
	<link rel="stylesheet" type="text/css" href="../style/style.css"/>
</head>
<body>

  <h1> <?php echo gettext("New Ticket type"); ?> </h1>

<?php

$inctype_id = POST('id');
$inctype_descr = POST('descr');
$custom = intval(POST('custom'));
ossim_valid($inctype_descr, OSS_ALPHA, OSS_SPACE, OSS_PUNC, OSS_AT, 'illegal:' . _("Description"));
ossim_valid($inctype_id, OSS_ALPHA, OSS_SPACE, OSS_PUNC, 'illegal:' . _("id"));
if (ossim_error()) {
    die(ossim_error());
}
if (POST('insert')) {
    
    $db = new ossim_db();
    $conn = $db->connect();
    
    Incident_type::insert($conn, $inctype_id, $inctype_descr,(($custom==1) ? "custom" : ""));
    $db->close($conn);
?>
    <br><br><p> <?php echo gettext("New Ticket type  succesfully inserted"); ?> </p>

<?php
    $location = ($custom==1) ? "modifyincidenttypeform.php?id=".urlencode($inctype_id) : "incidenttype.php";
    sleep(1);
    echo "<script>window.location='$location';</script>";
}
?>

</body>
</html>


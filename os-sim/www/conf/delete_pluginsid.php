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
Session::logcheck("MenuConfiguration", "ConfigurationPlugins");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <title> <?php
echo gettext("Plugin reference"); ?> </title>
  <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
  <link rel="stylesheet" type="text/css" href="../style/style.css"/>
  
  <script type="text/javascript" src="../js/jquery-1.3.2.min.js"></script>
</head>
<body>

<?php
include ("../hmenu.php");
require_once 'ossim_db.inc';
require_once 'classes/Security.inc';
$plugin_id = GET('id');
$plugin_sid = GET('sid');
ossim_valid($plugin_id, OSS_DIGIT, OSS_NULLABLE, 'illegal:' . _("plugin_id"));
ossim_valid($plugin_sid, OSS_DIGIT, OSS_NULLABLE, 'illegal:' . _("plugin_sid"));
if (ossim_error()) {
    die(ossim_error());
}
$db = new ossim_db();
$conn = $db->connect();

require_once 'classes/Plugin.inc';
require_once 'classes/Plugin_sid.inc';

?>
<table align="center">
<?php
$message = _("Can't delete reference");
if ($plugin_id!="" && $plugin_sid!="") {
	$error = Plugin_sid::delete($conn,$plugin_id,$plugin_sid);
	$message = ($error) ? _("Can't delete sid (not found)") : _("Event type deleted");
}
?>

	<tr><td class="nobborder" colspan=2 id="message" class="nobborder" style="text-align:center">
	<?=$message?>
	</td></tr>
	
</table>

</body>

<?php
$db->close($conn);
?>
<script type="text/javascript">
$(document).ready(function(){
	setTimeout("document.location = 'pluginsid.php?id=<?php echo $plugin_id ?>'",1000);
});
</script>
</html>

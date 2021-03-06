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
require_once "classes/Security.inc";
require_once "classes/Session.inc";
require_once "ossim_conf.inc";
require_once ('ossim_db.inc');
/* connect to db */
$db = new ossim_db();
$conn = $db->connect();
$user = Session::get_session_user();

if (preg_match("/pro|demo/i",$conf->get_conf("ossim_server_version", FALSE))) Acl::changefirst($conn, $user);
else Session::changefirst($conn, $user);

$conf = $GLOBALS["CONF"];

if (!isset($_SESSION["_user"])) {
    $ossim_link = $conf->get_conf("ossim_link", FALSE);
    $login_location = $ossim_link . '/session/login.php';
	header("Location: $login_location");
	exit;
}

$version = $conf->get_conf("ossim_server_version", FALSE);
$opensource = (!preg_match("/.*pro.*/i",$version) && !preg_match("/.*demo.*/i",$version)) ? true : false;
$demo = (preg_match("/.*demo.*/i",$version)) ? true : false;
$pass1 = GET('pass1');
$pass2 = GET('pass2');
$oldpass = GET('oldpass');
ossim_valid($pass1, OSS_ALPHA, OSS_PUNC_EXT, OSS_NULLABLE, 'illegal:' . _("Password"));
ossim_valid($pass2, OSS_ALPHA, OSS_PUNC_EXT, OSS_NULLABLE, 'illegal:' . _("Password"));
ossim_valid($oldpass, OSS_ALPHA, OSS_NULLABLE, 'illegal:' . _("Current Password"));
if (ossim_error()) {
    die(ossim_error());
}
if ($pass1 != "") {
	/* check passwords */
	if (0 != strcmp($pass1, $pass2)) {
		$msg = _("Passwords mismatches");
	} elseif (strlen($pass1) < 5) {
		$msg = _("Minimum password length is 5 characters.");
	} elseif (count($user_list = Session::get_list($conn, "WHERE login = '" . $user . "' and pass = '" . md5($oldpass) . "'")) < 1) {
		$msg = _("Current password is not correct");
	} elseif ($pass1 == $oldpass) {
		$msg = _("You must change your old password.");
	} else {
		if (preg_match("/pro|demo/i",$conf->get_conf("ossim_server_version", FALSE)))
			Acl::changepass($conn, $user, $pass1);
		else
			Session::changepass($conn, $user, $pass1);
		header("location:../index.php");
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title> <?php echo gettext("AlienVault - ".($opensource ? "Open Source SIM" : ($demo ? "Unified SIEM Demo" : "Unified SIEM"))); ?> </title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
  <script type="text/javascript" src="../js/jquery-1.3.2.min.js"></script>
  <script type="text/javascript" src="../js/jquery.pstrength.js"></script>
  <link rel="stylesheet" type="text/css" href="../style/style.css"/>
  <link rel="Shortcut Icon" type="image/x-icon" href="../favicon.ico">
</head>
<body onload="$('#pass1').pstrength()">
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<table align="center" style="padding:1px;background-color:#f2f2f2;border-color:#aaaaaa" class=nobborder><tr><td class="nobborder">
<table align="center" class="noborder" style="background-color:white">
<form>
	<tr> <td class="nobborder" style="text-align:center;padding:30px 20px 0px 20px">
	   <img src="../pixmaps/ossim<?= (preg_match("/.*pro.*/i",$version)) ? "_siem" : ((preg_match("/.*demo.*/i",$version)) ? "_siemdemo" : "") ?>.png" />
	</td> </tr>

	<tr><td class="center nobborder" style="padding-top:20px;padding-bottom:20px"><?=_("Your password has <b>expired</b>.<br>Please enter your new password.")?></td></tr>
	<tr>
		<td class="nobborder center">
		  <table align="center" cellspacing=4 cellpadding=2 style="background-color:#eeeeee;border-color:#dedede">
		  <tr>
			<td style="text-align:right" class="nobborder"> <?php
		echo gettext("Current Password"); ?> </td>
			<td style="text-align:left" class="nobborder"><input type="password" name="oldpass" id="oldpass"/></td>
		  </tr>
		  <tr>
			<td style="text-align:right;padding-top:4px" valign="top" class="nobborder"> <b><?php
		echo gettext("New Password"); ?></b> </td>
			<td style="text-align:left" class="nobborder"><input type="password" name="pass1" id="pass1"/></td>
		  </tr>
		  <tr>
			<td style="text-align:right" class="nobborder"> <?php
		echo gettext("Rewrite Password"); ?> </td>
			<td style="text-align:left" class="nobborder"><input type="password" name="pass2" id="pass2" /></td>
		  </tr>
		  </table>
		</td>
   </tr>
   <? if ($msg != "") { ?>
   <tr><td class="center nobborder" style="color:red"><?=$msg?></td></tr>
   <? } ?>
   <tr>
    <td class="nobborder" style="text-align:center;padding-top:20px">

    <input type="submit" value="<?php
    echo gettext("Change"); ?>" class="btn" style="font-size:12px">
	
	<input type="button" onclick="document.location.href='../index.php'" value="<?php
    echo gettext("Skip"); ?>" class="btn" style="font-size:12px">

    </td>
   </tr>
   <tr>
    <td class="nobborder" style="text-align:center">
    <br/>
    </td>
  </tr>
</form>
</table>
</td></tr></table>
</body>
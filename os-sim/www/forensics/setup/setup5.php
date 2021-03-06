<?php
/**
* Class and Function List:
* Function list:
* Classes list:
*/
/*******************************************************************************
** OSSIM Forensics Console
** Copyright (C) 2009 OSSIM/AlienVault
** Copyright (C) 2004 BASE Project Team
** Copyright (C) 2000 Carnegie Mellon University
**
** (see the file 'base_main.php' for license details)
**
** Built upon work by Roman Danyliw <rdd@cert.org>, <roman@danyliw.com>
** Built upon work by the BASE Project Team <kjohnson@secureideas.net>
*/
include ("../includes/base_setup.inc.php");
include ("base_conf_contents.php");
require_once 'classes/Util.inc'
session_start();
$language = $_SESSION['language'];
$useauth = $_SESSION['useuserauth'];
$adodb = $_SESSION['adodbpath'];
$uri = preg_replace("/\/setup\/setup5.php/", "", $_SERVER['SCRIPT_NAME']);
$dbtype = $_SESSION['dbtype'];
$dbhost = $_SESSION['dbhost'];
$dbport = $_SESSION['dbport'];
$dbname = $_SESSION['dbname'];
$dbusername = $_SESSION['dbusername'];
$dbpasswd = $_SESSION['dbpasswd'];
$arcdbexists = $_SESSION['arcdbexists'];
$arcdbhost = $_SESSION['arcdbhost'];
$arcdbport = $_SESSION['arcdbport'];
$arcdbname = $_SESSION['arcdbname'];
$arcdbusername = $_SESSION['arcdbusername'];
$arcdbpasswd = $_SESSION['arcdbpasswd'];
if (file_exists('../base_conf.php')) die("If you wish to re-run the setup routine, please either move OR delete your previous base_conf file first.");
$writeable = (is_writable("..")) ? "Yes" : "No";
if ($writeable == "Yes") {
    $confcontents = returnContents();
    $handle = fopen("../base_conf.php", "w");
    if (fwrite($handle, $confcontents) === FALSE) {
        $errorMsg = "Can't write base_conf.php file! <br>Please copy the below info into your base_conf.php file";
        $error = 1;
    } else {
        $error = 0;
    }
} else {
    $confcontents = returnContents();
    $error = 1;
    $errorMsg = "Can't write base_conf.php file! <br>Please copy the below info into your base_conf.php file";
}
if ($error != 1) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- Forensics Console -->
<HTML>

<HEAD>
  <META HTTP-EQUIV="pragma" CONTENT="no-cache">
  <TITLE>Forensics Console</TITLE>
  <LINK rel="stylesheet" type="text/css" HREF="../styles/base_style.css">
</HEAD>
<BODY>
<TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=5>
    <TR>
      <TD class="mainheader"> &nbsp </TD>
      <TD class="mainheadertitle">
         Forensics Console Setup Program
      </TD>
    </TR>
</TABLE>
<br>
<P>
<?php
echo ("<div class='errorMsg' align='center'>" . $errorMsg . "</div>");
echo ("<hr><div class='code'><pre>" . Util::htmlentities($confcontents, ENT_COMPAT, "UTF-8") . "</pre><div><br><hr>");
?>
<center>
You can now safely remove the <b>setup</b> directory from your BASE folder. Please take the time to do this now.<p>
Once you have created your base_conf.php,<br> <a href="../">click here to access your install</a>.
</center>
</center>
</BODY>
</HTML>

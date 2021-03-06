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
Session::logcheck("MenuConfiguration", "ToolsDownloads");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <title> <?php
echo gettext("OSSIM Framework"); ?> </title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
  <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
  <link rel="stylesheet" type="text/css" href="../style/style.css"/>
  <script type="text/javascript" src="../js/jquery-1.3.2.min.js"></script>
  <style type="text/css">
  	li a { font-weight: bold; font-size:12px }
  	li { padding:5px;
  		background:#f2f2f2; border:1px solid #dddddd; 
  		border-radius: 6px; -moz-border-radius: 6px; -webkit-border-radius: 6px; 
  		}
  	li p { text-align:left; margin:5px 15px 5px 15px}
  </style>
</head>
<body>

<?php
include ("../hmenu.php");
require_once ('classes/Downloads.inc');
?>
<center>
<div align="left" style="margin-left:30px">
<dl>
<?php
foreach($downloads as $download) {
    print "<li><a href=\"" . $download["URL"] . "\">" . _($download["Name"]) . "</a> (" . $download["Version"] . ")\n";
    print "<p><a href=\"" . $download["Homepage"] . "\"><small>" . $download["Homepage"] . "</small></a><br/>\n";
    print "<small>" . _($download["Description"]) . "</small><br/>\n";
    print "</li></p>";
}
?>
</dl>
</div>
</center>
    
</body>
</html>


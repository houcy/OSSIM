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
// load column layout
require_once ('../conf/layout.php');
$category = "conf";
$name_layout = "plugin_layout";
$layout = load_layout($name_layout, $category);
$gheight = 200;
$category_id = GET('category_id');
$subcategory_id = GET('subcategory_id');
$sourcetype = GET('sourcetype');
ossim_valid($category_id, OSS_DIGIT, OSS_NULLABLE, 'illegal:' . _("Category ID"));
ossim_valid($subcategory_id, OSS_DIGIT, OSS_NULLABLE, 'illegal:' . _("SubCategory ID"));
ossim_valid($sourcetype, OSS_ALPHA, OSS_SPACE, OSS_NULLABLE, 'illegal:' . _("Source Type"));
if (ossim_error()) {
	die(ossim_error());
}
if ($category_id != "") {
	$gheight = 135;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title> <?php echo gettext("Priority and Reliability configuration"); ?> </title>
  <meta http-equiv="Pragma" content="no-cache"/>
  <link rel="stylesheet" type="text/css" href="../style/style.css"/>
  <link rel="stylesheet" type="text/css" href="../style/flexigrid.css"/>
  <script type="text/javascript" src="../js/jquery-1.3.2.min.js"></script>
  <script type="text/javascript" src="../js/jquery.flexigrid.js"></script>
  <script type="text/javascript" src="../js/urlencode.js"></script>
</head>
<body>

	<?php if (GET('nohmenu') == "") include ("../hmenu.php"); ?>
	<div  id="headerh1" style="width:100%;height:1px">&nbsp;</div>

	<style>
		table, th, tr, td {
			background:transparent;
			border-radius: 0px;
			-moz-border-radius: 0px;
			-webkit-border-radius: 0px;
			border:none;
			padding:0px; margin:0px;
		}
		input, select {
			border-radius: 0px;
			-moz-border-radius: 0px;
			-webkit-border-radius: 0px;
			border: 1px solid #8F8FC6;
			font-size:12px; font-family:arial; vertical-align:middle;
			padding:0px; margin:0px;
		}
	</style>
	
	<?php if ($cloud_instance) {  $gheight=250; ?>
	<p style="text-align:center"><a href="../session/detect.php" style="text-decoration:none"><span class="buttonlink"><img src="../pixmaps/wand--plus.png" border="0" align="absmiddle" style="padding-bottom:2px;padding-right:8px"><?=_("Auto-detect Data Sources")?></span></a></p>
	<?php } ?>
	<?php if ($sourcetype != "") { ?>
	<p style="text-align:center"><a href="plugin.php" style="text-decoration:none"><span class="buttonlink"><img src="../pixmaps/cross-circle-frame.png" border="0" align="absmiddle" style="padding-bottom:2px;padding-right:8px"><?=_("Remove Source Type filter").": ".$sourcetype ?> </span></a></p>
	<?php } ?>
	<table id="flextable" style="display:none"></table>
	<script>
	function get_width(id) {
		if (typeof(document.getElementById(id).offsetWidth)!='undefined') 
			return document.getElementById(id).offsetWidth-20;
		else
			return 700;
	}
	function get_height() {
	   return parseInt($(document).height()) - <?=$gheight?>;
	}
    
	function action(com,grid) {
        var items = $('.trSelected', grid);
        if (com=='<?=_("Insert new event type")?>') {
			if (typeof(items[0]) != 'undefined')
				<?php if ($category_id != "") { ?>
				parent.location.href = 'newpluginsidform.php?plugin='+urlencode(items[0].id.substr(3));
				<?php } else { ?>
				document.location.href = 'newpluginsidform.php?plugin='+urlencode(items[0].id.substr(3));
				<?php } ?>
			else alert('<?php echo _("You must select a data source")?>');
		}
        else if (com=='<?php echo _("Edit") ?>') {
        	if (typeof(items[0]) != 'undefined')
				<?php if ($category_id != "") { ?>
            	parent.location.href = 'pluginsid.php?id='+urlencode(items[0].id.substr(3));
				<?php } else { ?>
				document.location.href = 'pluginsid.php?id='+urlencode(items[0].id.substr(3));
				<?php } ?>
            else
              alert('<?php echo _('You must select a data source')?>');
        }
	}
    function linked_to(rowid) {
    	<?php if ($category_id != "") { ?>
    	parent.location.href = 'pluginsid.php?id='+urlencode(rowid);
		<?php } else { ?>
        document.location.href = 'pluginsid.php?id='+urlencode(rowid);
        <?php } ?>
    }
	function save_layout(clayout) {
		$("#flextable").changeStatus('<?=_('Saving column layout')?>...',false);
		$.ajax({
				type: "POST",
				url: "../conf/layout.php",
				data: { name:"<?php echo $name_layout
?>", category:"<?php echo $category
?>", layout:serialize(clayout) },
				success: function(msg) {
					$("#flextable").changeStatus(msg,true);
				}
		});
	}
	$(document).ready(function() {
		$("#flextable").flexigrid({
			url: 'getplugin.php<?php if ($sourcetype != "") echo "?query=$sourcetype&qtype=sourcetype"; elseif ($category_id != "") echo "?query=$category_id&qtype=category_id&subcategory_id=".$subcategory_id; ?>',
			dataType: 'xml',
			colModel : [
			<?php
$default = array(
    "id" => array(
        _('Data Source ID'),
        100,
        'true',
        'center',
        false
    ) ,
    "name" => array(
        _('Name'),
        120,
        'true',
        'center',
        false
    ) ,
    "type" => array(
        _('Type'),
        80,
        'true',
        'center',
        false
    ) ,
    "source_type" => array(
        _('Source Type'),
        200,
        'true',
        'left',
        false
    ) ,
    "description" => array(
        _('Description'),
        560,
        'true',
        'left',
        false
    )
);
list($colModel, $sortname, $sortorder) = print_layout($layout, $default, "id", "asc");
echo "$colModel\n";
?>
				],
            buttons : [
                {name: '<?=_("Insert new event type")?>', bclass: 'add', onpress : action},
                {separator: true},
                {name: '<?=_("Edit")?>', bclass: 'modify', onpress : action},
				{separator: true}
            ],
			searchitems : [
				{display: 'Name', name : 'name', isdefault: true},
				{display: 'Description', name : 'description'}
				],
			sortname: "<?php echo $sortname
?>",
			sortorder: "<?php echo $sortorder
?>",
			usepager: true,
			title: '<?=_('DATA SOURCES')?>',
			pagestat: '<?=_('Displaying {from} to {to} of {total} data sources')?>',
			nomsg: '<?=_('No data sources')?>',
			useRp: true,
			rp: 50,
			showTableToggleBtn: true,
			singleSelect: true,
			width: get_width('headerh1'),
			height: get_height(),
			onColumnChange: save_layout,
			onEndResize: save_layout,
			onDblClick: linked_to
		});   
	});
	</script>

</body>
</html>


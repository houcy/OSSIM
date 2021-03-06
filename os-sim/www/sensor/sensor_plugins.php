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

require_once ('classes/Session.inc');
require_once 'ossim_conf.inc';
require_once 'ossim_db.inc';
require_once 'classes/Sensor.inc';
require_once 'classes/Plugin.inc';
require_once 'get_sensors.php';
require_once 'get_sensor_plugins.php';
require_once 'classes/Security.inc';


$info_error = null;

$ip_get = GET('sensor');
$cmd    = GET('cmd');
$id     = GET('id');

ossim_valid($ip_get, OSS_IP_ADDR, OSS_NULLABLE, 'illegal:' . _("Sensor"));
ossim_valid($cmd, OSS_ALPHA, OSS_SPACE, OSS_SCORE, OSS_NULLABLE, 'illegal:' . _("Cmd"));
ossim_valid($id, OSS_ALPHA, OSS_SPACE, OSS_SCORE, OSS_NULLABLE, 'illegal:' . _("Id"));

if (ossim_error()) {
    die(ossim_error());
}

Session::logcheck("MenuConfiguration", "MonitorsSensors");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title> <?php echo gettext("OSSIM Framework"); ?> </title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
	<meta http-equiv="Pragma" content="no-cache"/>
	<link rel="stylesheet" type="text/css" href="../style/style.css"/>
	<script type="text/javascript" src="../js/jquery-1.3.2.min.js"></script>
	<script type="text/javascript">
		function contenido(id) {
			$("#"+id).toggle();
			id_icono = id.substr (4);

			if ($("#icono"+id_icono).attr('src') == "../pixmaps/server--plus.png") {
				$("#icono"+id_icono).attr('src','../pixmaps/server--minus.png');
				var loading = '<img width="16" align="absmiddle" src="../vulnmeter/images/loading.gif">';
				$("#"+id).html(loading+' <?php echo _("Loading data..."); ?>');
				$("#"+id).css({ padding: "5px 0px 5px 0px" }); 
				var ip = id.substr (4);
				ip = ip.replace(/_/g, ".");
				$.ajax({
						type: "GET",
						url: "get_sensor_info.php",
						data: { sensor_ip: ip },
						success: function(msg) {
							$("#"+id).css({ padding: "0px 0px 0px 0px" });
							$('#'+id).html(msg);
							
						}
				});
			}
			else
				$("#icono"+id_icono).attr('src','../pixmaps/server--plus.png');
			
		}
		
		function load_lead(pid) {
			$.ajax({
				type: "GET",
				url: "get_sensor_leads.php?pid="+pid,
				data: "",
				success: function(msg) {
					  $('#plugin_'+pid).html(msg);
					  $('#plugin_'+pid).show();
					  $('#selector_'+pid).show();
					  mark(pid);
				}
			});
		}
		
		function mark(id) {
			var y = $('#yellow_'+id).val()*3600;
			var r = $('#red_'+id).val()*3600; // need seconds
			var now = new Date; // Generic JS date object
			var unixtime_ms = now.getTime(); // Returns milliseconds since the epoch
			var unixtime = parseInt(unixtime_ms / 1000);
			$('#plugin_'+id+' .trc').each(function(){
				var eventdate = parseInt($(this).attr('txt'));
				var img = "";
				var bgcolor = "";
				if (unixtime - eventdate >= r) {
					img = "../pixmaps/flag_red.png";
					bgcolor = "#FFDFDF";
				} else if (unixtime - eventdate >= y) {
					img = "../pixmaps/flag_yellow.png";
					bgcolor = "#FFFBCF";
				} else {
					img = "../pixmaps/flag_green.png";
					bgcolor = "#CFFFD1";
				}
				$(this).css("background-color",bgcolor);
				$('td img',this).attr("src",img);
			});
		}
		
		$(document).ready(function() {
			
			
			<?php if( $ip_get!="" && Session::sensorAllowed($ip_get) ) {?>
				var loading = '<img width="16" align="absmiddle" src="../vulnmeter/images/loading.gif">';
				var id = '<?php echo $ip_get;?>'; 
				id = id.replace(/\./g, "_");
				id = 'capa'+id;
				$("#icono"+id).attr('src','../pixmaps/server--minus.png');
				$("#"+id).html(loading+' <?php echo _("Loading data..."); ?>');
				$("#"+id).css({ padding: "5px 0px 5px 0px" }); 
				$.ajax({
					type: "GET",
					url: "get_sensor_info.php",
					data: { sensor_ip: '<?php echo $ip_get; ?>' },
					success: function(msg) {
						$("#"+id).css({ padding: "0px 0px 0px 0px" });
						$('#'+id).html(msg);
						$('#'+id).show();
					}
					$('#loading').hide();
				});
			<?php
			}
			else
			{
				?>$('#loading').hide();<?php
			}
			?>
		});
	</script>

<style type="text/css"> 
	html,body { 
		height : auto !important; 
		height:100%; 
		min-height:100%; 
	} 
	
	#error_messages {
		width: 70%;
	}
		
	.ossim_error { width: auto;}
		
	.error_item { padding-left: 50px;}
	
	.s_info {
		font-family:tahoma; 
		font-size:11px;
		font-weight:normal;
	}
	
	#loading {
			position: absolute; 
			width: 99%; 
			height: 99%; 
			margin: auto; 
			text-align: center;
			background: #FFFFFF;
			z-index: 10000;
		}
		
	#loading div{
		position: relative;
		top: 40%;
		margin:auto;
	}
		
	#loading div span{
		margin-left: 5px;
		font-weight: bold;	
	}
		
</style>
	<?php include ("../host_report_menu.php") ?>
</head>

<body>                             
<?php
/* connect to db */
	
	$db             = new ossim_db();
	$conn           = $db->connect();
	$conn_snort     = $db->snort_connect();

// Ajax called BG Execute CMD
if ((!empty($cmd)) && (!empty($id)) && (!empty($ip_get))) {
	if ($cmd == "start") {
		$action = _("Starting");
	} elseif ($cmd == "stop") {
		$action = _("Stopping");
	} elseif ($cmd == "disable") {
		$action = _("Disabling");
	} elseif ($cmd == "stop") {
		$action = _("Enabling");
	}
	
	if ($plugin_list = Plugin::get_list($conn, "WHERE id = $id")) 
	{
		$plugin_name = $plugin_list[0]->get_name();
	} 
	else 
	{
		$plugin_name = $id;
	}
?>
<div id='loading'>
	<div><img src='../pixmaps/loading3.gif' alt='<?php echo _("Loading")?>'/><span><?php echo $action._(" $plugin_name, please wait a few seconds")?> ...</span></div>
</div>
<?php send_msg($cmd, $ip_get, $id); ?>
<script type="text/javascript">parent.location.href='sensor_plugins.php';</script>
<?php }


include ("../hmenu.php");

// Sensors perm check
if ( !Session::menu_perms("MenuConfiguration", "PolicySensors") ) 
{
	echo ossim_error(_("You need permissions of section '")."<b>"._("Configuration -> SIEM Components -> Sensors")."</b>"._("' to see this page. Contact with the administrator."), 'NOTICE');
	exit;
}

?>

<div id='loading'>
	<div><img src='../pixmaps/loading3.gif' alt='<?php echo _("Loading")?>'/><span><?php echo _("Loading sensors information, please wait a few seconds,")?> ...</span></div>
</div>

<?php
ob_flush();

	$conf           = $GLOBALS["CONF"];
	$acid_link      = $conf->get_conf("acid_link");
	$acid_prefix    = $conf->get_conf("event_viewer");
	$acid_main_link = str_replace("//", "/", $conf->get_conf("acid_link") . "/" . $acid_prefix . "_qry_main.php?clear_allcriteria=1&search=1&bsf=Query+DB&ossim_risk_a=+");
	#
	
	$db_sensor_list = array();
	$list_no_active = array();
	$tmp_list = Sensor::get_all($conn);
	if (is_array($tmp_list)) 
	{
		foreach($tmp_list as $tmp)
		{
			$db_sensor_list[]                = $tmp->get_ip();
			$db_sensor_rel[$tmp->get_ip() ]  = $tmp->get_name();
			$list_no_active[$tmp->get_ip() ] = $tmp->get_name();
		}
	}
	list($sensor_list, $err) = server_get_sensors($conn);

	if ($err != "") 
		$info_error[] = $err;
		
	if (!$sensor_list && empty($ip_get)) 
		$info_error[] = "<center>"._("There aren't any sensors connected to OSSIM server")."</center>";

	$ossim_conf = $GLOBALS["CONF"];
	$use_munin  = $ossim_conf->get_conf("use_munin");
	

if ( !empty($info_error) )
{
	?>
	<div id='error_messages' class='ossim_error'>
		<div class='error_item'><?php echo implode("</div><div class='error_item'>", $info_error)?></div>
	</div>

	<?php
}

?>

<table class="noborder" border='0' cellpadding='0' cellspacing='0' width='100%' align='center'>

	<?php
	foreach($sensor_list as $sensor=>$sensor_plugins_list) 
	{
		$ip = $sensor;
		unset($list_no_active[$ip]); // Remove active sensors of inactive list 
		if (isset($db_sensor_rel[$ip])) $name = $db_sensor_rel[$ip];
		$state = "start";

		/* get plugin list for each sensor */
		//$sensor_plugins_list = server_get_sensor_plugins($ip);
		/*
		*  show sensor ip (and sensor name if available)
		*  at the top of the table
		*/
		$up_enabled = 0;
		$down_disabled = 0;
		$totales = 0;
		foreach($sensor_plugins_list as $plugin_id=>$sensor_plugin) {
			$state = $sensor_plugin["state"];
			$enabled = $sensor_plugin["enabled"];
			if ($state == 'start' || $enabled == 'true') {
				$up_enabled++;
			}
			if ($state == 'stop' || $enabled != 'true') {
				$down_disabled++;
			}
			$totales++;
		}
  	
	?>
	
	
	<tr>
		<td class='noborder'>
			<a href='' onclick="contenido('capa<?php echo str_replace(".","_",$ip)?>'); return false;">
				<?php 
					$id_estado = "icono" . str_replace(".","_",$ip);
					$src       = ( $ip_get==$ip ) ? "../pixmaps/server--minus.png" : "../pixmaps/server--plus.png";
				?>
				<img id='<?php echo $id_estado?>' align='bottom' src="<?php echo $src?>" border='0'>
			</a>
		</td>
    	
		<td class='noborder' style='text-align: left;padding-left:5px;' height='25' bgcolor='#DCDCDC' nowrap='nowrap'>
			<table class='noborder'  border='0' cellpadding='0' cellspacing='0' style='background-color:transparent;' nowrap='nowrap'>
				<tr>
					<td class='noborder' style='padding-right:2px;'></td>
					<td class='noborder' style='text-align: left;padding-right:4px;'>
						<?php 
							$suf      = ( isset($name) ) ? $name : $ip;
							$id_s     = $ip.";".$suf;
							$name_txt = ( isset($name) ) ? " [ $name ] " : "";
						?>
						<a href='' onclick="contenido('capa<?php echo str_replace(".", "_", $ip)?>');return false;" class='HostReportMenu' id='<?php echo $id_s?>'><?php echo $ip.$name_txt?></a>
					</td>
			
					<td class='noborder' style='padding-right:4px;'>
					<?php
					/*
					* Show munin link for every sensor
					*
					*/
					if ($use_munin == 1) 
					{
						$munin_link = $ossim_conf->get_conf("munin_link");
						if ($munin_link=="") 
							$munin_link = "/munin/";
						
						$server_ip= trim(`grep framework_ip /etc/ossim/ossim_setup.conf | cut -f 2 -d "="`);
						$https    = trim(`grep framework_https /etc/ossim/ossim_setup.conf | cut -f 2 -d "="`);
						
						if ($ip == $server_ip) 
						{
							$munin_url = 'http'.(($https=="yes") ? "s" : "").'://'.$_SERVER["SERVER_NAME"].$munin_link;
							$munin_url = str_replace("localhost",$ip,$munin_url);
							$testmunin = "http://" . $ip . "/munin/";
						} 
						else 
						{
							$munin_url  = 'http://'.$ip.$munin_link;
							$testmunin  = $munin_url;
						}	
						
						// check valid munin url
						error_reporting(0);
						$testlink = get_headers($testmunin);
						error_reporting(E_ALL ^ E_NOTICE);
	
						if ( preg_match("/200 OK/",$testlink[0]) ) 
						{
							?><a href="<?php echo $munin_url;?>"><img align="bottom" src="../pixmaps/chart_bar.png" border="0"/></a><?php
						} 
						else 
						{
							?><img align="bottom" src="../pixmaps/chart_bar_off.png" border="0"/><?php
						}
					}
					?>	
					</td>
					
					<td class="noborder" style="text-align: left;">
						<span class="s_info"> [ <?php echo _("UP or ENABLED")?>: </span>
						<span class="s_info" style="color:#089313;font-weight:bold;"><?php echo $up_enabled?></span>
						<span class="s_info">/ <?php echo _("DOWN or DISABLED")?>: </span>
						<span class="s_info" style="color:#E00E01;font-weight:bold;"><?php echo $down_disabled?></span> 
						<span class="s_info">/ <?php echo _("Totals")?>: </span>
						<span class="s_info" style="color:#000000;font-weight:bold;"><?php echo $totales?></span>
						<span class="s_info"> ]</span>
						<?php
						
						if ( is_array($db_sensor_list) ) 
						{
							if (!in_array($ip, $db_sensor_list) ) 
							{
								echo "<span style='margin-left: 15px;'>";
									echo "<b>"._("Warning")."</b>:"._("The sensor is being reported as enabled by the server but isn't configured.");
									echo _("Click")." <a href=\"newsensorform.php?ip=$ip\">"._("here")."</a> "._("to configure the sensor").".";
								echo "</span>";
							}
						}
						?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	
	<tr>
		<td></td>
		<td height="1" bgcolor="#FFFFFF"></td>
	</tr>
	
	<?php
	
	?>
	<tr>
		<td class="noborder"></td>
		<td class="noborder" valign="top">
			<div id="<?php echo "capa" . str_replace(".","_",$ip); ?>" style="diplay:none"></div>
		</td>
	</tr>

	<?php
	}
	
	foreach($list_no_active as $key => $value) 
	{
		?>
		<tr>
			<td class="noborder"><img align="bottom" src="../pixmaps/server.png" border="0"/></td>
			<td class="noborder" style="text-align: left;padding-left:5px;" height="25" bgcolor="#EDEDED" nowrap='nowrap'>
				<table class="noborder transparent" border='0' cellpadding='0' cellspacing='0' nowrap='nowrap'>
					<tr>
						<td class="noborder" style="padding-right:2px;"></td>
						<td class="noborder" style="text-align: left;color:#696563;padding-right:4px;"><?php echo $key ."[". $value ."]"; ?></td>
						<td class="noborder" style="padding-right:4px;"><img align="bottom" src="../pixmaps/chart_bar_off.png" border="0"/></td>
						<td class="noborder" style="text-align: left;">
							<span class="s_info" style="color:#696563;"> [ <?php echo _("UP or ENABLED")?>: </span>
							<span class="s_info" style="color:#089313;font-weight:bold;"> - </span> 
							<span class="s_info" style="color:#696563;">/ <?php echo _("DOWN or DISABLED")?>: </span>
							<span class="s_info" style="color:#E00E01;font-weight:bold;"> - </span> 
							<span class="s_info" style="color:#696563;">/<?php echo _("Totals")?>: </span>
							<span class="s_info" style="color:#000000;font-weight:bold;"> - </span>
							<span class="s_info" style="color:#696563;"> ]</span>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		
		<tr>
			<td></td>
			<td height="1" bgcolor="#FFFFFF"></td>
		</tr>
		<?php
	}
	?>

	</table>
	
<?php
$db->close($conn);
$db->close($conn_snort);
?>
 
</body>
</html>


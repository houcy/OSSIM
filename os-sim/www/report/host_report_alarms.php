<?php
/*****************************************************************************
*
*    License:
*
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
?>

	<script type="text/javascript">$("#pbar").progressBar(80);$("#progressText").html('<?=gettext("Loading")?> <b><?=gettext("Alarms")?></b>...');</script>

<?php
ob_flush();
flush();
usleep(500000);

require_once ('classes/Session.inc');
$hide_closed = 1; 

if($host!='any')
	list($alarm_list, $count) = Alarm::get_list3($conn, $host, $host, $hide_closed, "ORDER BY a.timestamp DESC", 0, 5, $date_from, $date_to, "");
else
	list($alarm_list, $count) = Alarm::get_list3($conn, '', '', $hide_closed, "ORDER BY a.timestamp DESC", 0, 5, $date_from, $date_to, "");

if ($network)
{
	list($host_start, $host_end) = Util::cidr_conv($host);
	$retfields = Alarm::get_max_byfield($conn,"risk","WHERE (inet_aton('$host_start') <= a.src_ip AND inet_aton('$host_end') >= a.src_ip) OR (inet_aton('$host_start') <= a.dst_ip AND inet_aton('$host_end') >= a.dst_ip)");
} 
else
{
	if( $host!='any' )
		$retfields = Alarm::get_max_byfield($conn,"risk","WHERE a.src_ip=INET_ATON('$host') OR a.dst_ip=INET_ATON('$host')");
	else
		$retfields = Alarm::get_max_byfield($conn,"risk");
}

$a_maxrisk  = $retfields[0];
$backlog_id = $retfields[1];
$alarm_link = "../control_panel/events.php?backlog_id=$backlog_id";
$a_date     = "-";

 
if($host!='any')
	$temp_url="../control_panel/alarm_console.php?&hide_closed=1&src_ip=".urlencode($host)."&dst_ip=".urlencode($host);
else
	$temp_url="../control_panel/alarm_console.php?&hide_closed=1";  
 
?>

<table align="center" width="100%" height="100%" class="bordered">
	<tr>
		<td class="headerpr" height="20"><a style="color:black" href="<?php echo $temp_url?>"><?php echo gettext("Alarms"); ?></a></td>
	</tr>
	
	<?php 
		if (count($alarm_list) < 1) 
		{ 
			$host_txt = ( $host == 'any') ? _("No Alarms found in the System") : _("No Alarms found for")."<i>".$host."</i>"; 
			?>
			<tr><td><?php echo $host_txt;?></td></tr>
			
			<script type="text/javascript">
				document.getElementById('statusbar_unresolved_alarms').innerHTML = '<?php echo Util::number_format_locale((int)0,0)?>';
				document.getElementById('statusbar_alarm_max_risk').innerHTML = '<?php echo preg_replace("/\n|\r/","",Incident::get_priority_in_html("-"))?>';
			</script>
			
			<?php 
		} 
		else 
		{ 
			?>
			<tr>
				<td class="nobborder" valign="top">
					<table class="noborder">
						<tr>
							<th><?php echo _("Alarm")?></th>
							<th><?php echo _("Risk")?></th>
							<th><?php echo _("Source")?></th>
							<th><?php echo _("Destination")?></th>
						</tr>
					
						<?php
						$i = 0;
						
						foreach($alarm_list as $alarm) 
						{
							$i++;
							
							/* hide closed alarmas */
							$bgcolor = ($i%2==0) ? "#E1EFE0" : "#FFFFFF";
							if (($alarm->get_status() == "closed") and ($hide_closed == 1)) 
								continue;
							
							$id         = $alarm->get_plugin_id();
							$sid        = $alarm->get_plugin_sid();
							$backlog_id = $alarm->get_backlog_id();
							$sid_name   = $alarm->get_sid_name();
							$risk       = $alarm->get_risk();
							$date       = Util::timestamp2date($alarm->get_timestamp());
							
							if ($a_date == "-") 
							{ 
								$a_date = $date;
								?>
								<script type="text/javascript">
									document.getElementById('alarms_date').innerHTML = "<?=$a_date?>";
								</script>
								<?php
							} 
							
							$since = ( $backlog_id && $id==1505 ) ? Util::timestamp2date($alarm->get_since()) : $date;
													
							
							$alarm_name = ereg_replace("directive_event: ", "", $sid_name);
							$alarm_name = Util::translate_alarm($conn, $alarm_name, $alarm);
							
							if ($backlog_id && $id==1505)
							{
								$aid               = $alarm->get_event_id();
								$summary           = Alarm::get_alarm_stats($conn, $backlog_id, $aid);
								$event_count_label = $summary["total_count"] . " events";
							} 
							else 
							{
								$event_count_label = 1 . " event";
							}
							$event_count_label = "<br><font color=\"#AAAAAA\" style=\"font-size: 8px;\">(" . $event_count_label . ")</font>";
							
							$src_ip   = $alarm->get_src_ip();
							$dst_ip   = $alarm->get_dst_ip();
							$src_port = $alarm->get_src_port();
							$dst_port = $alarm->get_dst_port();
							$sensors  = $alarm->get_sensors();
							
							if ($risk > 7)     { $bg="red"; $color="white"; }
							elseif ($risk > 4) { $bg="orange"; $color="black"; }
							elseif ($risk > 2) { $bg="green"; $color="white"; }
							else { $bg="transparent"; $color="black"; }
							
							$ss=""; 
							
							foreach($sensors as $sensor) 
								$ss .= Host::ip2hostname($conn, $sensor)."<br>";
							
							$target           = ($greybox) ? "&greybox=1" : "";
							$src_link         = "../report/host_report.php?host=$src_ip$target";
							$dst_link         = "../report/host_report.php?host=$dst_ip$target";
							$src_name         = Host::ip2hostname($conn, $src_ip);
							$dst_name         = Host::ip2hostname($conn, $dst_ip);
							$src_img          = Host_os::get_os_pixmap($conn, $src_ip);
							$dst_img          = Host_os::get_os_pixmap($conn, $dst_ip);
							$src_country      = strtolower(geoip_country_code_by_addr($gi, $src_ip));
							$src_country_name = geoip_country_name_by_addr($gi, $src_ip);
							$src_country_img  = "<img src=\"/ossim/pixmaps/flags/" . $src_country . ".png\" title=\"" . $src_country_name . "\">";
							$dst_country      = strtolower(geoip_country_code_by_addr($gi, $dst_ip));
							$dst_country_name = geoip_country_name_by_addr($gi, $dst_ip);
							$dst_country_img  = "<img src=\"/ossim/pixmaps/flags/" . $dst_country . ".png\" title=\"" . $dst_country_name . "\">";
						?>
						
						<tr>
							<td bgcolor="<?=$bgcolor?>" style="text-align:left">
								<a href="<?php echo $temp_url?>"><?= "<strong>".$alarm_name."</strong>".$event_count_label ?></a>
							</td>
							<td style="text-align:center;background-color:<?=$bg?>;color:<?=$color?>"><strong><?= $risk ?></strong></td>
							
							<td style="background-color:<?=$bgcolor?>;padding-left:2px;padding-right:2px;text-align:center">
								<?php
								if ($src_country) {
									echo "<a href=\"$src_link\"$target>$src_name</a> $src_img $src_country_img";
								} else {
									echo "<a href=\"$src_link\"$target>$src_name</a> $src_img";
								}
							?>
							</td>
							
							<td style="background-color:<?=$bgcolor?>;padding-left:3px;padding-right:3px;text-align:center">
								<?php
								if ($dst_country) 
									echo "<a href=\"$dst_link\">$dst_name</a> $dst_img $dst_country_img";
								else 
									echo "<a href=\"$dst_link\">$dst_name</a> $dst_img";
								
								?>
							</td>
						</tr>
						<?php 
					} 
					?>
					</table>
				</td>
			</tr>
							
			<tr>
				<td style="text-align:right;padding-right:20px">
					<a style="color:black" href="<?php echo $temp_url?>"><strong>More >></strong></a>
				</td>
			</tr>
			
			<script type="text/javascript">
				document.getElementById('statusbar_unresolved_alarms').innerHTML = '<?php echo Util::number_format_locale((int)$count,0)?>';
				document.getElementById('statusbar_alarm_max_risk').innerHTML = '<?php echo preg_replace("/\n|\r/","",Incident::get_priority_in_html($a_maxrisk))?>';
				document.getElementById('statusbar_alarm_max_risk').href = '<?php echo $alarm_link?>';
				document.getElementById('statusbar_alarm_max_risk_txt').href = '<?php echo $alarm_link?>';
			</script>
			
			<?php 
		} 
		
		?>

	<tr><td></td></tr>
</table>
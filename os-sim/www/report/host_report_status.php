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
/**
* Class and Function List:
* Function list:
* - html_service_level()
* - global_score()
* Classes list:
*/
require_once 'classes/Incident.inc';
require_once 'classes/Incident_ticket.inc';
require_once 'classes/Alarm.inc';
require_once 'classes/Status.inc';
require_once 'classes/Util.inc';

function html_service_level($conn, $host="", $date_range=null) 
{
    global $user;
	
	// For custom
	if($date_range != null)
	{
		$date_range['date_from']  =  date("Y-m-d", strtotime($date_range['date_from']));
		$date_range['date_to']    =  date("Y-m-d", strtotime($date_range['date_to']));
			
		$from = strtotime($date_range['date_from']);
		$to   = strtotime($date_range['date_to']);
		
		$diff = $to - $from;
		
		$limit_1 = 86400 + 86400;
		$limit_2 = 86400 + 604800;
		$limit_3 = 86400 + 1296000;
		$limit_4 = 86400 + 2678400;
								
		if ( $diff <= $limit_1 )
			$range = 'day';
		elseif ($diff > $limit_1 && $diff <= $limit_2)
			$range = "week";
		elseif ($diff > $limit_2 && $diff <= $limit_3)
			$range='month';
		elseif ($diff > $limit_3 && $diff <= $limit_4)
			$range='year';
	}
	else
		$range = "day";
	
	
    $level = 100;
    $class = "level4";
    
	if($host!='any')
	{
		$sql = "SELECT c_sec_level, a_sec_level FROM control_panel WHERE id = ? AND time_range = ?";
		$params = array(
			//"global_$user",
			$host,
			$range
		);
	}
	else
	{
		$sql = "SELECT c_sec_level, a_sec_level FROM control_panel WHERE id = ? AND time_range = ? AND rrd_type='global'";
		$params = array(
			'global_'.$user,
			$range
		);
	}
	
	if (!$rs = & $conn->Execute($sql, $params)) {
        echo "Error";
        die($conn->ErrorMsg());
    }
    if ($rs->EOF) {
        return array(
            $level,
            "level11"
        );
    }
    $level = number_format(($rs->fields["c_sec_level"] + $rs->fields["a_sec_level"]) / 2, 0);
    $class = "level" . round($level / 9, 0);
    return array(
        $level,
        $class
    );
}

function global_score($conn, $host) {
    global $conf_threshold;
    	
	if($host!='any')
		$sql = "SELECT host_ip, compromise, attack FROM host_qualification WHERE host_ip='$host'";
	else
		$sql = "SELECT host_ip, compromise, attack FROM host_qualification";
	
    if (!$rs = & $conn->Execute($sql)) {
        die($conn->ErrorMsg());
    }
    
	$score_a = 0;
    $score_c = 0;
    
	while (!$rs->EOF) {
        $score_a+= $rs->fields['attack'];
        $score_c = $rs->fields['compromise'];
        $rs->MoveNext();
    }
    
	$risk_a = round($score_a / $conf_threshold * 100);
    $risk_c = round($score_c / $conf_threshold * 100);
    $risk   = ($risk_a > $risk_c) ? $risk_a : $risk_c;
    $img    = 'green'; // 'off'
    $color  = '';
    
	if ($risk > 500)
        $img = 'red';
    elseif ($risk > 300)
        $img = 'yellow';
    elseif ($risk > 100) 
        $img = 'green';
    
    $alt = "$risk " . _("metric/threshold");
    
	return array(
        $img,
        $alt
    );
}

$conf_threshold = $conf->get_conf('threshold');

if( $date_range != null )
{
	$date_from = $date_range['date_from'];
	$date_to               = ( preg_match("/^\d+\-\d+\-\d+$/",$date_to) ) ?  $date_to." 23:59:59" : $date_to;
	$date_range['date_to'] = $date_to;
}
else
{
	$date_from      = $date_range['date_from'] = strftime("%Y-%m-%d %H:%M:%S", time() - (24 * 60 * 60));
	$date_to        = $date_range['date_to']   = strftime("%Y-%m-%d %H:%M:%S", time());
}



// Get service LEVEL

list($level, $levelgr) = html_service_level($conn, $host, $date_range);
list($score, $alt)     = global_score($conn,$host);

?>

<script type="text/javascript">$("#pbar").progressBar(30);$("#progressText").html('<?php echo _("Loading")?> <strong><?php echo _("SIEM Events")?></strong>...');</script>

<?php
	ob_flush();
	flush();
	usleep(500000);



// Get SIM Events
$limit = 6;

if( $host!='any' )
	list($sim_foundrows,$sim_highrisk,$sim_risknum,$sim_date) = Status::get_SIM_Resume($host, $host, $date_from, $date_to);
else
	list($sim_foundrows,$sim_highrisk,$sim_risknum,$sim_date) = Status::get_SIM_Resume('', '', $date_from, $date_to);

// Get SIM Events
if($host!='any')
	list($sim_ports,$sim_ipsrc,$sim_ipdst) = Status::get_SIM_Clouds($host, $host, $date_range);
else
	list($sim_ports,$sim_ipsrc,$sim_ipdst) = Status::get_SIM_Clouds('', '', $date_range);


if($host!='any')
	$sim_gplot = Status::get_SIM_Plot($host, $host, $date_from, $date_to);
else
	$sim_gplot = Status::get_SIM_Plot('', '', $date_from, $date_to);

//print_r($sim_gplot);

if($host!='any')
	list($unique_events,$plots,$sim_numevents) = Status::get_SIM_Unique($host, $host, $date_from, $date_to, $limit);
else
	list($unique_events,$plots,$sim_numevents) = Status::get_SIM_Unique('', '', $date_from, $date_to, $limit);


if ($event_cnt < 1) $event_cnt = 1;

?>
	<script type="text/javascript">$("#pbar").progressBar(40);$("#progressText").html('<?php echo _("Loading")?> <strong><?php echo _("Logger Events")?></strong>...');</script>
<?php
ob_flush();
flush();
usleep(500000);

// Get SEM Events


$date_from_week = date("Y-m-d 00:00:00", strtotime("$date_to -1 week"));

if($host!='any')
{
	list($sem_events_week,$sem_foundrows_week,$sem_date,$sem_wplot_y,$sem_wplot_x) = Status::get_SEM("", $date_from_week, $date_to, "none", 1234, $host);
	list($sem_foundrows, $sem_date) = Status::get_SEM_Resume($date_from, $date_to, $host);
}
else
{
	list($sem_events_week,$sem_foundrows_week,$sem_date,$sem_wplot_y,$sem_wplot_x) = Status::get_SEM("", $date_from_week, $date_to, "none", 1234);
	list($sem_foundrows, $sem_date) = Status::get_SEM_Resume($date_from, $date_to, '');
}

?>
	<script type="text/javascript">$("#pbar").progressBar(50);$("#progressText").html('<?php echo _("Loading")?> <strong><?php echo _("Anomalies")?></strong>...');</script>
	
<?php

ob_flush();
flush();
usleep(500000);

// Anomalies
if($host!='any')
	list($anm_events,$anm_foundrows,$anm_foundrows_week,$anm_date) = Status::get_anomalies($conn, $host, $date_from, $date_to);
else
	list($anm_events,$anm_foundrows,$anm_foundrows_week,$anm_date) = Status::get_anomalies($conn, '', $date_from, $date_to);

?>
	<script type="text/javascript">$("#pbar").progressBar(60);$("#progressText").html('<?php echo _("Loading")?> <strong><?php echo _("Vulnerabilities")?></strong>...');</script>
<?php
ob_flush();
flush();
usleep(500000);

// Vulnerabilities
list($vul_events,$vul_foundrows,$vul_highrisk,$vul_risknum,$vul_lastdate) = Status::get_vul_events($conn, $host, $date_from, $date_to);

// Availability (nagios)
list($ava_date,$ava_foundrows,$ava_highprio,$ava_prionum) = Status::get_availability_events($conn_snort,$host, $date_from, $date_to);

?>
	<script type="text/javascript">$("#pbar").progressBar(70);</script>
<?php
ob_flush();
flush();
usleep(500000);
?>

<table cellpadding='0' cellspacing='2' border='0' width="100%" height="100%">
	<tr>
		<td class="headerpr" height="20"><?php echo _("General Status")?></td>
	</tr>
	<tr>
		<td style="text-align:center">
			<table cellpadding='0' cellspacing='0' border='0' align="center">
			<tr>
				<td class="blackp" valign="middle" nowrap='nowrap' align="right" style="border:0px solid white;text-align:right"><strong><?php echo _("Service");?></strong><?php echo " "._("level:");?></td>
				<td class="<?php echo $levelgr ?>" width="90" height="30" nowrap='nowrap' align="left" id="service_level_gr" style="border:0px solid white"><a href="../control_panel/show_image.php?range=day&ip=level_admin&what=attack&start=N-1D&end=N&type=level&zoom=1" id="service_level" class="black" style="text-decoration:none"><?php echo $level ?> %</a></td>
				<td></td>
				<td class="nobborder">
					<table class="noborder" cellpadding='0' cellspacing='0' border='0'>
						<tr>
							<td style="padding-left:4px;text-align:right"><a href="../control_panel/global_score.php" class="blackp" style="text-decoration:none"><strong><?php echo _("Global");?></strong><?php echo " "._("score:");?></a></td>
							<td class="nobborder" style="text-align:left"><a href="../control_panel/global_score.php"><img id="semaphore" src="../pixmaps/statusbar/sem_<?php echo $score ?>_h.gif" border="0" alt="<?php echo $alt ?>" title="<?php echo $alt ?>"/></a></td>
						</tr>
					</table>
				</td>
				<!--
				<td>
					Time range: 
					<select name="timerange">
						<option value="day">Last Day
						<option value="week">Last Week
						<option value="2weeks">Last 2 Weeks
						<option value="month">Last Month
					</select>
				</td>
				-->
			</tr>
			</table>
		</td>
	</tr>
	<tr><td class="vsep" style="border:0px solid white"></td></tr>
	<tr>
		<td valign="top">
			<table cellspacing="2" cellpadding="4">
				<!-- TICKETS -->
				<tr bgcolor="#E1EFE0">
					<td class="bartitle" width="125"><a href="../incidents/index.php?status=Open" class="blackp"><?php echo _("Tickets")?> <strong><?php echo _("Opened")?></strong></a></td>
					<td width="50">
					
						<table class="noborder" cellpadding="0" cellspacing="0">
						<tr>
							<td class="theme_i"></td>
							<td class="theme_b" id="tickets_num"><a href="../incidents/index.php?status=Open" class="whitepn">-</a></td>
							<td class="theme_d"></td>
						</tr>
						</table>
						
					</td>
					
					<td class="blackp" style="font-size:8px;border:0px solid white" align="center" id="tickets_date" nowrap='nowrap'>-</td>
					
					<td class="blackp" nowrap='nowrap' style="text-align:right">
						<a href="javascript:;" id="statusbar_incident_max_priority_txt" class="blackp"><?php echo gettext("Max")?> <strong><?php echo _("priority")?></strong>:</a>
					</td>
					
					<td>
						<table style="width:auto; border: none; background:none;" cellpadding="0" cellspacing="0">
							<tr>
								<td style="text-align:left"><a href="" class="blackp" id="statusbar_incident_max_priority">-</a></td>
							</tr>
						</table>
					</td>
				</tr>
				<!-- ALARMS -->
				<tr>
					<?php
						if( $host!='any' )
							$url_temp= "?hide_closed=1&src_ip=".urlencode($host)."&dst_ip=".urlencode($host);
						else
							$url_temp="?hide_closed=1"; 
					?>
						
					<td class="bartitle" width="125"><a href="../control_panel/alarm_console.php<?php echo $url_temp?>" class="blackp"><?php echo _("Unresolved")?> <strong><?php echo _("Alarms")?></strong></a></td>
					<td width="50">
					
						<table class="noborder" cellpadding="0" cellspacing="0">
							<tr>
								<td class="theme_i"></td>
								<td class="theme_b"><a href="../control_panel/alarm_console.php<?php echo $url_temp?>" class="whitepn" id="statusbar_unresolved_alarms">0</a></td>
								<td class="theme_d"></td>
							</tr>
						</table>					
					
					</td>
					<td class="blackp" style="font-size:8px" align="center" id="alarms_date" nowrap='nowrap'>-</td>
					<td class="blackp" nowrap='nowrap' style="text-align:right"><a href="javascript:;" class="blackp" id="statusbar_alarm_max_risk_txt"><?php echo _("Highest")?> <strong><?php echo _("risk")?></strong>:</a></td>
					<td>
						<table style="width:auto" cellpadding='0' cellspacing='0'>
							<tr>
								<td style="text-align:left"><a href="" class="blackp" id="statusbar_alarm_max_risk">-</a></td>
							</tr>
						</table>
					</td>
				</tr>
				<!-- VULNS -->
				<tr bgcolor="#E1EFE0">
					<td class="bartitle"><?php echo _("Vulnerabilities")?></td>
					<td width="50">
					
						<table class="noborder" cellpadding="0" cellspacing="0">
							<tr>
								<td class="theme_i"></td>
								<td class="theme_b"><a href="../vulnmeter/index.php?value=<?php echo urlencode($host)?>&type=hn" class="whitepn"><?=Util::number_format_locale((int)$vul_foundrows,0)?></a></td>
								<td class="theme_d"></td>
							</tr>
						</table>					
					
					</td>
					<td class="blackp" style="font-size:8px" nowrap='nowrap'><?=$vul_lastdate?></td>
					<td class="blackp" nowrap='nowrap' style="text-align:right"><?php echo _("Highest")?> <strong><?php echo _("risk")?></strong>:</td>
					<td class="blackp" style="text-align:left">
						<table style="width:auto;background-color:transparent" cellpadding='0' cellspacing='0'>
							<tr>
								<td class="blackp"><a href="../vulnmeter/index.php?value=<?php echo urlencode($host)?>&type=hn" class="blackp" style="background-color:transparent"><?=Incident::get_priority_in_html($vul_highrisk)?></a></td>
								<td class="blackp" style="background-color:transparent"> (<strong><?=$vul_risknum?></strong> <i><?php echo _("events")?></i>)</td>
							</tr>
						</table>
					</td>
				</tr>
				<!-- SIEM EVENTS -->
				<tr>
					<td style="border:0px solid white" class="bartitle"><strong><?=_("SIEM")?></strong> <?php echo _("Events")?></td>
					<td style="border:0px solid white" width="50">
					
						<table class="noborder" cellpadding="0" cellspacing="0">
						<tr>
							<td class="theme_i"></td>
							<td class="theme_b"><a href="../forensics/base_qry_main.php?clear_allcriteria=1&num_result_rows=-1&submit=Query+DB&current_view=-1&sort_order=time_d&ip=<?php echo urlencode($host)?>&date_range=All" class="whitepn"><?=Util::number_format_locale((int)$sim_foundrows,0)?></a></td>
							<td class="theme_d"></td>
						</tr>
						</table>		
										
					</td>
					<td class="blackp" style="font-size:8px;border:0px" align="center" nowrap='nowrap'><?=$sim_date?></td>
					<td class="blackp" nowrap='nowrap' style="text-align:right"><?php echo _("Highest")?> <strong><?php echo _("risk")?></strong>:</td>
					<td class="blackp" style="text-align:left" nowrap='nowrap'>
						<table style="width:auto" cellpadding='0' cellspacing='0'>
							<tr>
								<td class="blackp"><a href="../forensics/base_qry_main.php?clear_allcriteria=1&num_result_rows=-1&submit=Query+DB&current_view=-1&sort_order=time_d&ip=<?php echo urlencode($host)?>&date_range=All"><?=Incident::get_priority_in_html($sim_highrisk)?></a></td>
								<td class="blackp"> (<strong><?=$sim_risknum?></strong> <i><?php echo _("events")?></i>)</td>
							</tr>
						</table>
					</td>
				</tr>
				<!-- LOGGER -->
				<tr bgcolor="#E1EFE0">
					<td style="border:0px" class="bartitle"><strong><?=_("Logger")?></strong> <?php echo _("Events")?></td>
					<td style="border:0px" width="50">
					
						<table class="noborder" cellpadding="0" cellspacing="0"><tr>
						<td class="theme_i"></td>
						<td class="theme_b"><a href="../sem/index.php?query=<?php echo urlencode("ip=$host")?>" class="whitepn"><?php echo Util::number_format_locale((int)$sem_foundrows,0)?></a></td>
						<td class="theme_d"></td>
						</tr></table>		
						
					</td>
					<td class="blackp" style="font-size:8px;border:0px" align="center" nowrap='nowrap'><?=$sem_date?></td>
					<td class="blackp" nowrap='nowrap' style="text-align:right"><?php echo _("Last")?> <strong><?php echo _("week")?></strong>:</td>
					<td class="blackp" style="text-align:left"><a href="../sem/index.php?query=<?php echo urlencode("ip=$host")?>" class="blackp"><strong><?=Util::number_format_locale((int)$sem_foundrows_week,0)?></strong> <i><?php echo _("events")?></i></a></td>
				</tr>
				<!-- ANOMALIES -->
				<tr>
					<td class="bartitle"><?php echo _("Anomalies")?></td>
					<td class="capsule" width="50">
					
						<table class="noborder" cellpadding="0" cellspacing="0">
							<tr>
								<td class="theme_i"></td>
								<td class="theme_b"><a href="../control_panel/anomalies.php" class="whitepn"><?=Util::number_format_locale((int)$anm_foundrows,0)?></a></td>
								<td class="theme_d"></td>
							</tr>
						</table>					
						
					</td>
					<td class="blackp" style="font-size:8px" align="center" nowrap='nowrap'><?=$anm_date?></td>
					<td class="blackp" nowrap='nowrap' style="text-align:right"><?php echo _("Last")?> <strong><?php echo _("week")?></strong>:</td>
					<td class="blackp" style="text-align:left"><a href="../control_panel/anomalies.php" class="blackp"><strong><?php echo Util::number_format_locale((int)$anm_foundrows_week,0)?></strong> <i><?php echo _("events")?></i></a></td>
				</tr>
				<!-- AVAILABILITY -->
				<tr bgcolor="#E1EFE0">
					<td class="bartitle"><?php echo _("Availability Events")?></td>
					<td width="50">
					
						<table class="noborder" cellpadding="0" cellspacing="0">
							<tr>
								<td class="theme_i"></td>
								<td class="theme_b"><a href="../forensics/base_qry_main.php?clear_allcriteria=1&num_result_rows=-1&submit=Query+DB&current_view=-1&sort_order=time_d&ip=<?php echo urlencode($host)?>&date_range=All" class="whitepn"><?php echo Util::number_format_locale((int)$ava_foundrows,0)?></a></td>
								<td class="theme_d"></td>
							</tr>
						</table>					
											
					</td>
					<td class="blackp" style="font-size:8px;border:0px" align="center" nowrap='nowrap'><?=$ava_date?></td>
					<td class="blackp" nowrap='nowrap' style="text-align:right"><?php echo _("High Prio")?>:</td>
					<td class="blackp" style="text-align:left">
						<a href="../forensics/base_qry_main.php?clear_allcriteria=1&num_result_rows=-1&submit=Query+DB&current_view=-1&sort_order=time_d&ip=<?php echo urlencode($host)?>&date_range=All" class="blackp">
							<?php
							$bgcolor = Incident::get_priority_bgcolor($ava_highprio);
							$fgcolor = Incident::get_priority_fgcolor($ava_highprio);
							$ava_highprio = ( empty($ava_highprio) ) ? " - " : $ava_highprio;
							?>
							<table class="transparent" width='20' bgcolor="<?=$bgcolor?>">
								<tr>
									<td style="text-align:center;color:<?=$fgcolor?>" bgcolor="<?=$bgcolor?>" width='20' class='blackp'>
										<b><?php echo $ava_highprio?></b>
									</td>
									<td style="text-align:left;" class='blackp'><span style='margin-left:2px;'>(<strong><?php echo $ava_prionum?></strong> <i><?php echo _("events")?></i>)</span></td>
								</tr>
							</table>
						</a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

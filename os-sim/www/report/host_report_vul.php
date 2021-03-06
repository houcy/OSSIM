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

if( $host!='any' ) 
	$temp_url= "../vulnmeter/index.php?value=".urlencode($host)."&type=hn";
else
	$temp_url="../vulnmeter/index.php?type=hn";
?>

<table class="bordered" height="100%">
	<tr>
		<td class="headerpr" height="20"><a style="color:black" href="<?php echo $temp_url?>"><?php echo _("Latest Vulnerabilities")?></a></td>
	</tr>
	<?php
	if (count($vul_events) < 1) 
	{ 
		$host_txt = ( $host == 'any') ? _("No Vulnerabilities found in the System") : _("No Vulnerabilities found for")."<i>".$host."</i>"; 
		?>
		<tr><td><?php echo $host_txt?></td></tr>
		<?php 
	} 
	else 
	{ 
	?>
	<tr>
		<td valign="top">
			<table>
				<tr>
					<th><?php echo _("Name")?></th>
					<th><?php echo _("Risk")?></th>
					<?php if ($network || $host=='any') { ?><th>IP</th><? } ?>
				</tr>
				<?php 
				
				$i = 0; 
				foreach ($vul_events as $vul_event) 
				{ 
					if ($i > 4) continue; 
					$color = (($i+1)%2==0) ? "#E1EFE0" : "#FFFFFF"; 
					?>
				<tr>
					<td bgcolor="<?=$color?>" style="text-align:left;color: #17457c;font-size:10px"><b><?php echo $vul_event['name']?></b></td>
					<td bgcolor="<?=$color?>">
					<?php
					switch($vul_event['risk']){
						case 1:
							$bgcolor = 'FF0000';
							$fgcolor = 'fff';
							$name_risk='Serious';
							break;
						case 2:
							$bgcolor = 'FF0000';
							$fgcolor = 'fff';
							$name_risk='High';
							break;
						case 3:
							$bgcolor = 'FFA500';
							$fgcolor = 'fff';
							$name_risk='Medium';
							break;
						case 6:
							$bgcolor = 'FFD700';
							$fgcolor = '000';
							$name_risk='Low';
							break;
						case 7:
							$bgcolor = 'F0E68C';
							$fgcolor = '000';
							$name_risk='Info';
							break;
						default:
							$bgcolor = 'FF0000';
							$fgcolor = 'fff';
							$name_risk=$vul_event['risk'];
							break;
					}
					?>
						<table align="center" bgcolor="#<?php echo $bgcolor; ?>" fgcolor="#<?php echo $fgcolor; ?>" class="transparent" width="20">
							<tr>
								<td bgcolor="#<?php echo $bgcolor; ?>" fgcolor="#<?php echo $fgcolor; ?>" style="border-width: 0px;" width="20">
								&nbsp;<font color="#<?php echo $fgcolor; ?>"><?php echo _($name_risk);?></font>&nbsp;
								</td>
							</tr>
						</table>
					</td>
					<?php if ( $network || $host=='any') { ?><td bgcolor="<?=$color?>"><a href="host_report.php?host=<?=$vul_event['ip']?>" class="HostReportMenu" id="<?=$vul_event['ip']?>;<?=$vul_event['ip']?>"><?=$vul_event['ip']?></a></td><? } ?>
				</tr>
				<?php
					$i++; 
				} 
			?>
			</table>
		</td>
	</tr>
	<tr><td style="text-align:right;padding-right:20px"><a style="color:black" href="<?php echo $temp_url?>"><strong><?=gettext("More")?> >></strong></a></td></tr>
	<?php } ?>
	<tr><td></td></tr>
</table>
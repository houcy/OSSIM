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
*   but WITHOUT ANY WARRANTY; without even the implied warranty of$
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
* - 
* - 
* - 
* - 
* - 
* - 
* - 
* Classes list:
* - 
*/
require_once ('classes/Incident.inc');
require_once 'classes/Log_action.inc';
require_once ("ossim_db.inc");

class Form_builder {

/*Attributes*/

private $params;
private $scripts;
private $def_funcs;
private static $types = array("Asset", "Check Yes/No", "Check True/False", "Checkbox", "Date", "Date Range", "Map", "Radio button", "Select box", "Slider", "Textarea", "Textbox");

/*Methods*/
	 
	public function __construct(){
		$this->scripts = array();
		$this->def_funcs = array();
	;}
	
	public function set_attributes($params) { $this->params = $params;}
	
	public function get_scripts() { return implode("\n", $this->scripts);}
	public function add_script($script) { $this->scripts[] = $script;}
	
	public function get_def_funcs() { return implode("\n", $this->def_funcs);}
	public function add_def_func($name, $def_func) { $this->def_funcs[$name] = $def_func;}
	
	private function exists_def_func($func_name){
	
		if (in_array ($func_name, $this->def_funcs) )
			return true;
		else
			return false;
	
	}	
	
		
	public function draw_element($type){ 
		switch ($type){
			case "Asset":
				$element= $this->draw_asset();
			break;
			
			case "Check Yes/No":
				$element= $this->draw_radioexc("y/n");
				
			break;
			
			case "Check True/False":
				$element= $this->draw_radioexc("t/f");
			break;
			
			case "Checkbox":
				$element= $this->draw_checkbox();
			break;
			
			case "Date":
				$element= $this->draw_date();
			break;
			
			case "Date Range":
				$element= $this->draw_daterange();
			break;
			
			case "Map";
				$element= $this->draw_map();
			break;
			
			case "Radio button";
				$element= $this->draw_radiob();	
			break;
			
			case "Select box":
				$element= $this->draw_selectbox();
			break;
			
			case "Slider":
				$element= $this->draw_slider();
			break;
						
			case "Textarea":
				$element= $this->draw_textarea();
			break;
			
			case "Textbox":
				$element= $this->draw_textbox();
			break;
			
		}
		
		return $element;
		
		
	}
	
public function draw_asset(){
		
	foreach ( $this->params as $k => $v)
		$attributes .= "$k=\"$v\" ";
	
	if($assetst == '' || $assetst=='ALL_ASSETS')
		$txt_asset =  "All Assets";  
	else
		$txt_asset = "</span>";
	
	$element .= "<div class='ct_assets field_fix'><b>"._('Selected:')."</b>&nbsp;<span id='dassets_".$this->params["id"]."'>";
	$element .= "All Assets</span>";
	$element .= "<div id='tree_".$this->params["id"]."' style='text-align:left;padding:5px 0px 0px 0px;'></div>";
	$element .= "<input type='hidden' $attributes value='All Assets'/>";
	$element .= "</div>";
	
	
	$script = "
		$('#tree_".$this->params["id"]."').dynatree({
		initAjax: { url: '../report/entities_tree_users.php' },
		clickFolderMode: 2,
		onActivate: function(dtnode) {
			if(dtnode.data.url!='') {
				$('#".$this->params["id"]."').val(dtnode.data.url);
				
				if(!dtnode.data.url.match(/ENTITY/)) {
					$('#dassets_".$this->params["id"]."').html(dtnode.data.url.replace('ALL_ASSETS', 'All Assets'));
				}
				else {
					$('#dassets_".$this->params["id"]."').html('ENTITY:'+dtnode.data.name);
				}
			}
		},
		onDeactivate: function(dtnode) {},
		onLazyRead: function(dtnode){
			dtnode.appendAjax({
				url: '../report/entities_tree_users.php',
				data: {key: dtnode.data.key, page: dtnode.data.page}
			});
		}
	});\n\n";
	
	$this->add_script($script);
	 
	return $element;
}

public function draw_radioexc($subtype){
	
	if ($subtype == "y/n")
		$values = array( _("Yes"), _("No") );
	else
		$values = array( _("True"), _("False") );
		
	$aux = "<div style='float: left; width:50px;'><input type='radio' ";
	
	foreach ( $this->params as $k => $v)
	{
		if ($k != "id")
			$aux .= "$k=\"$v\" ";
	}
	
	$element  = $aux." id='".$this->params["id"]."_1' value='Yes'/><span>".$values[0]."</span></div>";
	$element .= $aux." id='".$this->params["id"]."_2' value='No'/><span>".$values[1]."</span></div>";
	
	return $element;
}


public function draw_checkbox(){
	
	foreach ( $this->params as $k => $v)
	{
		if ($k != "id" && $k != "name" && $k != "values")
			$attributes .= "$k=\"$v\" ";
	}
	
	foreach ( $this->params['values'] as $k => $v )
	{
		$v = trim($v);
		$value = explode(":", $v);
		$num_value = count($value);
	
		if ( !empty ($v) )
		{
			if ($num_value == 1)
				$values []= "value='".htmlentities($value[0])."'/><span style='padding-left: 5px;'>".htmlentities($value[0])."</span> ";
			else if ($num_value == 2)
				$values []= "value='".htmlentities($value[0])."'/><span style='padding-left: 5px;'>".htmlentities($value[1])."</span> ";
			else 
			{
				if ($num_value > 2)
					$values []= "value='".htmlentities($value[0])."' checked='true'/><span style='padding-left: 5px;'>".htmlentities($value[1])."</span> ";
			}
			
			
		}
	}	
	
	
		
	for ($i=0; $i<count($values); $i++)
		$element .= "<div><input type='checkbox' name='".$this->params['name']."' id='".$this->params['id'][$i]."' ".$attributes." ".$values[$i]."</div>\n";
		
	return $element;
}

public function draw_date(){

	foreach ( $this->params as $k => $v)
		$attributes .= "$k=\"$v\" ";

	$element = "<div id='cont_".$this->params['id']."'>
			<input type='text' ".$attributes." value='Click to select a range' readonly='readonly' style='width:150px;'/>
			<div class='ct_widget' style='display:inline; padding-left: 3px;'>
				<a style='cursor:pointer'><img src='../pixmaps/calendar.png' class='imgcalendar' border='0' align='absmiddle' style='padding-bottom:1px'/></a>
				<div class='ct_widgetCalendar' style='width:200px;'></div>
			</div>
		</div>";
	
	if ( $this->exists_def_func('toogle_calendar') == false)
	{
		$def_func = "	
			function toogle_calendar(id_cont)
			{
						
				if ( $(id_cont).attr('class').length >= 1 ) 
				{
					$('.ct_widgetCalendar').stop().animate({height: 0}, 500);
					$('.active').removeClass('active');
				}
				else
				{
					$('.ct_widgetCalendar').stop().animate({height: 0}, 500);
					$('.active').removeClass('active');
					$(id_cont + ' .ct_widgetCalendar').stop().animate({height: $(id_cont+ ' .ct_widgetCalendar div.datepicker').get(0).offsetHeight}, 500);
					$(id_cont).addClass('active');
				}
				return false;
			}\n\n";
			
		$this->add_def_func("toogle_calendar", $def_func);
	}
	
	if ( $this->exists_def_func('create_date') == false)
	{
		$def_func = "
			function create_date(id)
			{
				var id_cont= '#cont_'+id;
				var id = '#'+id;
				
				var date = new Date() 
				
				var day   = date.getDate();
				var month = date.getMonth();
				var year  = date.getFullYear();
						
				date = new Date(year, month, day);
				
				$(id_cont + ' .ct_widgetCalendar').DatePicker({
					flat: true,
					format: 'Y-m-d',
					date: date,
					starts: 1,
					onChange: function(formated) {
						var f = formated.split(/-/);
						$(id).attr('value', f[0]+'-'+f[1]+'-'+f[2]);	
						$(id_cont).removeClass('active');
						$('.ct_widgetCalendar').stop().animate({height: 0}, 500);		
					}
				});
				
							
				$(id_cont + ' .ct_widget>a, '+ id_cont + ' input').bind('click', function(){ toogle_calendar(id_cont);});
				$(id_cont + ' div.datepicker').css('position', 'absolute');\n\n
			}
		";
		$this->add_def_func("create_date", $def_func);
	}
    
	$script = "create_date('".$this->params['id']."');";
	$this->add_script($script);
	
	return $element;	

}

public function draw_daterange(){

	foreach ( $this->params as $k => $v)
		$attributes .= "$k=\"$v\" ";;

	$element = "<div id='cont_".$this->params['id']."'>
			<input type='text' ".$attributes." value='Click to select a range' readonly='readonly' style='width:150px;'/>
			<div class='ct_widget' style='display:inline; padding-left: 3px;'>
				<a style='cursor:pointer'><img src='../pixmaps/calendar.png' class='imgcalendar' border='0' align='top' style='padding-bottom:1px'/></a>
				<div class='ct_widgetCalendar' style='width:395px;'></div>
			</div>
		</div>";
	
	if ( $this->exists_def_func('toogle_calendar') == false)
	{
		$def_func = "	
			function toogle_calendar(id_cont)
			{
						
				if ( $(id_cont).attr('class').length >= 1 ) 
				{
					$('.ct_widgetCalendar').stop().animate({height: 0}, 500);
					$('.active').removeClass('active');
				}
				else
				{
					$('.ct_widgetCalendar').stop().animate({height: 0}, 500);
					$('.active').removeClass('active');
					$(id_cont + ' .ct_widgetCalendar').stop().animate({height: $(id_cont+ ' .ct_widgetCalendar div.datepicker').get(0).offsetHeight}, 500);
					$(id_cont).addClass('active');
				}
				return false;
			}\n\n";
			
		$this->add_def_func("toogle_calendar", $def_func);
	}
	
	
	if ( $this->exists_def_func('create_daterange') == false)
	{
		$def_func = "
			function create_daterange(id)
			{
				var id_cont= '#cont_'+id;
				var id = '#'+id;
				
				var date   = new Date() 
				var day    = date.getDate();
				var month  = date.getMonth();
				var month2 = (month == 0) ? 11 : date.getMonth()-1;
				var year   = date.getFullYear();				

								
				var datefrom = new Date(year, month, day);
				var dateto = new Date(year, month2, day);
				
				$(id_cont + ' .ct_widgetCalendar').DatePicker({
					flat: true,
					format: 'Y-m-d',
					date: [new Date(datefrom), new Date(dateto)],
					calendars: 2,
					mode: 'range',
					starts: 1,
					onChange: function(formated) {
						if (formated[0]!=formated[1]) {
							
							var f1 = formated[0].split(/-/);
							var f2 = formated[1].split(/-/);
							
							var date1=new Date();
							date1.setFullYear(f1[0],f1[1],f1[2]);
							
							var date2 = new Date();
							date2.setFullYear(f2[0],f2[1],f2[2]);

							if (date1>date2)
								$(id).attr('value', f2[0]+'-'+f2[1]+'-'+f2[2]+', '+f1[0]+'-'+f1[1]+'-'+f1[2]);						
							else
								$(id).attr('value', f1[0]+'-'+f1[1]+'-'+f1[2]+', '+ f2[0]+'-'+f2[1]+'-'+f2[2]);	
							
							$(id_cont).removeClass('active');
							$('.ct_widgetCalendar').stop().animate({height: 0}, 500);		
							
						}
					}
				});
							
				$(id_cont + ' .ct_widget>a, '+ id_cont + ' input').bind('click', function(){ toogle_calendar(id_cont);});
				$(id_cont + ' div.datepicker').css('position', 'absolute');
				
			}\n\n";
				
		$this->add_def_func('create_daterange', $def_func);
	}
	
	$script = "create_daterange('".$this->params['id']."');";
	$this->add_script($script);
	
	return $element;	
}	

public function draw_map(){
	
	foreach ( $this->params as $k => $v)
		$attributes .= "$k=\"$v\" ";
	
	$element  = "<div style='margin: 10px 0px 10px 0px;'><span style='padding-right: 13px; font-weight: bold;'>Location:</span><input type='text' ".$attributes."/></div>";
	$element .= "<div id='map_".$this->params["id"]."' class='field_fix' style='height:300px; display:none;'></div>";
	
	$map_key = ($this->params["values"][0] != '' ) ? $map_key : "ABQIAAAAbnvDoAoYOSW2iqoXiGTpYBTIx7cuHpcaq3fYV4NM0BaZl8OxDxS9pQpgJkMv0RxjVl6cDGhDNERjaQ";
	
	if ( $this->exists_def_func('') == false)
	{
		$def_func = "	
						
		var map;
						
		$('#".$this->params["id"]."').geo_autocomplete(new google.maps.Geocoder, {
			mapkey: '".$map_key."', 
			selectFirst: false,
			minChars: 3,
			cacheLength: 50,
			width: 300,
			scroll: true,
			scrollHeight: 330
		}).result(function(_event, _data) {
			if (_data) 
			{
				$('#map_".$this->params["id"]."').css('display', '');
				
				var myOptions = {
				  zoom: 8,
				  mapTypeId: google.maps.MapTypeId.ROADMAP
				};
		
				map = new google.maps.Map(document.getElementById('map_".$this->params["id"]."'), myOptions);
				
				map.fitBounds(_data.geometry.viewport);
				map.setCenter(_data.geometry.location);
				var marker = new google.maps.Marker({
					map: map,
					position: _data.geometry.location
				});
									
			}
		});
					
		";
		
		$this->add_def_func("", $def_func);
	}
		
	return $element;
}	

public function draw_radiob(){
	
	
	foreach ( $this->params as $k => $v)
	{
		if ($k != "id" && $k != "name" && $k != "values")
			$attributes .= "$k=\"$v\" ";
	}
	
	
	$num_radio = count($this->params['values']);
			
	foreach ( $this->params['values'] as $k => $v )
	{
		$v = trim($v);
		$value = explode(":", $v);
		$num_value = count($value);
		
		if ( !empty ($v) )
		{
			if ($num_value == 1)
				$values []= "value='".htmlentities($value[0])."'/><span style='padding-left: 5px;'>".htmlentities($value[0])."</span> ";
			else if ($num_value == 2)
				$values []= "value='".htmlentities($value[0])."'/><span style='padding-left: 5px;'>".htmlentities($value[1])."</span> ";
			else {
				if ($num_value > 2)
					$values []= "value='".htmlentities($value[0])."' checked='true'/><span style='padding-left: 5px;'>".htmlentities($value[1])."</span> ";
			}
		}
	}	
		
	for ($i=0; $i<count($values); $i++)
		$element .= "<div><input type='radio' name='".$this->params['name']."' id='".$this->params['id'][$i]."' ".$attributes." ".$values[$i]."</div>\n";
		
	return $element;
}	

public function draw_selectbox(){
		
	$attributes = '';
	
	foreach ( $this->params['values'] as $k => $v )
	{
		$v = trim($v);
		$value = explode(":", $v);
		$num_value = count($v);
					
		if ( !empty ($v) )
		{
			if ($num_value == 1)
				$options.= "<option value='".htmlentities($value[0])."'>".htmlentities($value[0])."</option>\n";
			else if ($num_value == 2)
				$options.= "<option value='".htmlentities($value[0])."'>".htmlentities($value[1])."</option>\n";
			else 
			{
				if ($num_value > 2)
					$options.= "<option value='".htmlentities($value[0])."' selected='selected'>".htmlentities($value[1])."</option>\n";
			}
		}	
	}
	
	unset($this->params['values']);
	foreach ( $this->params as $k => $v)
			$attributes .="$k=\"$v\" ";
	
	$element = "<select ".$attributes.">".$options."</select>";
	
	return $element;
}

public function draw_slider(){

	foreach ( $this->params as $k => $v)
		$attributes .= "$k=\"$v\" ";
		
	$min_value = ( is_numeric($this->params["values"][0]) ) ? $this->params["values"][0] : "0";
	$max_value = ( is_numeric($this->params["values"][1]) ) ? $this->params["values"][1] : "10";
	$step      = ( is_numeric($this->params["values"][2]) ) ? $this->params["values"][2] : "1";
	
	$element="  <div style='float:left; padding-top: 3px; width:40px; heigth: 25px;'>
					<span id='dial_".$this->params['name']."' style='font-weight: bold; padding: 0px 12px'>".$min_value."</span>
				</div>
				<div class='ct_slider'>
				<div id='cont_".$this->params['name']."' style='width:95%; height:3px;'></div>
				</div>
				<input type='hidden' ".$attributes." value='".$min_value."'/>";
	

	$script = "
		$('#cont_".$this->params['name']."').slider({
			animate: true,
			range: 'min',
			value: $min_value,
			min: $min_value,
			max: $max_value,
			step: $step,
			slide: function(event, ui) {
				\$('#dial_".$this->params['name']."').html(ui.value);
				\$('#".$this->params['name']."').val(ui.value);
			}
		});\n\n";
	
	$this->add_script($script);
	
	return $element;
}

public function draw_textarea(){

	$element = "<textarea ";
		
	foreach ( $this->params as $k => $v)
		$element .= "$k=\"$v\" ";
	
	$element .= "></textarea>";
	
	return $element;

}

public function draw_textbox(){

	$element = "<input type='text'";
	
	foreach ( $this->params as $k => $v)
		$element .= "$k=\"$v\" ";
	
	$element .= "/>";
	
	return $element;

}
			
	
	
	

}

?>
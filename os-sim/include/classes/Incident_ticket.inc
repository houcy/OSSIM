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
* - Incident_ticket()
* - get_id()
* - get_incident_id()
* - get_status()
* - get_priority()
* - get_user()
* - get_description()
* - get_action()
* - get_in_charge()
* - get_transferred()
* - get_attachment()
* - get_date()
* - get_list()
* - insert()
* - update_priority()
* - delete()
* - change_status()
* - mail_subscription()
* - get_email_template_values()
* - get_email_template()
* - build_email_template()
* - save_email_template()
* Classes list:
* - Incident_ticket
*/
require_once 'classes/Security.inc';
require_once 'classes/Session.inc';
require_once 'classes/Incident.inc';
require_once 'classes/Log_action.inc';
require_once 'ossim_db.inc';

class Incident_ticket {
    var $id;
    var $incident_id;
    var $date;
    var $status;
    var $priority;
    var $user;
    var $description;
    var $action;
    var $in_charge;
    var $transferred;
    function Incident_ticket($id, $incident_id, $date, $status, $priority, $user, $description, $action, $in_charge, $transferred) {
        $this->id = $id;
        $this->incident_id = $incident_id;
        $this->date        = $date;
        $this->status      = $status;
        $this->priority    = $priority;
        $this->user        = $user;
        $this->description = $description;
        $this->action      = $action;
        $this->in_charge   = $in_charge;
        $this->transferred = $transferred;
    }
    function get_id() {
        return $this->id;
    }
    function get_incident_id() {
        return $this->incident_id;
    }
    function get_status() {
        return $this->status;
    }
    function get_priority() {
        return $this->priority;
    }
    function get_user() {
        return $this->user;
    }
    function get_description() {
        return $this->description;
    }
    function get_action() {
        return $this->action;
    }
    function get_in_charge() {
        return $this->in_charge;
    }
    function get_transferred() {
        return $this->transferred;
    }
    function get_attachment($conn) {
        require_once ('classes/Incident_file.inc');
        $attachment = Incident_file::get_list($conn, " WHERE incident_id = " . $this->incident_id . " AND incident_ticket = " . $this->id);
        if ($attachment) return $attachment[0];
        else return false;
    }
    function get_date() {
        return Util::timestamp2date($this->date);
    }
    function get_list($conn, $args) {
        require_once 'classes/Util.inc';
    
        $tz = Util::get_timezone();
        
        $list = array();
        $query = OssimQuery("SELECT * FROM incident_ticket $args");
        if (!$rs = & $conn->Execute($query)) {
            print $conn->ErrorMsg();
        } else {
            while (!$rs->EOF) {
                if($tz!=0) {
                    $rs->fields["date"] = gmdate("Y-m-d H:i:s",Util::get_utc_unixtime($conn,$rs->fields["date"])+3600*$tz);
                }
                $list[] = new Incident_ticket($rs->fields["id"], $rs->fields["incident_id"], $rs->fields["date"], $rs->fields["status"], $rs->fields["priority"], $rs->fields["users"], $rs->fields["description"], $rs->fields["action"], $rs->fields["in_charge"], $rs->fields["transferred"]);
                $rs->MoveNext();
            }
        }
        return $list;
    }
    /*
    * @param $tags Array with the tag IDs related to this incident
    */
    function insert($conn, $incident_id, $status, $priority, $user, $description, $action, $transferred, $tags, $attachment) {
    
        require_once 'classes/Util.inc';
        
        $tz = Util::get_timezone();
        
        $vals = array(
            'incident_id' => array(
                OSS_DIGIT,
                'error:' . _("Invalid Incident ID")
            ) ,
            'status' => array(
                'func:Incident::chk_status',
                'error:' . _("Status possible values are 'Open' or 'Close'")
            ) ,
            'priority' => array(
                'regex:^([0-9]|10)$', // 'regex:^([1-9]|10)$'
                'error:' . _("Priority should be a value between 1 and 10")
            ) ,
            'user' => array(
                OSS_USER,
                'error:' . _("Invalid user login format")
            ) ,
            'description' => array(
				OSS_TEXT, 
				OSS_PUNC_EXT,
				"\t",
				'>',
				'<',
                'error:' . _("Description required")
            ) ,
            'action' => array(
                OSS_NULLABLE, 
				OSS_TEXT, 
				OSS_PUNC_EXT,
				"\t",
				'>',
				'<',
            ) ,
            'transferred' => array(
                OSS_NULLABLE,
                OSS_USER,
                OSS_ALPHA,
                OSS_SPACE,
                OSS_PUNC_EXT,
                'error:' . _("Invalid transferred login format")
            ) ,
            'tag' => array(
                OSS_DIGIT,
                'error:' . _("Invalid TAG ID")
            ) ,
        );
		
        $description = strip_tags($description,'<div><span><ul><li><ol><b><i><u><strike><p><h1><h2><h3><h4><h5><h6><font><br><blockquote>');
        $action      = strip_tags($action,'<div><span><ul><li><ol><b><i><u><strike><p><h1><h2><h3><h4><h5><h6><font><br><blockquote>');
				
		ossim_valid($incident_id, $vals['incident_id']);
        ossim_valid($status, $vals['status']);
        ossim_valid($priority, $vals['priority']);
        ossim_valid($user, $vals['user']);
        ossim_valid($description, $vals['description']);
        ossim_valid($action, $vals['action']);
        ossim_valid($transferred, $vals['transferred']);
        
		foreach($tags as $tag) {
            ossim_valid($tag, $vals['tag']);
        }
        
		if (ossim_error()) 
			return ossim_error();
        
		$ticket_id = $conn->GenID('incident_ticket_seq');
        
        if($tz==0) {
            $date = date('Y-m-d H:i:s');
        }
        else {
            $date = gmdate("Y-m-d H:i:s");
        }
        // Get previous in charge
        $sql = 'SELECT in_charge FROM incident WHERE id=?';
        $in_charge = $conn->GetOne($sql, array($incident_id));
        if ($in_charge === false) die($conn->ErrorMsg());
        //
        // Insert new ticket
        //
        $conn->StartTrans();
        $sql = 'INSERT INTO incident_ticket ' . '(id, incident_id, status, priority, users, description, ' . 'action, transferred, in_charge, date) VALUES' . '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		// transferred is the new in_charge
        $params = array(
            $ticket_id,
            $incident_id,
            $status,
            $priority,
            $user,
            $description,
            $action,
            $transferred,
            $in_charge,
            $date
        );

        
        $conn->Execute($sql, $params);
        //
        // Reflect changes in the Incident
        //
        if ($transferred) {
            $in_charge = $transferred;
        }
        $sql = 'UPDATE incident SET priority=?, last_update=?, ' . 'in_charge=?, status=?' . 'WHERE id=?';
        $params = array(
            $priority,
            $date,
            $in_charge,
            $status,
            $incident_id
        );
        $conn->Execute($sql, $params);
        //
        // Update the TAGS related to this Incident
        //
        $sql = 'DELETE FROM incident_tag WHERE incident_id=?';
        $conn->Execute($sql, array(
            $incident_id
        ));
        $sql = 'INSERT INTO incident_tag (tag_id, incident_id) VALUES (?, ?)';
        foreach($tags as $tag) {
            $conn->Execute($sql, array(
                $tag,
                $incident_id
            ));
        }
        //
        // Suscribe the new person in charge when transferred
        //
        if ($transferred) {
            Incident::insert_subscription($conn, $incident_id, $transferred);
        }
        //
        // Finish transaction
        //
        $conn->CompleteTrans();
        
		if ($conn->HasFailedTrans()) {
            die($conn->ErrorMsg());
        }
        
		if ($attachment) {
            require_once ('classes/Incident_file.inc');
            Incident_file::insert($conn, $incident_id, $ticket_id, $attachment["name"], $attachment["type"], $attachment["content"]);
        }
        //
        // Mail ticket to subscribers
        //
        Incident_ticket::mail_subscription($conn, $ticket_id);
        
        $infolog = array($incident_id);
        Log_action::log(50, $infolog);
        
        return true;
    }
    function update_priority($conn, $id, $incident_id, $priority) {
        /* update priority */
        $id = intval($id);
        $incident_id = intval($incident_id);
        $priority = intval($priority);
        $sql = "UPDATE incident_ticket SET priority = ?, date = date WHERE id = ?";
        $params = array(
            $priority,
            $id
        );
        if ($conn->Execute($sql, $params) === false) {
            print 'error inserting: ' . $conn->ErrorMsg() . '<BR>';
            exit;
        }
        /* get last id */
        $sql = "SELECT id, incident_id FROM incident_ticket WHERE incident_id = ? ORDER BY id DESC LIMIT 1";
        $params = array(
            $incident_id
        );
        if (!$rs = & $conn->Execute($sql, $params)) {
            print $conn->ErrorMsg();
            exit;
        }
        $max_id = $rs->fields["id"];
        $incident_id = $rs->fields["incident_id"];
        /* if it's the last id, update global priority */
        if ($id == $max_id) {
            $sql = "UPDATE incident SET priority = ? WHERE id = ?";
            $params = array(
                $priority,
                $incident_id
            );
            if ($conn->Execute($sql, $params) === false) {
                print 'error inserting: ' . $conn->ErrorMsg() . '<BR>';
                exit;
            }
        }
    }
    function delete($conn, $ticket_id) {
        if (!ossim_valid($ticket_id, OSS_DIGIT)) {
            die("invalid ID");
        }
        $conn->StartTrans();
        $conn->Execute("DELETE FROM incident_ticket WHERE id=$ticket_id");
        $conn->Execute("DELETE FROM incident_file   WHERE incident_ticket=$ticket_id");
        $conn->CompleteTrans();
        if ($conn->HasFailedTrans()) {
            die('error deleting: ' . $conn->ErrorMsg());
        }
        $infolog = array($ticket_id);
        Log_action::log(51, $infolog);
    }
    function change_status($conn, $id) {
        $id = intval($id);
        $sql = "SELECT status FROM incident_ticket WHERE id = ?";
        $params = array(
            $id
        );
        if (!$rs = & $conn->Execute($sql, $params)) {
            print $conn->ErrorMsg();
            exit;
        }
        $status = $rs->fields["status"];
        if ($status == 'Open') {
            $sql2 = "UPDATE incident_ticket SET status = 'Closed',
                date = date WHERE id = ?";
        } elseif ($status == 'Closed') {
            $sql2 = "UPDATE incident_ticket SET status = 'False',
                date = date WHERE id = ?";
        } elseif ($status = 'False') {
            $sql2 = "UPDATE incident_ticket SET status = 'Open',
                date = date WHERE id = ?";
        }
        $params = array(
            $id
        );
        if (isset($sql2)) {
            if ($conn->Execute($sql2, $params) === false) {
                print 'error inserting: ' . $conn->ErrorMsg() . '<BR>';
                exit;
            }
        }
    }
    function mail_subscription($conn, $ticket_id) {
        $values      = Incident_ticket::get_email_template_values($conn, $ticket_id);
        $from_name   = $values['TICKET_AUTHOR_NAME'];
        $from_mail   = $values['TICKET_AUTHOR_EMAIL'];
        $incident_id = $values['ID'];
        
		if (!$from_mail) 
			$from_mail = "mail_not_set@invalid";
        
		$from        = "From: \"$from_name\" <$from_mail>";
        $force_from  = "-f{$from_mail}";
        $subject_tpl = Incident_ticket::get_email_template('subject');
        $body_tpl    = Incident_ticket::get_email_template('body');
        $subject     = Incident_ticket::build_email_template($subject_tpl, $values);
        $body        = Incident_ticket::build_email_template($body_tpl, $values);
        $users       = Incident::get_subscribed_users($conn, $incident_id, $values['IN_CHARGE_LOGIN']);
        
		
				
		
		
		$error = '';
        foreach($users as $user) {
            if (!$user['email']) {
                continue;
            }
            if (!mail($user['email'], $subject, $body, $from, $force_from)) {
                $error.= _("Could not email ticket to the following address") . ": " . $user['email'] . "<br/>";
            }
        }
        return $error ? ossim_set_error($error) : true;
    }
	
    function get_email_template_values($conn, $ticket_id) {
        //
        // Ticket data
        //
        list($ticket) = Incident_ticket::get_list($conn, "WHERE incident_ticket.id = $ticket_id");
        $incident_id = $ticket->get_incident_id();
        $values['ID'] = $incident_id;
        $values['TICKET_DESCRIPTION'] = $ticket->get_description();
        $values['TICKET_ACTION'] = $ticket->get_action();
        //
        // Ticket author details
        //
        $login = $ticket->get_user();
        list($author) = Session::get_list($conn, "WHERE login = '$login'");
        $values['TICKET_AUTHOR_NAME'] = $author->get_name();
        $values['TICKET_AUTHOR_EMAIL'] = $author->get_email();
        $values['TICKET_AUTHOR_DPTO'] = $author->get_department();
        $values['TICKET_AUTHOR_COMPANY'] = $author->get_company();
        //
        // Ticket history
        //
        $history = '';
        $list = Incident_ticket::get_list($conn, "WHERE incident_ticket.incident_id = $incident_id ORDER BY incident_ticket.date");
        foreach($list as $ticket) {
            $login = $ticket->get_user();
            list($author) = Session::get_list($conn, "WHERE login = '$login'");
            if (!$author) {
            	$author_name = _("Unknown");
            	$author_mail = _("Unknown");
            } else {
            	$author_name = $author->get_email();
            	$author_mail = $author->get_email();
            }
            $history.= $ticket->get_date() . ' ' . $author_name . ' <' . $author_mail . ">\n" . "------------------------------------------------------\n" . _("Description") . ":\n" . $ticket->get_description() . "\n\n" . _("Action") . ":\n" . $ticket->get_action() . "\n\n";
        }
        $values['TICKET_HISTORY'] = $history;
        $history = '';
        $list = Incident_ticket::get_list($conn, "WHERE incident_ticket.incident_id = $incident_id ORDER BY incident_ticket.date DESC");
        foreach($list as $ticket) {
            $login = $ticket->get_user();
            list($author) = Session::get_list($conn, "WHERE login = '$login'");
        	if (!$author) {
            	$author_name = _("Unknown");
            	$author_mail = _("Unknown");
            } else {
            	$author_name = $author->get_email();
            	$author_mail = $author->get_email();
            }
            $history.= $ticket->get_date() . ' ' . $author_name. ' <' . $author_mail . ">\n" . "------------------------------------------------------\n" . _("Description") . ":\n" . $ticket->get_description() . "\n\n" . _("Action") . ":\n" . $ticket->get_action() . "\n\n";
        }
        $values['TICKET_INVERSE_HISTORY'] = $history;
        //
        // Incident data
        //
        list($incident) = Incident::search($conn, array(
            'incident_id' => $values['ID']
        ));
        $values['INCIDENT_NO'] = $incident->get_ticket();
        $values['TITLE'] = $incident->get_title();
        $values['PRIORITY_NUM'] = $incident->get_priority();
        $values['PRIORITY_STR'] = $incident->get_priority_string($values['PRIORITY_NUM']);
        $values['STATUS'] = $incident->get_status();
        $values['CLASS'] = $incident->get_ref();
        $values['TYPE'] = $incident->get_type();
        $values['CREATION_DATE'] = $incident->get_date();
        $values['LIFE_TIME'] = $incident->get_life_time();
        //
        // Related Incident tags
        //
        require_once 'classes/Incident_tag.inc';
        $incident_tag = new Incident_tag($conn);
        $tags = array();
        foreach($incident->get_tags() as $tag) {
            $tags[] = $incident_tag->get_tag_info($tag, 'name');
        }
        $values['TAGS'] = implode(", ", $tags);
        //
        // Extra incident info
        //
        $info = '';
		
		        
		switch ($values['CLASS']) {
            case 'Event':
            case 'Alarm':
                $method = $values['CLASS'] == 'Event' ? 'get_events' : 'get_alarms';
                list($obj) = $incident->$method($conn);
                if ($obj) {
                    if ($obj->get_src_ips())
                        $info .= _('Source IPs') . ': ' . $obj->get_src_ips() . "\n";
                    if ($obj->get_src_ports())
                        $info .= _('Source Ports') . ': ' . $obj->get_src_ports() . "\n";
                    if ($obj->get_dst_ips())
                        $info .= _('Dest. IPs') .    ': ' . $obj->get_dst_ips() . "\n";
                    if ($obj->get_dst_ports())
                        $info .= _('Dest. Ports') .  ': ' . $obj->get_dst_ports();
                }
                break;

            case 'Metric':
                list($obj) = $incident->get_metrics($conn);
                if ($obj) {
                    if ($obj->get_target())
                        $info .= _('Target') .       ': ' . $obj->get_target() . "\n";
                    if ($obj->get_metric_type())
                        $info .= _('Metric Type') .  ': ' . $obj->get_metric_type() . "\n";
                    if ($obj->get_metric_value())
                        $info .= _('Metric Value') . ': ' . $obj->get_metric_value();
                }
                break;

            case 'Anomaly':
                list($obj) = $incident->get_anomalies($conn);
                if ($obj) {
                    if ($obj->get_anom_type())
                        $info .= _('Anomaly type') . ': ' . strtoupper($obj->get_anom_type()) . "\n";
                    if ($obj->get_ip())
                        $info .= _('IP') .           ': ' . $obj->get_ip() . "\n";
                    if ($obj->get_data_orig())
                        $info .= _('Previous') .     ': ' . $obj->get_data_orig() . "\n";
                    if ($obj->get_data_new())
                        $info .=_('New') .           ': ' . $obj->get_data_new();
                }
                break;
			
			case 'Vulnerability':
                list($obj) = $incident->get_vulnerabilities($conn);
                if ($obj) {
                    if ($obj->get_ip())
                        $info .= _('IP')          .   ': ' . $obj->get_ip() . "\n";
                    if ($obj->get_port())
                        $info .= _('Port')        .   ': ' . strtoupper($obj->get_port()) . "\n";
					if ($obj->get_nessus_id())
                        $info .= _('Scanner ID')  .   ': ' . $obj->get_nessus_id() . "\n";
                    if ($obj->get_risk())
                        $info .=_('Risk')         .   ': ' . $obj->get_risk() . "\n";
					if ($obj->get_description())
                        $info .=_('Description')  .  ': ' . $obj->get_description();
                }
                break;
		}
		
        $values['EXTRA_INFO'] = $info;
		
			
		
        //
        // In charge details
        //
        $login = $incident->get_in_charge();
        
        if (preg_match("/^\d+$/",$login)) { 
            $all_entities = $name_entities = array();
            $all_entities = Acl::get_entities($conn);
            foreach($all_entities[0] as $data_entities) $name_entities[$data_entities["id"]] = $data_entities["name"];

            $values['IN_CHARGE_LOGIN'] = $login;
            $values['IN_CHARGE_NAME'] = $name_entities[$login];
            $values['IN_CHARGE_EMAIL'] = "";
            $values['IN_CHARGE_DPTO'] = "";
            $values['IN_CHARGE_COMPANY'] = "";
        } else {
            list($in_charge) = Session::get_list($conn, "WHERE login = '$login'");
        	if (!$in_charge) {
            	$in_charge_name = _("Unknown");
            	$in_charge_mail = _("Unknown");
            	$in_charge_dep = _("Unknown");
            	$in_charge_com = _("Unknown");
            } else {
            	$in_charge_name = $in_charge->get_name();
            	$in_charge_mail = $in_charge->get_email();
            	$in_charge_dep = $in_charge->get_department();
            	$in_charge_com = $in_charge->get_company();
            }
            $values['IN_CHARGE_LOGIN'] = $login;
            $values['IN_CHARGE_NAME'] = $in_charge_name;
            $values['IN_CHARGE_EMAIL'] = $in_charge_mail;
            $values['IN_CHARGE_DPTO'] = $in_charge_department;
            $values['IN_CHARGE_COMPANY'] = $in_charge_company;
        }
        
        return $values;
    }
    function get_email_template($type) {
        $default_subject_template = '[alienvault-incident] PRIORITY_STR: TITLE';
        $default_body_template = 'Incident details
------------------------------------------------
     Title: INCIDENT_NO - TITLE
    Status: STATUS
      Type: CLASS - TYPE
  Priority: PRIORITY_NUM (PRIORITY_STR)
 In charge: IN_CHARGE_NAME <IN_CHARGE_EMAIL>
   Created: CREATION_DATE (LIFE_TIME ago)
      Tags: TAGS

Extra info:
EXTRA_INFO

Ticket details
------------------------------------------------
Author: TICKET_AUTHOR_NAME <TICKET_AUTHOR_EMAIL>

TICKET_DESCRIPTION

Actions:

TICKET_ACTION

Past tickets:

TICKET_INVERSE_HISTORY
';
        require_once 'ossim_conf.inc';
        $conf = $GLOBALS["CONF"];
        if (!in_array($type, array('subject','body' ))) die("Invalid template type: $type");
        
		$tpl = $conf->get_conf('email_' . $type . '_template', false);
        // There is no such conf option or it's empty
        if (!$tpl && $type == 'subject') {
            $tpl = $default_subject_template;
        } elseif (!$tpl && $type == 'body') {
            $tpl = $default_body_template;
        }
        return $tpl;
    }
    /*
    * @param $templates assoc array ex: array(
    *                                         'subject' => 'blah',
    *                                         'body' => 'blah'
    *                                      )
    * @param $values assoc array es: array(
    *                                          'TICKET_ID' => 24,
    *                                          'TICKET_AUTHOR' => jsmith
    *                                      )
    * @return The same $template array with the replacements done
    */
    function build_email_template($template, $values) {
        foreach($values as $k => $v) {
            $regex[] = "/(?:^|([^A-Z])){$k}(?:([^A-Z])|\$)/";
            $replace[] = "\${1}{$v}\${2}"; // ex: "ID" ==> "23" (preserve the surrounding quotes)
            
        }
        return preg_replace($regex, $replace, $template);
    }
    /*
    * For resetting the templates back to defaults set the vars to ''
    */
    function save_email_template($subject_tpl, $body_tpl) {
        require_once 'classes/Config.inc';
        $conf = new Config;
        $conf->update('email_subject_template', $subject_tpl);
        $conf->update('email_body_template', $body_tpl);
    }
	
	
	//@return true if current user have permissions to delete an incident_ticket
	
	function user_tickets_perms($conn, $id, $user=null)
	{
		$current_user = ( empty($user) ) ? Session::get_session_user() : $user;
		
		//I'm admin
		if ( Session::am_i_admin() ) 
			return true;
		
				
		$query = OssimQuery("SELECT users FROM incident_ticket WHERE id = ".$id);
		
		if (!$rs = & $conn->Execute($query)) 
		    return $conn->ErrorMsg();
        else 
		{
            if (!$rs->EOF) 
			    $creator = $rs->fields["users"];
		}
		
		//I'm the submmiter
		if ( $creator == $current_user )
			return true;
		
		//I'm pro_admin and some of my users is the creator
		$conf = $GLOBALS["CONF"];
						
		if (!$conf) 
		{
			require_once 'ossim_db.inc';
			require_once 'ossim_conf.inc';
			$conf = new ossim_conf();
		}
		
		$version  = $conf->get_conf("ossim_server_version", FALSE);
		$pro      = ( preg_match("/pro|demo/i",$version) ) ? true : false;
		
		
		if ( $pro && Acl::am_i_proadmin() )
		{
			$users_list = Acl::get_my_users($conn, $current_user);
				
			if ( is_array($users_list) && !empty($users_list) )
			{
				foreach($users_list as $k => $v)
					$my_users[] = $v["login"];
					
				if ( in_array($creator, $my_users) )
					return true;
			}
		
		}
		
		return false;
	}
}
?>

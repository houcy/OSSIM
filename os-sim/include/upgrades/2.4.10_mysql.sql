use ossim;
SET AUTOCOMMIT=0;
BEGIN;

REPLACE INTO `inventory_search` (`type`, `subtype`, `match`, `list`, `query`, `ruleorder`) VALUES ('Vulnerabilities', 'Vuln Contains', 'text', '', 'SELECT DISTINCT INET_NTOA(hp.host_ip) as ip FROM host_plugin_sid hp, plugin_sid p WHERE hp.plugin_id = 3001 AND p.plugin_id = 3001 AND hp.plugin_sid = p.sid AND p.name %op% ? UNION SELECT DISTINCT INET_NTOA(s.host_ip) as ip FROM vuln_nessus_plugins p,host_plugin_sid s WHERE s.plugin_id=3001 and s.plugin_sid=p.id AND p.name %op% ?', 4);
REPLACE INTO `inventory_search` (`type`, `subtype`, `match`, `list`, `query`, `ruleorder`) VALUES ('SIEM Events', 'Has Plugin Groups', 'fixed', 'SELECT group_id,name FROM plugin_group_descr', 'SELECT INET_NTOA(ip_src) as ip FROM snort.acid_event WHERE plugin_id in (SELECT plugin_id FROM ossim.plugin_group WHERE group_id=?) UNION SELECT INET_NTOA(ip_dst) as ip FROM snort.acid_event WHERE plugin_id in (SELECT plugin_id FROM ossim.plugin_group WHERE group_id=?)', 5);

UPDATE custom_report_types SET inputs=REPLACE(inputs,";Source:source:select:OSS_ALPHA:EVENTSOURCE:",";Plugin Groups:plugin_groups:select:OSS_DIGIT.OSS_NULLABLE:PLUGINGROUPS:;Source:source:select:OSS_ALPHA:EVENTSOURCE:") WHERE file='SIEM/List.php' AND name!="List" AND inputs not like '%PLUGINGROUPS%';

-- From now on, always add the date of the new releases to the .sql files
use ossim;
UPDATE config SET value="2010-11-30" WHERE conf="last_update";

-- WARNING! Keep this at the end of this file
-- WARNING! Keep this at the end of this file
-- WARNING! Keep this at the end of this file
-- WARNING! Keep this at the end of this file
-- WARNING! Keep this at the end of this file
-- ATENCION! Keep this at the end of this file
-- ATENCION! Keep this at the end of this file
-- ATENCION! Keep this at the end of this file
-- ATENCION! Keep this at the end of this file
-- ATENCION! Keep this at the end of this file
REPLACE INTO config (conf, value) VALUES ('ossim_schema_version', '2.4.10');
COMMIT;
-- NOTHING BELOW THIS LINE / NADA DEBAJO DE ESTA LINEA
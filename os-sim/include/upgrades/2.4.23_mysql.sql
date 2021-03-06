use ossim;
SET AUTOCOMMIT=0;
BEGIN;

UPDATE custom_report_types SET inputs = 'Status:status:select:OSS_LETTER:Open,Closed,All' WHERE id in (320,321,322,323,324);
UPDATE custom_report_types SET inputs = 'Logo:logo:FILE:OSS_NULLABLE::;I.T. Security:it_security:text:OSS_TEXT.OSS_PUNC_EXT.OSS_NULLABLE::35;Address:address:text:OSS_TEXT.OSS_PUNC_EXT.OSS_NULLABLE::35;Tel:tlfn:text:OSS_TEXT.OSS_PUNC_EXT.OSS_NULLABLE::;Date:date:text:OSS_TEXT.OSS_PUNC_EXT.OSS_NULLABLE::' WHERE id=440;
REPLACE INTO config (conf, value) VALUES ('nessus_pre_scan_locally', '1');
REPLACE INTO config (conf, value) VALUES ('server_logger_if_priority', '1');

REPLACE INTO `acl_perm` (`id`, `type`, `name`, `value`, `description`, `granularity_sensor`, `granularity_net`, `enabled`, `ord`) VALUES
(48, 'MENU', 'MenuEvents', 'EventsForensics', 'Analysis -> SIEM Events', 1, 1, 1, '03.01'),
(71, 'MENU', 'MenuEvents', 'EventsForensicsDelete', 'Analysis -> SIEM Events -> Delete Events', 0, 0, 1, '03.02'),
(51, 'MENU', 'MenuEvents', 'EventsRT', 'Analysis -> SIEM Events -> Real Time', 1, 0, 1, '03.03'),
(61, 'MENU', 'MenuEvents', 'ControlPanelSEM', 'Analysis -> Logger', 1, 0, 1, '03.04'),
(49, 'MENU', 'MenuEvents', 'EventsVulnerabilities', 'Analysis -> Vulnerabilities -> View', 1, 1, 1, '03.07'),
(72, 'MENU', 'MenuEvents', 'EventsVulnerabilitiesScan', 'Analysis -> Vulnerabilities -> Scan/Import', 1, 1, 1, '03.08'),
(73, 'MENU', 'MenuEvents', 'EventsVulnerabilitiesDeleteScan', 'Analysis -> Vulnerabilities -> Delete Scan Report', 1, 1, 1, '03.09'),
(78, 'MENU', 'MenuEvents', 'EventsNids', 'Analysis -> Detection -> NIDS', 1, 1, 1, '03.10'),
(79, 'MENU', 'MenuEvents', 'EventsHids', 'Analysis -> Detection -> HIDS', 1, 1, 1, '03.11'),
(62, 'MENU', 'MenuEvents', 'ReportsWireless', 'Analysis -> Detection -> Wireless', 1, 0, 1, '03.12'),
(50, 'MENU', 'MenuEvents', 'EventsAnomalies', 'Analysis -> Detection -> Anomalies', 1, 1, 1, '03.13');

REPLACE INTO `acl_perm` (`id`, `type`, `name`, `value`, `description`, `granularity_sensor`, `granularity_net`, `enabled`, `ord`) VALUES
(56, 'MENU', 'MenuConfiguration', 'ConfigurationMaps', '', 0, 0, 0, '0'),
(38, 'MENU', 'MenuConfiguration', 'ConfigurationHostScan', '', 0, 0, 0, '0'),
(37, 'MENU', 'MenuConfiguration', 'ConfigurationRRDConfig', '', 0, 0, 0, '0'),
(34, 'MENU', 'MenuConfiguration', 'ConfigurationMain', 'Configuration -> Main', 0, 0, 1, '08.01'),
(35, 'MENU', 'MenuConfiguration', 'ConfigurationUsers', 'Configuration -> Users', 0, 0, 1, '08.02'),
(39, 'MENU', 'MenuConfiguration', 'ConfigurationUserActionLog', 'Configuration -> Users -> User activity', 0, 0, 1, '08.03'),
(12, 'MENU', 'MenuConfiguration', 'PolicySensors', 'Configuration -> SIEM Components -> Sensors', 1, 0, 1, '08.04'),
(53, 'MENU', 'MenuConfiguration', 'PolicyServers', 'Configuration -> SIEM Components -> Servers', 0, 0, 1, '08.05'),
(36, 'MENU', 'MenuConfiguration', 'ConfigurationPlugins', 'Configuration -> Collection -> Data Sources', 0, 0, 1, '08.06'),
(17, 'MENU', 'MenuConfiguration', 'PluginGroups', 'Configuration -> Collection -> DS Groups', 0, 0, 1, '08.07'),
(57, 'MENU', 'MenuConfiguration', 'ToolsDownloads', 'Configuration -> Collection -> Downloads', 0, 0, 1, '08.08'),
(29, 'MENU', 'MenuConfiguration', 'MonitorsSensors', 'Configuration -> Collection -> Sensors', 1, 0, 1, '08.09'),
(69, 'MENU', 'MenuConfiguration', 'ToolsUserLog', 'Configuration -> Collection -> User Activity', 0, 0, 1, '08.10'),
(80, 'MENU', 'MenuConfiguration', 'NetworkDiscovery', 'Configuration -> Network Discovery', 0, 0, 1, '08.11'),
(41, 'MENU', 'MenuConfiguration', 'ConfigurationUpgrade', 'Configuration -> Software Upgrade', 0, 0, 1, '08.12'),
(44, 'MENU', 'MenuConfiguration', 'ToolsBackup', 'Configuration -> Backup', 0, 0, 1, '08.13');

REPLACE INTO `ossim_acl`.`aco` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES ('86', 'MenuEvents', 'EventsNids', '0', 'EventsNids', '0'),
('87', 'MenuEvents', 'EventsHids', '0', 'EventsHids', '0'),
('88', 'MenuConfiguration', 'NetworkDiscovery', '0', 'NetworkDiscovery', '0');

REPLACE INTO `protocol` (`id`, `name`, `alias`, `descr`) VALUES
(0, 'hopopt', 'HOPOPT', 'IPv6 Hop-by-Hop Option'),
(1, 'icmp', 'ICMP', 'Internet Control Message'),
(2, 'igmp', 'IGMP', 'Internet Group Management'),
(3, 'ggp', 'GGP', 'Gateway-to-Gateway'),
(4, 'ip', 'IP', 'IP in IP'),
(5, 'st', 'ST', 'Stream'),
(6, 'tcp', 'TCP', 'Transmission Control'),
(7, 'cbt', 'CBT', 'CBT'),
(8, 'egp', 'EGP', 'Exterior Gateway Protocol'),
(9, 'igp', 'IGP', 'any private interior gateway'),
(10, 'bbn-rcc-mon', 'BBN-RCC-MON', 'BBN RCC Monitoring'),
(11, 'nvp-ii', 'NVP-II', 'Network Voice Protocol'),
(12, 'pup', 'PUP', 'PUP'),
(13, 'argus', 'ARGUS', 'ARGUS'),
(14, 'emcon', 'EMCON', 'EMCON'),
(15, 'xnet', 'XNET', 'Cross Net Debugger'),
(16, 'chaos', 'CHAOS', 'Chaos'),
(17, 'udp', 'UDP', 'User Datagram'),
(18, 'mux', 'MUX', 'Multiplexing'),
(19, 'dcn-meas', 'DCN-MEAS', 'DCN Measurement Subsystems'),
(20, 'hmp', 'HMP', 'Host Monitoring'),
(21, 'prm', 'PRM', 'Packet Radio Measurement'),
(22, 'xns-idp', 'XNS-IDP', 'XEROX NS IDP'),
(23, 'trunk-1', 'TRUNK-1', 'Trunk-1'),
(24, 'trunk-2', 'TRUNK-2', 'Trunk-2'),
(25, 'leaf-1', 'LEAF-1', 'Leaf-1'),
(26, 'leaf-2', 'LEAF-2', 'Leaf-2'),
(27, 'rdp', 'RDP', 'Reliable Data Protocol'),
(28, 'irtp', 'IRTP', 'Internet Reliable Transaction'),
(29, 'iso-tp4', 'ISO-TP4', 'ISO Transport Protocol Class 4'),
(30, 'netblt', 'NETBLT', 'Bulk Data Transfer Protocol'),
(31, 'mfe-nsp', 'MFE-NSP', 'MFE Network Services Protocol'),
(32, 'merit-inp', 'MERIT-INP', 'MERIT Internodal Protocol'),
(33, 'sep', 'SEP', 'Sequential Exchange Protocol'),
(34, '3pc', '3PC', 'Third Party Connect Protocol'),
(35, 'idpr', 'IDPR', 'Inter-Domain Policy Routing Protocol'),
(36, 'xtp', 'XTP', 'XTP'),
(37, 'ddp', 'DDP', 'Datagram Delivery Protocol'),
(38, 'idpr-cmtp', 'IDPR-CMTP', 'IDPR Control Message Transport Proto'),
(39, 'tp++', 'TP', 'TP++ Transport Protocol'),
(40, 'il', 'IL', 'IL Transport Protocol'),
(41, 'ipv6', 'IPv6', 'Ipv6'),
(42, 'sdrp', 'SDRP', 'Source Demand Routing Protocol'),
(43, 'ipv6-route', 'IPv6-Route', 'Routing Header for IPv6'),
(44, 'ipv6-frag', 'IPv6-Frag', 'Fragment Header for IPv6'),
(45, 'idrp', 'IDRP', 'Inter-Domain Routing Protocol'),
(46, 'rsvp', 'RSVP', 'Reservation Protocol'),
(47, 'gre', 'GRE', 'General Routing Encapsulation'),
(48, 'mhrp', 'MHRP', 'Mobile Host Routing Protocol'),
(49, 'bna', 'BNA', 'BNA'),
(50, 'esp', 'ESP', 'Encap Security Payload for IPv6'),
(51, 'ah', 'AH', 'Authentication Header for IPv6'),
(52, 'i-nlsp', 'I-NLSP', 'Integrated Net Layer Security  TUBA'),
(53, 'swipe', 'SWIPE', 'IP with Encryption'),
(54, 'narp', 'NARP', 'NBMA Address Resolution Protocol'),
(55, 'mobile', 'MOBILE', 'IP Mobility'),
(56, 'tlsp', 'TLSP', 'Transport Layer Security Protocol using Kryptonet key management'),
(57, 'skip', 'SKIP', 'SKIP'),
(58, 'ipv6-icmp', 'IPv6-ICMP', 'ICMP for IPv6'),
(59, 'ipv6-nonxt', 'IPv6-NoNxt', 'No Next Header for IPv6'),
(60, 'ipv6-opts', 'IPv6-Opts', 'Destination Options for IPv6'),
(62, 'cftp', 'CFTP', 'CFTP'),
(64, 'sat-expak', 'SAT-EXPAK', 'SATNET and Backroom EXPAK'),
(65, 'kryptolan', 'KRYPTOLAN', 'Kryptolan'),
(66, 'rvd', 'RVD', 'MIT Remote Virtual Disk Protocol'),
(67, 'ippc', 'IPPC', 'Internet Pluribus Packet Core'),
(69, 'sat-mon', 'SAT-MON', 'SATNET Monitoring'),
(70, 'visa', 'VISA', 'VISA Protocol'),
(71, 'ipcv', 'IPCV', 'Internet Packet Core Utility'),
(72, 'cpnx', 'CPNX', 'Computer Protocol Network Executive'),
(73, 'cphb', 'CPHB', 'Computer Protocol Heart Beat'),
(74, 'wsn', 'WSN', 'Wang Span Network'),
(75, 'pvp', 'PVP', 'Packet Video Protocol'),
(76, 'br-sat-mon', 'BR-SAT-MON', 'Backroom SATNET Monitoring'),
(77, 'sun-nd', 'SUN-ND', 'SUN ND PROTOCOL-Temporary'),
(78, 'wb-mon', 'WB-MON', 'WIDEBAND Monitoring'),
(79, 'wb-expak', 'WB-EXPAK', 'WIDEBAND EXPAK'),
(80, 'iso-ip', 'ISO-IP', 'ISO Internet Protocol'),
(81, 'vmtp', 'VMTP', 'VMTP'),
(82, 'secure-vmtp', 'SECURE-VMTP', 'SECURE-VMTP'),
(83, 'vines', 'VINES', 'VINES'),
(84, 'ttp', 'TTP', 'TTP'),
(85, 'nsfnet-igp', 'NSFNET-IGP', 'NSFNET-IGP'),
(86, 'dgp', 'DGP', 'Dissimilar Gateway Protocol'),
(87, 'tcf', 'TCF', 'TCF'),
(88, 'eigrp', 'EIGRP', 'EIGRP'),
(89, 'ospfigp', 'OSPFIGP', 'OSPFIGP'),
(90, 'sprite-rpc', 'Sprite-RPC', 'Sprite RPC Protocol'),
(91, 'larp', 'LARP', 'Locus Address Resolution Protocol'),
(92, 'mtp', 'MTP', 'Multicast Transport Protocol'),
(93, '25', 'AX', 'AX'),
(94, 'ipip', 'IPIP', 'IP-within-IP Encapsulation Protocol'),
(95, 'micp', 'MICP', 'Mobile Internetworking Control Pro'),
(96, 'scc-SP', 'SCC-SP', 'Semaphore Communications Sec'),
(97, 'etherip', 'ETHERIP', 'Ethernet-within-IP Encapsulation'),
(98, 'encap', 'ENCAP', 'Encapsulation Header'),
(100, 'gmtp', 'GMTP', 'GMTP'),
(101, 'ifmp', 'IFMP', 'Ipsilon Flow Management Protocol'),
(102, 'pnni', 'PNNI', 'PNNI over IP'),
(103, 'pim', 'PIM', 'Protocol Independent Multicast'),
(104, 'aris', 'ARIS', 'ARIS'),
(105, 'scps', 'SCPS', 'SCPS'),
(106, 'qnx', 'QNX', 'QNX'),
(107, 'n', 'A', 'Active Networks'),
(108, 'ipcomp', 'IPComp', 'IP Payload Compression Protocol'),
(109, 'snp', 'SNP', 'Sitara Networks Protocol'),
(110, 'compaq-peer', 'Compaq-Peer', 'Compaq Peer Protocol'),
(111, 'ipx-in-ip', 'IPX-in-IP', 'IPX in IP'),
(112, 'vrrp', 'VRRP', 'Virtual Router Redundancy Protocol'),
(113, 'pgm', 'PGM', 'PGM Reliable Transport Protocol'),
(115, 'l2tp', 'L2TP', 'Layer Two Tunneling Protocol'),
(116, 'ddx', 'DDX', 'D-II Data Exchange'),
(117, 'iatp', 'IATP', 'Interactive Agent Transfer Protocol'),
(118, 'stp', 'STP', 'Schedule Transfer Protocol'),
(119, 'srp', 'SRP', 'SpectraLink Radio Protocol'),
(120, 'uti', 'UTI', 'UTI'),
(121, 'smp', 'SMP', 'Simple Message Protocol'),
(122, 'sm', 'SM', 'SM'),
(123, 'ptp', 'PTP', 'Performance Transparency Protocol'),
(124, 'isis-over-ipv4', 'ISIS-over-IPv4', ''),
(125, 'fire', 'FIRE', ''),
(126, 'crtp', 'CRTP', 'Combat Radio Transport Protocol'),
(127, 'crudp', 'CRUDP', 'Combat Radio User Datagram'),
(128, 'sscopmce', 'SSCOPMCE', ''),
(129, 'iplt', 'IPLT', ''),
(130, 'sps', 'SPS', 'Secure Packet Shield'),
(131, 'pipe', 'PIPE', 'Private IP Encapsulation within IP'),
(132, 'sctp', 'SCTP', 'Stream Control Transmission Protocol'),
(133, 'fc', 'FC', 'Fibre Channel'),
(134, 'rsvp-e2e-ignore', 'RSVP-E2E-IGNORE', ''),
(135, 'mobility header', 'MOBILITY HEADER', ''),
(136, 'udplite', 'UDPLITE', ''),
(137, 'mpls-in-ip', 'MPLS-IN-IP', ''),
(138, 'manet', 'MANET', 'MANET Protocols'),
(139, 'hip', 'HIP', 'Host Identity Protocol'),
(140, 'shim6', 'SHIM6', 'Shim6 Protocol'),
(141, 'wesp', 'WESP', 'Wrapped Encapsulating Security Payload'),
(142, 'rohc', 'ROHC', 'Robust Header Compression');

-- WARNING! Keep this at the end of this file
-- ATENCION! Keep this at the end of this file
use ossim;
REPLACE INTO config (conf, value) VALUES ('last_update', '2011-04-01');
REPLACE INTO config (conf, value) VALUES ('ossim_schema_version', '2.4.23');
COMMIT;
-- NOTHING BELOW THIS LINE / NADA DEBAJO DE ESTA LINEA

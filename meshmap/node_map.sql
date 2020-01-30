-- --------------------------------------------------------
-- Host:                         192.168.81.222
-- Server version:               10.3.7-MariaDB-log - MariaDB Server
-- Server OS:                    Linux
-- HeidiSQL Version:             9.4.0.5125
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for node_map
CREATE DATABASE IF NOT EXISTS `node_map` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `node_map`;

-- Dumping structure for table node_map.hosts_ignore
CREATE TABLE IF NOT EXISTS `hosts_ignore` (
  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(70) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reason` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  UNIQUE KEY `name_2` (`name`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Hostnames to ignore because they are probably not AREDN nodes.';

-- Data exporting was unselected.
-- Dumping structure for table node_map.map_info
CREATE TABLE IF NOT EXISTS `map_info` (
  `id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `table_or_script_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `table_records_num` int(11) DEFAULT NULL,
  `table_update_num` int(11) NOT NULL DEFAULT 0,
  `table_last_update` datetime DEFAULT NULL,
  `script_last_run` datetime DEFAULT NULL,
  `currently_running` int(11) DEFAULT 0 COMMENT 'tells us if getNodeInfo is currently running or not',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Keeping track of some things about the map';

-- Data exporting was unselected.
-- Dumping structure for table node_map.marker_info
CREATE TABLE IF NOT EXISTS `marker_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `lat` double NOT NULL,
  `lon` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.
-- Dumping structure for table node_map.node_info
CREATE TABLE IF NOT EXISTS `node_info` (
  `node` varchar(70) COLLATE utf8_unicode_ci DEFAULT NULL,
  `wlan_ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_seen` datetime DEFAULT NULL,
  `uptime` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `loadavg` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `model` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firmware_version` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ssid` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `channel` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `chanbw` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tunnel_installed` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active_tunnel_count` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lat` double DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `wifi_mac_address` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `api_version` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `board_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firmware_mfg` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `grid_square` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lan_ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `services` varchar(2048) COLLATE utf8_unicode_ci DEFAULT NULL,
  `location_fix` int(11) DEFAULT 0,
  UNIQUE KEY `node` (`node`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Information about the nodes\r\ntaken from sysinfo.json that is on every node.';

-- Data exporting was unselected.
-- Dumping structure for table node_map.removed_nodes
CREATE TABLE IF NOT EXISTS `removed_nodes` (
  `node` varchar(70) COLLATE utf8_unicode_ci DEFAULT NULL,
  `wlan_ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_seen` datetime DEFAULT NULL,
  `uptime` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `loadavg` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `model` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firmware_version` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ssid` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `channel` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `chanbw` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tunnel_installed` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active_tunnel_count` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lat` double DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `wifi_mac_address` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `api_version` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `board_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firmware_mfg` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `grid_square` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lan_ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `services` varchar(2048) COLLATE utf8_unicode_ci DEFAULT NULL,
  `time_removed` datetime DEFAULT NULL,
  UNIQUE KEY `node` (`node`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='A place to put old nodes';

-- Data exporting was unselected.
-- Dumping structure for table node_map.topology
CREATE TABLE IF NOT EXISTS `topology` (
  `node` varchar(70) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nodelat` double DEFAULT NULL,
  `nodelon` double DEFAULT NULL,
  `linkto` varchar(70) COLLATE utf8_unicode_ci DEFAULT NULL,
  `linklat` double DEFAULT NULL,
  `linklon` double DEFAULT NULL,
  `cost` double DEFAULT NULL,
  `distance` double DEFAULT NULL,
  `bearing` double DEFAULT NULL,
  `lastupd` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.
-- Dumping structure for table node_map.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin` int(1) DEFAULT 0,
  `user` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `passwd` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='a place to put users for the admin page(s)';

-- Data exporting was unselected.
-- Dumping structure for trigger node_map.node_info_after_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `node_info_after_insert` AFTER INSERT ON `node_info` FOR EACH ROW BEGIN
INSERT INTO map_info (id, table_or_script_name, table_records_num, table_last_update)
VALUES ('NODEINFO', 'node_info', (SELECT COUNT(*) FROM node_info), NOW())
ON DUPLICATE KEY UPDATE id = 'NODEINFO', table_or_script_name = 'node_info', table_records_num = (SELECT COUNT(*) FROM node_info), table_last_update = NOW();
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger node_map.node_info_after_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
CREATE TRIGGER `node_info_after_update` AFTER UPDATE ON `node_info` FOR EACH ROW BEGIN
INSERT INTO map_info (id, table_or_script_name, table_records_num, table_last_update)
VALUES ('NODEINFO', 'node_info', (SELECT COUNT(*) FROM node_info), NOW())
ON DUPLICATE KEY UPDATE id = 'NODEINFO', table_or_script_name = 'node_info', table_records_num = (SELECT COUNT(*) FROM node_info), table_last_update = NOW();
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger node_map.topology_after_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
CREATE TRIGGER `topology_after_insert` AFTER INSERT ON `topology` FOR EACH ROW BEGIN
-- thanks go to K6GSE for making this trigger much cleaner and easier to read
 IF (SELECT count(*) FROM map_info WHERE id='LINKINFO') =0 THEN
  INSERT INTO map_info (id, table_or_script_name, table_records_num, table_last_update)
   VALUES ('LINKINFO', 'topology', (SELECT COUNT(*) FROM topology), NOW());
-- ON DUPLICATE KEY UPDATE id = 'LINKINFO', table_or_script_name = 'topology', table_records_num = (SELECT COUNT(*) FROM topology), table_last_update = NOW();
 ELSE
  UPDATE map_info SET table_records_num = (SELECT count(*) FROM topology), table_last_update = NOW() WHERE id= 'LINKINFO';
END IF;
-- prune the hosts_ignore table 
-- clear 404 errors after 26 hours
-- hopefully the user will update
-- also remove no_route errors after 90 min
-- PHP controlled now
-- DELETE FROM hosts_ignore WHERE HOUR(TIMEDIFF(NOW(), hosts_ignore.timestamp)) > 26 AND hosts_ignore.reason = '404';
-- DELETE FROM hosts_ignore WHERE TIMESTAMPDIFF(MINUTE, hosts_ignore.timestamp, NOW()) > 90 AND hosts_ignore.reason = 'no_route';
-- DELETE FROM hosts_ignore WHERE TIMESTAMPDIFF(MINUTE, hosts_ignore.timestamp, NOW()) > 30 AND hosts_ignore.reason = 'refused';
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger node_map.topology_get_latlons_brg_dist
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
CREATE TRIGGER `topology_get_latlons_brg_dist` BEFORE INSERT ON `topology` FOR EACH ROW BEGIN

-- get locations and names for each end of the link
IF NEW.node IS NOT NULL THEN
 SET NEW.nodelat = (SELECT lat FROM node_info WHERE wlan_ip = NEW.node AND (lat IS NOT NULL && lat != 0 && lat != 0.00));
 SET NEW.nodelon = (SELECT lon FROM node_info WHERE wlan_ip = NEW.node AND (lon IS NOT NULL && lon != 0 && lon != 0.00));
 SET NEW.node = (SELECT node FROM node_info WHERE wlan_ip = NEW.node);
END IF;
IF NEW.linkto IS NOT NULL THEN
 SET NEW.linklat = (SELECT lat FROM node_info WHERE wlan_ip = NEW.linkto AND (lat IS NOT NULL && lat != 0 && lat != 0.00));
 SET NEW.linklon = (SELECT lon FROM node_info WHERE wlan_ip = NEW.linkto AND (lon IS NOT NULL && lon != 0 && lon != 0.00));
 SET NEW.linkto = (SELECT node FROM node_info WHERE wlan_ip = NEW.linkto);
END IF;

-- figure out distance and bearing

SET NEW.bearing = round(mod(degrees(atan2(sin(radians(NEW.linklon)-radians(NEW.nodelon))*cos(radians(NEW.linklat)), cos(radians(NEW.nodelat))*sin(radians(NEW.linklat))-sin(radians(NEW.nodelat))*cos(radians(NEW.linklat))*cos(radians(NEW.linklon)-radians(NEW.nodelon)))) + 360,360),1);
SET NEW.distance = round(2*asin(sqrt(pow(sin((radians(NEW.linklat)-radians(NEW.nodelat))/2),2)+cos(radians(NEW.nodelat))*cos(radians(NEW. linklat))*pow(sin((radians(NEW.linklon)-radians(NEW.nodelon))/2),2)))*6371,2);

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

-- drop old trigger
DROP TRIGGER topology_get_latlons_brg_dist;

-- create new trigger based on Km instead of miles.
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

USE `alph`;

DROP FUNCTION IF EXISTS `MACADDRESS`;
DROP FUNCTION IF EXISTS `GENERATE_PRIVATE_IP`;
DROP FUNCTION IF EXISTS `GENERATE_PUBLIC_IP`;
DROP FUNCTION IF EXISTS `SPLIT_STR`;
DROP FUNCTION IF EXISTS `IdDirectoryFromPath`;
DROP PROCEDURE IF EXISTS `NewNetwork`;
DROP PROCEDURE IF EXISTS `NewTerminal`;
DROP FUNCTION IF EXISTS `GET_REVERSED_FULL_PATH_FROM_FILE_ID`;

DELIMITER $$

/**
 * Generate a new random MAC address
 */
CREATE DEFINER=`root`@`localhost` FUNCTION `MACADDRESS`() RETURNS char(17) CHARSET utf8
BEGIN
RETURN CONCAT(
	SUBSTRING('123456789ABCDEF', FLOOR(RAND() * 15 + 1), 1),
	SUBSTRING('123456789ABCDEF', FLOOR(RAND() * 15 + 1), 1),
    '-',
	SUBSTRING('123456789ABCDEF', FLOOR(RAND() * 15 + 1), 1),
	SUBSTRING('123456789ABCDEF', FLOOR(RAND() * 15 + 1), 1),
    '-',
	SUBSTRING('123456789ABCDEF', FLOOR(RAND() * 15 + 1), 1),
	SUBSTRING('123456789ABCDEF', FLOOR(RAND() * 15 + 1), 1),
    '-',
	SUBSTRING('123456789ABCDEF', FLOOR(RAND() * 15 + 1), 1),
	SUBSTRING('123456789ABCDEF', FLOOR(RAND() * 15 + 1), 1),
    '-',
	SUBSTRING('123456789ABCDEF', FLOOR(RAND() * 15 + 1), 1),
	SUBSTRING('123456789ABCDEF', FLOOR(RAND() * 15 + 1), 1),
    '-',
	SUBSTRING('123456789ABCDEF', FLOOR(RAND() * 15 + 1), 1),
	SUBSTRING('123456789ABCDEF', FLOOR(RAND() * 15 + 1), 1)
);
END$$

/**
 * Generate a new private IP with no conflict between others terminal's private IP
 */
CREATE DEFINER=`root`@`localhost` FUNCTION `GENERATE_PRIVATE_IP`(terminal_mac CHAR(17), network_mac CHAR(17)) RETURNS varchar(15) CHARSET utf8
BEGIN
DECLARE actual VARCHAR(15);

DECLARE host1 VARCHAR(3);
DECLARE host2 VARCHAR(3);

SET actual = (SELECT ip FROM PRIVATEIP WHERE network=network_mac ORDER BY ip DESC LIMIT 1);

IF actual IS NULL THEN RETURN '192.168.0.2';
ELSEIF actual = '192.168.255.254' THEN RETURN NULL;
ELSE
	SET host1 = SUBSTRING_INDEX(actual, '.', -1);
	SET host2 = SUBSTRING_INDEX(SUBSTRING_INDEX(actual, '.', -2), '.', 1);
    
	IF CAST(host1 AS UNSIGNED INT) = 255 THEN
        SET host2 = CAST(host2 AS UNSIGNED INT) + 1;
        
        SET host1 = '1';
	ELSE 
		SET host1 = CAST(host1 AS UNSIGNED INT) + 1;
    END IF;
    
	RETURN CONCAT('192.168.', host2, '.', host1);
END IF;
END$$

/**
 * Generate a new random public IP
 */
CREATE DEFINER=`root`@`localhost` FUNCTION `GENERATE_PUBLIC_IP`() RETURNS varchar(15) CHARSET utf8
BEGIN
DECLARE part INT(3);

generateFirstPart: LOOP
    SET part = ROUND((RAND() * (191 - 5)) + 5);
    
    IF part = 10 OR part = 127 OR part = 128 OR part = 192 THEN
		ITERATE generateFirstPart;
    END IF;
	LEAVE generateFirstPart;
END LOOP generateFirstPart;

RETURN CONCAT(
	part,
    '.',
    FLOOR(RAND() * 255),
    '.',
    FLOOR(RAND() * 255),
    '.',
    FLOOR(RAND() * 254)
);
END$$

/**
 * Split a string by a specific delimiter and index
 */
CREATE DEFINER=`root`@`localhost` FUNCTION `SPLIT_STR`(x VARCHAR(255), delim VARCHAR(12), pos INT) RETURNS varchar(255) CHARSET utf8
BEGIN
RETURN REPLACE(SUBSTRING(
				SUBSTRING_INDEX(x, delim, pos),
				CHAR_LENGTH(
					SUBSTRING_INDEX(x, delim, pos -1)
				) + 1),
				delim, "");--
END$$

/**
 * Get a directory's id from an absolute path
 */
CREATE DEFINER=`root`@`localhost` FUNCTION `IdDirectoryFromPath`(path TEXT, terminal_mac CHAR(17)) RETURNS text CHARSET utf8
BEGIN
	DECLARE i INT;
	DECLARE id INT;
    DECLARE dirname VARCHAR(255);
    DECLARE rootid INT;
    
    SET i = 2;
    
    SET rootid = (SELECT iddir FROM TERMINAL_DIRECTORY WHERE parent IS NULL AND name = '' AND terminal = terminal_mac);
    
    IF path = '' OR path = '/' THEN
		RETURN rootid;
    END IF;
    
    SET dirname = SPLIT_STR(path, '/', 2);
    
    SET id = (SELECT iddir FROM TERMINAL_DIRECTORY WHERE terminal = terminal_mac AND name = dirname AND parent = rootid);

	SET i = 3;
    
    WHILE dirname <> '' DO
		SET dirname = SPLIT_STR(path, '/', i);

		IF dirname <> '' THEN
			SET id = (SELECT iddir FROM TERMINAL_DIRECTORY WHERE terminal = terminal_mac AND parent = id AND `name` = dirname);
			SET i = i + 1;
        END IF;
    END WHILE;
    
    RETURN id;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `IdFileFromPath`(path TEXT, terminal_mac CHAR(17)) RETURNS text CHARSET utf8
BEGIN
	DECLARE i INT;
	DECLARE id INT;
    DECLARE lastid INT;
    DECLARE fileid INT;
    DECLARE dirname VARCHAR(255);
    DECLARE rootid INT;
    
    SET i = 2;
    
    SET rootid = (SELECT iddir FROM TERMINAL_DIRECTORY WHERE parent IS NULL AND name = '' AND terminal = terminal_mac);
    
    SET dirname = SPLIT_STR(path, '/', 2);
    
    SET id = (SELECT iddir FROM TERMINAL_DIRECTORY WHERE terminal = terminal_mac AND name = dirname AND parent = rootid);

	IF id IS NULL THEN
		SET fileid = (SELECT idfile FROM TERMINAL_FILE WHERE terminal = terminal_mac AND `name` = dirname AND parent = rootid);
        
        RETURN fileid;
    END IF;

	SET i = 3;
    
    WHILE dirname <> '' DO
		SET dirname = SPLIT_STR(path, '/', i);

		IF dirname <> '' THEN
			SET lastid = id;
			SET id = (SELECT iddir FROM TERMINAL_DIRECTORY WHERE terminal = terminal_mac AND parent = id AND `name` = dirname);
            
            IF id IS NULL THEN
				SET fileid = (SELECT idfile FROM TERMINAL_FILE WHERE terminal = terminal_mac AND `name` = dirname AND parent = lastid);
				
				RETURN fileid;
			END IF;
			SET i = i + 1;
        END IF;
    END WHILE;
    
    RETURN null;
END$$

/**
 * Generate a new network
 */
CREATE DEFINER=`root`@`localhost` PROCEDURE `NewNetwork`()
BEGIN
	generateMac: LOOP
		SET @network_mac = MACADDRESS();
		
		IF (SELECT COUNT(mac) FROM NETWORK WHERE mac = @network_mac) > 0 THEN
			ITERATE generateMac;
		END IF;
		
		LEAVE generateMac;
	END LOOP generateMac;

	generateIPv4: LOOP
		SET @network_ipv4 = GENERATE_PUBLIC_IP();
		
		IF (SELECT COUNT(mac) FROM NETWORK WHERE ipv4 = @network_ipv4) > 0 THEN
			ITERATE generateIPv4;
		END IF;
		
		LEAVE generateIPv4;
	END LOOP generateIPv4;

	generateIPv6: LOOP
		SET @network_ipv6 = CONCAT('fd', ROUND((RAND() * (99 - 10)) + 10), ':', ROUND((RAND() * 9999)));
		
		IF (SELECT COUNT(mac) FROM NETWORK WHERE ipv6 = @network_ipv6) > 0 THEN
			ITERATE generateIPv6;
		END IF;
		
		LEAVE generateIPv6;
	END LOOP generateIPv6;

	INSERT INTO NETWORK (mac, ipv4, ipv6) VALUES (@network_mac, @network_ipv4, @network_ipv6);
    
    SELECT @network_mac;
END$$

/**
 * Generate a new terminal
 */
CREATE DEFINER=`root`@`localhost` PROCEDURE `NewTerminal`(IN idaccount INT, IN network_mac CHAR(17))
BEGIN
    DECLARE moment DATETIME;
    SET moment = NOW();
    
	SET @terminal_mac = MACADDRESS();
    
    WHILE @terminal_mac IN (SELECT mac FROM TERMINAL) DO
		SET @terminal_mac = MACADDRESS();
    END WHILE;
    
	INSERT INTO TERMINAL (mac, account, localnetwork) VALUES(@terminal_mac, idaccount, network_mac);
    
    INSERT INTO PRIVATEIP (network, terminal, ip) VALUES (network_mac, @terminal_mac, GENERATE_PRIVATE_IP(@terminal_mac, network_mac)) ON DUPLICATE KEY UPDATE network=network;
    
    INSERT INTO TERMINAL_GROUP (terminal, gid, status, groupname) VALUES(@terminal_mac, 0, 1, 'root');
    SET @terminal_group = LAST_INSERT_ID();
    
    INSERT INTO TERMINAL_USER (terminal, uid, gid, status, username, password) VALUES(@terminal_mac, 0, @terminal_group, 1, 'root', (SELECT password FROM ACCOUNT WHERE ACCOUNT.idaccount=idaccount));
    SET @terminal_user = LAST_INSERT_ID();
    
	INSERT INTO TERMINAL_GROUP_LINK (terminal_user, terminal_group) VALUES(@terminal_user, @terminal_group);

    INSERT INTO TERMINAL_DIRECTORY (terminal, name, chmod, owner, `group`, createddate, editeddate) VALUES (@terminal_mac, '', 644, @terminal_user, @terminal_group, moment, moment);
	SET @terminal_root = LAST_INSERT_ID();

	INSERT INTO TERMINAL_DIRECTORY (terminal, parent, name, chmod, owner, `group`, createddate, editeddate) VALUES (@terminal_mac, @terminal_root, 'home', 644, @terminal_user, @terminal_group, moment, moment);

	SELECT @terminal_mac;
END$$
DROP FUNCTION IF EXISTS `GET_REVERSED_FULL_PATH_FROM_FILE_ID`;

/** 
 * Give reversed Full Path from ID
 */
CREATE DEFINER=`root`@`localhost` FUNCTION `GET_REVERSED_FULL_PATH_FROM_FILE_ID`(id INT, terminal_mac CHAR(17)) RETURNS TEXT CHARSET utf8
BEGIN
	DECLARE parentId INT;
    DECLARE fullPath TEXT;
    DECLARE parentName VARCHAR(255);
    
    set parentId = (SELECT parent FROM TERMINAL_FILE where idfile = id AND terminal = terminal_mac);
    SET fullPath = CONCAT((SELECT name FROM TERMINAL_FILE where idfile = id AND terminal = terminal_mac),'/');
    
    WHILE parentId IS NOT null DO
		SET parentName = (SELECT name FROM TERMINAL_DIRECTORY WHERE iddir = parentId AND terminal = terminal_mac);
		SET fullPath = CONCAT(fullPath, parentName, '/');
		SET parentId = (SELECT parent FROM TERMINAL_DIRECTORY where iddir = parentId AND terminal = terminal_mac);
	END WHILE;
    
    RETURN  LEFT(fullPath, length(fullpath)-2);
END$$

DELIMITER ;

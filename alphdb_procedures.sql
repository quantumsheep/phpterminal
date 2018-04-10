USE `alph`;
DROP function IF EXISTS `GENERATE_PRIVATE_IP`;

DELIMITER $$
USE `alph`$$
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

DELIMITER ;

USE `alph`;
DROP function IF EXISTS `GENERATE_PUBLIC_IP`;

DELIMITER $$
USE `alph`$$
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
END
$$

DELIMITER ;

USE `alph`;
DROP function IF EXISTS `MACADDRESS`;

DELIMITER $$
USE `alph`$$
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
END
$$

DELIMITER ;

USE `alph`;
DROP procedure IF EXISTS `NewTerminal`;

DELIMITER $$
USE `alph`$$
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
    
    INSERT INTO TERMINAL_DIRECTORY (terminal, name, chmod, owner, `group`, createddate, editeddate) VALUES (@terminal_mac, 'home', 644, @terminal_user, @terminal_group, moment, moment);

	SELECT @terminal_mac;
END
$$

DELIMITER ;

USE `alph`;
DROP procedure IF EXISTS `NewNetwork`;

DELIMITER $$
USE `alph`$$
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
END

$$

DELIMITER ;

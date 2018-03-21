DELETE FROM TERMINAL_GROUP_LINK;
DELETE FROM TERMINAL_DIRECTORY;
DELETE FROM TERMINAL_USER;
DELETE FROM TERMINAL_GROUP;
DELETE FROM PRIVATEIP;
DELETE FROM TERMINAL;
DELETE FROM NETWORK;
DELETE FROM ACCOUNT;

SELECT * FROM ACCOUNT, NETWORK;

INSERT INTO ACCOUNT (status, email, username, password, createddate, editeddate) VALUES(1, 'test@test.fr', 'TestAccount', '$2y$10$HSFF4XZPd1zEha15dnSWhOsdw2tUL1/XeXhnjbf04g/N53cEIs0NC', NOW(),  NOW());
SET @userid = (SELECT last_insert_id());
SET @network_mac = MACADDRESS();
INSERT INTO network (mac, ipv4, ipv6) VALUES (@network_mac, '54.25.112.238', 'fff0');
CALL NewTerminal(@userid, @network_mac);
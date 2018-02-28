CREATE DATABASE IF NOT EXISTS alph;
USE alph;

CREATE TABLE `ACCOUNT` (
	`idaccount` INT NOT NULL AUTO_INCREMENT,
	`email` varchar(254) NOT NULL UNIQUE,
	`username` varchar(36) NOT NULL UNIQUE,
	`password` varchar(255) NOT NULL,
	`createddate` DATETIME NOT NULL,
	`editeddate` DATETIME,
	PRIMARY KEY (`idaccount`)
);

CREATE TABLE `DIRECTORY` (
	`iddir` INT NOT NULL AUTO_INCREMENT,
	`terminal` varchar(17) NOT NULL,
	`parent` INT,
	`name` varchar(255) NOT NULL,
	`chmod` INT(3) NOT NULL,
	`createddate` DATETIME NOT NULL,
	`editeddate` DATETIME,
	PRIMARY KEY (`iddir`)
);

CREATE TABLE `FILE` (
	`idfile` INT NOT NULL AUTO_INCREMENT,
	`terminal` varchar(17) NOT NULL,
	`parentdir` INT,
	`name` varchar(255),
	`extension` varchar(255) NOT NULL,
	`data` TEXT,
	`chmod` INT(3) NOT NULL,
	`createddate` DATETIME NOT NULL,
	`editeddate` DATETIME,
	PRIMARY KEY (`idfile`)
);

CREATE TABLE `TERMINAL` (
	`mac` varchar(17) NOT NULL,
	`account` INT NOT NULL,
	`localnetwork` INT,
	PRIMARY KEY (`mac`)
);

CREATE TABLE `NETWORK` (
	`idnetwork` INT NOT NULL AUTO_INCREMENT,
	`ip` varchar(15) NOT NULL UNIQUE,
	PRIMARY KEY (`idnetwork`)
);

CREATE TABLE `PORT` (
	`idport` INT NOT NULL AUTO_INCREMENT,
	`idnetwork` INT NOT NULL,
	`port` INT NOT NULL,
	`status` INT NOT NULL,
	`ip` varchar(15) NOT NULL,
	`ipport` INT NOT NULL,
	PRIMARY KEY (`idport`)
);

CREATE TABLE `PRIVATEIP` (
	`idprivateip` INT NOT NULL AUTO_INCREMENT,
	`network` INT NOT NULL,
	`terminal` varchar(17) NOT NULL,
	`ip` varchar(15) NOT NULL,
	PRIMARY KEY (`idprivateip`)
);

CREATE TABLE `TERMINAL_USER` (
	`idterminal_user` INT NOT NULL AUTO_INCREMENT,
	`terminal` varchar(17) NOT NULL,
	`username` varchar(255) NOT NULL,
	`password` varchar(255) NOT NULL,
	PRIMARY KEY (`idterminal_user`)
);

CREATE TABLE `TERMINAL_GROUP` (
	`idterminal_group` INT NOT NULL AUTO_INCREMENT,
	`terminal` varchar(17) NOT NULL,
	`groupname` varchar(255) NOT NULL,
	PRIMARY KEY (`idterminal_group`)
);

CREATE TABLE `TERMINAL_GROUP_LINK` (
	`idterminal_group_link` INT NOT NULL AUTO_INCREMENT,
	`terminal` varchar(17) NOT NULL,
	`terminal_user` INT NOT NULL,
	`terminal_group` INT NOT NULL,
	PRIMARY KEY (`idterminal_group_link`)
);

CREATE TABLE `SESSION` (
	`id` varchar(32) NOT NULL,
	`access` INT(10),
	`data` TEXT NOT NULL,
	PRIMARY KEY (`id`)
);

ALTER TABLE `DIRECTORY` ADD CONSTRAINT `DIRECTORY_fk0` FOREIGN KEY (`terminal`) REFERENCES `TERMINAL`(`mac`);

ALTER TABLE `DIRECTORY` ADD CONSTRAINT `DIRECTORY_fk1` FOREIGN KEY (`parent`) REFERENCES `DIRECTORY`(`iddir`);

ALTER TABLE `FILE` ADD CONSTRAINT `FILE_fk0` FOREIGN KEY (`terminal`) REFERENCES `TERMINAL`(`mac`);

ALTER TABLE `FILE` ADD CONSTRAINT `FILE_fk1` FOREIGN KEY (`parentdir`) REFERENCES `DIRECTORY`(`iddir`);

ALTER TABLE `TERMINAL` ADD CONSTRAINT `TERMINAL_fk0` FOREIGN KEY (`account`) REFERENCES `ACCOUNT`(`idaccount`);

ALTER TABLE `TERMINAL` ADD CONSTRAINT `TERMINAL_fk1` FOREIGN KEY (`localnetwork`) REFERENCES `NETWORK`(`idnetwork`);

ALTER TABLE `PORT` ADD CONSTRAINT `PORT_fk0` FOREIGN KEY (`idnetwork`) REFERENCES `NETWORK`(`idnetwork`);

ALTER TABLE `PRIVATEIP` ADD CONSTRAINT `PRIVATEIP_fk0` FOREIGN KEY (`network`) REFERENCES `NETWORK`(`idnetwork`);

ALTER TABLE `PRIVATEIP` ADD CONSTRAINT `PRIVATEIP_fk1` FOREIGN KEY (`terminal`) REFERENCES `TERMINAL`(`mac`);

ALTER TABLE `TERMINAL_USER` ADD CONSTRAINT `TERMINAL_USER_fk0` FOREIGN KEY (`terminal`) REFERENCES `TERMINAL`(`mac`);

ALTER TABLE `TERMINAL_GROUP` ADD CONSTRAINT `TERMINAL_GROUP_fk0` FOREIGN KEY (`terminal`) REFERENCES `TERMINAL`(`mac`);

ALTER TABLE `TERMINAL_GROUP_LINK` ADD CONSTRAINT `TERMINAL_GROUP_LINK_fk0` FOREIGN KEY (`terminal`) REFERENCES `TERMINAL`(`mac`);

ALTER TABLE `TERMINAL_GROUP_LINK` ADD CONSTRAINT `TERMINAL_GROUP_LINK_fk1` FOREIGN KEY (`terminal_user`) REFERENCES `TERMINAL_USER`(`idterminal_user`);

ALTER TABLE `TERMINAL_GROUP_LINK` ADD CONSTRAINT `TERMINAL_GROUP_LINK_fk2` FOREIGN KEY (`terminal_group`) REFERENCES `TERMINAL_GROUP`(`idterminal_group`);


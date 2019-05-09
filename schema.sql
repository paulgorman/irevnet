-- MySQL Schema for iRev Web App Database
-- Presence 2017
--  mysql> GRANT ALL PRIVILEGES ON irevnet.* TO username@'localhost' IDENTIFIED BY 'password';

-- DANGER!!! XXX --------------
-- DROP ALL TABLES FOR A FRESH START HERE 
-- DROP TABLE IF EXISTS samples, samplecategories, samplesubcategories, samplelocations, locations, categories, media, sitehits, samplestyles, styles, admins, pages;
-- DANGER!!! XXX --------------

CREATE TABLE `irevnet`.`samples` (
	`oid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`name` VARCHAR(50) NOT NULL,
	`display_name` VARCHAR(50) NULL,
	`url` VARCHAR(50) NOT NULL UNIQUE,
	`alt_url` VARCHAR(50) NOT NULL UNIQUE,
	`slug` VARCHAR(255) NOT NULL,
	`bio` TEXT,
	`use_display_name` BOOLEAN NOT NULL DEFAULT 0,
	`is_active` BOOLEAN NOT NULL DEFAULT 0,
	`is_highlighted` BOOLEAN NOT NULL DEFAULT 0,
	`is_searchable` BOOLEAN NOT NULL DEFAULT 0,
	`last_updated` DATETIME NOT NULL,
	INDEX(`url`),
	INDEX(`alt_url`),
	INDEX(`name`),
	INDEX(`display_name`),
	INDEX(`slug`)
) ENGINE=InnoDB;

CREATE TABLE `irevnet`.`samplecategories` (
	`oid` INT NOT NULL,
	`cid` INT NOT NULL,
	INDEX(`oid`),
	INDEX(`cid`)
) ENGINE=InnoDB;

CREATE TABLE `irevnet`.`samplelocations` (
	`oid` INT NOT NULL, 
	`lid` INT NOT NULL,
	INDEX(`oid`),
	INDEX(`lid`)
) ENGINE=InnoDB;

CREATE TABLE `irevnet`.`locations` (
	`lid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`city` VARCHAR(40) NOT NULL UNIQUE,
	`state` CHAR(2) NOT NULL,
	INDEX(`city`),
	INDEX(`state`)
) ENGINE=InnoDB;

CREATE TABLE `irevnet`.`categories` (
	`cid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`category` VARCHAR(40) NOT NULL UNIQUE,
	`url` VARCHAR(50) NOT NULL UNIQUE,
	`description` VARCHAR(250),
	`force_display_names` CHAR NOT NULL DEFAULT "N",	-- N force real names only, I individually chosen, Y force obfuscated display names only
	`published` BOOLEAN NOT NULL DEFAULT 1,
	`image_filename` VARCHAR(30) NULL,
	`image_id` CHAR(17) NULL,
	`carousel_filename` VARCHAR(30) NULL,
	`carousel_id` CHAR(17) NULL,
	`is_highlighted` BOOLEAN NOT NULL DEFAULT 0,
	`last_updated` DATETIME NOT NULL,
	INDEX(`url`)
) ENGINE=InnoDB;

CREATE TABLE `irevnet`.`media` (
	`mid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`oid` INT NOT NULL,
	`name` VARCHAR(80),
	`filename` VARCHAR(80),
	`filetype` VARCHAR(4),
	`thumbwidth` INT,
	`thumbheight` INT,
	`width` INT,
	`height` INT,
	`vidlength` INT,
	`is_highlighted` BOOLEAN NOT NULL DEFAULT 0,
	`viewable` BOOLEAN NOT NULL DEFAULT 0,
	`published` DATETIME NOT NULL,
	INDEX(`oid`)
) ENGINE=InnoDB;

CREATE TABLE `irevnet`.`sitehits` (
	`hit_datetime` DATETIME NOT NULL ,
	`hit_ip` VARCHAR(16) NOT NULL ,
	`hit_addr` VARCHAR(255) NULL, 
	`hit_url` VARCHAR(120) NOT NULL ,
	`user_agent` VARCHAR(255) NULL ,
	`referrer` VARCHAR(255) NULL ,
	`sessionid` VARCHAR(255) NOT NULL,
	`sesscount` INT NOT NULL DEFAULT 0,
	INDEX(`hit_datetime`,`hit_ip`,`hit_url`)
) ENGINE=InnoDB;

CREATE TABLE `irevnet`.`samplestyles` (
	`oid` INT NOT NULL,
	`sid` INT NOT NULL,
	INDEX(`oid`),
	INDEX(`sid`)
) ENGINE=InnoDB;

CREATE TABLE `irevnet`.`styles` (
	`sid` INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
	`name` varchar(40) NOT NULL UNIQUE,
	INDEX(`name`)
) ENGINE=InnoDB;

CREATE TABLE `irevnet`.`admins` (
	`username` VARCHAR(16) NOT NULL PRIMARY KEY,
	`password` VARCHAR(256) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE `irevnet`.`subcategories` (
	`subid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`subcategory` VARCHAR(40) NOT NULL UNIQUE,
	`url` VARCHAR(50) NOT NULL UNIQUE,
	`parent_cid` INT NOT NULL,
	`description` VARCHAR(250),
	`image_filename` VARCHAR(30) NULL,
	`image_id` CHAR(17) NULL,
	INDEX(`parent_cid`),
	INDEX(`url`)
) ENGINE=InnoDB;

CREATE TABLE `irevnet`.`samplesubcategories` (
	`oid` INT NOT NULL,
	`subid` INT NOT NULL,
	INDEX(`oid`),
	INDEX(`subid`)
) ENGINE=InnoDB;

CREATE TABLE `irevnet`.`pages` (
	`pagename` VARCHAR(24) NOT NULL PRIMARY KEY,
	`undo` TEXT NULL,
	`html` TEXT NULL,
	`undotime` DATETIME,
	`htmltime` DATETIME
) ENGINE=InnoDB;
INSERT INTO `pages` (`pagename`,`html`,`htmltime`) VALUES ('home','Home','2014-01-01 00:00:00');
INSERT INTO `pages` (`pagename`,`html`,`htmltime`) VALUES ('about-top','About (Top)','2014-01-01 00:00:00');
INSERT INTO `pages` (`pagename`,`html`,`htmltime`) VALUES ('about-bottom','About (Bottom)','2014-01-01 00:00:00');
INSERT INTO `pages` (`pagename`,`html`,`htmltime`) VALUES ('irev-top','iRev.net (Top)','2014-01-01 00:00:00');
INSERT INTO `pages` (`pagename`,`html`,`htmltime`) VALUES ('irev-bottom','iRev.net (Bottom)','2014-01-01 00:00:00');
INSERT INTO `pages` (`pagename`,`html`,`htmltime`) VALUES ('now','News','2014-01-01 00:00:00');


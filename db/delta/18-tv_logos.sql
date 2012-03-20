--
ALTER TABLE `itv` ADD `logo` varchar(128) NOT NULL default '';

ALTER TABLE `administrators` ADD `debug_key` varchar(128) NOT NULL default '';

UPDATE `administrators` SET `debug_key`=md5(rand()) WHERE `access`=0;
--//@UNDO
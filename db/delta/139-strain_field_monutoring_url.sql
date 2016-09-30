--

ALTER TABLE `ch_links` CHANGE COLUMN `monitoring_url` `monitoring_url` VARCHAR(255) NOT NULL DEFAULT '' ;

-- //@UNDO

ALTER TABLE `ch_links` CHANGE COLUMN `monitoring_url` `monitoring_url` VARCHAR(128) NOT NULL DEFAULT '' ;

--
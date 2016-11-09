--

ALTER TABLE `ch_links` ADD COLUMN `akamai_auth_support` TINYINT DEFAULT 0;
ALTER TABLE `video_series_files` CHANGE COLUMN `tmp_link_type` `tmp_link_type` ENUM('flussonic','nginx','wowza', 'edgecast_auth', 'akamai_auth') NULL DEFAULT NULL ;


--//@UNDO

ALTER TABLE `ch_links` DROP COLUMN `edgecast_auth_support`;
ALTER TABLE `video_series_files` CHANGE COLUMN `tmp_link_type` `tmp_link_type` ENUM('flussonic','nginx','wowza', 'edgecast_auth') NULL DEFAULT NULL ;

--
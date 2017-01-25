--

ALTER TABLE `ch_links` ADD COLUMN `nimble_auth_support` TINYINT DEFAULT 0;
ALTER TABLE `video_series_files` CHANGE COLUMN `tmp_link_type` `tmp_link_type` ENUM('flussonic','nginx','wowza', 'edgecast_auth', 'nimble') NULL DEFAULT NULL ;
ALTER TABLE `storages` ADD COLUMN `nimble_dvr` TINYINT(4) NULL DEFAULT 0;
ALTER TABLE `itv` ADD COLUMN `nimble_dvr` TINYINT(4) NULL DEFAULT 0;


--//@UNDO

ALTER TABLE `ch_links` DROP COLUMN `nimble_auth_support`;
ALTER TABLE `video_series_files` CHANGE COLUMN `tmp_link_type` `tmp_link_type` ENUM('flussonic','nginx','wowza', 'edgecast_auth') NULL DEFAULT NULL ;
ALTER TABLE `storages` DROP COLUMN `nimble_dvr`;
ALTER TABLE `itv` DROP COLUMN `nimble_dvr`;

--
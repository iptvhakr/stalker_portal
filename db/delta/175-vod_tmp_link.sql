--

ALTER TABLE `video_series_files` ADD COLUMN `tmp_link_type` ENUM('flussonic','nginx','wowza') DEFAULT NULL;

ALTER TABLE `storages` ADD COLUMN `stream_server_type` ENUM('flussonic','wowza') DEFAULT NULL;
UPDATE `storages` SET stream_server_type='wowza' WHERE wowza_server=1;
ALTER TABLE `storages` CHANGE `wowza_app` `stream_server_app` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `storages` CHANGE `wowza_port` `stream_server_port` varchar(8) NOT NULL DEFAULT '';
ALTER TABLE `storages` DROP COLUMN `wowza_server`;

-- //@UNDO

ALTER TABLE `video_series_files` DROP COLUMN `tmp_link_type`;

ALTER TABLE `storages` ADD COLUMN `wowza_server` TINYINT NOT NULL DEFAULT 0;
UPDATE `storages` SET wowza_server=1 WHERE stream_server_type='wowza';
ALTER TABLE `storages` DROP COLUMN `stream_server_type`;
ALTER TABLE `storages` CHANGE `stream_server_app` `wowza_app` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `storages` CHANGE `stream_server_port` `wowza_port` varchar(8) NOT NULL DEFAULT '';

--
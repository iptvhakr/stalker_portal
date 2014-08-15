--
ALTER TABLE `itv` ADD `wowza_tmp_link` tinyint default 0;
ALTER TABLE `itv` ADD `wowza_dvr` tinyint default 0;
ALTER TABLE `itv` ADD `monitoring_status` tinyint default 0;
ALTER TABLE `itv` ADD `monitoring_status_updated` datetime;
ALTER TABLE `itv` ADD `enable_monitoring` tinyint default 0;
ALTER TABLE `itv` ADD `monitoring_url` varchar(128) NOT NULL default '';
--//@UNDO

ALTER TABLE `itv` DROP `wowza_tmp_link`;
ALTER TABLE `itv` DROP `wowza_dvr`;
ALTER TABLE `itv` DROP `monitoring_status`;
ALTER TABLE `itv` DROP `monitoring_status_updated`;
ALTER TABLE `itv` DROP `enable_monitoring`;
ALTER TABLE `itv` DROP `monitoring_url`;

--
--

ALTER TABLE `storages` ADD `wowza_dvr` tinyint default 0;
ALTER TABLE `storages` CHANGE `flussonic_server` `flussonic_dvr` tinyint default 0;

-- //@UNDO

ALTER TABLE `storages` DROP `wowza_dvr`;
ALTER TABLE `storages` CHANGE `flussonic_dvr` `flussonic_server` tinyint default 0;

--
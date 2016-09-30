--

ALTER TABLE `storages` ADD `wowza_app` VARCHAR(128) NOT NULL DEFAULT '';
ALTER TABLE `storages` ADD `wowza_port` VARCHAR(8) NOT NULL DEFAULT '';

-- //@UNDO

ALTER TABLE `storages` DROP `wowza_app`;
ALTER TABLE `storages` DROP `wowza_port`;

--
--

ALTER TABLE `itv` ADD `flussonic_dvr` tinyint default 0;
ALTER TABLE `storages` ADD `flussonic_server` tinyint default 0;

--//@UNDO

ALTER TABLE `itv` DROP `flussonic_dvr`;
ALTER TABLE `storages` DROP `flussonic_server`;

--
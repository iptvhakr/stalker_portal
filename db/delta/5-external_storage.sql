--
ALTER TABLE `storages` ADD `external` tinyint default 0;
--//@UNDO

ALTER TABLE `storages` DROP `external`;

--

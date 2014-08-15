--
ALTER TABLE `storages` ADD `for_simple_storage` tinyint default 1;
--//@UNDO

ALTER TABLE `storages` DROP `for_simple_storage`;

--

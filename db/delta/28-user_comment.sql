--
ALTER TABLE `users` ADD `comment` text not null;
ALTER TABLE `epg_setting` ADD `id_prefix` VARCHAR(64) NOT NULL DEFAULT '';
--//@UNDO
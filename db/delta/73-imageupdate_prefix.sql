--

ALTER TABLE `image_update_settings` ADD `prefix` varchar(128) NOT NULL default '';

--//@UNDO

ALTER TABLE `image_update_settings` DROP `prefix`;

--
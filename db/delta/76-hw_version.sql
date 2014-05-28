--

ALTER TABLE `users` ADD `hw_version` varchar(32) NOT NULL default '';
ALTER TABLE `image_update_settings` ADD `hardware_version_contains` varchar(32) NOT NULL default '';

--//@UNDO
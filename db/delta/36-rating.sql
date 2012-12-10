--

ALTER TABLE `video` ADD `age` VARCHAR(32) default '';
ALTER TABLE `video` ADD `rating_mpaa` VARCHAR(32) default '';

ALTER TABLE `services_package` ADD `service_type` VARCHAR(32) default 'periodic';

--//@UNDO
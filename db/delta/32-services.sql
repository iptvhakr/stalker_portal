--

ALTER TABLE `services_package` ADD `all_services` tinyint default 0;

ALTER TABLE `users` MODIFY `comment` text;

--//@UNDO

ALTER TABLE `services_package` DROP `all_services`;

--
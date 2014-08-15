--

ALTER TABLE `users` ADD `device_id2` varchar(255) NOT NULL default '';

--//@UNDO

ALTER TABLE `users` DROP `device_id2`;

--
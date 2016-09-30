--

ALTER TABLE `users` ADD INDEX `device_id2`(`device_id2`);

-- //@UNDO

ALTER TABLE `users` DROP INDEX `device_id2`;

--
--

ALTER TABLE `user_log` ADD INDEX `mac_index` (`mac` ASC);

-- //@UNDO

ALTER TABLE `user_log` DROP INDEX `mac_index` ;

--
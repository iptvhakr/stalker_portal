--

ALTER TABLE `users_activity` CHANGE COLUMN `users_online` `users_online` VARCHAR(512) NOT NULL DEFAULT '{}' ;
UPDATE `users_activity` SET `users_online` = CONCAT("{'total':", `users_online`, "}"), `time` = `time`;

-- //@UNDO

ALTER TABLE `users_activity` CHANGE COLUMN `users_online` `users_online` INT(11) NOT NULL DEFAULT '0' ;

--
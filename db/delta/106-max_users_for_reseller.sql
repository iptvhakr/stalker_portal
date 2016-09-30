--

ALTER TABLE `reseller` ADD COLUMN `max_users` INT(11) NOT NULL DEFAULT 0;

-- //@UNDO

ALTER TABLE `reseller` DROP COLUMN `max_users`;

--
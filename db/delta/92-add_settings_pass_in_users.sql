--

ALTER TABLE `users`
ADD COLUMN `settings_password` VARCHAR(60) NOT NULL DEFAULT '0000';

-- //@UNDO

ALTER TABLE `users` DROP COLUMN `settings_password`;

--
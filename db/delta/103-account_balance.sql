--

ALTER TABLE `users` ADD COLUMN `account_balance` VARCHAR(16) NOT NULL DEFAULT '';

-- //@UNDO

ALTER TABLE `users` DROP COLUMN `account_balance`;

--
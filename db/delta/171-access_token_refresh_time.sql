--

ALTER TABLE `access_tokens` ADD COLUMN `last_refresh` timestamp default 0;

--//@UNDO

ALTER TABLE `access_tokens` DROP COLUMN `last_refresh`;

--
--

ALTER TABLE `access_tokens` ADD COLUMN `last_refresh` timestamp null default null;

-- //@UNDO

ALTER TABLE `access_tokens` DROP COLUMN `last_refresh`;

--
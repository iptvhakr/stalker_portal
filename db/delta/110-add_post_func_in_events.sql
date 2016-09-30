--

ALTER TABLE `events` ADD COLUMN `post_function` VARCHAR(255) NULL;

-- //@UNDO

ALTER TABLE `events` DROP COLUMN `post_function`;

--
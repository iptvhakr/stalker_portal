--

ALTER TABLE `events` ADD COLUMN `param1` VARCHAR(255) NOT NULL DEFAULT '';

-- //@UNDO

ALTER TABLE `events` DROP COLUMN `param1`;

--
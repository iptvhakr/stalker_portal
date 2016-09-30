--

ALTER TABLE `apps` ADD COLUMN `autoupdate` TINYINT NOT NULL DEFAULT 0;

-- //@UNDO

ALTER TABLE `apps` DROP COLUMN `autoupdate`;

--
--

ALTER TABLE `launcher_apps` ADD COLUMN `manual_install` TINYINT NOT NULL DEFAULT 0;

-- //@UNDO

ALTER TABLE `launcher_apps` DROP COLUMN `manual_install`;

--
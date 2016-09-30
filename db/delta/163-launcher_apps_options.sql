--

ALTER TABLE `launcher_apps` ADD COLUMN `options` TEXT;

-- //@UNDO

ALTER TABLE `launcher_apps` DROP COLUMN `options`;

--
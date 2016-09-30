--

ALTER TABLE `launcher_apps` ADD COLUMN `available_version` varchar(16) NOT NULL DEFAULT '' AFTER `current_version`;

-- //@UNDO

ALTER TABLE `launcher_apps` DROP COLUMN `available_version`;

--
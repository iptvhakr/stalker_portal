--

ALTER TABLE `apps` ADD COLUMN `description` TEXT;
ALTER TABLE `apps` ADD COLUMN `icon_color` VARCHAR(16) NOT NULL DEFAULT '';

-- //@UNDO

ALTER TABLE `apps` DROP COLUMN `description`;
ALTER TABLE `apps` DROP COLUMN `icon_color`;

--
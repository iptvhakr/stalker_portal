--

ALTER TABLE `administrators` ADD COLUMN `language` CHAR(2) NOT NULL DEFAULT '';

--//@UNDO

ALTER TABLE `administrators` DROP INDEX `language`;

--
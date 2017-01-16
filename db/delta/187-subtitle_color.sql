--

ALTER TABLE `users` ADD `subtitle_size` TINYINT NOT NULL DEFAULT 20 AFTER `sec_subtitle_lang`;
ALTER TABLE `users` ADD `subtitle_color` INT NOT NULL DEFAULT 16777215  AFTER `sec_subtitle_lang`;

--//@UNDO

ALTER TABLE `users` DROP COLUMN `subtitle_size`;
ALTER TABLE `users` DROP COLUMN `subtitle_color`;

--
--

ALTER TABLE `image_update_settings` ADD COLUMN `stb_group_id` INT NOT NULL DEFAULT 0;

-- //@UNDO

ALTER TABLE `image_update_settings` DROP COLUMN `stb_group_id`;

--
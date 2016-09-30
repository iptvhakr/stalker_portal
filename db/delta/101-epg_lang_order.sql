--

ALTER TABLE `epg_setting`
ADD COLUMN `lang_code` VARCHAR(20) NULL DEFAULT NULL AFTER `status`;

-- //@UNDO

ALTER TABLE `epg_setting` DROP COLUMN `lang_code`;

--
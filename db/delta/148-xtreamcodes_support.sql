--

ALTER TABLE `ch_links` ADD COLUMN `xtream_codes_support` TINYINT DEFAULT 0;

-- //@UNDO

ALTER TABLE `ch_links` DROP COLUMN `xtream_codes_support`;

--
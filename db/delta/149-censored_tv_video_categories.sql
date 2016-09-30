--

ALTER TABLE `tv_genre` ADD COLUMN `censored` TINYINT NOT NULL  DEFAULT 0;
ALTER TABLE `media_category` ADD COLUMN `censored` TINYINT NOT NULL DEFAULT 0;

UPDATE `tv_genre` SET `censored` = 1 WHERE `title` = 'for adults';
UPDATE `media_category` SET `censored` = 1 WHERE `category_name` = 'adult';

-- //@UNDO

ALTER TABLE `tv_genre` DROP COLUMN `censored`;
ALTER TABLE `media_category` DROP COLUMN `censored`;

--
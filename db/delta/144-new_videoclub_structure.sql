--


CREATE TABLE `video_season` (
  `id`                   INT          NOT NULL AUTO_INCREMENT,
  `video_id`             INT          NOT NULL,
  `season_number`        TINYINT      NOT NULL,
  `season_name`          VARCHAR(255) NULL     DEFAULT '',
  `season_original_name` VARCHAR(255) NULL,
  `season_series`        TINYINT      NOT NULL DEFAULT 0,
  `date_add` DATETIME NOT NULL,
  `date_modify` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET = UTF8;

CREATE TABLE `video_season_series` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `season_id` INT NOT NULL,
  `series_number` TINYINT NOT NULL,
  `series_name` VARCHAR(255) NOT NULL,
  `series_original_name` VARCHAR(255) NOT NULL,
  `series_files` TINYINT NOT NULL DEFAULT 0,
  `date_add` DATETIME NOT NULL,
  `date_modify` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET = UTF8;

CREATE TABLE `video_series_files` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `video_id` INT NOT NULL,
  `series_id` INT NULL DEFAULT NULL,
  `file_type` ENUM('video', 'sub') NOT NULL,
  `protocol` VARCHAR(64) NOT NULL DEFAULT 'http',
  `url` varchar(512) NOT NULL DEFAULT '',
  `file_name` VARCHAR(255) NOT NULL  DEFAULT '',
  `languages` TEXT NOT NULL,
  `quality` SMALLINT NOT NULL,
  `volume_level` TINYINT(4) NOT NULL DEFAULT 0,
  `accessed` TINYINT(1) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 0,
  `date_add` DATETIME NOT NULL,
  `date_modify` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET = UTF8;

ALTER TABLE `video` ADD COLUMN `is_series` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `screenshots` ADD COLUMN `video_episodes` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `video` ADD COLUMN `year_end` INT(11) NOT NULL DEFAULT 0;

-- //@UNDO

DROP TABLE `video_season`;
DROP TABLE `video_season_series`;
DROP TABLE `video_series_files`;
ALTER TABLE `video` DROP COLUMN `is_series`;
ALTER TABLE `video` DROP COLUMN `year_end`;
ALTER TABLE `screenshots` DROP COLUMN `video_episodes`;

--
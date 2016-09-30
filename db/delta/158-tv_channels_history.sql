--

ALTER TABLE `itv` ADD COLUMN `added` DATETIME DEFAULT NULL;

CREATE TABLE `deleted_channels` (
  `id`      INT         NOT NULL AUTO_INCREMENT,
  `ch_id`   INT NOT NULL DEFAULT 0,
  `deleted` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET = UTF8;

-- //@UNDO

ALTER TABLE `itv` DROP COLUMN `added`;

DROP TABLE `deleted_channels`;

--
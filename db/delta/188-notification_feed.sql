--

CREATE TABLE `notification_feed` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(128) NOT NULL DEFAULT '',
  `description` text,
  `link` VARCHAR(128) NOT NULL DEFAULT '',
  `category` VARCHAR(32) NOT NULL DEFAULT '',
  `pub_date` TIMESTAMP,
  `guid` VARCHAR(32) NOT NULL DEFAULT '',
  `read` TINYINT NOT NULL DEFAULT 0,
  `delay_finished_time` TIMESTAMP,
  `added` TIMESTAMP,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET = UTF8;

--//@UNDO

DROP TABLE `notification_feed`;

--
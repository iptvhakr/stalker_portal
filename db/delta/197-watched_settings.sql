--

CREATE TABLE `watched_settings` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `enable_not_ended` TINYINT NOT NULL DEFAULT 1,
  `enable_watched` TINYINT NOT NULL DEFAULT 1,
  `not_ended_history_size` INT NOT NULL DEFAULT 30,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET = UTF8;

INSERT INTO `watched_settings` VALUES (null, 1, 1, 30);

--//@UNDO

DROP TABLE `watched_settings`;

--
--

CREATE TABLE IF NOT EXISTS `diagnostic_info` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `uid` INT NOT NULL DEFAULT 0,
  `changed` TIMESTAMP,
  `info` TEXT,
  INDEX `uid` (`uid`),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET = UTF8;

--//@UNDO

DROP TABLE `diagnostic_info`;

--
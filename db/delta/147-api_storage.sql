--

CREATE TABLE `api_storage` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL DEFAULT 0,
  `key` VARCHAR(128) NULL DEFAULT '',
  `value` TEXT,
  `added` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  `last_access` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET = UTF8;

-- //@UNDO

DROP TABLE `api_storage`;

--
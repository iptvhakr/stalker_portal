--

CREATE TABLE `launcher_apps` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `alias` varchar(128) NOT NULL DEFAULT '',
  `name` varchar(64) NOT NULL DEFAULT '',
  `type` ENUM('app', 'theme', 'plugin', 'core', 'osd', 'launcher', 'system'),
  `category` ENUM('media', 'apps', 'games', 'notification'),
  `current_version` varchar(16) NOT NULL DEFAULT '',
  `description` text,
  `author` VARCHAR(64),
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `is_unique` tinyint(4) NOT NULL DEFAULT 0,
  `url` varchar(128) NOT NULL DEFAULT '',
  `autoupdate` tinyint(4) NOT NULL DEFAULT 0,
  `config` text,
  `localization` text,
  `added` timestamp null default null,
  `updated` timestamp null default null,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET = UTF8;

-- //@UNDO

DROP TABLE `launcher_apps`;

--
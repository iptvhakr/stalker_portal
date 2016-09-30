--

ALTER TABLE `apps` ADD COLUMN `name` VARCHAR(64) NOT NULL DEFAULT '';

CREATE TABLE `github_api_cache` (
  `id` int NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL DEFAULT '',
  `etag` varchar(128) NOT NULL DEFAULT '',
  `data` TEXT,
  `updated` timestamp null default null,
  UNIQUE INDEX `url` (`url`),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

-- //@UNDO

ALTER TABLE `apps` DROP COLUMN `name`;
DROP TABLE `github_api_cache`;

--
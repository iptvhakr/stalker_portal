--

CREATE TABLE `apps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `url` varchar(128) NOT NULL DEFAULT '',
  `current_version` varchar(16) NOT NULL DEFAULT '',
  `status` TINYINT NOT NULL DEFAULT 0, /* 0 - off, 1 - on */
  `options` TEXT,
  `added` timestamp null default null,
  `updated` timestamp null default null,
  UNIQUE INDEX `url` (`url`),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `apps_tos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tos_en` TEXT,
  `accepted` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

INSERT INTO apps_tos (`tos_en`) VALUE ('Terms of Use text'); /* todo: text */

-- //@UNDO

DROP TABLE `apps`;
DROP TABLE `apps_tos`;

--
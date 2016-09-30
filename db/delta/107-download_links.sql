--

CREATE TABLE `download_links` (
  `id` int NOT NULL AUTO_INCREMENT,
  `link_hash` char(32) NOT NULL DEFAULT '',
  `uid` int NOT NULL DEFAULT 0,
  `type` varchar(16) NOT NULL DEFAULT '',
  `media_id` int NOT NULL DEFAULT 0,
  `param1` varchar(32) NOT NULL DEFAULT '',
  `added` timestamp null default null,
  UNIQUE INDEX `link_hash` (`link_hash`),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

-- //@UNDO

DROP TABLE `download_links`;

--
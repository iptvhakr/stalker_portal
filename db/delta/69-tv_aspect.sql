--

CREATE TABLE `tv_aspect` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT 0,
  `aspect` text,
  `changed` timestamp not null,
  UNIQUE KEY `uid` (`uid`),
  PRIMARY KEY (`id`)
);

--//@UNDO

DROP TABLE `tv_aspect`;

--
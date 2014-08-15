--

CREATE TABLE IF NOT EXISTS `dvb_channels`(
  `id` int NOT NULL auto_increment,
  `uid` int NOT NULL default 0,
  `channels` text,
  `modified` datetime,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`uid`)
) DEFAULT CHARSET=utf8;

--//@UNDO

DROP TABLE `dvb_channels`;

--
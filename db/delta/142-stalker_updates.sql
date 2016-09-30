--

CREATE TABLE IF NOT EXISTS `updates`(
  `id` int NOT NULL auto_increment,
  `version` varchar(32) NOT NULL default '',
  `url` varchar(128) NOT NULL default '',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

-- //@UNDO

DROP TABLE `updates`;

--
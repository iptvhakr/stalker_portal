--

CREATE TABLE `settings` (
  `default_template` varchar(255) NOT NULL DEFAULT ''
) DEFAULT CHARSET=utf8;

INSERT INTO `settings` (`default_template`) VALUE ('smart_launcher:magcore-theme-graphite');

--//@UNDO

DROP TABLE `settings`;

--
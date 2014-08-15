--

CREATE TABLE `settings` (
  `default_template` varchar(255) NOT NULL DEFAULT ''
) DEFAULT CHARSET=utf8;

INSERT INTO `settings` (`default_template`) VALUE ('default');

--//@UNDO

DROP TABLE `settings`;

--
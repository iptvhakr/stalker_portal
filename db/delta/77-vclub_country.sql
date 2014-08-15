--

ALTER TABLE `video` ADD `country` varchar(128) NOT NULL default '';

--//@UNDO

ALTER TABLE `video` DROP `country`;

--
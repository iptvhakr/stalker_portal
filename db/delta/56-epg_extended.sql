--

ALTER TABLE `epg` ADD `category` varchar(128) NOT NULL default '';
ALTER TABLE `epg` ADD `director` varchar(128) NOT NULL default '';
ALTER TABLE `epg` ADD `actor` text;

--//@UNDO

ALTER TABLE `epg` DROP `category`;
ALTER TABLE `epg` DROP `director`;
ALTER TABLE `epg` DROP `actor`;

--
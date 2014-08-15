--

ALTER TABLE `epg_setting` ADD `status` tinyint NOT NULL default 1;

--//@UNDO

ALTER TABLE `epg_setting` DROP `status`;

--
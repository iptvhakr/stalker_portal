--

ALTER TABLE `storages` ADD `apache_port` varchar(8) NOT NULL default '88';

CREATE TABLE IF NOT EXISTS `pvr_storages`(
    `id` int NOT NULL auto_increment,
    `ch_id` int NOT NULL default 0,
    `storage_name` varchar(128) NOT NULL default '',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO pvr_storages (ch_id, storage_name) SELECT itv.id, storage_name FROM itv CROSS JOIN storages WHERE storages.status=1 AND for_records=1 AND allow_pvr=1;

--//@UNDO

ALTER TABLE `storages` DROP `apache_port`;

DROP TABLE `pvr_storages`;

--
--
DELETE FROM `user_log` WHERE `time`<FROM_UNIXTIME(UNIX_TIMESTAMP(NOW())-86400);
OPTIMIZE TABLE `user_log`;

TRUNCATE `stream_error`;

DELETE FROM `events` WHERE `eventtime`<now();
OPTIMIZE TABLE `events`;

CREATE TABLE IF NOT EXISTS `ch_links`(
    `id` int NOT NULL auto_increment,
    `ch_id` int NOT NULL default 0,
    `priority` int NOT NULL default 0,
    `url` varchar(255) NOT NULL default '',
    `status` tinyint default 1,
    `use_http_tmp_link` tinyint default 0,
    `wowza_tmp_link` tinyint default 0,
    `user_agent_filter` varchar(32) NOT NULL default '',
    `monitoring_url` varchar(128) NOT NULL default '',
    `use_load_balancing` tinyint default 0,
    `changed` timestamp NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

TRUNCATE `ch_links`;

INSERT INTO `ch_links` (`ch_id`, `url`, `use_http_tmp_link`, `wowza_tmp_link`, `monitoring_url`) SELECT id, cmd, use_http_tmp_link, wowza_tmp_link, monitoring_url  FROM `itv`;

CREATE TABLE IF NOT EXISTS `streaming_servers`(
  `id` int NOT NULL auto_increment,
  `name` varchar(128) not null default '',
  `address` varchar(128) not null default '',
  `max_sessions` int not null default 0,
  `status` tinyint default 1,
  `live_status` tinyint default 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ch_link_on_streamer`(
  `id` int NOT NULL auto_increment,
  `link_id` int not null default 0,
  `streamer_id` tinyint default 0,
  `modified` timestamp not null,
  INDEX (`link_id`),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `users` ADD `now_playing_link_id` int not null default 0;
ALTER TABLE `users` ADD `now_playing_streamer_id` int not null default 0;

--//@UNDO

DROP TABLE `ch_links`;
DROP TABLE `streaming_servers`;
DROP TABLE `ch_link_on_streamer`;

ALTER TABLE `users` DROP `now_playing_link_id`;
ALTER TABLE `users` DROP `now_playing_streamer_id`;

--
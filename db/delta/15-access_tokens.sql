--
CREATE TABLE IF NOT EXISTS `access_tokens`(
    `id` int NOT NULL auto_increment,
    `uid` int NOT NULL default 0,
    `token` varchar(128) NOT NULL default '',
    `expires` timestamp null default null,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`token`),
    UNIQUE KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `clients`(
    `id` int NOT NULL auto_increment,
    `secret` varchar(256) NOT NULL default '',
    `description` text,
    `added` datetime,
    `active` tinyint default 1,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `image_update_settings`(
    `id` int NOT NULL auto_increment,
    `enable` tinyint default 0,
    `require_image_version` varchar(32) NOT NULL default '',
    `require_image_date` varchar(128) NOT NULL default '',
    `image_version_contains` varchar(32) NOT NULL default '',
    `image_description_contains` varchar(128) NOT NULL default '',
    `update_type` varchar(64) NOT NULL default 'http_update',
    `changed` timestamp not null,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE users modify image_version varchar(64) NOT NULL default '';

-- //@UNDO
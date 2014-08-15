--
ALTER TABLE `access_tokens` ADD `refresh_token` varchar(128) not null default '';

CREATE TABLE IF NOT EXISTS `tariff_plan`(
  `id` int NOT NULL auto_increment,
  `external_id` varchar(64) not null default '',
  `name` varchar(64) not null default '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `services_package`(
  `id` int NOT NULL auto_increment,
  `external_id` varchar(64) not null default '',
  `name` varchar(64) not null default '',
  `description` text not null,
  `type` varchar(64) not null default '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `package_in_plan`(
  `id` int NOT NULL auto_increment,
  `package_id` int NOT NULL DEFAULT 0,
  `plan_id` int NOT NULL DEFAULT 0,
  `optional` tinyint default 0,
  `modified` timestamp not null,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `service_in_package`(
  `id` int NOT NULL auto_increment,
  `service_id` varchar(64) not null default '',
  `package_id` int NOT NULL DEFAULT 0,
  `type` varchar(64) not null default '',
  `modified` timestamp not null,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user_package_subscription`(
  `id` int NOT NULL auto_increment,
  `user_id` int NOT NULL DEFAULT 0,
  `package_id` int NOT NULL DEFAULT 0,
  `modified` timestamp not null,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `users` ADD `tariff_plan_id` int not null default 0;
--//@UNDO
ALTER TABLE `access_tokens` DROP `refresh_token`;
ALTER TABLE `users` DROP `tariff_plan_id`;
--
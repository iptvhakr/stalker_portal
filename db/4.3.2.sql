ALTER TABLE `storages` ADD `for_moderator` tinyint default 0;
ALTER TABLE `video` ADD `rtsp_url` varchar(255) NOT NULL default '';
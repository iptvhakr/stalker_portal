ALTER TABLE `storages` ADD `for_moderator` tinyint default 0;
ALTER TABLE `video` ADD `rtsp_url` varchar(255) NOT NULL default '';
ALTER TABLE `rec_files` ADD `uid` int NOT NULL default 0;
ALTER TABLE `karaoke` ADD `rtsp_url` varchar(255) NOT NULL default '';
--

CREATE TABLE `audio_playlists` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL DEFAULT 0,
  `name` varchar(128) NOT NULL DEFAULT '',
  `modified` timestamp DEFAULT 0,
  INDEX `user` (`user_id`),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `audio_playlist_tracks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `playlist_id` int NOT NULL DEFAULT 0,
  `track_id` int NOT NULL DEFAULT 0,
  `added` timestamp DEFAULT 0,
  INDEX `playlist` (`playlist_id`),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

--//@UNDO

DROP TABLE `audio_playlists`;
DROP TABLE `audio_playlist_tracks`;

--
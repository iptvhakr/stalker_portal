--

CREATE TABLE `playback_sessions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `session_id` CHAR(40) NOT NULL DEFAULT '',
  `user_id` INT NOT NULL DEFAULT 0,
  `type` ENUM('tv-channel', 'video', 'karaoke', 'tv-archive', 'audio', 'pvr') NOT NULL,
  `media_id` INT NOT NULL DEFAULT 0,
  `title` VARCHAR(128) NOT NULL DEFAULT '',
  `streamer_id` INT NOT NULL DEFAULT 0,
  `storage_id` INT NOT NULL DEFAULT 0,
  `started` timestamp null default null,
  PRIMARY KEY (`id`),
  INDEX `session_id` (`session_id`),
  INDEX `user_id` (`user_id`)
) DEFAULT CHARSET = UTF8;

-- //@UNDO

DROP TABLE `playback_sessions`;

--
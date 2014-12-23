--

CREATE TABLE `audio_albums` (
  `id` int NOT NULL AUTO_INCREMENT,
  `performer_id` int NOT NULL DEFAULT 0,
  `name` varchar(128) NOT NULL DEFAULT '',
  `year_id` int NOT NULL DEFAULT 0,
  `country_id` int NOT NULL DEFAULT 0,
  `cover` varchar(32) NOT NULL DEFAULT '',
  `added` timestamp DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 0,
  INDEX `performer` (`performer_id`),
  INDEX `country` (`country_id`),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `audio_compositions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `number` int NOT NULL DEFAULT 0,
  `name` varchar(128) NOT NULL DEFAULT '',
  `album_id` int NOT NULL DEFAULT 0,
  `language_id` int NOT NULL DEFAULT 0,
  `protocol` varchar(128) NOT NULL DEFAULT 'custom',
  `url` varchar(128) NOT NULL DEFAULT '',
  `status` tinyint NOT NULL DEFAULT 0,
  `added` timestamp DEFAULT 0,
  INDEX `album` (`album_id`),
  INDEX `language` (`language_id`),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `audio_performers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT '',
  `modified` timestamp DEFAULT 0,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `audio_genres` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT '',
  `modified` timestamp DEFAULT 0,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

INSERT INTO `audio_genres` (`name`, `modified`) VALUES
('Alternative Rock', NOW()),
('Blues', NOW()),
('Children\'s Music', NOW()),
('Jazz', NOW()),
('Disco', NOW()),
('Indie', NOW()),
('Country', NOW()),
('Classical', NOW()),
('Latin Music', NOW()),
('Easy Listening', NOW()),
('Metal', NOW()),
('Hillbilly music', NOW()),
('Pop', NOW()),
('Oldies', NOW()),
('Rock', NOW()),
('Adult Contemporary', NOW()),
('Soul and R&B', NOW()),
('Themes music', NOW()),
('Hip Hop', NOW()),
('Electronic', NOW());

CREATE TABLE `audio_genre` (
  `id` int NOT NULL AUTO_INCREMENT,
  `album_id` int NOT NULL DEFAULT 0,
  `genre_id` int NOT NULL DEFAULT 0,
  UNIQUE `album_genre` (`album_id`, `genre_id`),
  INDEX `album` (`album_id`),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `audio_years` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT '',
  `modified` timestamp DEFAULT 0,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

INSERT INTO `audio_years` (`name`, `modified`) VALUES
('1920s and earlier', NOW()),
('1930s', NOW()),
('1940s', NOW()),
('1950s', NOW()),
('1960s', NOW()),
('1970s', NOW()),
('1980s', NOW()),
('1990s', NOW()),
('2000s', NOW()),
('2010', NOW()),
('2011', NOW()),
('2012', NOW()),
('2013', NOW()),
('2014', NOW()),
('2015', NOW());

CREATE TABLE `audio_languages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT '',
  `modified` timestamp DEFAULT 0,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

--//@UNDO

DROP TABLE `audio_albums`;
DROP TABLE `audio_compositions`;
DROP TABLE `audio_performers`;
DROP TABLE `audio_genres`;
DROP TABLE `audio_genre`;
DROP TABLE `audio_years`;
DROP TABLE `audio_languages`;

--
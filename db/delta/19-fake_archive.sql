--
ALTER TABLE `storages` ADD `fake_tv_archive` tinyint default 0;

ALTER TABLE `video` ADD `kinopoisk_id` varchar(64) not null default '';
ALTER TABLE `video` ADD `rating_kinopoisk` varchar(64) not null default '';
ALTER TABLE `video` ADD `rating_count_kinopoisk` varchar(64) not null default '';
ALTER TABLE `video` ADD `rating_imdb` varchar(64) not null default '';
ALTER TABLE `video` ADD `rating_count_imdb` varchar(64) not null default '';

ALTER TABLE `video` ADD `rating_last_update` timestamp default 0;

CREATE TEMPORARY TABLE `tmp_itv_subscription` AS SELECT * FROM `itv_subscription` GROUP BY `uid`;
TRUNCATE `itv_subscription`;
ALTER TABLE `itv_subscription` DROP INDEX `uid`;
ALTER TABLE `itv_subscription` ADD UNIQUE INDEX (`uid`);
INSERT INTO `itv_subscription` SELECT * FROM `tmp_itv_subscription`;
DROP TABLE `tmp_itv_subscription`;

--//@UNDO

ALTER TABLE `storages` DROP `fake_tv_archive`;

ALTER TABLE `video` DROP `kinopoisk_id`;
ALTER TABLE `video` DROP `rating_kinopoisk`;
ALTER TABLE `video` DROP `rating_count_kinopoisk`;
ALTER TABLE `video` DROP `rating_imdb`;
ALTER TABLE `video` DROP `rating_count_imdb`;

ALTER TABLE `video` DROP `rating_last_update`;

--
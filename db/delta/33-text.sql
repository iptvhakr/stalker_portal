--

ALTER TABLE `video` MODIFY `series` text;
ALTER TABLE `video` MODIFY `rate` text;

ALTER TABLE `itv` MODIFY `descr` text;

ALTER TABLE `events` MODIFY `msg` text;

ALTER TABLE `fav_itv` MODIFY `fav_ch` text;
ALTER TABLE `fav_vclub` MODIFY `fav_video` text;

ALTER TABLE `moderators_history` MODIFY `comment` text;

ALTER TABLE `itv_subscription` MODIFY `sub_ch` text;
ALTER TABLE `itv_subscription` MODIFY `bonus_ch` text;

ALTER TABLE `rss_cache_weather` MODIFY `content` text;
ALTER TABLE `rss_cache_horoscope` MODIFY `content` text;

ALTER TABLE `anec` MODIFY `anec_body` text;
ALTER TABLE `course_cache` MODIFY `content` text;

ALTER TABLE `storage_cache` MODIFY `storage_data` text;

ALTER TABLE `user_modules` MODIFY `restricted` text;
ALTER TABLE `user_modules` MODIFY `disabled` text;

ALTER TABLE `censored_channels` MODIFY `list` text;
ALTER TABLE `censored_channels` MODIFY `exclude` text;

ALTER TABLE `storages_failure` MODIFY `description` text;

ALTER TABLE `developer_api_key` MODIFY `comment` text;

ALTER TABLE `user_downloads` MODIFY `downloads` text;

ALTER TABLE `media_favorites` MODIFY `favorites` text;

ALTER TABLE `services_package` MODIFY `description` text;

ALTER TABLE `users` MODIFY `comment` text;

ALTER TABLE `itv` ADD `allow_pvr` tinyint default 0;

UPDATE `itv` SET allow_pvr=1 WHERE mc_cmd!='';

ALTER TABLE `video_log` ADD `video_name` varchar(128) not null default '';

--//@UNDO

ALTER TABLE `itv` DROP `allow_pvr`;
ALTER TABLE `video_log` DROP `video_name`;

--
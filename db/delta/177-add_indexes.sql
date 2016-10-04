--

ALTER TABLE `played_video` ADD INDEX `uid_index` (`uid` ASC);
ALTER TABLE `played_itv` ADD INDEX `uid_index` (`uid` ASC);
ALTER TABLE `played_tv_archive` ADD INDEX `uid_index` (`uid` ASC);
ALTER TABLE `media_claims_log` ADD INDEX `uid_index` (`uid` ASC);
ALTER TABLE `played_timeshift` ADD INDEX `uid_index` (`uid` ASC);
ALTER TABLE `readed_anec` ADD INDEX `mac_index` (`mac` ASC);

--//@UNDO

ALTER TABLE `played_video` DROP INDEX `uid_index`;
ALTER TABLE `played_itv` DROP INDEX `uid_index`;
ALTER TABLE `played_tv_archive` DROP INDEX `uid_index`;
ALTER TABLE `media_claims_log` DROP INDEX `uid_index`;
ALTER TABLE `played_timeshift` DROP INDEX `uid_index`;
ALTER TABLE `readed_anec` DROP INDEX `mac_index`;

--
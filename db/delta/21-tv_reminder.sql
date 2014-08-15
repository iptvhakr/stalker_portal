--
ALTER TABLE `epg` ADD `real_id` varchar(64) not null default '';
UPDATE `epg` SET `real_id`=CONCAT(ch_id, '_', UNIX_TIMESTAMP(`time`)), `time`=`time`;
ALTER TABLE `tv_reminder` ADD `tv_program_real_id` varchar(64) not null default '';
UPDATE `tv_reminder` SET `tv_program_real_id` = (SELECT `real_id` FROM `epg` WHERE `id`=`tv_program_id`);
DELETE FROM `tv_reminder` WHERE `tv_program_real_id`='';
ALTER TABLE `users_rec` ADD `program_real_id` varchar(64) not null default '';
--//@UNDO
ALTER TABLE `epg` DROP `real_id`;
ALTER TABLE `tv_reminder` DROP `tv_program_real_id`;
ALTER TABLE `users_rec` DROP `program_real_id`;
--
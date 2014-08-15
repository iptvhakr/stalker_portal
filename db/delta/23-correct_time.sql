--
ALTER TABLE `itv` ADD `correct_time` int not null default 0;
--//@UNDO
ALTER TABLE `itv` DROP `correct_time`;
--
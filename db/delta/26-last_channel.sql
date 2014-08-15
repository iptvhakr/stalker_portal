--
ALTER TABLE `last_id` ADD `uid` int not null default 0;
UPDATE `last_id` SET `uid`=(SELECT id FROM `users` where mac=ident limit 1);
ALTER TABLE `last_id` DROP KEY `ident`;
--//@UNDO

ALTER TABLE `last_id` DROP `uid`;
ALTER TABLE `last_id` ADD UNIQUE KEY (`ident`);

--
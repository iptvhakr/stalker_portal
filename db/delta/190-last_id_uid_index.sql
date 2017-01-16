--

ALTER TABLE `last_id` ADD INDEX uid (`uid`);

--//@UNDO

ALTER TABLE `last_id` DROP INDEX uid;

--
--
ALTER TABLE `users` ADD `phone` varchar(64) NOT NULL default '';
--//@UNDO

ALTER TABLE `users` DROP `phone`;

--

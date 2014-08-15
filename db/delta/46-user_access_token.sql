--

ALTER TABLE `users` ADD `access_token` varchar(64) NOT NULL default '';

--//@UNDO

ALTER TABLE `users` DROP `access_token`;

--
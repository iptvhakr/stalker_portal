--

ALTER TABLE `users` ADD `country` varchar(8) NOT NULL default '';

--//@UNDO

ALTER TABLE `users` DROP `country`;

--
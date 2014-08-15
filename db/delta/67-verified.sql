--

ALTER TABLE `users` ADD `verified` tinyint NOT NULL;

--//@UNDO

ALTER TABLE `users` DROP `verified`;

--
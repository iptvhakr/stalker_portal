--

ALTER TABLE `users` ADD `plasma_saving` tinyint NOT NULL default 0;

--//@UNDO

ALTER TABLE `users` DROP `plasma_saving`;

--
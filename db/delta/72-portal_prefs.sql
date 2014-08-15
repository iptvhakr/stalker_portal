--

ALTER TABLE `users` ADD `show_after_loading` varchar(16) NOT NULL default '';
ALTER TABLE `users` ADD `play_in_preview_by_ok` tinyint DEFAULT NULL;

--//@UNDO

ALTER TABLE `users` DROP `show_after_loading`;
ALTER TABLE `users` DROP `play_in_preview_by_ok`;

--
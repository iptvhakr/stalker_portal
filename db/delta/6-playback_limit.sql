--
ALTER TABLE `users` ADD `playback_limit` tinyint default 5;
ALTER TABLE `users` ADD `screensaver_delay` tinyint default 10;
--//@UNDO

ALTER TABLE `users` DROP `playback_limit`;
ALTER TABLE `users` DROP `screensaver_delay`;

--
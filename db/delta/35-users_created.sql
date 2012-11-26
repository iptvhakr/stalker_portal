--

ALTER TABLE `users` ADD `just_started` tinyint default 0;
ALTER TABLE `users` ADD `last_watchdog` timestamp;
ALTER TABLE `users` ADD `created` timestamp;

--//@UNDO
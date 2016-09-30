--

ALTER TABLE `users` ADD `just_started` tinyint default 0;
ALTER TABLE `users` ADD `last_watchdog` timestamp NULL DEFAULT NULL;
ALTER TABLE `users` ADD `created` timestamp NULL DEFAULT NULL;

-- //@UNDO

ALTER TABLE `users` DROP `just_started`;
ALTER TABLE `users` DROP `last_watchdog`;
ALTER TABLE `users` DROP `created`;

--
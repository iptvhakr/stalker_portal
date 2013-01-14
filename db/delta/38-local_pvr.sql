--

ALTER TABLE `users_rec` ADD `local` tinyint default 0;
ALTER TABLE `users_rec` ADD `file` varchar(255) NOT NULL default '';
ALTER TABLE `users_rec` ADD `internal_id` varchar(32) NOT NULL default '';
ALTER TABLE `itv` ADD `allow_local_pvr` tinyint default 1;

--//@UNDO
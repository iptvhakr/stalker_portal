--

ALTER TABLE `users_rec` ADD `local` tinyint default 0;
ALTER TABLE `users_rec` ADD `file` varchar(255) NOT NULL default '';
ALTER TABLE `users_rec` ADD `internal_id` varchar(32) NOT NULL default '';
ALTER TABLE `itv` ADD `allow_local_pvr` tinyint default 1;

--//@UNDO

ALTER TABLE `users_rec` DROP `local`;
ALTER TABLE `users_rec` DROP `file`;
ALTER TABLE `users_rec` DROP `internal_id`;
ALTER TABLE `itv` DROP `allow_local_pvr`;

--
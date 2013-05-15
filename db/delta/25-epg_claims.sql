--
ALTER TABLE `media_claims` ADD `no_epg` int not null default 0;
ALTER TABLE `media_claims` ADD `wrong_epg` int not null default 0;
ALTER TABLE `daily_media_claims` ADD `no_epg` int not null default 0;
ALTER TABLE `daily_media_claims` ADD `wrong_epg` int not null default 0;
--//@UNDO
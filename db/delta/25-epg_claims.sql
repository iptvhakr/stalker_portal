--
ALTER TABLE `media_claims` ADD `no_epg` int not null default 0;
ALTER TABLE `media_claims` ADD `wrong_epg` int not null default 0;
ALTER TABLE `daily_media_claims` ADD `no_epg` int not null default 0;
ALTER TABLE `daily_media_claims` ADD `wrong_epg` int not null default 0;
--//@UNDO
ALTER TABLE `media_claims` DROP `no_epg`;
ALTER TABLE `media_claims` DROP `wrong_epg`;
ALTER TABLE `daily_media_claims` DROP `no_epg`;
ALTER TABLE `daily_media_claims` DROP `wrong_epg`;
--
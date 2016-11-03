--

ALTER TABLE `ch_links` ADD COLUMN `edgecast_auth_support` TINYINT DEFAULT 0;

--//@UNDO

ALTER TABLE `ch_links` DROP COLUMN `edgecast_auth_support`;

--
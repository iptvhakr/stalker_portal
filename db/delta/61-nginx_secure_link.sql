--

ALTER TABLE `itv` ADD `nginx_secure_link` tinyint default 0;
ALTER TABLE `ch_links` ADD `nginx_secure_link` tinyint default 0;

--//@UNDO

ALTER TABLE `itv` DROP `nginx_secure_link`;
ALTER TABLE `ch_links` DROP `nginx_secure_link`;

--
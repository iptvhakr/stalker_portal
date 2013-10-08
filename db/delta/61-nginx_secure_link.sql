--

ALTER TABLE `itv` ADD `nginx_secure_link` tinyint default 0;
ALTER TABLE `ch_links` ADD `nginx_secure_link` tinyint default 0;

--//@UNDO
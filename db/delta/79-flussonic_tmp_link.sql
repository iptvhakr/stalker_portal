--

ALTER TABLE `ch_links` ADD `flussonic_tmp_link` tinyint default 0;

--//@UNDO

ALTER TABLE `ch_links` DROP `flussonic_tmp_link`;

--
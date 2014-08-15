--
ALTER TABLE `video` ADD `for_sd_stb` tinyint default 0;
--//@UNDO

ALTER TABLE `video` DROP `for_sd_stb`;

--
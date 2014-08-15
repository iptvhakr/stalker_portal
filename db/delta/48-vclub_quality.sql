--

ALTER TABLE `video` ADD `high_quality` tinyint NOT NULL DEFAULT 1;

--//@UNDO

ALTER TABLE `video` DROP `high_quality`;

--
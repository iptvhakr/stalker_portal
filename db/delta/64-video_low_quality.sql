--

ALTER TABLE `video` ADD `low_quality` tinyint NOT NULL DEFAULT 0;

--//@UNDO

ALTER TABLE `video` DROP `low_quality`;

--
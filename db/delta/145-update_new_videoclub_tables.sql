--

ALTER TABLE `video_series_files` CHANGE COLUMN `quality` `quality` VARCHAR(16) NOT NULL DEFAULT '' ;

-- //@UNDO

ALTER TABLE `video_series_files` CHANGE COLUMN `quality` `quality` SMALLINT NOT NULL;

--
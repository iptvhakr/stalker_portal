--

ALTER TABLE `played_video` ADD INDEX video_id_playtime(`video_id`, `playtime`);

--//@UNDO

ALTER TABLE `played_video` DROP INDEX video_id_playtime;

--
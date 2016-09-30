--
ALTER TABLE `users` MODIFY `audio_out` int NOT NULL default 1;
UPDATE `users` SET audio_out=1 WHERE audio_out=0;
-- //@UNDO
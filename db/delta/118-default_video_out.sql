--

ALTER TABLE `users` MODIFY `video_out` varchar(8) NOT NULL DEFAULT '';
UPDATE `users` SET video_out='';

-- //@UNDO


--
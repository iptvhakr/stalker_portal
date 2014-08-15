--

ALTER TABLE `users` ADD `video_clock` varchar(9) NOT NULL default 'Off';

--//@UNDO

ALTER TABLE `users` DROP `video_clock`;

--
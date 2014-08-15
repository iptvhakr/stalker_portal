--

ALTER TABLE `users` ADD `pri_audio_lang` varchar(4) NOT NULL default '';
ALTER TABLE `users` ADD `sec_audio_lang` varchar(4) NOT NULL default '';
ALTER TABLE `users` ADD `pri_subtitle_lang` varchar(4) NOT NULL default '';
ALTER TABLE `users` ADD `sec_subtitle_lang` varchar(4) NOT NULL default '';

--//@UNDO

ALTER TABLE `users` DROP `pri_audio_lang`;
ALTER TABLE `users` DROP `sec_audio_lang`;
ALTER TABLE `users` DROP `pri_subtitle_lang`;
ALTER TABLE `users` DROP `sec_subtitle_lang`;

--
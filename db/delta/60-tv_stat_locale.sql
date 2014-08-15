--

ALTER TABLE `played_itv` ADD `user_locale` varchar(64) NOT NULL default '';

UPDATE `played_itv`, `users` SET user_locale = locale WHERE played_itv.uid = users.id;

--//@UNDO

ALTER TABLE `played_itv` DROP `user_locale`;

--
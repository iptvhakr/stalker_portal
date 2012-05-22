--
ALTER TABLE `access_tokens` ADD `refresh_token` varchar(128) not null default '';
--//@UNDO
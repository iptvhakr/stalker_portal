--

ALTER TABLE `storages` ADD `user_agent_filter` varchar(32) NOT NULL default '';

--//@UNDO

ALTER TABLE `storages` DROP `user_agent_filter`;

--
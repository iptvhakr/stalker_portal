--

ALTER TABLE `moderator_tasks` ADD INDEX `media_id_idx` (`media_id` ASC);

-- //@UNDO

ALTER TABLE `moderator_tasks` DROP INDEX `media_id_idx`;

--
--

ALTER TABLE `adm_grp_action_access` ADD COLUMN `blocked` TINYINT NOT NULL DEFAULT 0;
UPDATE `adm_grp_action_access` SET `blocked` = 1 WHERE `controller_name` = 'video-club';

-- //@UNDO

ALTER TABLE `adm_grp_action_access` DROP COLUMN `blocked`;

--
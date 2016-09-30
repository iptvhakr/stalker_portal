--

CREATE TABLE `agaa_tmp` AS SELECT * FROM `adm_grp_action_access` WHERE `controller_name` = "video-club";

ALTER TABLE `agaa_tmp` DROP COLUMN `id`;
UPDATE `agaa_tmp` SET `controller_name` = "new-video-club";

CREATE TABLE `agaa_tmp_2` AS SELECT * FROM `agaa_tmp` WHERE `action_name` = "edit-video" AND `edit_access` = 1;

UPDATE `agaa_tmp_2` SET `is_ajax` = 1, `view_access` = 1, `edit_access` = 0, `action_access` = 1;
UPDATE `agaa_tmp_2` SET `action_name` = 'get-video-season-list-json', `description` = 'Getting list of seasons and episodes of video';
INSERT INTO `agaa_tmp` SELECT * FROM `agaa_tmp_2`;
UPDATE `agaa_tmp_2` SET `action_name` = 'get-video-files-list-json', `description` = 'Getting list of files of episode';
INSERT INTO `agaa_tmp` SELECT * FROM `agaa_tmp_2`;
UPDATE `agaa_tmp_2` SET `action_name` = 'get-one-video-file-json', `description` = 'Getting info by one file of episode';
INSERT INTO `agaa_tmp` SELECT * FROM `agaa_tmp_2`;

UPDATE `agaa_tmp_2` SET `is_ajax` = 1, `view_access` = 0, `edit_access` = 1, `action_access` = 1;

UPDATE `agaa_tmp_2` SET `action_name` = 'save-video-files', `description` = 'Adding and editing files of episode';
INSERT INTO `agaa_tmp` SELECT * FROM `agaa_tmp_2`;
UPDATE `agaa_tmp_2` SET `action_name` = 'toggle-video-accessed', `description` = 'Change accessed status of one file of episode';
INSERT INTO `agaa_tmp` SELECT * FROM `agaa_tmp_2`;
UPDATE `agaa_tmp_2` SET `action_name` = 'save-season-series-names', `description` = 'Change names and original names for episodes and seasons';
INSERT INTO `agaa_tmp` SELECT * FROM `agaa_tmp_2`;
UPDATE `agaa_tmp_2` SET `action_name` = 'remove-video-data', `description` = 'Deleting info about one video file from database';
INSERT INTO `agaa_tmp` SELECT * FROM `agaa_tmp_2`;
UPDATE `agaa_tmp_2` SET `action_name` = 'add-video-season-series', `description` = 'Adding episodes into season';
INSERT INTO `agaa_tmp` SELECT * FROM `agaa_tmp_2`;
UPDATE `agaa_tmp_2` SET `action_name` = 'add-video-season', `description` = 'Adding seasons into video';
INSERT INTO `agaa_tmp` SELECT * FROM `agaa_tmp_2`;
DROP TABLE `agaa_tmp_2`;

ALTER TABLE `agaa_tmp` ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
SELECT @max := MAX(`id`) FROM `adm_grp_action_access`;
UPDATE `agaa_tmp` SET `id` = `id` + @max;

INSERT INTO `adm_grp_action_access` SELECT * FROM `agaa_tmp`;
DROP TABLE `agaa_tmp`;

-- //@UNDO

DELETE FROM `adm_grp_action_access`  WHERE `controller_name` = "new-video-club";
DROP TABLE IF EXISTS `agaa_tmp`;
DROP TABLE IF EXISTS `agaa_tmp_2`;

--
--

ALTER TABLE `audio_compositions` ADD COLUMN `duration` INT DEFAULT 0;
INSERT INTO `adm_grp_action_access`
          (`controller_name`,         `action_name`, `is_ajax`, `description`)
VALUES    ('audio-club',      'get-media-info-json',         1, 'Getting audio-info from source');

--//@UNDO

ALTER TABLE `audio_compositions` DROP COLUMN `duration`;

--
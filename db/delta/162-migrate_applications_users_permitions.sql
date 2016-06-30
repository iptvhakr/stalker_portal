--

CREATE TABLE `agaa_tmp` AS SELECT * FROM `adm_grp_action_access` WHERE `controller_name` = "application-catalog" AND (`action_name` = '' OR `action_name` = 'index');

ALTER TABLE `agaa_tmp` DROP COLUMN `id`;
UPDATE `agaa_tmp` SET `action_name` = 'application-list';

ALTER TABLE `agaa_tmp` ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
SELECT @max := MAX(`id`) FROM `adm_grp_action_access`;
UPDATE `agaa_tmp` SET `id` = `id` + @max;

INSERT INTO `adm_grp_action_access` SELECT * FROM `agaa_tmp`;
DROP TABLE `agaa_tmp`;

--//@UNDO

DELETE FROM `adm_grp_action_access`  WHERE `controller_name` = "application-catalog";
DROP TABLE IF EXISTS `agaa_tmp`;

--
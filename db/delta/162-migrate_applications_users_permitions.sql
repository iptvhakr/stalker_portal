--

CREATE TABLE `agaa_tmp` AS SELECT * FROM `adm_grp_action_access` WHERE `controller_name` = "application-catalog" AND (`action_name` = '' OR `action_name` = 'index');

ALTER TABLE `agaa_tmp` DROP COLUMN `id`;
UPDATE `agaa_tmp` SET `action_name` = 'application-list';

ALTER TABLE `agaa_tmp` ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
SELECT @max := MAX(`id`) FROM `adm_grp_action_access`;
UPDATE `agaa_tmp` SET `id` = `id` + @max;

INSERT INTO `adm_grp_action_access` SELECT * FROM `agaa_tmp`;
DROP TABLE `agaa_tmp`;

INSERT INTO `adm_grp_action_access`
          (`controller_name`,         `action_name`,                        `is_ajax`, `description`)
VALUES    ('application-catalog',    'smart-application-list',                      0, 'Application for SmartLauncher. List of applications'),
          ('application-catalog',    'smart-application-detail',                    0, 'Application for SmartLauncher. Application info and list of available application versions'),
          ('application-catalog',    'smart-application-list-json',                 1, 'Application for SmartLauncher. List of applications  by page + filters'),
          ('application-catalog',    'smart-application-get-data-from-repo',        1, 'Application for SmartLauncher. Getting application info from repository'),
          ('application-catalog',    'smart-application-add',                       1, 'Application for SmartLauncher. Add new application by package name'),
          ('application-catalog',    'smart-application-version-list-json',         1, 'Application for SmartLauncher. List of application versions'),
          ('application-catalog',    'smart-application-version-install',           1, 'Application for SmartLauncher. Install available application version'),
          ('application-catalog',    'smart-application-version-delete',            1, 'Application for SmartLauncher. Delete installed application version'),
          ('application-catalog',    'smart-application-toggle-state',              1, 'Application for SmartLauncher. Enable and disable application'),
          ('application-catalog',    'smart-application-delete',                    1, 'Application for SmartLauncher. Delete application'),
          ('application-catalog',    'smart-application-reset-all',                 1, 'Application for SmartLauncher. Uninstalling all old applications and installation of latest base apps');

-- //@UNDO

DROP TABLE IF EXISTS `agaa_tmp`;

--
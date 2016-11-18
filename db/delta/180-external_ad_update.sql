--

DROP TABLE `ext_adv_register`;
ALTER TABLE `ext_adv_sources` DROP COLUMN `owner`;
ALTER TABLE `ext_adv_companies` DROP COLUMN `old_skin_pos`;
ALTER TABLE `ext_adv_companies` DROP COLUMN `smart_skin_pos`;
ALTER TABLE `ext_adv_companies` CHANGE COLUMN `platform` `platform` ENUM('stb', 'ios', 'android', 'smarttv');

INSERT INTO `apps_tos` (`tos_en`, `accepted`, `alias`) VALUES ('', 0, 'external_ad');
UPDATE `apps_tos` SET `tos_en` = '
<br>
<h1>
<span>Conditions of Use for VertaMedia ads</span>
</h1>
'
WHERE `alias` = 'external_ad';

CREATE TABLE `ext_adv_campaigns_position` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `campaigns_id` INT NOT NULL,
  `position_code` INT NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET = UTF8;

CREATE TABLE `ext_adv_positions`(
  `id` INT NOT NULL AUTO_INCREMENT,
  `platform` ENUM('stb', 'ios', 'android', 'smarttv'),
  `position_code` INT NOT NULL,
  `label` VARCHAR(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET = UTF8;

INSERT INTO `ext_adv_positions` (`platform`, `position_code`, `label`)
    VALUES  ('stb', 101, 'Start applications'),
            ('stb', 102, 'Before starting the film in Video Club'),
            ('stb', 103, 'During movie playback in Video Club'),
            ('stb', 201, 'Start applications'),
            ('stb', 202, 'Before starting the film in Video Club'),
            ('stb', 203, 'During movie playback in Video Club'),
            ('android', 301, 'Application Startup'),
            ('android', 302, 'Before starting TV section'),
            ('android', 303, 'Before starting Video Club section'),
            ('android', 304, 'Before starting Radio section'),
            ('android', 305, 'Before starting the film in Video Club'),
            ('android', 306, 'During movie playback in Video Club'),
            ('ios', 401, 'Application Startup'),
            ('ios', 402, 'Before starting TV section'),
            ('ios', 403, 'Before starting Video Club section'),
            ('ios', 404, 'Before starting Radio section'),
            ('ios', 405, 'Before starting the film in Video Club'),
            ('ios', 406, 'During movie playback in Video Club'),
            ('smarttv', 501, 'Application Startup'),
            ('smarttv', 502, 'Before starting the film in Video Club'),
            ('smarttv', 503, 'During movie playback in Video Club');

DELETE FROM `adm_grp_action_access` WHERE `controller_name` = 'external-advertising' AND `action_name` = 'request-new-source';

ALTER TABLE `ext_adv_companies` RENAME TO `ext_adv_campaigns`;

--//@UNDO

ALTER TABLE `ext_adv_campaigns` RENAME TO `ext_adv_companies`;

CREATE TABLE `ext_adv_register` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL DEFAULT '',
  `phone` varchar(64) NOT NULL DEFAULT '',
  `email` varchar(64) NOT NULL DEFAULT '',
  `region` varchar(64) NOT NULL DEFAULT '',
  `added`  timestamp NULL DEFAULT NULL,
  `updated`  timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET = UTF8;

ALTER TABLE `ext_adv_sources` ADD COLUMN `owner`  INT NOT NULL DEFAULT 0;
ALTER TABLE `ext_adv_companies` ADD COLUMN `old_skin_pos` tinyint(3) UNSIGNED ZEROFILL NOT NULL DEFAULT 0;
ALTER TABLE `ext_adv_companies` ADD COLUMN `smart_skin_pos` tinyint(3) UNSIGNED ZEROFILL NOT NULL DEFAULT 0;
ALTER TABLE `ext_adv_companies` CHANGE COLUMN `platform` `platform` ENUM('settopbox', 'ios', 'android', 'smarttv');

DELETE FROM `apps_tos` WHERE `alias` = 'external_ad';

DROP TABLE `ext_adv_campaigns_position`;
DROP TABLE `ext_adv_positions`;

INSERT INTO `adm_grp_action_access`
        (`controller_name`,      `action_name`,       `is_ajax`, `description`)
VALUES
        ('external-advertising', 'request-new-source',        1, 'Make request for getting new source for ad-company');

--
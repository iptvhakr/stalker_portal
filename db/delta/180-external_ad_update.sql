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
    VALUES  ('stb', 101, 'Before launching the application'),
            ('stb', 102, 'Before launching the movie in the VideoClub'),
            ('stb', 103, 'During the movie playback in the VideoClub'),
            ('stb', 201, 'Before launching the application'),
            ('stb', 202, 'Before launching the movie in the VideoClub'),
            ('stb', 203, 'During the movie playback in the VideoClub'),
            ('android', 301, 'Before launching the application'),
            ('android', 302, 'Before launching a TV section'),
            ('android', 303, 'Before launching a VideoClub section'),
            ('android', 304, 'Before launching a Radio section'),
            ('android', 305, 'Before launching the movie in the VideoClub'),
            ('android', 306, 'During the movie playback in the VideoClub'),
            ('ios', 401, 'Before launching the application'),
            ('ios', 402, 'Before launching a TV section'),
            ('ios', 403, 'Before launching a VideoClub section'),
            ('ios', 404, 'Before launching a Radio section'),
            ('ios', 405, 'Before launching the movie in the VideoClub'),
            ('ios', 406, 'During the movie playback in the VideoClub'),
            ('smarttv', 501, 'Before launching the application'),
            ('smarttv', 502, 'Before launching the movie in the VideoClub'),
            ('smarttv', 503, 'During the movie playback in the VideoClub');

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
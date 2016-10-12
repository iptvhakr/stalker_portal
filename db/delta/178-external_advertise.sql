--

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

CREATE TABLE `ext_adv_sources` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `owner` INT NOT NULL,
  `source` varchar(32) NULL DEFAULT '',
  `added`  timestamp NULL DEFAULT NULL,
  `updated`  timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET = UTF8;

CREATE TABLE `ext_adv_companies` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `source` INT NOT NULL,
  `platform` ENUM('settopbox', 'ios', 'android', 'smarttv'),
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `old_skin_pos` tinyint(3) UNSIGNED ZEROFILL NOT NULL DEFAULT 0,
  `smart_skin_pos` tinyint(3) UNSIGNED ZEROFILL NOT NULL DEFAULT 0,
  `added`  timestamp NULL DEFAULT NULL,
  `updated`  timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET = UTF8;

INSERT INTO `adm_grp_action_access`
          (`controller_name`,              `action_name`,               `is_ajax`, `description`)
VALUES    ('external-advertising',         '',                                  0, 'External Advertising'),
          ('external-advertising',         'verta-media-company-list',          0, 'List of companies'),
          ('external-advertising',         'verta-media-register',              0, 'Register form for new client'),
          ('external-advertising',         'verta-media-company-add',           0, 'Adding new ad-company'),
          ('external-advertising',         'verta-media-company-edit',          0, 'Editing exists ad-company'),
          ('external-advertising',         'verta-media-settings',              0, 'Form for viewing, adding, editing and requesting sources for ad-companies'),
          ('external-advertising',         'verta-media-company-list-json',     1, 'List of companies by page + filters'),
          ('external-advertising',         'toggle-company-state',              1, 'Toggle company state'),
          ('external-advertising',         'delete-company',                    1, 'Deleting exists ad-company'),
          ('external-advertising',         'request-new-source',                1, 'Make request for getting new source for ad-company');

--//@UNDO

DROP TABLE `ext_adv_register`;
DROP TABLE `ext_adv_sources`;
DROP TABLE `ext_adv_companies`;

--
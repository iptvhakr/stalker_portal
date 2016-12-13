--

CREATE TABLE `support_info` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `lang` ENUM('ru', 'en', 'uk', 'pl', 'el', 'nl', 'it', 'de', 'sk', 'es') NOT NULL,
  `content` TEXT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET = UTF8;

INSERT INTO `adm_grp_action_access`
          (`controller_name`,          `action_name`, `is_ajax`, `description`)
VALUES    ('users',                   'support-info',         0, 'Form adding support info'),
          ('users',            'get-support-content',         1, 'Obtaining support info for the specified language'),
          ('users',           'save-support-content',         1, 'Saving support info for the specified language');

--//@UNDO

DROP TABLE `support_info`;

--
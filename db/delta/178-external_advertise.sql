--

CREATE TABLE `ext_adv_register` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL DEFAULT '',
  `phone` varchar(64) NOT NULL DEFAULT '',
  `email` varchar(64) NOT NULL DEFAULT '',
  `region` tinyint(3) NULL,
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

--//@UNDO

DROP TABLE `ext_adv_register`;
DROP TABLE `ext_adv_sources`;
DROP TABLE `ext_adv_companies`;

--
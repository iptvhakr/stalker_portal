--

CREATE TABLE IF NOT EXISTS `admin_dropdown_attributes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `admin_id` INT NOT NULL,
  `controller_name` VARCHAR(100) NOT NULL,
  `action_name` VARCHAR(100) NOT NULL,
  `dropdown_attributes` TEXT NOT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `itv` ADD COLUMN `locked` TINYINT NOT NULL DEFAULT 0;

--//@UNDO

DROP TABLE IF EXISTS `admin_dropdown_attributes`;
ALTER TABLE `itv` DROP `locked`;

--
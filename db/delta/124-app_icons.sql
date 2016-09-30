--

ALTER TABLE `apps` ADD COLUMN `icons` TEXT;

INSERT INTO `apps` (`url`, `added`, `alias`, `name`) VALUES
  ('https://github.com/StalkerApps/vk.music', NOW(), 'vk.music', 'vk.music'),
  ('https://github.com/StalkerApps/exua', NOW(), 'ex.ua', 'ex.ua');

-- //@UNDO

ALTER TABLE `apps` DROP COLUMN `icons`;

TRUNCATE `apps`;

--
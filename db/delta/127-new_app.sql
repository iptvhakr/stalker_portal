--

INSERT INTO `apps` (`url`, `added`, `alias`, `name`) VALUES
  ('https://github.com/StalkerApps/vk.video', NOW(), 'vk.video', 'vk.video');

-- //@UNDO

DELETE FROM `apps` WHERE `name`='vk.video';

--
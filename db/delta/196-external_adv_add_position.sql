--

INSERT INTO `ext_adv_positions` (`platform`, `position_code`, `label`)
VALUES  ('stb', 104, 'Before starting the TV channel'),
  ('stb', 204, 'Before starting the TV channel'),
  ('stb', 205, 'During a TV channel playback');

--//@UNDO

DELETE FROM `ext_adv_positions` WHERE `position_code` = 104;
DELETE FROM `ext_adv_positions` WHERE `position_code` = 204;
DELETE FROM `ext_adv_positions` WHERE `position_code` = 205;

--
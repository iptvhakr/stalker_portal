--

ALTER TABLE `ext_adv_campaigns_position` ADD COLUMN `skip_after` TINYINT NOT NULL DEFAULT 7;

--//@UNDO

ALTER TABLE `ext_adv_campaigns_position` DROP COLUMN `skip_after`;

--
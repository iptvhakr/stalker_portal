--

ALTER TABLE `ext_adv_campaigns_position` ADD COLUMN `blocks` TINYINT NOT NULL DEFAULT 1;

--//@UNDO

ALTER TABLE `ext_adv_campaigns_position` DROP COLUMN `blocks`;

--
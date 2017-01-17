--

ALTER TABLE `schedule_events` ADD COLUMN `channel` INT UNSIGNED NULL;

--//@UNDO

ALTER TABLE `schedule_events` DROP COLUMN `channel`;

--
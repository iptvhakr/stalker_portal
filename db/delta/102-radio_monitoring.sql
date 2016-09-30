--

ALTER TABLE `radio`
ADD COLUMN `enable_monitoring` TINYINT NOT NULL DEFAULT 0,
ADD COLUMN `monitoring_status` TINYINT NOT NULL DEFAULT 1,
ADD COLUMN `monitoring_status_updated` DATETIME NULL;

-- //@UNDO

ALTER TABLE `radio` DROP COLUMN `enable_monitoring`, DROP COLUMN `monitoring_status`, DROP COLUMN `monitoring_status_updated`;

--
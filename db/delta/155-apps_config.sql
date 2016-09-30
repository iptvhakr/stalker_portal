--

ALTER TABLE `apps` ADD COLUMN `config` text;

-- //@UNDO

ALTER TABLE `apps` DROP COLUMN `config`;

--
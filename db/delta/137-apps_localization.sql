--

ALTER TABLE `apps` ADD `localization` TEXT;

-- //@UNDO

ALTER TABLE `apps` DROP `localization`;

--
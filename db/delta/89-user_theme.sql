--

ALTER TABLE `users` ADD `theme` VARCHAR(128) NOT NULL DEFAULT '';

-- //@UNDO

ALTER TABLE `users` DROP `theme`;

--
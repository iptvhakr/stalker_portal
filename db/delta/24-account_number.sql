--
ALTER TABLE `users` MODIFY `ls` VARCHAR(64) NOT NULL DEFAULT '';

ALTER TABLE `tariff_plan` ADD `user_default` tinyint default 0;
--//@UNDO
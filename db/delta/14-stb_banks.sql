--
ALTER TABLE `users` ADD `num_banks` tinyint default 0;

--//@UNDO

ALTER TABLE `users` DROP `num_banks`;

--
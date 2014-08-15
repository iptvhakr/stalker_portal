--

ALTER TABLE `tv_archive` DROP INDEX `ch_id`;

--//@UNDO

ALTER TABLE `tv_archive` ADD UNIQUE KEY (`ch_id`);

--
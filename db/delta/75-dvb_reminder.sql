--

ALTER TABLE `tv_reminder` ADD `tv_program_name` text;

--//@UNDO

ALTER TABLE `tv_reminder` DROP `tv_program_name`;

--
--

ALTER TABLE `itv` ADD `tv_archive_duration` int not null default @ARCHIVE_DURATION@;

--//@UNDO

ALTER TABLE `itv` DROP `tv_archive_duration`;

--
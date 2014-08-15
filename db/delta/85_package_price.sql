--

ALTER TABLE `services_package` ADD `price` decimal(8,2) NOT NULL;

--//@UNDO

ALTER TABLE `services_package` DROP `price`;

--
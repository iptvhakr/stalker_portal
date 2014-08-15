--

ALTER TABLE `users` ADD `hdmi_event_reaction` INT DEFAULT NULL;

--//@UNDO

ALTER TABLE `users` DROP `hdmi_event_reaction`;

--
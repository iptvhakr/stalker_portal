--

ALTER TABLE `last_id` ADD index ident(`ident`);

--//@UNDO

ALTER TABLE `last_id` DROP index ident;

--
--

ALTER TABLE `video` ADD `comments` text;

--//@UNDO

ALTER TABLE `video` DROP `comments`;

--
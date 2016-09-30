--

ALTER TABLE `video` ADD COLUMN `autocomplete_provider` ENUM('kinopoisk', 'tmdb');

-- //@UNDO

ALTER TABLE `video` DROP COLUMN `provider`;

--
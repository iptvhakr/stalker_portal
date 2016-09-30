--

INSERT INTO `filters` (`title`,       `description`,                            `method`,                `type`,       `values_set`,             `default`)
       VALUES         ('Media type', 'Users by type of mediacontent  watching', 'getUsersByPlayingType', 'VALUES_SET', 'getUsersPlayingTypeSet', '0');

UPDATE `filters` SET `title` = 'Streaming server' WHERE `method` = 'getUsersByUsingStreamServer';

-- //@UNDO

DELETE FROM `filters` WHERE `method` = 'getUsersByPlayingType';

--
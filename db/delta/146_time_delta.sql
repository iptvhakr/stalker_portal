--

ALTER TABLE `access_tokens` MODIFY `time_delta` VARCHAR(8) NOT NULL DEFAULT '300';
UPDATE `access_tokens` SET time_delta='300';

-- //@UNDO

ALTER TABLE `access_tokens` MODIFY `time_delta` VARCHAR(128) NOT NULL DEFAULT '' ;

--
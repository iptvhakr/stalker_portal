CREATE TABLE `generation_time`(
    `time` varchar(32),
    `counter` int unsigned NOT NULL default 0,
    INDEX(`time`)
) ENGINE=MEMORY;

INSERT INTO `generation_time` (`time`) values ('0ms'), ('100ms'), ('200ms'), ('300ms'), ('400ms'), ('500ms');
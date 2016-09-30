--

CREATE TABLE `schedule_events` (
  `id`              INT          NOT NULL AUTO_INCREMENT,
  `event`           VARCHAR(128) NOT NULL DEFAULT '',
  `header`          VARCHAR(128) DEFAULT NULL,
  `msg`             TEXT,
  `post_function`   VARCHAR(255) DEFAULT NULL,
  `recipient`       VARCHAR(255) NOT NULL,
  `periodic`        TINYINT(1)   NOT NULL DEFAULT 0,
  `date_begin`      TIMESTAMP    NULL DEFAULT NULL,
  `date_end`        TIMESTAMP    NULL DEFAULT NULL,
  `last_run`        TIMESTAMP    NULL DEFAULT NULL,
  `schedule`        VARCHAR(255) NOT NULL,
  `state`           TINYINT(1)   NOT NULL DEFAULT 0,
  `reboot_after_ok` TINYINT(4)   DEFAULT '0',
  `param1`          VARCHAR(255) NOT NULL DEFAULT '',
  `ttl`             INT(11)      NOT NULL DEFAULT 0,

  PRIMARY KEY (`id`)
) DEFAULT CHARSET = UTF8;

INSERT INTO `adm_grp_action_access`
          (`controller_name`,          `action_name`,     `is_ajax`, `description`)
VALUES    ('events',               'event-scheduler',             0, 'List of scheduled events'),
          ('events',     'event-scheduler-list-json',             1, 'List of scheduled events by page + filters'),
          ('events',           'save-schedule-event',             1, 'Adding and editing the scheduled events'),
          ('events',              'scheduler-remove',             1, 'Removing the scheduled events'),
          ('events',        'scheduler-toggle-state',             1, 'Stopping or starting the scheduled events');

-- //@UNDO

DROP TABLE `schedule_events`;

--
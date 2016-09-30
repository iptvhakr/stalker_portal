--

CREATE TABLE `filters` (
  `id`          INT(11)                                            NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(45)                                        NOT NULL DEFAULT '',
  `description` VARCHAR(255)                                       NULL,
  `method`      VARCHAR(45)                                        NOT NULL,
  `type`        ENUM('DATETIME', 'NUMBER', 'STRING', 'VALUES_SET') NOT NULL,
  `values_set`  VARCHAR(45)                                        NOT NULL,
  PRIMARY KEY (`id`)
)  DEFAULT CHARSET = utf8;

INSERT INTO `filters`(`title`, `description`, `method`, `type`, `values_set`)
VALUES
  ('Users status', 'Users status', 'getUsersByStatus', 'VALUES_SET', 'getUsersStatusSet'),
  ('Users state', 'Users state', 'getUsersByState', 'VALUES_SET', 'getUsersStateSet'),
  ('Users date creation', 'Users date creation', 'getUsersByCreateDate', 'DATETIME', ''),
  ('User country', 'User country', 'getUsersByCountry', 'STRING', ''),
  ('Last start STB', 'Last start STB', 'getUsersByLastStart', 'DATETIME', ''),
  ('Last user activity', 'Last user activity', 'getUsersByLastActivity', 'DATETIME', ''),
  ('Users group', 'Users group', 'getUsersByGroup', 'NUMBER', 'getUsersGroupSet'),
  ('Users interface language', 'Users interface language', 'getUsersByInterfaceLanguage', 'VALUES_SET', 'getUsersInterfaceLanguageSet'),
  ('Watch the TV-channel', 'Watch the TV-channel', 'getUsersByWatchingTV', 'STRING', ''),
  ('Watch the movie', 'Watch the movie', 'getUsersByWatchingMovie', 'STRING', ''),
  ('Using streaming server ', 'Using streaming server ', 'getUsersByUsingStreamServer', 'NUMBER', 'getStreamServerSet'),
  ('User\'s STB model', 'User\'s STB model', 'getUsersBySTBModel', 'VALUES_SET', 'getUserSTBModelSet'),
  ('STB firmware version', 'STB firmware version', 'getUsersBySTBFirmwareVersion', 'STRING', ''),
  ('Connected tariff plan', 'User is connected on tariff plan', 'getUsersByConnectedTariffPlan', 'NUMBER', 'getUserTariffPlanSet'),
  ('User by accessible packages of service', 'User by accessible packages of service', 'getUsersByAaccessibleServicePackages', 'NUMBER', 'getUserAaccessibleServicePackagesSet');

CREATE TABLE `filter_set` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(45)  NOT NULL,
  `description` VARCHAR(255) NULL     DEFAULT '',
  `filter_set`  TEXT         NULL,
  `admin_id`    INT(11)      NOT NULL,
  `for_all`     TINYINT(1)   NOT NULL DEFAULT 0,
  `favorites`   TINYINT(1)   NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET = utf8;

CREATE TABLE `messages_templates` (
  `id`      INT          NOT NULL AUTO_INCREMENT,
  `title`   VARCHAR(255) NOT NULL,
  `header`  VARCHAR(255) NOT NULL,
  `body`    TEXT         NULL,
  `author`  INT(11)      NOT NULL,
  `created` DATETIME,
  `edited`  TIMESTAMP    NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET = utf8;

INSERT INTO `adm_grp_action_access`
          (`controller_name`,   `action_name`,            `is_ajax`, `description`)
VALUES    ('users',             'users-filters-list',             0, 'List of filters'),
          ('users',             'users-filters-list-json',        1, 'List of filters by page + filters'),
          ('users',             'get-filter',                     1, 'Get data for filter construct'),
          ('users',             'save-filter',                    1, 'Save filters set'),
          ('users',             'remove-filter',                  1, 'Remove filters set'),
          ('users',             'toggle-filter-favorite',         1, 'Toggle favorite status for current filter'),

          ('events',            'events',                         0, 'List of events'),
          ('events',            'message-templates',              1, 'List of templates of messages'),
          ('events',            'message-templates-list-json',    1, 'List of templates of messages by page + filters'),
          ('events',            'save-message-template',          1, 'Adding and editing the templates of messages'),
          ('events',            'remove-template',                1, 'Removing the templates of messages');

ALTER TABLE `events` ADD COLUMN `header` VARCHAR(128) NULL AFTER `event`;

-- //@UNDO

DROP TABLE `filters`;
DROP TABLE `filter_set`;
DROP TABLE `messages_templates`;
ALTER TABLE `events` DROP COLUMN `header`;

--
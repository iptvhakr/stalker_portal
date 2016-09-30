--

ALTER TABLE `filters` ADD COLUMN `default` TINYINT(1) NOT NULL DEFAULT 0;

UPDATE `filters` SET `title`='Country', `type` = 'VALUES_SET', `values_set` = 'getUsersCountrySet' WHERE `method` = 'getUsersByCountry';
UPDATE `filters` SET `title`='State', `default`='1' WHERE `method`='getUsersByStatus';
UPDATE `filters` SET `title`='Status', `default`='1' WHERE `method`='getUsersByState';
UPDATE `filters` SET `title`='Device', `default`='1' WHERE `method`='getUsersBySTBModel';
UPDATE `filters` SET `title`='Firmware version' WHERE `method`='getUsersBySTBFirmwareVersion';
UPDATE `filters` SET `title`='Registration Date' WHERE `method`='getUsersByCreateDate';
UPDATE `filters` SET `title`='Language' WHERE `method`='getUsersByInterfaceLanguage';
UPDATE `filters` SET `title`='Group' WHERE `method`='getUsersByGroup';
UPDATE `filters` SET `title`='Tariff plan' WHERE `method`='getUsersByConnectedTariffPlan';
UPDATE `filters` SET `title`='Package' WHERE `method`='getUsersByAaccessibleServicePackages';
UPDATE `filters` SET `title`='Last activity' WHERE `method`='getUsersByLastActivity';
UPDATE `filters` SET `title`='Watched TV channel' WHERE `method`='getUsersByWatchingTV';
UPDATE `filters` SET `title`='Watched movie' WHERE `method`='getUsersByWatchingMovie';
UPDATE `filters` SET `title`='Streaming server ' WHERE `method`='getUsersByUsingStreamServer';
UPDATE `filters` SET `title`='Last start' WHERE `method`='getUsersByLastStart';

INSERT INTO `adm_grp_action_access`
          (`controller_name`,                `action_name`,     `is_ajax`, `description`)
VALUES    ('users',         'get-autocomplete-watching-tv',             1, 'Auto-complete for watching-tv filter'),
          ('users',      'get-autocomplete-watching-movie',             1, 'Auto-complete for watching-movie filter'),
          ('users', 'get-autocomplete-stbfirmware-version',             1, 'Auto-complete for stbfirmware-version filter');

-- //@UNDO

UPDATE `filters` SET `title` = 'User country', `type` = 'STRING', `values_set` = '' WHERE `method` = 'getUsersByCountry';
UPDATE `filters` SET `title` = 'Users status' WHERE `method`='getUsersByStatus';
UPDATE `filters` SET `title` = 'Users state' WHERE `method`='getUsersByState';
UPDATE `filters` SET `title` = 'User\'s STB model' WHERE `method`='getUsersBySTBModel';
UPDATE `filters` SET `title` = 'STB firmware version' WHERE `method`='getUsersBySTBFirmwareVersion';
UPDATE `filters` SET `title` = 'Users date creation' WHERE `method`='getUsersByCreateDate';
UPDATE `filters` SET `title` = 'Users interface language' WHERE `method`='getUsersByInterfaceLanguage';
UPDATE `filters` SET `title` = 'Users group' WHERE `method`='getUsersByGroup';
UPDATE `filters` SET `title` = 'Connected tariff plan' WHERE `method`='getUsersByConnectedTariffPlan';
UPDATE `filters` SET `title` = 'User by accessible packages of service' WHERE `method`='getUsersByAaccessibleServicePackages';
UPDATE `filters` SET `title` = 'Last user activity' WHERE `method`='getUsersByLastActivity';
UPDATE `filters` SET `title` = 'Watch the TV-channel' WHERE `method`='getUsersByWatchingTV';
UPDATE `filters` SET `title` = 'Watch the movie' WHERE `method`='getUsersByWatchingMovie';
UPDATE `filters` SET `title` = 'Using streaming server' WHERE `method`='getUsersByUsingStreamServer';
UPDATE `filters` SET `title` = 'Last start STB' WHERE `method`='getUsersByLastStart';

ALTER TABLE `filters` DROP COLUMN `default`;

--
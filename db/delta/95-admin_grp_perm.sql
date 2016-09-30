--

--
-- Table structure for table `adm_grp_action_access`
--

CREATE TABLE IF NOT EXISTS `adm_grp_action_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `controller_name` varchar(45) NOT NULL DEFAULT 'index',
  `action_name` varchar(45) NOT NULL DEFAULT 'index',
  `is_ajax` tinyint(1) NOT NULL DEFAULT '0',
  `view_access` tinyint(1) NOT NULL DEFAULT '0',
  `edit_access` tinyint(1) NOT NULL DEFAULT '0',
  `action_access` tinyint(1) NOT NULL DEFAULT '0',
  `description` varchar(255) NOT NULL DEFAULT 'undefined action',
  `group_id` tinyint(4) DEFAULT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

--
-- Dumping data for table `adm_grp_action_access`
--

INSERT INTO `adm_grp_action_access`
(`controller_name`, `action_name`,              `is_ajax`,  `description`, `hidden`)
VALUES
-- -----------------------------------------------------------------------------

('admins',          '',                                     0, 'Management system administrators, groups and rights', 0),
('admins',          'admins-list',                          0, 'List of administrators',  0),
('admins',          'admins-list-json',                     1, 'The list of administrators by page + filters',  0),
('admins',          'check-admins-login',                   1, 'Validation Administrator Login',  0),
('admins',          'save-admin',                           1, 'Viewing and editing administrator',  0),
('admins',          'remove-admin',                         1, 'Removing administrator',  0),
('admins',          'admins-groups',                        0, 'List of groups of administrators',  0),
('admins',          'admins-groups-list-json',              1, 'List of groups of administrators by page + filters',  0),
('admins',          'check-admins-group-name',              1, 'Validation group name',  0),
('admins',          'save-admins-group',                    1, 'Viewing and editing group',  0),
('admins',          'remove-admins-group',                  1, 'Removing a group',  0),
('admins',          'admins-groups-permissions',            0, 'Form view the permissions of the current group',  0),
('admins',          'save-admins-group-permissions',        1, 'View and edit permissions of the current group',  0),

-- -----------------------------------------------------------------------------

('audio-club',      '',                                     0, 'Audio club',  0),
('audio-club',      'audio-albums',                         0, 'Album list',  0),
('audio-club',      'audio-albums-list-json',               1, 'Album list page by page + filters',  0),
('audio-club',      'remove-audio-albums',                  1, 'Removing album',  0),
('audio-club',      'toggle-audio-albums',                  1, 'Change the on/off state of the album',  0),
('audio-club',      'add-audio-albums',                     0, 'Add Album',  0),
('audio-club',      'edit-audio-albums',                    0, 'Viewing and editing album',  0),
('audio-club',      'edit-audio-cover',                     1, 'Change the album cover',  0),
('audio-club',      'delete-audio-cover',                   1, 'Delete the album cover',  0),
('audio-club',      'audio-albums-composition-list-json',   1, 'List of compositions in the album',  0),
('audio-club',      'audio-track-reorder',                  1, 'Change the order of compositions in the album',  0),
('audio-club',      'audio-tracks-manage',                  1, 'Editing a composition',  0),
('audio-club',      'remove-audio-album-track',             1, 'Removing a composition',  0),
('audio-club',      'toggle-audio-album-track',             1, 'Change the on/off state of the composition',  0),
('audio-club',      'audio-artists',                        0, 'List of artists',  0),
('audio-club',      'audio-artists-list-json',              1, 'List of artists by page + filters',  0),
('audio-club',      'add-audio-artists',                    1, 'Add artist',  0),
('audio-club',      'edit-audio-artists',                   1, 'Edit artist',  0),
('audio-club',      'remove-audio-artists',                 1, 'Remove artist',  0),
('audio-club',      'check-audio-artists-name',             1, 'Validation artist name',  0),
('audio-club',      'audio-genres',                         0, 'List of audio genres',  0),
('audio-club',      'audio-genres-list-json',               1, 'List of audio genres by page + filters',  0),
('audio-club',      'add-audio-genres',                     1, 'Add audio genre',  0),
('audio-club',      'edit-audio-genres',                    1, 'Edit audio genre',  0),
('audio-club',      'remove-audio-genres',                  1, 'Remove the audio genre',  0),
('audio-club',      'check-audio-genres-name',              1, 'Validation names audio genre',  0),
('audio-club',      'audio-languages',                      0, 'List of language performance',  0),
('audio-club',      'audio-languages-list-json',            1, 'List of language performance by page + filters',  0),
('audio-club',      'add-audio-languages',                  1, 'Add language of performance',  0),
('audio-club',      'edit-audio-languages',                 1, 'Edit language of performance',  0),
('audio-club',      'remove-audio-languages',               1, 'Remove language of performance',  0),
('audio-club',      'check-audio-languages-name',           1, 'Validation name of the language of performance',  0),
('audio-club',      'audio-years',                          0, 'List of years of release',  0),
('audio-club',      'audio-years-list-json',                1, 'List of years of release by page + filters',  0),
('audio-club',      'add-audio-years',                      1, 'Add the year of release',  0),
('audio-club',      'edit-audio-years',                     1, 'Edit the year of release',  0),
('audio-club',      'remove-audio-years',                   1, 'Remove year of year of release',  0),
('audio-club',      'check-audio-years-name',               1, 'Validation names of release',  0),
('audio-club',      'audio-logs',                           0, 'Audio logs',  1);

-- -----------------------------------------------------------------------------

INSERT INTO `adm_grp_action_access`
(`controller_name`, `action_name`,              `is_ajax`,  `view_access`, `edit_access`, `action_access`,`description`,      `hidden`)
VALUES

('auth-user',       '',                                     0,            1,              1,           1,  'Block the authorized user of the system',  1),
('auth-user',       'auth-user-profile',                    0,            1,              1,           1,  'Profile',  1),
('auth-user',       'auth-user-messages',                   0,            1,              1,           1,  'Messages',  1),
('auth-user',       'tasks-list',                           0,            1,              1,           1,  'Tasks',  1),
('auth-user',       'auth-user-settings',                   0,            1,              1,           1,  'Settings',  1),
('auth-user',       'auth-user-logout',                     0,            1,              1,           1,  'Logout',  1);

-- -----------------------------------------------------------------------------

INSERT INTO `adm_grp_action_access`
(`controller_name`, `action_name`,              `is_ajax`,  `description`, `hidden`)
VALUES

('broadcast-servers',   '',                                 0,  'Broadcast server',  0),
('broadcast-servers',   'broadcast-servers-list',           0,  'List of servers broadcasting',  0),
('broadcast-servers',   'broadcast-servers-list-json',      1,  'List of servers broadcasting by page + filters',  0),
('broadcast-servers',   'remove-server',                    1,  'Removing of servers broadcasting',  0),
('broadcast-servers',   'toggle-server-status',             1,  'Change the on/off status of servers broadcasting',  0),
('broadcast-servers',   'save-server',                      1,  'Adding and editing broadcast server',  0),
('broadcast-servers',   'broadcast-zone-list',              0,  'Broadcast area',  0),
('broadcast-servers',   'broadcast-zone-list-json',         1,  'Broadcast area by page + filters',  0),
('broadcast-servers',   'add-zone',                         0,  'Add a new area',  0),
('broadcast-servers',   'edit-zone',                        0,  'Editing an existing area',  0),
('broadcast-servers',   'remove-zone',                      1,  'Removing an existing area',  0),

-- -----------------------------------------------------------------------------

('events',          '',                                     0, 'List of events',  0),
('events',          'events-list-json',                     1, 'List of events by page + filters',  0),
('events',          'add-event',                            1, 'Add an event',  0),
('events',          'upload-list-addresses',                1, 'Download the list of addresses for sending events',  0),
('events',          'clean-events',                         1, 'Clear the list of events',  0);

-- -----------------------------------------------------------------------------

INSERT INTO `adm_grp_action_access`
(`controller_name`, `action_name`,              `is_ajax`,  `view_access`, `edit_access`, `action_access`,`description`,      `hidden`)
VALUES
('index',     '',                                   0,            1,              1,              1,      'Start page', 1),
('index',     'set-dropdown-attribute',             1,            1,              1,              1,      'Saving settings drop-down menu user', 1);

-- -----------------------------------------------------------------------------

INSERT INTO `adm_grp_action_access`
(`controller_name`, `action_name`,              `is_ajax`,  `description`, `hidden`)
VALUES

('infoportal',      '',                                     0, 'Info portal',  0),
('infoportal',      'phone-book',                           0, 'Phone book',  0),
('infoportal',      'phone-book-list-json',                 1, 'Phone book by page + filters',  0),
('infoportal',      'save-phone-book-item',                 1, 'Adding and editing phone number',  0),
('infoportal',      'remove-phone-book-item',               1, 'Removing phone numbers',  0),
('infoportal',      'humor',                                0, 'Humour',  0),
('infoportal',      'humor-list-json',                      1, 'Humour by page + filters',  0),
('infoportal',      'save-humor-item',                      1, 'View, add and edit joke',  0),
('infoportal',      'remove-humor-item',                    1, 'Removing joke',  0),

-- -----------------------------------------------------------------------------

('information',     '',                                     0, 'Information', 0),

-- -----------------------------------------------------------------------------

('karaoke',         '',                                     0, 'Karaoke. List of karaoke',  0),
('karaoke',         'karaoke-list-json',                    1, 'List of karaoke by page + filters',  0),
('karaoke',         'save-karaoke',                         1, 'View, add and edit karaoke',  0),
('karaoke',         'remove-karaoke',                       1, 'Remove karaoke',  0),
('karaoke',         'toggle-karaoke-done',                  1, 'Change the on/off state the task on the item karaoke',  0),
('karaoke',         'toggle-karaoke-accessed',              1, 'Change the on/off state of available items Karaoke',  0),
('karaoke',         'check-karaoke-source',                 1, 'Information about the source element of karaoke',  0);

-- -----------------------------------------------------------------------------

INSERT INTO `adm_grp_action_access`
(`controller_name`, `action_name`,              `is_ajax`,  `view_access`, `edit_access`, `action_access`,`description`,      `hidden`)
VALUES
('login',         '',                                   0,            1,              1,              1, 'Login. System authorization',  1),

-- -----------------------------------------------------------------------------

('logout',         '',                                  0,            1,              1,              1, 'Logout',  1);

-- -----------------------------------------------------------------------------

INSERT INTO `adm_grp_action_access`
(`controller_name`, `action_name`,              `is_ajax`,  `description`, `hidden`)
VALUES

('radio',           '',                                     0, 'List of radio',  0),
('radio',           'radio-list-json',                      1, 'List of radio by page + filters',  0),
('radio',           'toggle-radio',                         1, 'Change the on/off state radio',  0),
('radio',           'remove-radio',                         1, 'Remove radio',  0),
('radio',           'add-radio',                            0, 'Adding radio',  0),
('radio',           'edit-radio',                           0, 'Viewing and editing radio',  0),
('radio',           'radio-check-name',                     1, 'Validation names of radio',  0),
('radio',           'radio-check-number',                   1, 'Validation number of radio',  0);

-- -----------------------------------------------------------------------------

INSERT INTO `adm_grp_action_access`
(`controller_name`, `action_name`,              `is_ajax`,  `view_access`, `edit_access`, `action_access`,`description`,      `hidden`)
VALUES
('register',         '',                                   0,            1,              1,           1, 'Registration in the system',  1);

-- -----------------------------------------------------------------------------

INSERT INTO `adm_grp_action_access`
(`controller_name`, `action_name`,              `is_ajax`,  `description`, `hidden`)
VALUES

('settings',        '',                                     0, 'Settings',  0),
('settings',        'themes',                               0, 'Viewing of available skins',  0),
('settings',        'set-current-theme',                    1, 'Setting the skin',  0),
('settings',        'common',                               0, 'Firmware update',  0),
('settings',        'common-list-json',                     1, 'Firmware update by page + filters',  0),
('settings',        'save-common-item',                     1, 'Viewing and editing firmware updates',  0),
('settings',        'remove-common-item',                   1, 'Remove firmware update',  0),
('settings',        'toggle-common-item-status',            1, 'Change the on/off state firmware update',  0),

-- -----------------------------------------------------------------------------

('statistics',      '',                                     0, 'Statistics',  0),
('statistics',      'stat-video',                           0, 'Statistics of movies per month',  0),
('statistics',      'stat-video-list-json',                 1, 'Statistics of movies per month by page + filters',  0),
('statistics',      'stat-tv',                              0, 'Statistics of TV',  0),
('statistics',      'stat-tv-list-json',                    1, 'Statistics of TV by page + filters',  0),
('statistics',      'stat-tv-archive',                      0, 'Statistics of TV archive',  0),
('statistics',      'stat-tv-archive-list-json',            1, 'Statistics of TV archive by page + filters',  0),
('statistics',      'stat-timeshift',                       0, 'Statistics of TimeShift',  0),
('statistics',      'stat-timeshift-list-json',             1, 'Statistics of TimeShift by page + filters',  0),
('statistics',      'stat-moderators',                      0, 'Statistics of moderators',  0),
('statistics',      'stat-moderators-list-json',            1, 'Statistics of moderators by page + filters',  0),
('statistics',      'stat-abonents',                        0, 'Subscriber statistics',  0),
('statistics',      'stat-abonents-list-json',              1, 'Subscriber statistics by page + filters',  0),
('statistics',      'stat-abonents-unactive',               0, 'Inactive subscribers',  0),
('statistics',      'stat-abonents-unactive-list-json',     1, 'Inactive subscribers by page + filters',  0),
('statistics',      'stat-claims',                          0, 'Statistics of complaints',  0),
('statistics',      'stat-claims-list-json',                1, 'Statistics of complaints by page + filters',  0),
('statistics',      'stat-claims-logs',                     0, 'Logs of complaints',  0),
('statistics',      'stat-claims-logs-list-json',           1, 'Logs of complaints by page + filters',  0),

-- -----------------------------------------------------------------------------

('storages',         '',                                    0, 'Storages',  0),
('storages',         'storages-list',                       0, 'List of storages',  0),
('storages',         'storages-list-json',                  1, 'List of storages by page + filters',  0),
('storages',         'reset-cache',                         1, 'Reset cache storages',  0),
('storages',         'refresh-cache',                       1, 'Update cache storages',  0),
('storages',         'get-storage',                         1, 'Viewing and editing storages',  0),
('storages',         'save-storage',                        1, 'Adding storages',  0),
('storages',         'toggle-storages-status',              1, 'Change the on/off state storages',  0),
('storages',         'remove-storage',                      1, 'Remove storages',  0),
('storages',         'storages-video-search',               0, 'Search video',  0),
('storages',         'storages-video-search-json',          1, 'Search video by page + filters',  0),
('storages',         'storages-logs',                       0, 'Logs of storages',  0),
('storages',         'storages-logs-json',                  1, 'Logs of storages by page + filters',  0),

-- -----------------------------------------------------------------------------

('tariffs',         '',                                     0, 'Tariffs',  0),
('tariffs',         'service-packages',                     0, 'List of service packages',  0),
('tariffs',         'service-packages-list-json',           1, 'List of service packages by page + filters',  0),
('tariffs',         'remove-service-package',               1, 'Remove service package',  0),
('tariffs',         'add-service-package',                  0, 'Adding service package',  0),
('tariffs',         'edit-service-package',                 0, 'Viewing and editing service package',  0),
('tariffs',         'get-services',                         1, 'Getting a list of media content for the service (add/edit form package)',  0),
('tariffs',         'check-external-id',                    1, 'Validation of external ID for package',  0),
('tariffs',         'tariff-plans',                         0, 'List of tariff plans',  0),
('tariffs',         'tariff-plans-list-json',               1, 'List of tariff plans by page + filters',  0),
('tariffs',         'add-tariff-plans',                     0, 'Adding tariff plan',  0),
('tariffs',         'edit-tariff-plan',                     0, 'Viewing and editing tariff plan',  0),
('tariffs',         'remove-tariff-plan',                   1, 'Remove tariff plan',  0),

-- -----------------------------------------------------------------------------

('tasks',           '',                                     0, 'Tasks',  0),
('tasks',           'tasks-list',                           0, 'List of tasks',  0),
('tasks',           'tasks-list-json',                      1, 'List of tasks by page + filters',  0),
('tasks',           'task-state-change',                    1, 'Change of state in the category',  0),
('tasks',           'task-detail-video',                    0, 'Detailed on video-task',  0),
('tasks',           'send-task-message-video',              0, 'Sending messages, change status for video-task',  0),
('tasks',           'task-detail-karaoke',                  0, 'Detailed for karaoke-task',  0),
('tasks',           'send-task-message-karaoke',            0, 'Sending messages, change status karaoke-task',  0),
('tasks',           'tasks-report',                         0, 'Report',  0),
('tasks',           'tasks-report-json',                    1, 'Report by page + filters',  0),

-- -----------------------------------------------------------------------------

('tv-channels',     '',                                     0, 'IPTV channels',  0),
('tv-channels',     'iptv-list',                            0, 'List of channels',  0),
('tv-channels',     'enable-channel',                       1, 'Switch on channel',  0),
('tv-channels',     'disable-channel',                      1, 'Switch off channel',  0),
('tv-channels',     'remove-channel',                       1, 'Remove channel',  0),
('tv-channels',     'add-channel',                          0, 'Adding channel',  0),
('tv-channels',     'edit-channel',                         0, 'Editing channel',  0),
('tv-channels',     'edit-logo',                            1, 'Edit logo for channel',  0),
('tv-channels',     'delete-logo',                          1, 'Remove logo of channel',  0),
('tv-channels',     'move-channel',                         0, 'Move channel - Order of channels',  0),
('tv-channels',     'move-apply',                           1, 'Move channel - Save the changes of order channels',  0),
('tv-channels',     'toogle-lock-channel',                  1, 'Change the on/off state block move channel',  0),
('tv-channels',     'save_epg_item',                        1, 'Save EPG for channel',  0),
('tv-channels',     'get_epg_item',                         1, 'Viewing and editing EPG for channel',  0),
('tv-channels',     'epg',                                  0, 'EPG',  0),
('tv-channels',     'epg-list-json',                        1, 'EPG by page + filters',  0),
('tv-channels',     'save-epg-item',                        1, 'Viewing and editing EPG',  0),
('tv-channels',     'remove-epg-item',                      1, 'Remove EPG',  0),
('tv-channels',     'toggle-epg-item-status',               1, 'Change the on/off state EPG',  0),
('tv-channels',     'epg-check-uri',                        1, 'Validation of URL EPG',  0),
('tv-channels',     'update-epg',                           1, 'Update or force an update EPG',  0),
-- -----------------------------------------------------------------------------

('users',           '',                                     0, 'Users',  0),
('users',           'users-list',                           0, 'List of users',  0),
('users',           'users-list-json',                      1, 'List of users by page + filters',  0),
('users',           'toggle-user',                          1, 'Change the on/off state user',  0),
('users',           'remove-user',                          1, 'Remove user',  0),
('users',           'add-users',                            0, 'Adding user',  0),
('users',           'edit-users',                           0, 'Viewing and editing user',  0),
('users',           'check-login',                          1, 'Validation of login user',  0),
('users',           'reset-users-parent-password',          1, 'Reset the password of Parent Control user',  0),
('users',           'reset-user-fav-tv',                    1, 'Reset favorites TV user',  0),
('users',           'users-consoles-groups',                0, 'List of STB',  0),
('users',           'add-console-group',                    1, 'Adding STB',  0),
('users',           'edit-console-group',                   1, 'Viewing and editing title of STB',  0),
('users',           'remove-console-group',                 1, 'Remove group STB',  0),
('users',           'check-console-name',                   1, 'Validation of title STB',  0),
('users',           'users-groups-consoles-list',           0, 'List of STB in group',  0),
('users',           'users-groups-consoles-list-json',      1, 'List of STB in group by page + filters',  0),
('users',           'add-console-item',                     1, 'Adding STB in group',  0),
('users',           'remove-console-item',                  1, 'Remove STB from group',  0),
('users',           'check-console-item',                   1, 'Verify that you can add an STB to the group',  0),
('users',           'users-consoles-logs',                  0, 'Logs of activity STB',  0),
('users',           'users-consoles-logs-json',             1, 'Logs of activity STB by page + filters',  0),
('users',           'users-consoles-report',                0, 'Report on changing of state STB',  0),
('users',           'users-consoles-report-json',           1, 'Report on changing of state STB by page + filters',  0),

-- -----------------------------------------------------------------------------

('video-club',      '',                                     0, 'Video club',  0),
('video-club',      'video-list',                           0, 'List of movies',  0),
('video-club',      'video-list-json',                      1, 'List of movies by page + filters',  0),
('video-club',      'video-info',                           1, 'Information about source of movie',  0),
('video-club',      'remove-video',                         1, 'Remove movie',  0),
('video-club',      'disable-video',                        1, 'Switch off movie',  0),
('video-club',      'enable-video',                         1, 'Switch on or schedule add',  0),
('video-club',      'get-md5',                              1, 'Calculate md5 of movie',  0),
('video-club',      'create-tasks',                         1, 'Set the task on the movie',  0),
('video-club',      'add-video',                            0, 'Adding movie',  0),
('video-club',      'edit-video',                           0, 'Viewing and editing movie',  0),
('video-club',      'check-name',                           1, 'Validation of title movie',  0),
('video-club',      'edit-cover',                           1, 'Change cover for movie',  0),
('video-club',      'delete-cover',                         1, 'Deleting cover for movie',  0),
('video-club',      'update-rating-kinopoisk',              1, 'Update the rating of movie from kinopoisk.ru portal',  0),
('video-club',      'get-kinopoisk-info-by-name',           1, 'By title of movie fill in the form of adding movie using data from kinopoisk.ru portal',  0),
('video-club',      'get-kinopoisk-info-by-id',             1, 'By ID movie on kinopoisk.ru portal, fill in the form of adding movie using data from kinopoisk.ru portal',  0),
('video-club',      'get-image',                            1, 'Set cover for movie from kinopoisk.ru portal',  0),
('video-club',      'video-schedule',                       0, 'Schedule switch on of movie',  0),
('video-club',      'remove-tasks',                         1, 'Remove movie from schedule switch on',  0),
('video-club',      'video-advertise',                      0, 'List of advertising blocks',  0),
('video-club',      'remove-video-ads',                     1, 'Remove advertising block',  0),
('video-club',      'toggle-video-ads-status',              1, 'Change the on/off state advertising block',  0),
('video-club',      'add-video-ads',                        0, 'Adding advertising block',  0),
('video-club',      'edit-video-ads',                       0, 'Viewing and editing advertising block',  0),
('video-club',      'video-moderators-addresses',           0, 'Addresses of moderators, information about STB of moderators',  0),
('video-club',      'remove-video-moderators',              1, 'Remove moderator',  0),
('video-club',      'toggle-video-moderators-status',       1, 'Change the on/off state of moderator',  0),
('video-club',      'add-video-moderators',                 0, 'Adding moderator',  0),
('video-club',      'edit-video-moderators',                0, 'Viewing and editing moderator',  0),
('video-club',      'check-moderator-mac',                  1, 'Validation of MAC-address of STB of moderator',  0),
('video-club',      'video-logs',                           0, 'Logs of movie',  0),
('video-club',      'video-logs-json',                      1, 'Logs of movie by page + filters',  0);

-- //@UNDO

DROP TABLE IF EXISTS `adm_grp_action_access`;

--
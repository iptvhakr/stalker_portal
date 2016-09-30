--

INSERT INTO `adm_grp_action_access`
          (`controller_name`, `action_name`,                        `is_ajax`, `description`)
VALUES    ('tv-channels',     'iptv-list-json',                             1, 'List of tv-channels by page + filters'),
          ('video-club',      'video-schedule-list-json',                   1, 'Schedule switch on of movie by page + filters'),
          ('video-club',      'video-advertise-list-json',                  1, 'List of advertising blocks by page + filters'),
          ('video-club',      'video-moderators-addresses-list-json',       1, 'List of STBs of video-moderators by page + filters'),
          ('users',           'users-consoles-groups-list-json',            1, 'List of STB by page + filters');

-- //@UNDO

--
--

INSERT INTO `adm_grp_action_access`
          (`controller_name`, `action_name`, `is_ajax`, `description`)
VALUES    ('tv-channels',      'm3u-import',         0, 'Import tv-channels from m3u-file'),
          ('tv-channels',    'get-m3u-data',         1, 'Parse m3u-file'),
          ('tv-channels',   'save-m3u-item',         1, 'Save one m3u-item as channel');

-- //@UNDO

--
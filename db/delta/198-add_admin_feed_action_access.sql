--

INSERT INTO `adm_grp_action_access`
        (`controller_name`,          `action_name`,    `is_ajax`,  `description`,                         `hidden`, `only_top_admin`)
VALUES  ('index',                      'note-list',            1, 'Get list of feed news',                       1,               1),
        ('index',           'note-list-set-readed',            1, 'Set "readed" flag for item of feed news',     1,               1),
        ('index',           'note-list-set-remind',            1, 'Set remind date for item of feed news',       1,               1);

--//@UNDO

--
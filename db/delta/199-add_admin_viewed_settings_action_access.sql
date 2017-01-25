--

INSERT INTO `adm_grp_action_access`
        (`controller_name`,          `action_name`,    `is_ajax`, `description`                  )
VALUES  ('new-video-club',      'watched-settings',            0, 'Get current setting of viewed'),
        ('new-video-club', 'watched-settings-save',            1, 'Set new setting for viewed');

--//@UNDO

--
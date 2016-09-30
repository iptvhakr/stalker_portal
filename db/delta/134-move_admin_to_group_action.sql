--

INSERT INTO `adm_grp_action_access`
          (`controller_name`, `action_name`,                        `is_ajax`, `description`, `only_top_admin`)
VALUES    ('admins',          'move-admin-to-group',                        1, 'Change group for current admin', 1),
          ('admins',          'move-all-admin-to-group',                    1, 'Change group for all admins from current group', 1),
          ('users',           'reset-users-settings-password',              1, 'Resetting password of user access control', 0);

-- //@UNDO

--
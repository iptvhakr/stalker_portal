--

INSERT INTO `adm_grp_action_access`
        (`controller_name`,                          `action_name`,    `is_ajax`,  `description`, `hidden`, `only_top_admin`)
VALUES  ('application-catalog',                                 '',            0, 'Application catalog. List of applications',  0),
        ('application-catalog',            'application-list-json',            1, 'Application catalog. List of applications  by page + filters',  0);

--//@UNDO

--
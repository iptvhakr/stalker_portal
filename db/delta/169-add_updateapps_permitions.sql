--

INSERT INTO `adm_grp_action_access`
          (`controller_name`,                          `action_name`, `is_ajax`, `description`)
VALUES    ('application-catalog',  'smart-application-update',                1, 'Application for SmartLauncher. Updating applications'),
          ('application-catalog',  'smart-application-check-update',          1, 'Application for SmartLauncher. Checking for updates for applications');

-- //@UNDO

--
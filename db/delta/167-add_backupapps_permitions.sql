--

INSERT INTO `adm_grp_action_access`
          (`controller_name`,                          `action_name`, `is_ajax`, `description`)
VALUES    ('application-catalog',  'smart-application-download-list',         1, 'Application for SmartLauncher. Saving list of installed applications into the file'),
          ('application-catalog',  'smart-application-upload-list',           1, 'Application for SmartLauncher. Restoring installed applications from list of saved in file');

-- //@UNDO

--
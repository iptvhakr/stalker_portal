--

INSERT INTO `adm_grp_action_access`
          (`controller_name`,                          `action_name`, `is_ajax`, `description`)
VALUES    ('certificates',                                        '',         0, 'Certificates'),
          ('certificates',                                 'current',         0, 'List of current certificates'),
          ('certificates',                     'certificate-request',         0, 'Request of new certificate'),
          ('certificates',                     'certificate-request',         0, 'Details of the certificate'),
          ('certificates',                       'current-list-json',         1, 'Getting list of current certificates'),
          ('certificates',                     'certificate-install',         1, 'Installation of confirmed certificate');

-- //@UNDO

--
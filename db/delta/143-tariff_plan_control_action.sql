--

INSERT INTO `adm_grp_action_access`
          (`controller_name`, `action_name`,                        `is_ajax`, `description`)
VALUES    ('users',    'tariff-and-service-control',                        0, 'Setting the tariff plans and additional services for users on edit page'),
          ('users',    'billing-date-control',                              0, 'Managing of billing date for users on edit page'),
          ('users',    'user-reseller-control',                             0, 'Managing of users reseller on edit page');

-- //@UNDO

--
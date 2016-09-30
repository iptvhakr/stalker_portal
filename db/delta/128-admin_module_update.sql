--

UPDATE adm_grp_action_access SET hidden = 1 where controller_name = 'information';
UPDATE adm_grp_action_access SET description = "Logs on/off of user's packages" where controller_name = 'tariffs' AND action_name = 'subscribe-log';

-- //@UNDO

--
--

UPDATE adm_grp_action_access SET action_name = REPLACE(action_name, '_', '-'), controller_name = REPLACE(controller_name, '_', '-');

--//@UNDO

--
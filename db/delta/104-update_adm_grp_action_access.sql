--

UPDATE `adm_grp_action_access` SET `view_access` = 1 WHERE `action_name` LIKE "%json" AND is_ajax = 1;

-- //@UNDO

--
--

UPDATE `adm_grp_action_access` SET `description` = 'Publish or add to schedule' WHERE `action_name` = 'enable-video';
UPDATE `adm_grp_action_access` SET `description` = 'Unpublish video' WHERE `action_name` = 'disable-video';

-- //@UNDO

--
--

ALTER TABLE `administrators` ADD COLUMN `opinion_form_flag` ENUM('fill','remind', 'no') DEFAULT NULL;
INSERT INTO `adm_grp_action_access`
        (`controller_name`,             `action_name`,    `is_ajax`,  `description`,                           `hidden`, `only_top_admin`)
VALUES  ('index',                     'opinion-check',            1, 'Checking state of flag of opinion form',        1,               1),
        ('index',                       'opinion-set',            1, 'Setting state of flag of opinion form',         1,               1);

--//@UNDO

ALTER TABLE `administrators` DROP COLUMN `opinion_form_flag`;

--
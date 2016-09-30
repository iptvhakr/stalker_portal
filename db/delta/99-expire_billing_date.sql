--

ALTER TABLE `users`
ADD COLUMN `expire_billing_date` TIMESTAMP NULL DEFAULT NULL;

INSERT INTO `adm_grp_action_access`
        (`controller_name`,             `action_name`,    `is_ajax`,  `description`, `hidden`)
VALUES  ('users',           'set-expire-billing-date',            1, 'Set/unset expire billing date for user',  0);

-- //@UNDO

ALTER TABLE `users` DROP COLUMN `expire_billing_date`;

--
--

CREATE TABLE IF NOT EXISTS `users_activity` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `users_online` INT NOT NULL DEFAULT 0,
  `time` TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`)) DEFAULT CHARSET = utf8;

INSERT INTO `adm_grp_action_access`
        (`controller_name`,                `action_name`, `is_ajax`,  `view_access`, `edit_access`, `action_access`,                 `description`, `hidden`)
VALUES  ('index',           'index-datatable1-list-json',         1,              1,              1,              1, 'Getting data for index page',        1),
        ('index',           'index-datatable2-list-json',         1,              1,              1,              1, 'Getting data for index page',        1),
        ('index',           'index-datatable3-list-json',         1,              1,              1,              1, 'Getting data for index page',        1),
        ('index',           'index-datatable4-list-json',         1,              1,              1,              1, 'Getting data for index page',        1),
        ('index',           'index-datatable5-list-json',         1,              1,              1,              1, 'Getting data for index page',        1);

-- //@UNDO

DROP TABLE IF EXISTS `users_activity`;

--

--

CREATE TABLE IF NOT EXISTS `package_subscribe_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `set_state` TINYINT NOT NULL,
  `package_id` INT(11) NOT NULL,
  `initiator_id` INT(11) NULL,
  `initiator` SET("admin","user","api") NOT NULL DEFAULT 'api' ,
  `modified` TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`)) DEFAULT CHARSET = utf8;

INSERT INTO `adm_grp_action_access`
        (`controller_name`, `action_name`,              `is_ajax`,  `description`, `hidden`)
VALUES  ('tariffs',         'subscribe-log',                    0, 'Logs on/off of user\'s packages ',  0),
        ('tariffs',         'subscribe-log-json',               1, 'Logs on/off of user\'s packages by page + filters',  0);

-- //@UNDO

DROP TABLE IF EXISTS `package_subscribe_log`;

--
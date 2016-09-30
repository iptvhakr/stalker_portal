--

CREATE TABLE IF NOT EXISTS `fav_radio` (
  `id`        INT          NOT NULL AUTO_INCREMENT,
  `uid`       INT UNSIGNED NOT NULL,
  `fav_radio` TEXT         NULL,
  `addtime`   DATETIME     NULL,
  `edittime`  TIMESTAMP    NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uid_UNIQUE` (`uid` ASC)
) DEFAULT CHARSET = utf8;

-- //@UNDO

DROP TABLE IF EXISTS `fav_radio`;

--
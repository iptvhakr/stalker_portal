--

CREATE TABLE `fav_karaoke` (
  `id`        INT          NOT NULL AUTO_INCREMENT,
  `uid`       INT UNSIGNED NOT NULL,
  `fav_karaoke` TEXT         NULL,
  `addtime`   DATETIME     NULL,
  `edittime`  TIMESTAMP    NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uid_UNIQUE` (`uid` ASC)
) DEFAULT CHARSET = utf8;

--//@UNDO

DROP TABLE IF EXISTS `fav_radio`;

--
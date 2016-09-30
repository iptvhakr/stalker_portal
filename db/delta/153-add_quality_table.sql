--

CREATE TABLE `quality` (
  `id`          INT         NOT NULL AUTO_INCREMENT,
  `num_title`   VARCHAR(45) NOT NULL DEFAULT '',
  `text_title`  VARCHAR(45) NOT NULL DEFAULT '',
  `width`       SMALLINT    NOT NULL DEFAULT 0,
  `height`      SMALLINT    NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET = UTF8;

INSERT INTO `quality`
VALUES(NUll, '240', 'Regular quality', '320', '240'),
      (NUll, '320', 'Regular quality', '480', '320'),
      (NUll, '480', 'Good quality', '720', '480'),
      (NUll, '576', 'Good quality', '720', '576'),
      (NUll, '720', 'Excellent quality', '1280', '720'),
      (NUll, '1080', 'Excellent quality', '1920', '1080'),
      (NUll, '4K', 'Ultra high quality', '3840', '2160');


-- //@UNDO

DROP TABLE `quality`;

--
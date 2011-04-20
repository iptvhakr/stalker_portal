alter table screenshots drop path;

CREATE TABLE IF NOT EXISTS changelog (
  change_number BIGINT NOT NULL,
  delta_set VARCHAR(10) NOT NULL,
  start_dt TIMESTAMP NOT NULL,
  complete_dt TIMESTAMP NULL,
  applied_by VARCHAR(100) NOT NULL,
  description VARCHAR(500) NOT NULL,
);

INSERT INTO `changelog` VALUES (1,'Main','2011-04-19 10:41:11','2011-04-19 10:41:11','dbdeploy','1-initial_schema.sql'),
(2,'Main','2011-04-19 17:41:11','2011-04-19 17:41:11','dbdeploy','2-cities.sql');
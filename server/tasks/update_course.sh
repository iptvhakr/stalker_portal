#!/bin/sh

wget -q -O - http://localhost/stalker_portal/server/tasks/update_course.php && echo 1 || echo 0
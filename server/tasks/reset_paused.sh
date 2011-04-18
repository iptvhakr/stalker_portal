#!/bin/sh

wget -q -O - http://localhost/stalker_portal/server/tasks/reset_paused.php && echo 1 || echo 0
#!/bin/sh

wget -q -O - http://localhost/stalker_portal/server/tasks/clear_master_log.php && echo 1 || echo 0
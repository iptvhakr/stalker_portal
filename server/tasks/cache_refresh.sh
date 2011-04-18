#!/bin/sh

wget -q -O - http://localhost/stalker_portal/server/tasks/cache_refresh.php && echo 1 || echo 0
#!/bin/sh

wget -q -O - http://localhost/stalker_portal/server/tasks/clear_epg.php && echo 1 || echo 0
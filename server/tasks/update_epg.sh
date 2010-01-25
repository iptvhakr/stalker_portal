#!/bin/sh

wget -q -O - http://localhost/stalker_portal/server/tasks/update_epg.php?$1 && echo 1 || echo 0
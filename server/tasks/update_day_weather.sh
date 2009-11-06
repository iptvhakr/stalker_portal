#!/bin/sh

wget -q -O - http://localhost/stalker_portal/server/tasks/update_day_weather.php && echo 1 || echo 0
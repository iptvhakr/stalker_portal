#!/bin/bash

API_URL=http://localhost/stalker_portal/api/monitoring_links/
PART=1/1

function get_channels {
    curl -H "Accept: text/channel-monitoring-id-url,part=$PART" --request GET $API_URL 2>/dev/null
}

function set_ok {
    curl --request PUT $API_URL$1 --data 'status=1' >/dev/null 2>&1
}

function set_fail {
    curl --request PUT $API_URL$1 --data 'status=0' >/dev/null 2>&1
}


get_channels | while read line
do
    link_id=`echo $line | cut -f1 -d ' ' /dev/stdin`
    url=`echo $line | cut -f2 -d ' ' /dev/stdin`
    
    #echo $link_id
    echo "Start checking $url"

    result=$(./check_channel.sh $url $link_id)
    
    #echo $result

    if [ $result == "1" ] ; then
        echo "send OK"
        set_ok $link_id
    else
        echo "send FAIL"
        set_fail $link_id
    fi
done

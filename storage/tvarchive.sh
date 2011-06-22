#!/bin/bash


BASEDIR="$0"
BASEDIR="${BASEDIR%/*}"
BASEDIR=`cd $BASEDIR; pwd`

while : ; do

    result=`cd $BASEDIR;php ./tvarchivesync.php`
    
    #echo $result

    if [[ $result == "1" ]]; then
        exit 0;
    fi

    sleep 5m
done
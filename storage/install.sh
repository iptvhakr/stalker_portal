#!/bin/bash


BASEDIR="$0"
BASEDIR="${BASEDIR%/*}"
BASEDIR=`cd $BASEDIR; pwd`

touch .tasks
chmod 666 .tasks

sed "s%@STORAGE_PATH@%$BASEDIR%" ./src/tvarchivetasks.conf > /etc/init/tvarchivetasks.conf
start tvarchivetasks

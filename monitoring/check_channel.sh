#!/bin/bash

URL=$1
LINK_ID=$2

TMP_DIR=/tmp/
TMP_FILE=$TMP_DIR$LINK_ID.ts
PID_FILE=$TMP_DIR$LINK_ID.pid
TIMEOUT=5

#echo $TMP_FILE

cvlc $URL --daemon --pidfile $PID_FILE --sout '#duplicate{dst=std{access=file,mux=ts,dst=\"'$TMP_FILE'\"}}' >/dev/null 2>&1
#echo start counting $TIMEOUT s
sleep $TIMEOUT
#echo $!
kill -9 `cat $PID_FILE`

SIZE=`du $TMP_FILE | cut -f1`

#echo $SIZE

if [ $SIZE -gt 0 ]; then
	echo 1
else
	echo 0
fi

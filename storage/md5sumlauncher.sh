#!/bin/bash

PID=`cat /proc/self/status | grep ^Pid: | awk '{print $2}'`;
echo $PID >  /tmp/$1_$3.pid
cd $2$1
find * -type f -exec md5sum {} + > $1.md5
rm /tmp/$1_$3.pid

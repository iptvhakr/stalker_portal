#!/bin/bash
#compare environment variables with default values (file vars.ini)

ini_vars=`cat "${PORTAL_PATH}system/variables/vars.ini" | grep -v '^;' | sed '/^$/d'`
echo "environment correction file content: ${ini_vars}"

for i in ${ini_vars}
do
   varName=`echo ${i} | sed 's/=.*//g'`
   echo "varName ${varName}"
   varValue=${i#"${varName}="}
   echo "varValue ${varValue}"
   envValue=`fw_printenv ${varName} 2>/dev/null | sed 's/^.*=//g'`
   echo "envValue ${envValue}"

if [[ "${varValue}" == "${envValue}" ]]; then
   echo "environment variable ${varName} is OK"
else
   echo "set variable ${varName} from value ${envValue} to value ${varValue}"
   fw_setenv "${varName}" "${varValue}"
fi
done

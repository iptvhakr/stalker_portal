Environment variables checking mechanism.
=========================================


Description
===========

Check environment variables at the start of the portal page and at the start of the test.sh script.<br>
If variable is missing or its value is not the same as specified in the file it will be overwritten.


How to use
==========

- It is necessary to uncomment the following line in the test.sh file:<br>
`. "${PORTAL_PATH}system/variables/check.sh"`
- Replace files in the "variables" folder with files from "on" folder
- Add variables values in vars.ini file
- if path to portal folder not "/home/web/" replace old path with new one at test.sh file:<br>
`PORTAL_PATH="/home/web/"`

Files structure
===============

<table>
<tr><td>check.js</td><td>Function stub</td></tr>
<tr><td>check.sh</td><td>Shell script stub</td></tr>
<tr><td>vars.ini</td><td>variables file</td></tr>
<tr><td>readme.md</td><td>description</td></tr>
<tr><td>on/</td><td>enable check</td></tr>
<tr><td>on/check.js</td><td>Function code. It will check variables at portal page start</td></tr>
<tr><td>on/check.sh</td><td>Shell script code. It will check variables at test.sh script start</td></tr>
<tr><td>on/vars.ini</td><td>variables to check</td></tr>
<tr><td>on/readme.md</td><td>description</td></tr>
<tr><td>off/</td><td>disable check</td></tr>
<tr><td>off/check.js</td><td>Function stub</td></tr>
<tr><td>off/check.sh</td><td>Shell script stub </td></tr>
<tr><td>off/vars.ini</td><td>variables file</td></tr>
<tr><td>off/readme.md</td><td>description</td></tr>
</table>

Author
=========
- [Fedotov Dmytro]

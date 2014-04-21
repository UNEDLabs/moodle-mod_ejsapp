#!/bin/sh
#Signs the applets uploaded with EJSApp
# sh sign.sh jarToSign certificate passwd alias

jarsigner -storetype pkcs12 -keystore $2 -storepass $3 -tsa http://timestamp.comodoca.com/rfc3161 $1 $4

exit 0

###gmail-import-use-api

####google account setting
- goto [google console](https://console.developers.google.com/apis/credentials)
- create new project
- create OAuth 2.0 client IDs
- download OAuth client secret (json)

####project setting
- setting gmail account & password
- mkdir -p var/cache var/key
- cmod -R 777 var/

####Insufficient Permission (如果有發生權限不足的情況)
- rm var/key/google-token.json

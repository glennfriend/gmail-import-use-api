##gmail-import-use-api

####Google account setting
- goto [google console](https://console.developers.google.com/apis/credentials)
- create new project
- create OAuth 2.0 client IDs
- download OAuth client secret (json)

####Project setting
- create database
- setting gmail account & password
- mkdir -p var/cache var/key
- cmod -R 777 var/

####Try
- php shell/send-test exec
- php shell/import exec
- php shell/get

####Insufficient Permission
- rm var/key/google-token.json
- 如果有發生權限不足的情況, 刪除該檔案, 重新取得授權碼 & 修改至設定檔內容

####Import rule
- import unread email (update eamil from unread to read)
- import send email (<strong>delete the email</strong>)

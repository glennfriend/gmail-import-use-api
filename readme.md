##gmail-import-use-api

####Google account setting
- goto [google console](https://console.developers.google.com/apis/credentials)
- create new project
- create OAuth 2.0 client IDs
- download OAuth client secret (json)

####Project setting
- setting gmail account & password
- mkdir -p var/cache var/key
- cmod -R 777 var/

####Insufficient Permission
- rm var/key/google-token.json
- 如果有發生權限不足的情況, 刪除該檔案, 重新取得權限, 並且修改設定檔內容

####Import rule
- unread email to read
- send email to delete

##gmail-import-use-api

####Google account setting
- goto [google console](https://console.developers.google.com/apis/credentials)
- create new project
- create OAuth 2.0 client IDs
- download OAuth client secret (json)

####Google account setting
- https://myaccount.google.com/security
- 最下方  [允許安全性較低的應用程式] 設定處於停用狀態
- 改為    [允許安全性較低的應用程式] 設定處於啟用狀態
- 等一分鐘

####Project setting
- create database
- setting gmail account & password
- mkdir -p var/cache var/key
- cmod -R 777 var/

####取得權限
- 任意執行一個 shell
- console 提示一個取得權限的網址
- 將網址內的 secret code 放置在專案的設定檔中
- 完成之後該 secret code 可以清除

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

##Install

####Google account add OAuth client id
- goto [google console](https://console.developers.google.com/apis/credentials)
- create new project
- create OAuth 2.0 client IDs
- download OAuth client secret (json)

####Google account enable service
- goto [google console](https://console.developers.google.com/apis/credentials)
- enable "Gmail API"

####Google account security setting
- https://myaccount.google.com/security
- 最下方 [允許安全性較低的應用程式] 設定處於 "停用" 狀態
- 更改為 [允許安全性較低的應用程式] 設定處於 "啟用" 狀態
- 等一分鐘

####Gmail setting
- "轉寄和 POP/IMAP" > enable IMAP

####Project setting
- create database
- setting gmail account & password
- mkdir -p var/cache var/key
- cmod -R 777 var/

####取得權限
- 任意執行一個 shell ( ex. php shell/sent-test exec )
- console 提示一個取得權限的網址
- 將網址內的 secret code 放置在專案的設定檔中
- 再執行一次 shell
- 完成之後該 secret code 可以清除

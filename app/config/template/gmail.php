<?php

return [

    /**
     *  提供服務者 的 公關名稱
     */
    'name' => 'Client Service',

    /**
     *  提供服務者 的 email address
     */
    'email' => 'xxxxxx@gmail.com',

    /**
     *  client secret
     *      - 從 https://console.developers.google.com/apis 產生
     *      - 取得 OAuth 2.0 用戶端 ID
     */
    'client_secret' => conf('app.path') . '/var/key/client_secret.json',

    /**
     *  gmail allow permission code
     *      - by google accounts website
     *      - 在 console 下指令之後, 程式會提示你要如何取得該 code
     *      - 設定之後重新下指令, 完成之後, 該 code 即可清除
     */
    'allow_permission_code' => '',

    /**
     *  access token
     *  該 token 會依據 gmail allow permission code 來產生
     *  程式會定回寫 token content 到該檔案
     *
     *  Tip
     *      - 該檔案讚程式產生, 請勿自行編輯
     */
    'access_token' => conf('app.path') . '/var/key/google-token.json',

];

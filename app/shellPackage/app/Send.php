<?php
namespace AppModule;

/**
 *
 */
class Send extends Tool\BaseController
{
    /**
     *
     */
    protected function SendTest()
    {
        if ("exec" !== attrib(0)) {
            pr('---- debug mode ---- (你必須要輸入參數 exec 才會真正執行)');
            exit;
        }

        $to      = "SB.cs <". conf('gmail.email') .">";
        $subject = '[gmail-import-use-api] just test';
        $body    = 'Hello World';
        $result  = \GmailManager::sendMessage($to, $subject, $body);
        if ($result) {
            pr('Send success');
        }
        else {
            pr('Send fail !');
        }

    }

}

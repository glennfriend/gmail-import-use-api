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

        $getFrom = function() {
            $fromEmail  = conf('gmail.email');
            $fromName   = conf('gmail.name');
            return "{$fromName} <{$fromEmail}>";
        };

        $to      = conf('gmail.email');
        $subject = '[gmail-import-use-api] just test';
        $body    = 'Hello World at ' . date("Y-m-d H:i:s");

        $result = \GmailManager::sendMessage($getFrom(), $to, $subject, $body);
        if ($result) {
            pr('Send success');
        }
        else {
            pr('Send fail !');
        }

    }

}

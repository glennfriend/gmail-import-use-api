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
    protected function sendTest()
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

    /**
     *
     */
    protected function send()
    {
        if (!attrib('to') || !attrib('body')) {
            pr(<<<'EOD'
------------------------------------------------------------
arguments:
    --to            send to email address
    --subject       email subject
    --body          email content message

example:
    php send --to xxx@gmail.com --subject "hello" --body "hi"
    php send --to xxx@gmail.com --body "$(cat message.txt)"
------------------------------------------------------------
EOD
);
            exit;
        }

        $getFrom = function() {
            $fromEmail  = conf('gmail.email');
            $fromName   = conf('gmail.name');
            return "{$fromName} <{$fromEmail}>";
        };

        $to      = attrib('to');
        $subject = attrib('subject', 'not-subject');
        $body    = attrib('body');

        $result = \GmailManager::sendMessage($getFrom(), $to, $subject, $body);
        if ($result) {
            pr('Send success');
        }
        else {
            pr('Send fail !');
        }
    }

}

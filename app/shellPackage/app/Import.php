<?php
namespace AppModule;

/**
 *
 */
class Import extends Tool\BaseController
{

    /**
     *  import all emails
     *      - 將 send 信件匯入, 然後刪除
     *      - 將 get 的未讀信信匯入, 設定成已讀
     *      - 導入 send 信件要早於 get, 奇怪的原因如下面 "NOTE" 寫的
     *
     *  NOTE: 一個待查的問題, 如果寄信給自己, 只會收到一封信, 然而 匯入 "get" & "send" 都會取得這封信, 這個會造成一些問題....
     *
     */
    protected function importAll()
    {
        if ("exec" !== attrib(0)) {
            pr('---- debug mode ---- (你必須要輸入參數 exec 才會真正執行)');
            exit;
        }
        di('log')->record('start PHP '. phpversion() );

        $show = [];
        $inboxes = new \Inboxes();

        // import "send" emails
        $messages = \GmailManager::getSendMessages();
        foreach ($messages as $message) {
            $inbox = $this->makeInbox($message);
            $result = $inboxes->addInbox($inbox);
            if ($result) {
                // 刪除該信件!!
                \GmailManager::deleteMessage($message['googleMessageId']);
            }
            else {
                $result = 'fail';
            }

            $show[] = [
                $message['googleMessageId'],
                $inbox->getMessageId(),
                $inbox->getSubject(),
                'send to : '. $inbox->getToEmail(),
                date('Y-m-d H:i:s', $inbox->getEmailCreateTime()),
                $result
            ];
        }

        // import "get" emails
        $messages = \GmailManager::getUnreadMessages();
        foreach ($messages as $message) {
            $inbox = $this->makeInbox($message);
            $result = $inboxes->addInbox($inbox);
            if ($result) {
                // 將信件設定為 已讀
                \GmailManager::setMessageLabelToIsRead($message['googleMessageId']);
            }
            else {
                $result = 'fail';
            }

            $show[] = [
                $message['googleMessageId'],
                $inbox->getMessageId(),
                $inbox->getSubject(),
                'get from: ' . $inbox->getFromEmail(),
                date('Y-m-d H:i:s', $inbox->getEmailCreateTime()),
                $result
            ];
        }

        if ($show) {
            pr(
                \ConsoleHelper::table(
                    ['google message id', 'message id', 'subject', 'from/to', 'date', 'result'],
                    $show
                )
            );
        }

    }

    /**
     *
     */
    private function makeInbox($info)
    {
        $heads = \Ydin\ArrayKit\Dot::factory($info['headers']);

        if (false !== strstr($heads('from'), '<') ) {
            // has name
            $fromName   = strip_tags($heads('from'));
            $fromEmail  = trim( strstr($heads('from'),'<'), '<>');
        }
        else {
            $fromName   = '';
            $fromEmail  = $heads('from');
        }

        if (false !== strstr($heads('to'), '<') ) {
            // has name
            $toName     = strip_tags($heads('to'));
            $toEmail    = trim( strstr($heads('to'),'<'), '<>');
        }
        else {
            $toName     = '';
            $toEmail    = $heads('to');
        }


        $inbox = new \Inbox();
        $inbox->setMessageId            ( $heads('message-id')  );
        $inbox->setReplyToMessageId     ( $heads('in-reply-to') );
        $inbox->setReferenceMessageIds  ( $heads('references')  );
        $inbox->setFromEmail            ( $fromEmail            );
        $inbox->setToEmail              ( $toEmail              );
        $inbox->setReplyToEmail         ( $fromEmail            );
        $inbox->setFromName             ( $fromName             );
        $inbox->setToName               ( $toName               );
        $inbox->setReplyToName          ( $inbox->getFromName() );
        $inbox->setSubject              ( $heads('subject')     );

        $date = $this->timezoneConvert( $heads('date'), 'UTC', conf('app.timezone') );
        $inbox->setEmailCreateTime      ( strtotime($date)                          );

        $inbox->setProperty             ('googleMessageId', $info['googleMessageId']);
        $inbox->setProperty             ('headers',         $info['headers']        );
        $inbox->setProperty             ('data',            $info['data']           );

        if ($info['body']) {
            $inbox->setContent($info['body']);
        }
        foreach ($info['data'] as $item) {
            if ( 'text/plain' === $item['mimeType'] && isset($item['content']) ) {
                $inbox->setContent($item['content']);
            }
        }

        return $inbox;
    }

    /**
     *  時區轉換程式 helper
     *
     *  將一個已經格式化的值代入
     *      - 2000-12-31 00:10:20
     *      - 19-Nov-2015 03:38:50 +0000
     *
     *  並聲明是那一個 timezone
     *  最後要決定輸出為那一個 timezone
     *
     *  @string $timeString - time format
     *  @string $from       - timezone string
     *  @string $to         - timezone string
     *  @return time format
     */
    private function timezoneConvert($timeString, $from, $to)
    {
        try {
            $convert = new \DateTime($timeString, new \DateTimeZone($from));
            $convert->setTimezone(new \DateTimeZone($to));
            return $convert->format('Y-m-d H:i:s');
        }
        catch (Exception $e) {
            // error
        }
        return '1970-01-01 00:00:00';
    }

}

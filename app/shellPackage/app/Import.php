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

        // import "get", "send" emails
        $messages = array_merge(
            \GmailManager::getUnreadMessages(),
            \GmailManager::getSendMessages()
        );

        // 時間為軸 舊 -> 新 排序
        $messageSortByTime = [];
        foreach ($messages as $message) {
            $time = strtotime($message['headers']['date']);
            $messageSortByTime[$time] = $message;
        }
        sort($messageSortByTime);

        //
        foreach ($messageSortByTime as $message) {
            $inbox = $this->makeInbox($message);
            $result = $inboxes->addInbox($inbox);
            if ($result) {

                if ('unread'==$message['customType']) {
                    // 將信件設定為 已讀
                    \GmailManager::setMessageLabelToIsRead($message['googleMessageId']);
                }
                else {
                    // 刪除該信件!!
                    \GmailManager::deleteMessage($message['googleMessageId']);
                }

            }
            else {
                $result = 'fail';
            }

            if ('unread'==$message['customType']) {
                $type = 'get from: '. $inbox->getFromEmail();
            }
            else {
                $type = 'send to : '. $inbox->getToEmail();
            }

            $show[] = [
                $message['googleMessageId'],
                $inbox->getMessageId(),
                $inbox->getSubject(),
                $type,
                date('Y-m-d H:i:s', $inbox->getEmailCreateTime()),
                $result
            ];
        }

        if ($show) {
            table(
                $show,
                ['google message id', 'message id', 'subject', 'from/to', 'date', 'result']
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
        $inbox->setReferenceMessageIds  ( $heads('references')  );
        $inbox->setFromEmail            ( $fromEmail            );
        $inbox->setToEmail              ( $toEmail              );
        $inbox->setFromName             ( $fromName             );
        $inbox->setToName               ( $toName               );
        $inbox->setSubject              ( $heads('subject')     );

        $date = $this->timezoneConvert( $heads('date'), 'UTC', conf('app.timezone') );
        $inbox->setEmailCreateTime      ( strtotime($date)                          );

        $inbox->setProperty             ('googleMessageId', $info['googleMessageId']);
        $inbox->setProperty             ('headers',         $info['headers']        );
        $inbox->setProperty             ('data',            $info['data']           );

        if ($info['body']) {
            $inbox->setBodySnippet($info['body']);
        }
        /*
        foreach ($info['data'] as $item) {
            if ( 'text/plain' === $item['mimeType'] && isset($item['content']) ) {
                $inbox->setBodySnippet($item['content']);
            }
        }
        */

        $referenceMessageIds = $inbox->getReferenceMessageIds();
        if (!$referenceMessageIds) {
            $parentId = 0;
        }
        else {
            $tmp = explode(" ", $referenceMessageIds);
            $firstReferenceMessageId = $tmp[0];

            $inboxes = new \Inboxes();
            $theInbox = $inboxes->getInboxByMessageId($firstReferenceMessageId);
            if ($theInbox) {
                $parentId = $theInbox->getId();
            }
            else {
                // 找不到 message id, 這可能是一個大問題
                // 但也有可能只是一個小問題 -> 新信件 比 舊信件 先被匯入
                // 之後需要想辦法重新串起來
                $parentId = -1;
            }
        }
        $inbox->setParentId($parentId);

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

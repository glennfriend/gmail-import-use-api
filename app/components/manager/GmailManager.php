<?php

/**
 *  Gmail Manager
 */
class GmailManager
{
    private static $config = [];

    public static function init($attachPath)
    {
        // 儲存附件的位置
        self::$config['attachPath'] = $attachPath;
    }

    public static function getService()
    {
        static $service;
        if ($service) {
            return $service;
        }

        $client = \GmailApiHelper::getClient();
        $service = new \Google_Service_Gmail($client);
        return $service;
    }

    public static function getMessage($messageId, $customType=null)
    {
        $optParamsGet = [];
        $optParamsGet['format'] = 'full';
        $message = self::getService()->users_messages->get('me', $messageId, $optParamsGet);

        $parts = $message->getPayload()->getParts();
        // debug
        // pr($parts); pr(""); exit;

        $body = $message->getSnippet();
        if (isset(
                $message['payload'],
                $message['payload']['body'],
                $message['payload']['body']['data']
            )) {
            $body = self::_decodeRawData($message['payload']['body']['data']);
        }

        $headers = self::_makeHeaders( $message->getPayload()->getHeaders() );

        // 解析 parts 為程式所必須要的資訊
        $partItems = self::_parseParts($parts);

        // 將 附件 及 內置媒體訊息 建立為實體檔案
        $attachFolderName = self::_getAttachmentFolderName($headers);
        $data = self::_storageAttachments($messageId, $partItems, $attachFolderName);

        return [
            'googleMessageId'   => $messageId,
            'customType'        => $customType,
            'headers'           => $headers,
            'data'              => $data,
            'body'              => $body,
        ];
    }

    /**
     *  從 gmail 取得多筆 未閱讀 的郵件
     *
     *  NOTE:
     *      由 gmail 取得的郵件是由 新 -> 舊 取得
     *      程式直接將陣列反轉輸出為 舊 -> 新
     *
     *  @see https://developers.google.com/gmail/api/guides/labels
     */
    public static function getUnreadMessages()
    {
        $optParams = [];

        // maxResults 設定很高的值
        // 是為了讓 資料庫 中資料的建立時間, 最接近 舊 -> 新 郵件
        // 但是! 還是有機會不是這個順序
        // 請特別注意這一點
        $optParams['maxResults'] = 1000;  // 1000

        // INBOX, UNREAD, SENT
        $optParams['labelIds'] = 'UNREAD';
        $messagesResponse = self::getService()->users_messages->listUsersMessages('me', $optParams);

        $messages = [];
        $list = $messagesResponse->getMessages();
        foreach ($list as $gmailMessage) {
            $messageId  = $gmailMessage->getId();
            $messages[] = self::getMessage($messageId, 'unread');
        }

        return array_reverse($messages);
    }

    /**
     *  從 gmail 取得多筆 已寄信 的郵件
     *
     *  NOTE:
     *      由 gmail 取得的郵件是由 新 -> 舊 取得
     *      程式直接將陣列反轉輸出為 舊 -> 新
     *
     *  @see https://developers.google.com/gmail/api/guides/labels
     */
    public static function getSendMessages()
    {
        $optParams = [];

        // maxResults 設定很高的值
        // 原因相同於 getUnreadMessages()
        $optParams['maxResults'] = 1000;  // 1000
        $optParams['labelIds']   = 'SENT';
        $messagesResponse = self::getService()->users_messages->listUsersMessages('me', $optParams);

        $messages = [];
        $list = $messagesResponse->getMessages();
        foreach ($list as $gmailMessage) {
            $messageId = $gmailMessage->getId();
            $messages[] = self::getMessage($messageId, 'send');
        }

        return array_reverse($messages);
    }

    /**
     *  將郵件設定為 已讀
     *      - 將 label id "UNREAD" 移除即可
     *
     *  @return boolean
     */
    public static function setMessageLabelToIsRead($messageId)
    {
        $mods = new Google_Service_Gmail_ModifyMessageRequest();
        $mods->setRemoveLabelIds(['UNREAD']);
        try {
            $message = self::getService()->users_messages->modify('me', $messageId, $mods);
            return true;
        }
        catch (Exception $e) {
            echo 'Remove message lable error ' . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     *  刪除 gmail 中的郵件
     *
     *  @return boolean
     */
    public static function deleteMessage($messageId)
    {
        try {
            $message = self::getService()->users_messages->delete('me', $messageId);
            return true;
        }
        catch (Exception $e) {
            echo 'Delete message error ' . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     *  send gmail
     *
     *  TODO: 請加入附件, 但應該不是在這個 method 擴充
     *
     *  @return boolean
     */
    public static function sendMessage($from, $to, $subject, $body)
    {
        $mail = new \Nette\Mail\Message;
        $mail
            ->setFrom   ( $from )
            ->addTo     ( $to )
            ->setSubject( $subject )
            ->setBody   ( $body )
        ;

        $messageText = $mail->generateMessage();
        $data = self::_encodeRawData($messageText);

        try {
            $message = new Google_Service_Gmail_Message();
            $message->setRaw($data);
            self::getService()->users_messages->send("me", $message);
        }
        catch (Exception $e) {
            echo 'Send message error ' . $e->getMessage() . "\n";
            return false;
        }

        return true;
    }



    // --------------------------------------------------------------------------------
    // private
    // --------------------------------------------------------------------------------

    /**
     *  儲存附件的資料夾 名稱
     *      - 每一封信件的附件, 都會有一個儲存的資料夾
     *      - 資料夾名稱不能重覆
     *
     *  @return string $folderId or boolean false
     */
    private static function _getAttachmentFolderName(Array $headers)
    {
        if (!isset($headers['from'], $headers['message-id'])) {
            return false;
        }

        $tmp = explode('<', $headers['from']);
        $fromEmail  = $tmp[count($tmp)-1];
        $fromEmail  = trim($fromEmail,'<>');
        $fromEmail  = strip_tags($fromEmail);
        $name       = strstr($fromEmail, '@', true);

        return $name . '-' . md5($headers['message-id']);
    }

    /**
     *
     */
    private static function _decodeRawData($rawData)
    {
        $sanitizedData = strtr($rawData, '-_', '+/');
        return base64_decode($sanitizedData);
    }

    /**
     *
     */
    private static function _encodeRawData($data)
    {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($data)
        );
    }



    /**
     *  初步解析 parts
     */
    private static function _parseParts($parts, $info=[])
    {
        if (!$parts) {
            return $info;
        }

        foreach ($parts as $index => $part) {

            $item = [];
            $item['mimeType'] = $part['mimeType'];

            $name = $part->getFilename();
            if ($name || strlen($name)>0) {
                $storageFilename    = self::_getFilenameByName($name);
                $item['attachId']   = $part['body']['attachmentId'];
                $item['name']       = $name;
                $item['headers']    = self::_makeHeaders($part['headers']);
            }

            $content = self::_decodeRawData($part['body']['data']);
            if ($content) {
                $item['content'] = $content;
            }

            if ($part['body']['size'] > 0) {
                $info[] = $item;
            }

            $subParts = $part->getParts();
            if ($subParts) {
                $info = array_merge(
                    $info,
                    self::_parseParts($subParts, $info)
                );
            }
        }

        return $info;
    }

    /**
     *  將 header 裡面的 name, value 陣列訊息
     *  轉換為陣列的 key, value 形式
     *
     *  @return array
     */
    private static function _makeHeaders($headers)
    {
        $results = [];
        foreach ($headers as $header) {
            $key = strtolower($header->getName());
            $results[$key] = $header->getValue();
        }
        return $results;
    }

    /**
     *  將信件中的附件儲存至指定的路徑中
     *      - 將 附件 建立為實體檔案
     *      - 將 內置媒體訊息 建立為實體檔案
     *
     *  @return array
     */
    private static function _storageAttachments($messageId, $partItems, $attachFolderName)
    {
        foreach ($partItems as $index => $item) {

            if (!isset($item['attachId'])) {
                continue;
            }
            if (!isset($item['name'])) {
                continue;
            }
            if (!isset($item['mimeType'])) {
                continue;
            }

            $name = $item['name'];
            $storageFilename = self::_getFilenameByName($name);
            $attachPartBody = self::getService()->users_messages_attachments->get('me', $messageId, $item['attachId']);
            $resource = self::_decodeRawData($attachPartBody->data);

            $fliePath = self::$config['attachPath'] . '/' . $attachFolderName;
            if (!file_exists($fliePath)) {
                mkdir($fliePath, 0777, true);
            }
            file_put_contents($fliePath . '/' . $storageFilename, $resource);

            $partItems[$index]['filename'] = $storageFilename;
            $partItems[$index]['folder']   = $attachFolderName;
            unset($partItems[$index]['attachId']);
        }

        return $partItems;
    }

    /**
     *  檔案名稱的建立
     *      - 跟原本的檔案有相關
     *      - 去除不安全的字元
     *      - 不能因為去除字元, 使得檔名有機會重覆
     */
    private static function _getFilenameByName($name)
    {
        $extensionName  = pathinfo($name, PATHINFO_EXTENSION);
        $filename       = pathinfo($name, PATHINFO_FILENAME);
        $filename       = str_replace(' ', '-', $filename);
        $filename       = str_replace('.', '-', $filename);
        $filename       = preg_replace("/[^a-zA-Z0-9一-龥\-\_\.]/u", "", $filename);
        $filename       = preg_replace("/[-]+/", "-", $filename);
        $filename      .= '-' . substr(md5($name), 0, 6);
        if ($extensionName) {
            $filename .= '.' . $extensionName;
        }
        return strtolower($filename);
    }

}

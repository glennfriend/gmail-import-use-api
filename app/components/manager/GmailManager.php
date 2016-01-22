<?php

/**
 *  Gmail Manager
 */
class GmailManager
{
    public static function getAttachmentPath()
    {
        return conf('app.path').'/var/attach';
    }

    public static function getService()
    {
        static $service;
        if ($service) {
            return $service;
        }
        // Get the API client and construct the service object.
        $client = \GmailApiHelper::getClient();
        $service = new \Google_Service_Gmail($client);
        return $service;
    }

    /**
     *  從 gmail 取得多筆郵件
     *
     *  NOTE:
     *      郵件無法依照 舊到新的時間 取得
     *      由於寫到資料庫的時候, 舊的信件 id 可能會高於新的 id
     *      所以在自己的資料庫取郵件時
     *      順序請依照 日期, 不要使用 id 來做順序的依據
     *
     */
    public static function getAll()
    {
        $optParams = [];

         // maxResults 設定很高的值
         // 取得郵件後, 請逆轉陣列順序
         // 以便在資料庫可以接近 舊 -> 新 的資料
         // 但還是會有機會不是依照這個順序
         // 請特別注意這一點
        $optParams['maxResults'] = 3;  // 1000
        
        // INBOX, UNREAD
        // @see https://developers.google.com/gmail/api/guides/labels
        $optParams['labelIds'] = 'INBOX';

        $messagesResponse = self::getService()->users_messages->listUsersMessages('me', $optParams);
        $list = $messagesResponse->getMessages();
        $list = array_reverse($list);

        $messages = [];
        foreach ($list as $gmailMessage) {
            $messageId = $gmailMessage->getId();
            $messages[] = self::getMessage($messageId);
        }

        return $messages;
    }


    public static function getMessage($messageId)
    {
        $optParamsGet = [];
        $optParamsGet['format'] = 'full'; // Display message in payload
        $message = self::getService()->users_messages->get('me',$messageId,$optParamsGet);
        // $messagePayload = $message->getPayload();

        // 這裡是預設 第一組的 headers 是包含
        //      [name] => Content-Type
        //      [value] => text/plain; charset=UTF-8
        //

        $messagePacks = [];
        $parts = $message->getPayload()->getParts();
//pr(get_class_methods($message));
//pr($message->getSnippet());
//echo "\n";

//pr($parts); exit;



/*
        $textPartIndex = 0;
        foreach ($parts as $index => $part) {
            foreach ($part['headers'] as $heads) {
                if ($heads['name'] === 'Content-Type' && 
                    $heads['value'] === 'text/plain; charset=UTF-8' ) {
                    $textPartIndex = $index;
                    break;
                }
            }
        }

        $body = $parts[$textPartIndex]['body'];
        print_r($body);
*/

        //$body = $parts[0]['parts'][1]['body'];
        $body = $parts[0]['body'];



        $decodedMessage = self::_decodeRawData($body->data);

        // TODO: 未處理附件
        // TODO: 未處理內文mime檔案

        $headers = [];
        foreach ($message->getPayload()->getHeaders() as $header) {
            $key = strtolower($header->getName());
            $headers[$key] = $header->getValue();
        }

        $attachFolderName = self::_getAttachmentFolderName($headers);
        $attachsInfo = self::_storageMessageAttachments($messageId, $parts, $attachFolderName);

        return [
            'headers'   => $headers,
            'attachs'   => $attachsInfo,
            'message'   => $decodedMessage,
        ];
    }

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
        $sanitizedData = strtr($rawData,'-_', '+/');
        return base64_decode($sanitizedData);
    }

    /**
     *  將信件中的附件儲存至指定的路徑中
     *
     *  @return array
     */
    private static function _storageMessageAttachments($messageId, $parts, $attachFolderName)
    {
        $files = [];
        foreach ($parts as $index => $part) {
            $name = $part->getFilename();
            if (!$name || strlen($name)<=0) {
                continue;
            }
            $storageFilename = self::_getFilenameByName($name);

            $attachId = $part['body']->getAttachmentId();
            $attachPartBody = self::getService()->users_messages_attachments->get('me', $messageId, $attachId);
            $resource = self::_decodeRawData($attachPartBody->data);

            $fliePath = self::getAttachmentPath() . '/' . $attachFolderName . '/content';
            if (!file_exists($fliePath)) {
                mkdir($fliePath, 0777, true);
            }
            file_put_contents($fliePath. '/'. $storageFilename, $resource);
            
            $files[] = [
                'name'      => $name,
                'filename'  => $storageFilename,
                'folder'    => $attachFolderName,
            ];
        }
        return $files;
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

    private static function _base64Deocde($data)
    {
        echo '5123512524153124532';
        exit;
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, "=", STR_PAD_RIGHT));
    }

}

<?php

/**
 *  Gmail Manager
 */
class GmailManager
{

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
        $optParams['maxResults'] = 1;  // 1000
        
        // INBOX, UNREAD
        // @see https://developers.google.com/gmail/api/guides/labels
        $optParams['labelIds'] = 'INBOX';

        $messagesResponse = self::getService()->users_messages->listUsersMessages('me', $optParams);
        $list = $messagesResponse->getMessages();
        $list = array_reverse($list);

        $messages = [];
        foreach ($list as $gmailMessage) {
            $messageId = $gmailMessage->getId(); // Grab first Message
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

        $body = $parts[0]['body'];
        $rawData = $body->data;
        $sanitizedData = strtr($rawData,'-_', '+/');
        $decodedMessage = base64_decode($sanitizedData);

        // TODO: 未處理附件
        // TODO: 未處理內文mime檔案

        $headers = [];
        foreach ($message->getPayload()->getHeaders() as $header) {
            $key = strtolower($header->getName());
            $headers[$key] = $header->getValue();
        }

        pr([
            'headers'   => $headers,
            'message'   => $decodedMessage,
        ]);
        exit;

        return [
            'headers'   => $headers,
            'message'   => $decodedMessage,
        ];
    }

}

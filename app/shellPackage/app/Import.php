<?php
namespace AppModule;

/**
 *
 */
class Import extends Tool\BaseController
{

    /**
     *  import all emails
     */
    protected function importAll()
    {
        if (!attrib('exec')) {
            pr('---- debug mode ---- (你必須要輸入參數 exec 才會真正執行)');
            exit;
        }
        di('log')->record('start PHP '. phpversion() );


        $messages = \GmailManager::getAll();
        $inboxes = new \Inboxes();
        foreach ($messages as $message) {
            $inbox = $this->makeInbox($message);
            pr($inbox);
            //$inboxes->addInbox($inbox);
        }

    }

    /**
     *
     */
    protected function getLabels()
    {
        // Get the API client and construct the service object.
        $client = \GmailApiHelper::getClient();
        $service = new \Google_Service_Gmail($client);

        // Print the labels in the user's account.
        $user = 'me';
        $results = $service->users_labels->listUsersLabels($user);

        if (count($results->getLabels()) == 0) {
            pr("No labels found.");
        }
        else {
            pr("Labels:");
            foreach ($results->getLabels() as $label) {
                pr("- " . $label->getName());
            }
        }
    }

    private function makeInbox($info)
    {
        $heads = \Ydin\ArrayKit\Dot::factory($info['headers']);

        $from   = explode('<', $heads('from')   );
        $to     = explode('<', $heads('to')     );
        $date   = $this->timezoneConvert( $heads('date'), 'UTC', 'America/Los_Angeles' );

        $inbox = new \Inbox();
        $inbox->setMessageId            ( $heads('message-id')              );
        $inbox->setReplyToMessageId     ( $heads('in-reply-to')             );
        $inbox->setReferenceMessageIds  ( $heads('references')              );
        $inbox->setFromEmail            ( trim($from[1], '<>')              );
        $inbox->setToEmail              ( trim($to[1],   '<>')              );
        $inbox->setReplyToEmail         ( trim($heads('return-path'), '<>') );

        if ( isset($from[0]) ) {
            $inbox->setFromName ($from[0]);
        }
        if ( isset($to[0]) ) {
            $inbox->setToName ($to[0]);
        }
        $inbox->setReplyToName ( $inbox->getFromName() );

        $inbox->setSubject          ( $heads('subject') );
        $inbox->setContent          ( $info['message']  );
        $inbox->setEmailCreateTime  ( strtotime($date)  );
        $inbox->setProperty('headers', $info['headers'] );
        $inbox->setProperty('attachs', $info['attachs'] );

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

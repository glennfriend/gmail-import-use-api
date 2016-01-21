<?php
exit;


$basePath = dirname(__DIR__);
require_once $basePath . '/app/bootstrap.php';
initialize($basePath);

perform();
exit;

// --------------------------------------------------------------------------------
// 
// --------------------------------------------------------------------------------

/*
 * 
 */
function perform()
{
    if (!getParam('exec')) {
        pr('---- debug mode ---- (你必須要輸入參數 exec 才會真正執行)');
    }
    di('log')->record('start PHP '. phpversion() );

    //
    if (getParam('exec')) {
        $mails = di('gmail')->getEmails();
    }
    else {
        $mails = di('gmail')->getEmailsNotSettingRead();
    }

    if ($error = di('gmail')->getError()) {
        pr($error, true);
        exit;
    }

    $inboxes = new Inboxes();
    $messages = [];
    foreach ($mails as $mailInfo) {

        $inbox = makeInbox($mailInfo);

        if (getParam('exec')) {
            $result = $inboxes->addInbox($inbox);
            if ($result) {
                $messages[] = ['add success', $mailInfo['message_id'], $mailInfo['subject'], $result];
            }
            else {
                $messages[] = ['add error', $mailInfo['message_id'], $mailInfo['subject'], ''];
            }
        }
        else {
            $messages[] = ['pass', $mailInfo['message_id'], $mailInfo['subject'], ''];
        }

    }

    pr(
        Helper\Console::table(
            ['status','message id', 'Subject', 'inbox id'],
            $messages
        )
    );
    pr("done", true);
}

function makeInbox($info)
{
    $from    = (array) $info['from'][0];
    $replyTo = (array) $info['reply_to'][0];
    $to      = (array) $info['to'][0];

    $date    = timezoneConvert( $info['date'], 'UTC', 'America/Los_Angeles' );

    $inbox = new Inbox();
    $inbox->setMessageId            ( $info['message_id']                       );
    $inbox->setReplyToMessageId     ( $info['reply_to_message_id']              );
    $inbox->setReferenceMessageIds  ( $info['reference_message_ids']            );
    $inbox->setFromEmail            (    $from['mailbox'] .'@'.    $from['host']);
    $inbox->setToEmail              (      $to['mailbox'] .'@'.      $to['host']);
    $inbox->setReplyToEmail         ( $replyTo['mailbox'] .'@'. $replyTo['host']);

    if ( isset($from['personal']) ) {
        $inbox->setFromName ($from['personal']);
    }
    if ( isset($to['personal']) ) {
        $inbox->setToName ($to['personal']);
    }
    if ( isset($replyTo['personal']) ) {
        $inbox->setReplyToName ($replyTo['personal']);
    }

    $inbox->setSubject          ( $info['subject']  );
    $inbox->setContent          ( $info['body']     );
    $inbox->setEmailCreateTime  ( strtotime($date)  );
    $inbox->setProperty('info', [
        'date'              => $info['date'],
        'body_header'       => $info['body_header'],
        'mail_attachments'  => $info['mail_attachments'],
        'file_attachments'  => $info['file_attachments'],
    ]);

    /*
        // NOTE: you can try the information
        $inbox->setProperty('info', [
            'from'              => $info['from'],
            'reply_to'          => $info['reply_to'],
            'to'                => $info['to'],
            'date'              => $info['date'],
            'body_header'       => $info['body_header'],
            'mail_attachments'  => $info['mail_attachments'],
            'file_attachments'  => $info['file_attachments'],
        ]);
    */

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
function timezoneConvert($timeString, $from, $to)
{
    try {
        $convert = new DateTime($timeString, new DateTimeZone($from));
        $convert->setTimezone(new DateTimeZone($to));
        return $convert->format('Y-m-d H:i:s');
    }
    catch (Exception $e) {
        // error
    }
    return '1970-01-01 00:00:00';
}


<?php

/**
 *  Inbox
 *
 */
class Inbox extends BaseObject
{

    /**
     *  請依照 table 正確填寫該 field 內容
     *  @return array()
     */
    public static function getTableDefinition()
    {
        return [
            'id' => [
                'type'    => 'integer',
                'filters' => ['intval'],
                'storage' => 'getId',
                'field'   => 'id',
            ],
            'parentId' => [
                'type'    => 'integer',
                'filters' => ['intval'],
                'storage' => 'getParentId',
                'field'   => 'parent_id',
            ],
            'fromEmail' => [
                'type'    => 'string',
                'filters' => ['strip_tags','trim'],
                'storage' => 'getFromEmail',
                'field'   => 'from_email',
            ],
            'toEmail' => [
                'type'    => 'string',
                'filters' => ['strip_tags','trim'],
                'storage' => 'getToEmail',
                'field'   => 'to_email',
            ],
            'fromName' => [
                'type'    => 'string',
                'filters' => ['strip_tags','trim'],
                'storage' => 'getFromName',
                'field'   => 'from_name',
            ],
            'toName' => [
                'type'    => 'string',
                'filters' => ['strip_tags','trim'],
                'storage' => 'getToName',
                'field'   => 'to_name',
            ],
            'subject' => [
                'type'    => 'string',
                'filters' => ['strip_tags','trim'],
                'storage' => 'getSubject',
                'field'   => 'subject',
            ],
            'bodySnippet' => [
                'type'    => 'string',
                'filters' => ['trim'],
                'storage' => 'getBodySnippet',
                'field'   => 'body_snippet',
            ],
            'emailCreateTime' => [
                'type'    => 'timestamp',
                'filters' => ['dateval'],
                'storage' => 'getEmailCreateTime',
                'field'   => 'email_create_time',
                'value'   => strtotime('1970-01-01'),
            ],
            'messageId' => [
                'type'    => 'string',
                'filters' => ['message_trim'],
                'storage' => 'getMessageId',
                'field'   => 'message_id',
            ],
            'referenceMessageIds' => [
                'type'    => 'string',
                'filters' => ['message_trim'],
                'storage' => 'getReferenceMessageIds',
                'field'   => 'reference_message_ids',
            ],
            'properties' => [
                'type'    => 'string',
                'filters' => ['arrayval'],
                'storage' => 'getProperties',
                'field'   => 'properties',
            ],
        ];
    }

    /* ------------------------------------------------------------------------------------------------------------------------
        extends
    ------------------------------------------------------------------------------------------------------------------------ */

    /**
     *  取得附件資訊
     */
    public function getAttachments()
    {
        // 未使用, 考慮改成 getAttachmentsInfos
        die('23582034582349058023495823405');



        $attachments = $this->getProperty('info')['attachments'];
        if (!$attachments) {
            return [];
        }

        return [
            'file'      => $attachments['file'],
            'filename'  => $attachments['filename'],
            'path'      => $attachments['path'],
        ];

    }

    /**
     *  trim
     */
    protected function filter_message_trim( $value )
    {
        return trim($value);
    }

    /* ------------------------------------------------------------------------------------------------------------------------
        lazy loading methods
    ------------------------------------------------------------------------------------------------------------------------ */
}

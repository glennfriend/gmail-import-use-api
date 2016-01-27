<?php
namespace AppModule;

/**
 *
 */
class Info extends Tool\BaseController
{
    /**
     *
     */
    protected function getEmail()
    {
        if (!attrib(0)) {
            pr('----------------------------------------------------- [help]');
            pr('代入 參數 表示取得哪一筆資料');
            pr('    example:');
            pr('        $ php get.php 10');
            pr('        $ php get.php last');
            pr('------------------------------------------------------ [end]');
            exit;
        }
        $id = attrib(0);
        $inboxes = new \Inboxes();

        // get by last
        if ('last'===$id) {
            $myInboxes = $inboxes->findInboxes([
                '_order'        => 'id,DESC',
                '_itemsPerPage' => 1
            ]);
            if (isset($myInboxes[0])) {
                $this->_show($myInboxes[0]);
            }
            else {
                pr('Inbox data not found');
            }
            exit;
        }

        // get by index
        $inbox = $inboxes->getInbox($id);
        if (!$inbox) {
            pr('Inbox data not found');
            exit;
        }
        $this->_show($inbox);
    }

    /**
     *
     */
    protected function getLabels()
    {
        $client = \GmailApiHelper::getClient();
        $service = new \Google_Service_Gmail($client);

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

    /**
     *
     */
    private function _show($inbox)
    {
        pr(
            \ConsoleHelper::table(
                ['subject', 'date', 'from', 'to', 'id'],
                [[
                    $inbox->getSubject(),
                    date('Y-m-d H:i:s', $inbox->getEmailCreateTime()),
                    $inbox->getFromEmail(),
                    $inbox->getToEmail(),
                    $inbox->getId()
                ]]
            )
        );
        pr($inbox->getContent());
        pr('<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');

        $data = $inbox->getProperty('data');
        $attachs = [];
        foreach ($data as $item) {
            if (!isset($item['name'])) {
                // 不是附件
                continue;
            }
            $attachs[] = $item['name'];
        }

        $count = count($attachs);
        if ($count>0) {
            pr("---------- attachments x {$count} ----------");
            foreach ($attachs as $name) {
                pr($name);
            }
        }

        pr('');
    }

}

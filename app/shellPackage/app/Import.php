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

        // Get the API client and construct the service object.
        $client = \GmailApiHelper::getClient();
        $service = new \Google_Service_Gmail($client);


        $optParams = [];
        $optParams['maxResults'] = 5; // Return Only 5 Messages
        $optParams['labelIds'] = 'INBOX'; // Only show messages in Inbox
        $messages = $service->users_messages->listUsersMessages('me',$optParams);
        $list = $messages->getMessages();
        $messageId = $list[0]->getId(); // Grab first Message


        $optParamsGet = [];
        $optParamsGet['format'] = 'full'; // Display message in payload
        $message = $service->users_messages->get('me',$messageId,$optParamsGet);
        $messagePayload = $message->getPayload();
        $headers = $message->getPayload()->getHeaders();
        $parts = $message->getPayload()->getParts();

        $body = $parts[0]['body'];
        $rawData = $body->data;
        $sanitizedData = strtr($rawData,'-_', '+/');
        $decodedMessage = base64_decode($sanitizedData);

        var_dump($decodedMessage);


    }

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


}

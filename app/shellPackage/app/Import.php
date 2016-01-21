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

        // Get the API client and construct the service object.
        $client = \GmailApiManager::getClient();
        $service = new \Google_Service_Gmail($client);

        // Print the labels in the user's account.
        $user = 'me';
        $results = $service->users_labels->listUsersLabels($user);

        if (count($results->getLabels()) == 0) {
            print "No labels found.\n";
        }
        else {
            print "Labels:\n";
            foreach ($results->getLabels() as $label) {
                printf("- %s\n", $label->getName());
            }
        }






/*
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
echo "\n";
exit;
*/

    }

}

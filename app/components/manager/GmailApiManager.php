<?php

/**
 *  Gmail Api Manater
 */
class GmailApiManager
{

    /**
     *  get google client
     *  @return Google_Client the authorized client object
     */
    public static function getClient()
    {
        $scopes = implode(' ', array(
            Google_Service_Gmail::GMAIL_READONLY
        ));
        //$scopes = ['https://www.googleapis.com/auth/gmail.readonly'];

        $clientSecretFile = conf('gmail.client_secret');
        if (!file_exists($clientSecretFile)) {
            pr('Error: client secret file not found', true);
            pr('Please create "OAuth 2.0 client IDs"');
            pr('login to https://console.developers.google.com/apis/credentials/');
            exit;
        }

        $client = new Google_Client();
        $client->setScopes( $scopes );
        $client->setAuthConfigFile( $clientSecretFile );
        $client->setAccessType('offline');
        // $client->setApprovalPrompt('force');
        // $client->setApplicationName('');

        $tokenFile = conf('gmail.access_token');
        if (!file_exists($tokenFile)) {

            $authCode = conf('gmail.allow_permission_code');

            try {
                // 在取得 auth code 之後
                // 跟 google 要 token
                // 並且記得回存至 file 中, 以供日後使用
                $accessToken = $client->authenticate($authCode);
            }
            catch(Exception $e) {
                pr("Exception Message:");
                pr($e->getMessage());
                pr('');

                $authUrl = $client->createAuthUrl();
                pr("Open the following link in your browser:");
                pr($authUrl . "\n");
                exit;
            }

            file_put_contents($tokenFile, $accessToken);
        }

        $accessToken = file_get_contents($tokenFile);
        $client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            pr('514325243523532452352346523');
            pr('514325243523532452352346523');
            $client->refreshToken($client->getRefreshToken());
            file_put_contents($tokenFile, $client->getAccessToken());
        }

        return $client;
    }


}

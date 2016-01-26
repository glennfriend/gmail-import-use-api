<?php

/**
 *  Gmail Api Helper
 */
class GmailApiHelper
{

    /**
     *  get google client
     *  @return Google_Client the authorized client object
     */
    public static function getClient()
    {
        /*
        $scopes = implode(' ', array(
            Google_Service_Gmail::GMAIL_READONLY
        ));
        */
        // $scopes = ['https://www.googleapis.com/auth/gmail.readonly'];
        // $scopes = ['https://www.googleapis.com/auth/gmail.modify'];
        $scopes = ['https://mail.google.com/'];

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
        $accessToken = self::accessToken($client, $tokenFile);
        $client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->refreshToken($client->getRefreshToken());
            file_put_contents($tokenFile, $client->getAccessToken());
        }

        return $client;
    }

    /**
     *  取得 token
     *      - 如果無法取得會顯示錯誤訊息並中止程式
     *      - token 在過期後會自動重寫
     */
    protected static function accessToken($client, $tokenFile)
    {
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
                pr($authUrl);
                pr('');
                exit;
            }

            file_put_contents($tokenFile, $accessToken);
        }

        return file_get_contents($tokenFile);
    }

}

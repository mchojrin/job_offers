<?php

namespace App\APIGateway;
use Google\Client;
use Google\Service;
use Google\Service\Sheets\Spreadsheet;

class GoogleSpreadSheetGateway
{
    private Client $client;
    private Service $service;
    private Spreadsheet $spreadsheet;
    private $values;

    public function __construct(string $spreadsheetId, string $authConfigPath, string $tokenPath, string $sheetName)
    {
        $this->client = $this->getClient($authConfigPath, $tokenPath);
        $this->service = new Service\Sheets($this->client);
        $this->values = $this->service->spreadsheets_values->get($spreadsheetId, $sheetName);
    }

    /**
     * @return array
     */
    public function getCurrentWeekPosts(): array
    {
        return $this->getPostsSince(new \DateTimeImmutable('-7 day'));
    }

    /**
     * @param \DateTimeInterface $startDate
     * @return array
     */
    public function getPostsSince(\DateTimeInterface $startDate) : array
    {
        $ret = [];

        foreach ($this->values as $k => $rowData) {
            if ($k == 0) {
                continue; // Skip header row
            }
            $curDate = \DateTimeImmutable::createFromFormat('d/m/Y H:i:s', $rowData[0]);
            if ($curDate < $startDate) {
                continue;
            }

            $ret[] = $rowData;
        }

        return $ret;
    }

    private function getClient(string $authConfigPath, string $tokenPath) : Client
    {
        $client = new Client();
        $client->setScopes(Service\Sheets::SPREADSHEETS_READONLY);
        $client->setAuthConfig($authConfigPath);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }

        return $client;
    }
}
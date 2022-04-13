#!/usr/bin/env php

<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Command\SendJobOffersCommand;
use Symfony\Component\{Console\Application,Dotenv\Dotenv};
use Twig\Loader\FilesystemLoader;
use App\Template\TwigRenderer;
use Google\{Client,Service};
use App\SpreadSheet\GoogleSpreadSheet;
use App\Repository\JobOfferRepository;
use App\Campaign\Manager;
use App\Campaign\MailchimpAPIClient;
use Symfony\Component\Mailer\{Transport,Mailer};

$dotEnv = new Dotenv();
$dotEnv->loadEnv(__DIR__ . '/.env');

$app = new Application('Dispatch job offers', 'v1.0.0');
$googleClient = getClient(__DIR__ . '/credentials.json', __DIR__ . '/token.json');
$spreadSheetReader = new GoogleSpreadSheet(
    $googleClient,
    $_ENV['GOOGLE_SPREADSHEET_ID'],
);

$mailChimpClient = new MailchimpAPIClient(
    $_ENV['MAILCHIMP_API_KEY'],
);

$campaignManager = new Manager($mailChimpClient,
    [
        'listId' => $_ENV['MAILCHIMP_LIST_ID'],
        'segmentId' => $_ENV['MAILCHIMP_SEGMENT_ID'],
        'folderId' => $_ENV['MAILCHIMP_FOLDER_ID'],
    ]);

$jobOfferRepository = new JobOfferRepository(
    $spreadSheetReader,
    $_ENV['GOOGLE_SPREADSHEET_SHEET_NAME'],
    require_once __DIR__ . '/config/spreadsheet2tpl.php');

$templateRenderer = new TwigRenderer(new FilesystemLoader(__DIR__ . '/templates'),
    [
        'cache' => 'prod' === $_ENV['APP_ENV'] ? __DIR__ . '/var/cache/' . $_ENV['APP_ENV'] : false,
    ]
);

$transport = Transport::fromDsn($_ENV['MAILER_DSN']);

$mailer = new Mailer($transport);

$theCommand = new SendJobOffersCommand(
    $jobOfferRepository,
    $campaignManager,
    $templateRenderer,
    $mailer,
    [
        'subject' => $_ENV['MAILCHIMP_SUBJECT'],
        'fromName' => $_ENV['MAILCHIMP_FROM_NAME'],
        'title' => $_ENV['MAILCHIMP_TITLE'],
        'replyTo' => $_ENV['MAILCHIMP_REPLY_TO'],
    ]
);

$app->add($theCommand);
$app->setDefaultCommand($theCommand->getName(), true);
$app->run();

function getClient(string $authConfigPath, string $tokenPath): Client
{
    $client = new Client();
    $client->setScopes(Service\Sheets::SPREADSHEETS);
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
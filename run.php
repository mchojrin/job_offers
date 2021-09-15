#!/usr/bin/env php

<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Command\CreateEmailCommand;
use App\APIGateway\GoogleSpreadSheetGateway;
use App\APIGateway\MailChimpGateway;
use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$dotEnv = new Dotenv();
$dotEnv->loadEnv(__DIR__ . '/.env');

$app = new Application('Dispatch weekly job offers', 'v1.0.0');
$theCommand = new CreateEmailCommand(
    new GoogleSpreadSheetGateway(
        $_ENV['GOOGLE_SPREADSHEET_ID'],
        __DIR__ . '/credentials.json',
        __DIR__ . '/token.json',
        $_ENV['GOOGLE_SPREADSHEET_SHEET_NAME']
    ),
    new MailChimpGateway(
        $_ENV['MAILCHIMP_API_KEY'],
        $_ENV['MAILCHIMP_LIST_ID'],
        $_ENV['MAILCHIMP_SEGMENT_ID'],
        $_ENV['MAILCHIMP_FOLDER_ID'],
    ),
    new Environment(new FilesystemLoader(__DIR__ . '/templates'),
        [
            'cache' => 'prod' === $_ENV['APP_ENV'] ? __DIR__ . '/var/cache/' . $_ENV['APP_ENV'] : false,
        ]
    )
);

$app->add($theCommand);
$app->setDefaultCommand($theCommand->getName(), true);
$app->run();
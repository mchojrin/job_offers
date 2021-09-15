#!/usr/bin/env php

<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Command\CreateEmailCommand;
use App\APIGateway\GoogleSpreadSheetGateway;
use App\APIGateway\MailChimpGateway;
use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;

$dotEnv = new Dotenv();
$dotEnv->loadEnv(__DIR__ . '/.env');

$app = new Application('Dispatch weekly job offers', 'v1.0.0');
$app
    ->add(new CreateEmailCommand(
        new GoogleSpreadSheetGateway(
            getenv('GOOGLE_SPREADSHEET_ID'),
            __DIR__.'/credentials.json',
            __DIR__.'/token.json',
            getenv('GOOGLE_SPREADSHEET_SHEET_NAME')
        ),
        new MailChimpGateway(
            getenv('MAILCHIMP_API_KEY'),
            getenv('MAILCHIMP_LIST_ID'),
            getenv('MAILCHIMP_SEGMENT_ID'),
            getenv('MAILCHIMP_FOLDER_ID'),
        )
    ))
    ->run();
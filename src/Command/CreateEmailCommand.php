<?php

namespace App\Command;

use Google\Client;
use Google\Service;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateEmailCommand extends Command
{
    protected static $defaultName = 'app:create-email';
    protected static $defaultDescription = 'Create an email to be sent via MailChimp';
    private $spreadSheetId;

    public function __construct(string $spreadSheetId, string $name = null)
    {
        parent::__construct($name);
        $this->spreadSheetId = $spreadSheetId;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
        }

        $output->writeln('Opening spreadsheet '.$this->spreadSheetId);

        $client = $this->getClient();
        $service = new Service\Sheets($client);

        $range = 'Respuestas de formulario 1!A2:M3';

        $response = $service->spreadsheets_values->get($this->spreadSheetId, $range);
        $values = $response->getValues();

        if (empty($values)) {
            $output->writeln('No data found');

            return 0;
        } else {
            $output->writeln('Found '.count($values).' job offers');
            /**
             * @todo Filter only last weeks offers
             */
            foreach ($values as $row) {
                $output->writeln(print_r($row,1));
                /**
                 * @todo Build message
                 */
                $output->writeln('---');
            }
            /**
             * @todo Send message via MailChimp
             */
        }

        $io->success('Email sent');

        return 0;
    }

    private function getClient() : Client
    {
        $client = new Client();
        $client->setApplicationName('Google Sheets API PHP Quickstart');
        $client->setScopes(Service\Sheets::SPREADSHEETS_READONLY);
        $client->setAuthConfig('var/credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        $tokenPath = 'token.json';
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

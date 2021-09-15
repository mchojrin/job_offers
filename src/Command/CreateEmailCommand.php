<?php

namespace App\Command;

use App\APIGateway\GoogleSpreadSheetGateway;
use App\APIGateway\MailChimpGateway;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;

class CreateEmailCommand extends Command
{
    protected static $defaultName = 'app:create-email';
    protected static $defaultDescription = 'Create an email to be sent via MailChimp';
    private string $spreadSheetId;
    private GoogleSpreadSheetGateway $googleSpreadSheetGateway;
    private MailChimpGateway $mailChimpGateway;
    private Environment $twigEnvironment;

    /**
     * @param GoogleSpreadSheetGateway $googleSpreadSheetGateway
     * @param MailChimpGateway $mailChimp
     * @param Environment $twigEnvironment
     * @param string|null $name
     */
    public function __construct(GoogleSpreadSheetGateway $googleSpreadSheetGateway, MailChimpGateway $mailChimp, Environment $twigEnvironment, string $name = null)
    {
        parent::__construct($name);
        $this->googleSpreadSheetGateway = $googleSpreadSheetGateway;
        $this->mailChimpGateway = $mailChimp;
        $this->twigEnvironment = $twigEnvironment;
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

        $output->writeln('Opening spreadsheet');

        $posts = $this->googleSpreadSheetGateway->getCurrentWeekPosts();

        if (empty($posts)) {
            $output->writeln('No new posts for this week');

            return 0;
        }

        $html = $this->twigEnvironment->render('mailchimp/job_offers.html.twig',
        [
            'posts' => $posts,
        ]);

        $this->mailChimpGateway->send($html);

        $io->success('Email sent');

        return 0;
    }

    private function formatPost(array $post) : string
    {
        return implode(',', $post);
    }
}

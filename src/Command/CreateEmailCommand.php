<?php

namespace App\Command;

use App\APIGateway\GoogleSpreadSheetGateway;
use App\APIGateway\MailChimpGateway;
use App\Exceptions\MailChimpException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;

/**
 * @todo Raise the abstraction level, this class should communicate with higher level services such as:
 *  - DataGatherer (or SpreadSheetManager or something)
 *  - EmailRenderer
 *  - CampaignManager
 *
 *  This way it would allow for future changes to the specific providers used
 */
class CreateEmailCommand extends Command
{
    protected static $defaultName = 'app:create-email';
    protected static $defaultDescription = 'Gather job offers of the week and create an email to be sent to subscribers';
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $output->writeln('Opening spreadsheet');

        $posts = $this->googleSpreadSheetGateway->getCurrentWeekPosts();

        if (empty($posts)) {
            $output->writeln('No new posts for this week');

            return 0;
        }

        $html = $this->twigEnvironment->render('mailchimp/job_offers.html.twig',
            [
                'posts' => $this->formatPosts($posts),
            ]);

        try {
            $this->mailChimpGateway->send($html);

            $io->success('Email sent!');
        } catch (MailChimpException $exception) {
            $io->error($exception->getMessage());
        }

        return 0;
    }

    /**
     * @param array $posts
     * @return array
     * @todo Externalize this mapping into a YAML file or something
     */
    private function formatPosts(array $posts): array
    {
        return array_map(function (array $post) {
            return [
                'description' => $post[4],
                'jobType' => $post[5],
                'remoteAvailable' => $post[6],
                'compensation' => $post[7],
                'contact' => $post[8] ?? "",
                'required' => $post[9] ?? "",
                'optional' => $post[10] ?? "",
                'benefits' => $post[11] ?? "",
                'misc' => $post[12] ?? "",
            ];
        }, $posts);
    }
}

<?php

namespace App\Command;

use App\Campaign\ManagerInterface;
use App\Exceptions\MailChimpException;
use App\Repository\JobOfferRepositoryInterface;
use App\Template\RendererInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendWeeklyJobOffersCommand extends Command
{
    protected static $defaultName = 'app:send-weekly-job-offers';
    protected static $defaultDescription = 'Gather job offers of the week and create an email to be sent to subscribers';
    private JobOfferRepositoryInterface $jobOfferRepository;
    private ManagerInterface $campaignManager;
    private RendererInterface $templateRenderer;
    private array $defaults = [];

    /**
     * @param JobOfferRepositoryInterface $jobOfferRepository
     * @param ManagerInterface $campaignManager
     * @param RendererInterface $templateRenderer
     * @param array $defaults
     * @param string|null $name
     */
    public function __construct(JobOfferRepositoryInterface $jobOfferRepository, ManagerInterface $campaignManager, RendererInterface $templateRenderer, array $defaults = [], string $name = null)
    {
        $this->defaults = $defaults;
        parent::__construct($name);
        $this->jobOfferRepository = $jobOfferRepository;
        $this->campaignManager = $campaignManager;
        $this->templateRenderer = $templateRenderer;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('subject', 's', InputOption::VALUE_REQUIRED, 'Subject line for the email', $this->defaults['subject'])
            ->addOption('fromName', 'f', InputOption::VALUE_REQUIRED, 'Email sender\'s name', $this->defaults['fromName'])
            ->addOption('title', 't', InputOption::VALUE_REQUIRED, 'Prefix of the campagin\'s title', $this->defaults['title'])
            ->addOption('replyTo', 'r', InputOption::VALUE_REQUIRED, 'Address where replies should be sent', $this->defaults['replyTo'])
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $output->writeln('Opening spreadsheet');

        $jobOffers = $this->jobOfferRepository->getCurrentWeekPosts();

        if (empty($jobOffers)) {
            $output->writeln('No new offers for this week');

            return 0;
        }

        $html = $this->templateRenderer->render('email/job_offers.html.twig',
            [
                'offers' => $jobOffers,
            ]);

        $settings = $this->campaignManager->getSettings();

        $settings['subject'] = $input->getOption('subject');
        $settings['fromName'] = $input->getOption('fromName');
        $settings['replyTo'] = $input->getOption('replyTo');
        $settings['title'] = $input->getOption('title');

        $this->campaignManager->setSettings($settings);

        try {
            $this->campaignManager->send($html);

            $io->success('Email sent!');
        } catch (MailChimpException $exception) {
            $io->error($exception->getMessage());
        }

        return 0;
    }
}

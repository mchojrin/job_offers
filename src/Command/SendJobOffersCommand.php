<?php

namespace App\Command;

use App\Campaign\ManagerInterface;
use App\Exceptions\MailChimpException;
use App\Mail\GmailMailer;
use App\Repository\JobOfferRepositoryInterface;
use App\Template\RendererInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendJobOffersCommand extends Command
{
    protected static $defaultName = 'app:send-job-offers';
    protected static $defaultDescription = 'Gather unsent job offers and create an email to be sent to subscribers';
    private JobOfferRepositoryInterface $jobOfferRepository;
    private ManagerInterface $campaignManager;
    private RendererInterface $templateRenderer;
    private GmailMailer $mailer;
    private array $defaults = [];

    /**
     * @param JobOfferRepositoryInterface $jobOfferRepository
     * @param ManagerInterface $campaignManager
     * @param RendererInterface $templateRenderer
     * @param MailerInterface $mailer
     * @param array $defaults
     * @param string|null $name
     */
    public function __construct(JobOfferRepositoryInterface $jobOfferRepository, ManagerInterface $campaignManager, RendererInterface $templateRenderer, GmailMailer $mailer, array $defaults = [], string $name = null)
    {
        $this->defaults = $defaults;
        parent::__construct($name);
        $this->jobOfferRepository = $jobOfferRepository;
        $this->campaignManager = $campaignManager;
        $this->templateRenderer = $templateRenderer;
        $this->mailer = $mailer;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('subject', 's', InputOption::VALUE_REQUIRED, 'Subject line for the email', $this->defaults['subject'])
            ->addOption('fromName', 'f', InputOption::VALUE_REQUIRED, 'Email sender\'s name', $this->defaults['fromName'])
            ->addOption('title', 't', InputOption::VALUE_REQUIRED, 'Prefix of the campagin\'s title', $this->defaults['title'])
            ->addOption('replyTo', 'r', InputOption::VALUE_REQUIRED, 'Address where replies should be sent', $this->defaults['replyTo'])
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Simulate a run');
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

        $jobOffers = $this->jobOfferRepository->getUnsentPosts();

        if (empty($jobOffers)) {
            $output->writeln('No new offers to send');

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

        $now = new \DateTimeImmutable();

        $dryRun = $input->getOption('dry-run');

        try {
            if (!$dryRun) {
                $this->campaignManager->send($html);
            } else {
                $output->write('Message not sent -- dry-run');
            }

            $io->success('Email sent!');
            $senders = [];
            foreach ($jobOffers as $jobOffer) {
                $senders[$jobOffer->getPublisherEmail()] = [
                    'name' => $jobOffer->getPublisherName(),
                    'email' => $jobOffer->getPublisherEmail(),
                ];
                $jobOffer->setSent($now);
                if (!$dryRun) {
                    $this->jobOfferRepository->persist($jobOffer);
                }

                $io->writeln('Offer ' . $jobOffer->getDate()->format('d/m/Y H:i:s') . ' updated');
            }
            $this->sendACKsTo($senders);
        } catch (MailChimpException $exception) {
            $io->error($exception->getMessage());
        }

        return 0;
    }

    /**
     * @param array $senders
     * @return void
     */
    private function sendACKsTo(array $senders)
    {
        foreach ($senders as $sender) {
            $this->sendACKTo($sender);
        }
    }

    /**
     * @param string $publisherEmail
     * @param string $publisherName
     * @return void
     */
    private function sendACKTo(string $publisherEmail, string $publisherName)
    {
        $this
            ->mailer
            ->send(
                'mauro.chojrin@leewayweb.com',
                $publisherEmail,
                'Oferta enviada',
                '<p>La oferta fue enviada exitosamente</p>'
            );
    }
}

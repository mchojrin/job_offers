<?php

namespace App\Campaign;

use MailchimpAPI\Mailchimp;
use App\Entity\Campaign;

class MailchimpAPIClient extends Mailchimp implements ApiClientInterface
{
    /**
     * @param string $html
     * @return Campaign
     * @throws \MailchimpAPI\MailchimpException
     */
    public function createCampaign(array $settings): Campaign
    {
        $mailChimpSettings = [
            'type' => 'regular',
            'recipients' => [
                'segment_opts' => [
                    'saved_segment_id' => (int)$settings['segmentId'],
                    'match' => 'all',
                ],
                'list_id' => $settings['listId'],
            ],
            'settings' => [
                'subject_line' => $settings['subject'],
                'title' => $settings['title'].date('d.m.Y'),
                'from_name' => $settings['fromName'],
                'reply_to' => $settings['replyTo'],
                'folder_id' => $settings['folderId'],
            ]
        ];

        $theCampaign = new Campaign();
        $response = $this
            ->campaigns()
            ->post($mailChimpSettings);

        if ($response->wasSuccess()) {
            $body = json_decode($response->getBody());
            $theCampaign->setId($body->id);
        }

        return $theCampaign;
    }

    /**
     * @param Campaign $campaign
     * @throws \MailchimpAPI\MailchimpException
     */
    public function send(Campaign $campaign)
    {
        $response = $this
            ->campaigns($campaign->getId())
            ->content()
            ->put([
                'html' => $campaign->getContents(),
            ]);

        if (!$response->wasSuccess()) {
            throw new \Exception('Couldn\'t update the campaign');
        }

        $response = $this
            ->campaigns($campaign->getId())
            ->send()
            ;

        if (!$response->wasSuccess()) {
            throw new \Exception('Couldn\'t send the campaign');
        }
    }
}
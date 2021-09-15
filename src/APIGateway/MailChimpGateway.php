<?php

namespace App\APIGateway;

use DrewM\MailChimp\MailChimp;

class MailChimpGateway
{
    private string $apiKey;
    private string $listId;
    private string $segmentId;
    private string $folderId;

    private MailChimp $mailChimp;

    public function __construct(string $apiKey, string $listId, string $segmentId, string $folderId)
    {
        $this->apiKey = $apiKey;
        $this->listId = $listId;
        $this->segmentId = $segmentId;
        $this->folderId = $folderId;

        $this->mailChimp = new MailChimp($this->apiKey);
    }

    public function send(string $html)
    {
        $result = $this->mailChimp->post('campaigns', [
            'type' => 'regular',
            'recipients' => [
                'segment_opts' => [
                    'saved_segment_id' => (int)$this->segmentId,
                    'match' => 'all',
                ],
                'list_id' => $this->listId,
            ],
            'settings' => [
                'subject_line' => 'Ofertas de la semana',
                'title' => 'Ofertas '.date('d.m.Y'),
                'from_name' => 'Mauro Chojrin',
                'reply_to' => 'mauro.chojrin@leewayweb.com',
            ]
        ]);

        if (array_key_exists('errors', $result)) {
            echo 'Errors found: '.print_r($result['errors'],1).PHP_EOL;
        } else {
            $campaignId = $result['id'];
            $this->mailChimp->put('campaigns/'.$campaignId, [
                'html' => $html,
            ]);
        }
    }
}
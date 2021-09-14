<?php

namespace App\APIGateway;

use DrewM\MailChimp\MailChimp;

class MailChimpGateway
{
    private string $apiKey;
    private string $listID;
    private string $segmentID;
    private MailChimp $mailChimp;

    public function __construct(string $apiKey, string $listID, string $segmentId)
    {
        $this->apiKey = $apiKey;
        $this->listID = $listID;
        $this->segmentID = $segmentId;

        $this->mailChimp = new MailChimp($this->apiKey);
    }

    public function send(string $html)
    {
        echo 'Sending!'.PHP_EOL;

        $result = $this->mailChimp->post('campaigns', [
            'type' => 'regular',
            'recipients' => [
                'segment_opts' => [
                    'saved_segment_id' => (int)$this->segmentID,
                    'match' => 'all',
                ],
                'list_id' => $this->listID,
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
            echo 'Good. '.print_r($result, 1).PHP_EOL;
        }
    }
}
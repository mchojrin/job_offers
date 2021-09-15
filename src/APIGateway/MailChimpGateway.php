<?php

namespace App\APIGateway;

use App\Exceptions\MailChimpException;
use DrewM\MailChimp\MailChimp;

class MailChimpGateway
{
    private string $apiKey;
    private string $listId;
    private string $segmentId;
    private string $folderId;

    private MailChimp $mailChimp;

    /**
     * @param string $apiKey
     * @param string $listId
     * @param string $segmentId
     * @param string $folderId
     * @throws \Exception
     */
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
                'folder_id' => $this->folderId,
            ]
        ]);

        if (array_key_exists('errors', $result)) {

            throw new MailChimpException(implode(', ', $result['errors']));
        } else {
            $campaignId = $result['id'];
            $result = $this->mailChimp->put('campaigns/'.$campaignId.'/content', [
                'html' => $html,
            ]);

            if (array_key_exists('status', $result) && $result['status'] !== 200 ) {

                throw new MailChimpException($result['title'].': '.$result['details']);
            }

            $result = $this->mailChimp->post('campaigns'.$campaignId.'/actions/send');

            if (array_key_exists('status', $result) && $result['status'] !== 200 ) {

                throw new MailChimpException($result['title'].': '.$result['details']);
            }


        }
    }
}
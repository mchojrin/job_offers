<?php

namespace App\Mail;

use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;

class GmailMailer
{
    private Gmail $gmail;

    public function __construct(Client $client)
    {
        $this->gmail = new Gmail($client);
    }

    /**
     * @inheritDoc
     */
    public function send(string $from, string $to, string $subject, string $messageText): void
    {
        $rawMessageString = "From: <{$from}>\r\n";
        $rawMessageString .= "To: <{$to}>\r\n";
        $rawMessageString .= 'Subject: =?utf-8?B?' . base64_encode($subject) . "?=\r\n";
        $rawMessageString .= "MIME-Version: 1.0\r\n";
        $rawMessageString .= "Content-Type: text/html; charset=utf-8\r\n";
        $rawMessageString .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
        $rawMessageString .= "{$messageText}\r\n";
        $rawMessage = strtr(base64_encode($rawMessageString), array('+' => '-', '/' => '_'));

        $message = new Message();
        $message->setRaw($rawMessage);

        $this
            ->gmail
            ->users_messages
            ->send('me', $message);
    }
}
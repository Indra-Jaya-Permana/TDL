<?php

namespace App\Services;

use Google\Client;
use Google\Service\Gmail;

class GmailService
{
    protected $service;

    public function __construct()
    {
        $client = new Client();
        $client->setApplicationName('Laravel ToDoList');
        $client->setScopes(Gmail::GMAIL_SEND);
        $client->setAuthConfig(storage_path('app/google/credentials.json'));
        $client->setAccessType('offline');

        $this->service = new Gmail($client);
    }

    public function sendMail($subject, $body, $to = 'youremail@gmail.com')
    {
        $message = (new \Google\Service\Gmail\Message());
        $rawMessageString = "To: $to\r\nSubject: $subject\r\n\r\n$body";
        $rawMessage = base64_encode($rawMessageString);
        $message->setRaw(str_replace(['+', '/', '='], ['-', '_', ''], $rawMessage));
        $this->service->users_messages->send('me', $message);
    }
}

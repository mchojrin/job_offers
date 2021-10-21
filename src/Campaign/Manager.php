<?php

namespace App\Campaign;

class Manager implements ManagerInterface
{
    private ApiClientInterface $client;
    private array $settings;

    /**
     * @param ApiClientInterface $client
     * @param array $settings
     */
    public function __construct(ApiClientInterface $client, array $settings = [])
    {
        $this->client = $client;
        $this->settings = $settings;
    }

    /**
     * @param string $html
     */
    public function send(string $html): void
    {
        $campaign = $this
            ->client
            ->createCampaign($this->settings)
            ->setContents($html);
        ;

        $this->client->send($campaign);
    }

    /**
     * @param array $setting
     * @return $this
     */
    public function setSettings(array $settings = []) : self
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return array
     */
    public function getSettings() : array
    {
        return $this->settings;
    }
}
<?php

namespace App\Campaign;

use App\Entity\Campaign;

interface ApiClientInterface
{
    public function createCampaign(array $settings): Campaign;

    public function send(Campaign $campaign);
}
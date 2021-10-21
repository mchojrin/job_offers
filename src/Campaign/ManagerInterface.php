<?php

namespace App\Campaign;

interface ManagerInterface
{
    public function send(string $html) : void;

    public function setSettings(array $settings = []): ManagerInterface;

    public function getSettings() : array;
}
<?php

namespace App\Template;

interface RendererInterface
{
    public function render($name, array $context = []): string;
}
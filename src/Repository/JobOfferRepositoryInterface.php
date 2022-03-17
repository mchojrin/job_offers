<?php

namespace App\Repository;

interface JobOfferRepositoryInterface
{
    public function findAll() : array;

    public function getUnsentPosts(): array;
}
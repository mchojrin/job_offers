<?php

namespace App\Repository;

interface JobOfferRepositoryInterface
{
    public function findAll() : array;

    public function getPostsSince(\DateTimeInterface $startDate) : array;

    public function getCurrentWeekPosts(): array;
}
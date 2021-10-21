<?php

namespace App\Repository;

use App\Entity\JobOffer;
use App\SpreadSheet\ReaderInterface;

class JobOfferRepository implements JobOfferRepositoryInterface
{
    private ReaderInterface $spreadsheetReader;
    private string $spreadsheetId;
    private string $sheetName;
    private array $mapping;

    /**
     * @param ReaderInterface $spreadsheetReader
     * @param string $spreadsheetId
     * @param string $sheetName
     * @param array $mapping
     */
    public function __construct(ReaderInterface $spreadsheetReader, string $spreadsheetId, string $sheetName, array $mapping)
    {
        $this->spreadsheetReader = $spreadsheetReader;
        $this->spreadsheetId = $spreadsheetId;
        $this->sheetName = $sheetName;
        $this->mapping = $mapping;
    }

    /**
     * @return array
     */
    public function findAll() : array
    {
        $ret = [];

        foreach ($this->spreadsheetReader->getFullSheetContents($this->spreadsheetId, $this->sheetName) as $i => $row) {
            if ($i == 0) {
                // Skip header row
                continue;
            }
            $ret[] = $this->createOffer($row);
        }

        return $ret;
    }

    /**
     * @return array
     */
    public function getCurrentWeekPosts(): array
    {
        return $this->getPostsSince(new \DateTimeImmutable('-7 day'));
    }

    /**
     * @param \DateTimeInterface $startDate
     * @return array
     */
    public function getPostsSince(\DateTimeInterface $startDate) : array
    {
        return array_filter($this->findAll(), function(JobOffer $job) use ($startDate) {

            return $job->getDate() >= $startDate;
        });
    }

    /**
     * @param array $row
     * @return JobOffer
     * @todo Use mapping configuration
     */
    private function createOffer(array $row) : JobOffer
    {
        $jobOffer = new JobOffer();

        foreach ($this->mapping as $column => $property) {
            $method = 'set'.ucfirst($property);
            $jobOffer->$method($row[$column] ?? "");
        }

        $jobOffer->setDate(\DateTimeImmutable::createFromFormat('d/m/Y H:i:s', $row[0]));
        return $jobOffer;
    }
}
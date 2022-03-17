<?php

namespace App\Repository;

use App\Entity\JobOffer;
use App\SpreadSheet\SpreadsheetInterface;

class JobOfferRepository implements JobOfferRepositoryInterface
{
    const DATE_COL = 0;
    const SENT_COL = 14;

    private SpreadsheetInterface $spreadsheetReader;
    private string $sheetName;
    private array $mapping;

    /**
     * @param SpreadsheetInterface $spreadsheetReader
     * @param string $sheetName
     * @param array $mapping
     */
    public function __construct(SpreadsheetInterface $spreadsheetReader, string $sheetName, array $mapping)
    {
        $this->spreadsheetReader = $spreadsheetReader;
        $this->sheetName = $sheetName;
        $this->mapping = $mapping;
    }

    /**
     * @return array
     */
    public function findAll() : array
    {
        $ret = [];

        foreach ($this->spreadsheetReader->getFullSheetContents($this->sheetName) as $i => $row) {
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
    public function getUnsentPosts(): array
    {
        return array_filter($this->findAll(), function(JobOffer $job) {

            return empty($job->getSent());
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

        $jobOffer->setDate(\DateTimeImmutable::createFromFormat('d/m/Y H:i:s', $row[self::DATE_COL]));
        $jobOffer->setSent(array_key_exists(self::SENT_COL, $row) && !empty($row[self::SENT_COL]) ? \DateTimeImmutable::createFromFormat('d/m/Y H:i:s', $row[self::SENT_COL]) : null);

        return $jobOffer;
    }

    public function persist(JobOffer $jobOffer)
    {

    }
}
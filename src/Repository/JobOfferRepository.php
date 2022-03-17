<?php

namespace App\Repository;

use App\Entity\JobOffer;
use App\SpreadSheet\SpreadsheetInterface;
use Exception;

class JobOfferRepository implements JobOfferRepositoryInterface
{
    const DATE_COL = 0;
    const SENT_COL = 14;

    private SpreadsheetInterface $spreadSheet;
    private string $sheetName;
    private array $mapping;

    /**
     * @param SpreadsheetInterface $spreadSheet
     * @param string $sheetName
     * @param array $mapping
     */
    public function __construct(SpreadsheetInterface $spreadSheet, string $sheetName, array $mapping)
    {
        $this->spreadSheet = $spreadSheet;
        $this->sheetName = $sheetName;
        $this->mapping = $mapping;
    }

    /**
     * @return array
     */
    public function findAll(): array
    {
        $ret = [];

        foreach ($this->spreadSheet->getFullSheetContents($this->sheetName) as $i => $row) {
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
        return array_filter($this->findAll(), function (JobOffer $job) {

            return empty($job->getSent());
        });
    }

    /**
     * @param array $row
     * @return JobOffer
     * @todo Use mapping configuration
     */
    private function createOffer(array $row): JobOffer
    {
        $jobOffer = new JobOffer();

        foreach ($this->mapping as $column => $property) {
            $method = 'set' . ucfirst($property);
            $jobOffer->$method($row[$column] ?? "");
        }

        $jobOffer->setDate(\DateTimeImmutable::createFromFormat('d/m/Y H:i:s', $row[self::DATE_COL]));
        $jobOffer->setSent(array_key_exists(self::SENT_COL, $row) && !empty($row[self::SENT_COL]) ? \DateTimeImmutable::createFromFormat('d/m/Y H:i:s', $row[self::SENT_COL]) : null);

        return $jobOffer;
    }

    /**
     * @param JobOffer $jobOffer
     * @return void
     * @throws Exception
     *
     * @todo Lots of hardcoding in here... 'O' is too tied to the current SpreadSheet structure (Such as '0' for the date key)
     */
    public function persist(JobOffer $jobOffer)
    {
        $row = $this
            ->spreadSheet
            ->findRow(
                $this->sheetName,
                [
                    0 => $jobOffer->getDate()->format('d/m/Y H:i:s')
                ]);

        if (empty($row)) {

            throw new \Exception('Job offer not found');
        }

        $this
            ->spreadSheet
            ->updateCell('O'.$row, $jobOffer->getSent()->format('d/m/Y H:i:s'));
    }
}
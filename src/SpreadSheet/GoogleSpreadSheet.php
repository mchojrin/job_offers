<?php

namespace App\SpreadSheet;

use Google\Client;
use Google\Service;
use Google\Service\Sheets\ValueRange;

class GoogleSpreadSheet implements SpreadsheetInterface
{
    private Service $service;
    private string $spreadSheetId;

    /**
     * @param Client $client
     */
    public function __construct(Client $client, string $spreadSheetId)
    {
        $this->service = new Service\Sheets($client);
        $this->spreadSheetId = $spreadSheetId;
    }

    /**
     * @return array
     */
    public function getFullSheetContents(string $sheetName): array
    {
        return $this
            ->service
            ->spreadsheets_values
            ->get(
                $this->spreadSheetId,
                $sheetName)
            ->getValues();
    }

    public function updateCell(string $cell, $newContents): void
    {
        $body = new ValueRange();

        $body
            ->setValues(['values' => $newContents]);

        $this
            ->service
            ->spreadsheets_values
            ->update(
                $this->spreadSheetId,
                $cell,
                $body,
                [
                    'valueInputOption' => 'RAW',
                ]
            );
    }

    /**
     * @param string $sheetName
     * @param array $criteria The criteria is extremelly simple for the time being, it simply matches a column with a value
     * @return int|null
     */
    public function findRow(string $sheetName, array $criteria): ?int
    {
        $contents = $this->getFullSheetContents($sheetName);

        $key = key($criteria);
        $value = $criteria[$key];

        foreach ($contents as $i => $row) {
            if ($row[$key] == $value) {

                return $i + 1;
            }
        }

        return null;
    }
}
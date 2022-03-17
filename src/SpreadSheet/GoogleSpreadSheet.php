<?php

namespace App\SpreadSheet;

use Google\Client;
use Google\Service;

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

    public function updateCell(string $cell, string $newContents): void
    {
        $this
            ->service
            ->spreadsheets_values
            ->update(
                $this->spreadSheetId,
                $cell,
                $newContents
            );
    }
}
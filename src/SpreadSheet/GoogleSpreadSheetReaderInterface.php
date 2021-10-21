<?php

namespace App\SpreadSheet;

use Google\Client;
use Google\Service;

class GoogleSpreadSheetReaderInterface implements ReaderInterface
{
    private Service $service;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->service = new Service\Sheets($client);
    }

    /**
     * @return array
     */
    public function getFullSheetContents(string $spreadSheetId, string $sheetName): array
    {
        return $this->service->spreadsheets_values->get($spreadSheetId, $sheetName)->getValues();
    }
}
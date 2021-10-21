<?php

namespace App\SpreadSheet;

interface ReaderInterface
{
    public function getFullSheetContents(string $spreadSheetId, string $sheetName) : array;
}
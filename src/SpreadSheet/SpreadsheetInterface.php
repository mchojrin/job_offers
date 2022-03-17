<?php

namespace App\SpreadSheet;

interface SpreadsheetInterface
{
    public function getFullSheetContents(string $sheetName) : array;

    public function updateCell(string $cell, string $newContents): void;
}
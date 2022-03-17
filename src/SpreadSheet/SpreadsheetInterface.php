<?php

namespace App\SpreadSheet;

interface SpreadsheetInterface
{
    public function getFullSheetContents(string $sheetName) : array;

    public function updateCell(string $cell, $newContents): void;

    public function findRow(string $sheetName, array $criteria): ?int;
}
<?php

namespace App\Repository;

use App\Entity\JobOffer;
use PHPUnit\Framework\TestCase;

class JobOfferRepositoryTest extends TestCase
{
    /**
     * @todo Explode into three separate tests
     */
    public function testGetPostsSince()
    {
        $twoDaysAgo = new \DateTimeImmutable('-2');
        $twoWeeksAgo = new \DateTimeImmutable('-2 week');
        $spreadsheetReader = $this->createMock(
            'App\SpreadSheet\SpreadsheetInterface'
        );
        $newOffer = [
            $twoDaysAgo->format('d/m/Y H:i:s'),
            'New Offer',
            '',
            '',
            ''
        ];
        $oldOffer = [
            $twoWeeksAgo->format('d/m/Y H:i:s'),
            'Old offer',
        ];
        $spreadsheetReader
            ->method('getFullSheetContents')
            ->willReturn([
                [
                    'date',
                    'description',
                ],
                $newOffer,
                $oldOffer
            ]);

        $sut = new JobOfferRepository($spreadsheetReader, 'id', 'name', [1 => 'description']);
        $lastWeek = new \DateTimeImmutable('-7 day');
        $offers = $sut->getPostsSince($lastWeek);

        $this->assertCount(1, $offers);
        $theOffer = current($offers);
        $this->assertEquals($newOffer[1], $theOffer->getDescription());
        $this->assertContainsOnly(JobOffer::class, $offers);
    }
}

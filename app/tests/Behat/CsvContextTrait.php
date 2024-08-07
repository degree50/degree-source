<?php

namespace App\Tests\Behat;

use App\DataExport\Dto\TextFileDto;
use App\Domain\Course\Model\Course;
use Behat\Gherkin\Node\PyStringNode;
use DateTimeImmutable;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertIsObject;

trait CsvContextTrait
{
    /**
     * @When I convert all data for :courseId to csv
     */
    public function iConvertAllDataForCourseToCsv(string $courseId): void
    {
        /** @var Course $course */
        $course = $this->entityManager->find(Course::class, $courseId);
        $this->csvDtoList = $this->degreeDataToCsvService->getAllAsVirtualCSVs($course);
    }

    /**
     * @Then I have a CSVDto-list containing a file :fileName with a CSV content string
     */
    public function iHaveACsvDtoListContainingAFileWithACsvContentString(string $fileName, PyStringNode $contentString): void
    {
        /** @var TextFileDto $csvDto */
        $csvDto = current(array_filter($this->csvDtoList, function (TextFileDto $cSVDto) use ($fileName) {
            return $cSVDto->getFileName() === $fileName;
        }));

        $currentDate = new DateTimeImmutable();
        $expected = str_replace('{{CREATED_AT_DATE}}', $currentDate->format("d.m.Y"), $contentString->getRaw());

        assertIsObject($csvDto, "Virtual File <" . $fileName . "> not found in dtoList!");
        assertEquals($expected, $csvDto->getContentString());
    }

    /**
     * @Then I have CSVDto-list containing a file :fileName
     */
    public function iHaveCsvDtoListContainingAFile($fileName): void
    {
        /** @var TextFileDto $csvDto */
        $csvDto = current(array_filter($this->csvDtoList, function (TextFileDto $cSVDto) use ($fileName) {
            return $cSVDto->getFileName() === $fileName;
        }));
        assertIsObject($csvDto, "Virtual File <" . $fileName . "> not found in dtoList!");
    }
}

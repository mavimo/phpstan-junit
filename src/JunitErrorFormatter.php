<?php

declare(strict_types=1);

namespace Mavimo\PHPStan\ErrorFormatter;

use DOMDocument;
use DOMElement;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use PHPStan\File\RelativePathHelper;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Style\OutputStyle;
use function sprintf;

class JunitErrorFormatter implements ErrorFormatter
{
    /**
     * @var \PHPStan\File\RelativePathHelper
     */
    private $relativePathHelper;

    public function __construct(RelativePathHelper $relativePathHelper)
    {
        $this->relativePathHelper = $relativePathHelper;
    }

    public function formatErrors(
        AnalysisResult $analysisResult,
        OutputStyle $style
    ): int {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $testsuite = $dom->createElement('testsuite');
        $testsuite->setAttribute('name', 'phpstan');
        $testsuite->setAttribute('tests', (string) $analysisResult->getTotalErrorsCount());
        $testsuite->setAttribute('failures', (string) $analysisResult->getTotalErrorsCount());
        $dom->appendChild($testsuite);

        if (!$analysisResult->hasErrors()) {
            $this->createTestCase($dom, $testsuite, 'phpstan');
        } else {
            $fileErrors = $analysisResult->getFileSpecificErrors();

            foreach ($fileErrors as $error) {
                $fileName = $this->relativePathHelper->getRelativePath($error->getFile());
                $this->createTestCase($dom, $testsuite, sprintf('%s:%s', $fileName, (string) $error->getLine()), $error->getMessage());
            }

            $genericErrors = $analysisResult->getNotFileSpecificErrors();

            if (count($genericErrors) > 0) {
                foreach ($genericErrors as $genericError) {
                    $this->createTestCase($dom, $testsuite, 'Generic error', $genericError);
                }
            }
        }

        $style->write($style->isDecorated() ? OutputFormatter::escape($dom->saveXML()) : $dom->saveXML());

        return intval($analysisResult->hasErrors());
    }

    private function createTestCase(DOMDocument $dom, DOMElement $testsuite, string $reference, ?string $message = null): void
    {
        $testcase = $dom->createElement('testcase');
        $testcase->setAttribute('name', $reference);

        if ($message !== null) {
            $testcase->setAttribute('failures', (string) 1);
            $testcase->setAttribute('errors', (string) 0);
            $testcase->setAttribute('tests', (string) 1);

            $failure = $dom->createElement('failure');
            $failure->setAttribute('message', $message);
            $failure->setAttribute('type', 'error');

            $testcase->appendChild($failure);
        }

        $testsuite->appendChild($testcase);
    }
}

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

        $testsuites = $dom->createElement('testsuites');
        $testsuites->setAttribute('name', 'static analysis');
        $dom->appendChild($testsuites);

        $returnCode = 1;

        if (!$analysisResult->hasErrors()) {
            /** @var \DomElement $testsuite */
            $testsuite = $dom->createElement('testsuite');
            $testsuite->setAttribute('name', 'phpstan');
            $testsuite->setAttribute('tests', '1');
            $testsuite->setAttribute('failures', '0');

            $testsuites->appendChild($testsuite);

            $testcase = $dom->createElement('testcase');
            $testcase->setAttribute('name', 'phpstan');
            $testsuite->appendChild($testcase);

            $returnCode = 0;
        } else {
            /** @var array<string,array<int,\PHPStan\Analyser\Error>> $fileErrors */
            $fileErrors = [];

            foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
                if (!isset($fileErrors[$fileSpecificError->getFile()])) {
                    $fileErrors[$fileSpecificError->getFile()] = [];
                }

                $fileErrors[$fileSpecificError->getFile()][] = $fileSpecificError;
            }

            /** @var \DomElement $testsuite */
            $testsuite = $testsuites->appendChild($dom->createElement('testsuite'));

            $totalErrors = 0;

            foreach ($fileErrors as $file => $errors) {
                foreach ($errors as $error) {
                    $fileName = $this->relativePathHelper->getRelativePath($file);
                    $this->createTestCase($dom, $testsuite, sprintf('%s:%s', $fileName, (string) $error->getLine()), $error->getMessage());

                    $totalErrors += 1;
                }
            }

            $genericErrors = $analysisResult->getNotFileSpecificErrors();

            if (count($genericErrors) > 0) {
                foreach ($genericErrors as $genericError) {
                    $this->createTestCase($dom, $testsuite, 'Generic error', $genericError);

                    $totalErrors += 1;
                }
            }

            $testsuite->setAttribute('name', 'phpstan');
            $testsuite->setAttribute('failures', (string) $totalErrors);
        }

        $style->write($style->isDecorated() ? OutputFormatter::escape($dom->saveXML()) : $dom->saveXML());

        return $returnCode;
    }

    private function createTestCase(DOMDocument $dom, DOMElement $testsuite, string $reference, ?string $message): void
    {
        $testcase = $dom->createElement('testcase');
        $testcase->setAttribute('name', $reference);
        $testcase->setAttribute('failures', (string) 1);
        $testcase->setAttribute('errors', (string) 0);
        $testcase->setAttribute('tests', (string) 1);

        $failure = $dom->createElement('failure');
        $failure->setAttribute('type', 'error');

        if ($message !== null) {
            $failure->setAttribute('message', $message);
        }

        $testcase->appendChild($failure);

        $testsuite->appendChild($testcase);
    }
}

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

        $testsuite = $dom->createElement('testsuite');
        $testsuite->setAttribute('name', 'phpstan');
        $testsuite->setAttribute('tests', (string) $analysisResult->getTotalErrorsCount());
        $testsuite->setAttribute('failures', (string) $analysisResult->getTotalErrorsCount());
        $testsuites->appendChild($testsuite);

        if (!$analysisResult->hasErrors()) {
            $this->createTestCase($dom, $testsuite, 'phpstan', []);
        } else {
            /** @var array<string,array<int,\PHPStan\Analyser\Error>> $fileErrors */
            $fileErrors = [];

            foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
                if (!isset($fileErrors[$fileSpecificError->getFile()])) {
                    $fileErrors[$fileSpecificError->getFile()] = [];
                }

                $fileErrors[$fileSpecificError->getFile()][] = $fileSpecificError;
            }

            foreach ($fileErrors as $file => $errors) {
                $this->createTestCase($dom, $testsuite, $this->relativePathHelper->getRelativePath($file), $errors);
            }

            $genericErrors = $analysisResult->getNotFileSpecificErrors();

            if (count($genericErrors) > 0) {
                $this->createTestCase($dom, $testsuite, 'Generic errors', $genericErrors);
            }
        }

        $style->write($style->isDecorated() ? OutputFormatter::escape($dom->saveXML()) : $dom->saveXML());

        return intval($analysisResult->hasErrors());
    }

    private function createTestCase(DOMDocument $dom, DOMElement $testsuite, string $reference, array $errors): void
    {
        $testcase = $dom->createElement('testcase');
        $testcase->setAttribute('name', $reference);
        $testcase->setAttribute('failures', (string) count($errors));
        $testcase->setAttribute('errors', '0');
        $testcase->setAttribute('tests', (string) count($errors));

        foreach ($errors as $error) {
            if ($error instanceof Error) {
                $this->createFailure($dom, $testcase, sprintf('Line %s: %s', $error->getLine(), $error->getMessage()));
                continue;
            }

            $this->createFailure($dom, $testcase, $error);
        }

        $testsuite->appendChild($testcase);
    }

    private function createFailure(DOMDocument $dom, DOMElement $testcase, string $message): void
    {
        $failure = $dom->createElement('failure');
        $failure->setAttribute('type', 'error');
        $failure->setAttribute('message', $message);

        $testcase->appendChild($failure);
    }
}

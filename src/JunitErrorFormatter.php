<?php

declare(strict_types=1);

namespace Mavimo\PHPStan\ErrorFormatter;

use DOMDocument;
use DomElement;
use PHPStan\Analyser\Error;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use PHPStan\Command\ErrorFormatter\RelativePathHelper;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Style\OutputStyle;

class JunitErrorFormatter implements ErrorFormatter
{

    public function formatErrors(
        AnalysisResult $analysisResult,
        OutputStyle $style
    ): int
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $testsuites = $dom->appendChild($dom->createElement('testsuites'));
        $testsuites->setAttribute('name', 'static analysis');

        $returnCode = 1;

        if (!$analysisResult->hasErrors()) {
            /** @var DomElement $testsuite */
            $testsuite = $testsuites->appendChild($dom->createElement('testsuite'));
            $testsuite->setAttribute('name', 'phpstan');
            $testsuite->setAttribute('tests', '1');
            $testsuite->setAttribute('failures', '0');

            $testcase = $dom->createElement('testcase');
            $testcase->setAttribute('name', 'phpstan');
            $testsuite->appendChild($testcase);

            $returnCode = 0;
        } else {
            $currentDirectory = $analysisResult->getCurrentDirectory();

            /** @var \PHPStan\Analyser\Error[][] $fileErrors */
            $fileErrors = [];
            foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
                if (!isset($fileErrors[$fileSpecificError->getFile()])) {
                    $fileErrors[$fileSpecificError->getFile()] = [];
                }

                $fileErrors[$fileSpecificError->getFile()][] = $fileSpecificError;
            }

            /** @var DomElement $testsuite */
            $testsuite = $testsuites->appendChild($dom->createElement('testsuite'));

            $totalErrors = 0;
            foreach ($fileErrors as $file => $errors) {
                foreach ($errors as $error) {
                    $fileName = RelativePathHelper::getRelativePath($currentDirectory, $file);
                    $this->createTestCase($dom, $testsuite, sprintf('%s:%s', $fileName, (string) $error->getLine()), $error->getMessage());

                    $totalErrors++;
                }
            }

            $genericErrors = $analysisResult->getNotFileSpecificErrors();
            if (count($genericErrors) > 0) {
                foreach ($genericErrors as $i => $genericError) {
                    $this->createTestCase($dom, $testsuite, 'Generic error', $genericError);

                    $totalErrors++;
                }
            }

            $testsuite->setAttribute('name', 'phpstan');
            $testsuite->setAttribute('failures', (string) $totalErrors);
        }

        $style->write($style->isDecorated() ? OutputFormatter::escape($dom->saveXML()) : $dom->saveXML());

        return $returnCode;
    }

    private function createTestCase(DOMDocument $dom, DomElement $testsuite, string $reference, ?string $message)
    {
        $testcase = $dom->createElement('testcase');
        $testcase->setAttribute('name', $reference);
        $testcase->setAttribute('failures', (string) 1);
        $testcase->setAttribute('errors', (string) 0);
        $testcase->setAttribute('tests', (string) 1);

        $failure = $dom->createElement('failure');
        $failure->setAttribute('type', 'error');
        $failure->setAttribute('message', $message);
        $testcase->appendChild($failure);

        $testsuite->appendChild($testcase);
    }
}

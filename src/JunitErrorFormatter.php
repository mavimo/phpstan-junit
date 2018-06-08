<?php

declare(strict_types=1);

namespace Mavimo\PHPStan\ErrorFormatter;

use DOMDocument;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorFormatter\ErrorFormatter;
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

        $testsuites = $dom->appendChild($dom->createElement('testsuites'));
        $testsuites->setAttribute('name', 'phpstan');

        $returnCode = 1;

        if (!$analysisResult->hasErrors()) {
            /** @var \DomElement $testsuite */
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

            foreach ($fileErrors as $file => $errors) {
                /** @var \DomElement $testsuite */
                $testsuite = $testsuites->appendChild($dom->createElement('testsuite'));
                $testsuite->setAttribute('name', RelativePathHelper::getRelativePath($currentDirectory, $file));
                $testsuite->setAttribute('failures', (string) count($errors));

                foreach ($errors as $error) {
                    $testcase = $dom->createElement('testcase');
                    $testcase->setAttribute('name', (string) $error->getLine());
                    $testcase->setAttribute('failures', (string) 1);
                    $testcase->setAttribute('errors', (string) 0);
                    $testcase->setAttribute('tests', (string) 1);

                    $failure = $dom->createElement('failure');
                    $failure->setAttribute('type', 'error');
                    $failure->setAttribute('message', $error->getMessage());
                    $testcase->appendChild($failure);

                    $testsuite->appendChild($testcase);
                }
            }

            $genericErrors = $analysisResult->getNotFileSpecificErrors();
            if (count($genericErrors) > 0) {
                /** @var \DomElement $testsuite */
                $testsuite = $testsuites->appendChild($dom->createElement('testsuite'));
                $testsuite->setAttribute('name', 'Generic errors');
                $testsuite->setAttribute('failures', (string) count($genericErrors));

                foreach ($genericErrors as $i => $genericError) {
                    $testcase = $dom->createElement('testcase');
                    $testcase->setAttribute('name', sprintf('issue %d', $i + 1));
                    $testcase->setAttribute('failures', (string) 1);
                    $testcase->setAttribute('errors', (string) 0);
                    $testcase->setAttribute('tests', (string) 1);

                    $failure = $dom->createElement('failure');
                    $failure->setAttribute('type', 'error');
                    $failure->setAttribute('message', $genericError);
                    $testcase->appendChild($failure);

                    $testsuite->appendChild($testcase);
                }
            }
        }

        $dom->formatOutput = true;

        $style->write($style->isDecorated() ? OutputFormatter::escape($dom->saveXML()) : $dom->saveXML());

        return $returnCode;
    }

}

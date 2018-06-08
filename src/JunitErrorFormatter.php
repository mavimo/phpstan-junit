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
        /** @var \DomElement $testsuite */
        $testsuite = $testsuites->appendChild($dom->createElement('testsuite'));
        $testsuite->setAttribute('name', 'phpstan');

        $returnCode = 1;

        if (!$analysisResult->hasErrors()) {
            $testcase = $dom->createElement('testcase');
            $testsuite->appendChild($testcase);
            $testsuite->setAttribute('tests', '1');
            $testsuite->setAttribute('failures', '0');

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
                $testcase = $dom->createElement('testcase');
                $testcase->setAttribute('name', RelativePathHelper::getRelativePath($currentDirectory, $file));
                $testcase->setAttribute('failures', (string) count($errors));
                $testcase->setAttribute('errors', (string) count($errors));
                $testcase->setAttribute('tests', (string) count($errors));
                $testcase->setAttribute('file', RelativePathHelper::getRelativePath($currentDirectory, $file));

                foreach ($errors as $error) {
                    $failure = $dom->createElement('failure', sprintf('line %d', $error->getLine()));
                    $failure->setAttribute('type', 'error');
                    $failure->setAttribute('message', $error->getMessage());
                    $testcase->appendChild($failure);
                }

                $testsuite->appendChild($testcase);
            }

            $genericErrors = $analysisResult->getNotFileSpecificErrors();
            if (count($genericErrors) > 0) {
                $testcase = $dom->createElement('testcase');
                $testcase->setAttribute('failures', (string) count($genericErrors));
                $testcase->setAttribute('errors', (string) count($genericErrors));
                $testcase->setAttribute('tests', (string) count($genericErrors));

                foreach ($genericErrors as $genericError) {
                    $failure = $dom->createElement('failure');
                    $failure->setAttribute('type', 'error');
                    $failure->setAttribute('message', $genericError);
                    $testcase->appendChild($failure);
                }

                $testsuite->appendChild($testcase);
            }
        }

        $dom->formatOutput = true;

        $style->write($style->isDecorated() ? OutputFormatter::escape($dom->saveXML()) : $dom->saveXML());

        return $returnCode;
    }

}

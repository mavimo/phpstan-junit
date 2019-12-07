<?php

declare(strict_types=1);

namespace Mavimo\Tests\PHPStan\ErrorFormatter;

use DOMDocument;
use Generator;
use Mavimo\PHPStan\ErrorFormatter\JunitErrorFormatter;
use PHPStan\File\SimpleRelativePathHelper;
use PHPStan\Testing\ErrorFormatterTestCase;

class JunitErrorFormatterTest extends ErrorFormatterTestCase
{
    /**
     * phpcs:disable
     *
     * @return \Generator<array<int, string|int>>
     *
     * phpcs:enable
     */
    public function dataFormatterOutputProvider(): Generator
    {
        yield [
            'No errors',
            0,
            0,
            0,
            '<?xml version="1.0" encoding="UTF-8"?>
<testsuite failures="0" name="phpstan" tests="0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://raw.githubusercontent.com/junit-team/junit5/r5.5.1/platform-tests/src/test/resources/jenkins-junit.xsd">
  <testcase name="phpstan"/>
</testsuite>
',
        ];

        yield [
            'One file error',
            1,
            1,
            0,
            '<?xml version="1.0" encoding="UTF-8"?>
<testsuite failures="1" name="phpstan" tests="1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://raw.githubusercontent.com/junit-team/junit5/r5.5.1/platform-tests/src/test/resources/jenkins-junit.xsd">
  <testcase name="folder with unicode &#x1F603;/file name with &quot;spaces&quot; and unicode &#x1F603;.php:4">
    <failure message="Foo" />
  </testcase>
</testsuite>
',
        ];

        yield [
            'One generic error',
            1,
            0,
            1,
            '<?xml version="1.0" encoding="UTF-8"?>
<testsuite failures="1" name="phpstan" tests="1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://raw.githubusercontent.com/junit-team/junit5/r5.5.1/platform-tests/src/test/resources/jenkins-junit.xsd">
  <testcase name="Generic error">
    <failure message="first generic error" />
  </testcase>
</testsuite>
',
        ];

        yield [
            'Multiple file errors',
            1,
            4,
            0,
            '<?xml version="1.0" encoding="UTF-8"?>
<testsuite failures="4" name="phpstan" tests="4" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://raw.githubusercontent.com/junit-team/junit5/r5.5.1/platform-tests/src/test/resources/jenkins-junit.xsd">
  <testcase name="folder with unicode &#x1F603;/file name with &quot;spaces&quot; and unicode &#x1F603;.php:2">
    <failure message="Bar" />
  </testcase>
  <testcase name="folder with unicode &#x1F603;/file name with &quot;spaces&quot; and unicode &#x1F603;.php:4">
    <failure message="Foo" />
  </testcase>
  <testcase name="foo.php:1">
    <failure message="Foo"/>
  </testcase>
  <testcase name="foo.php:5">
    <failure message="Bar"/>
  </testcase>
</testsuite>
',
        ];

        yield [
            'Multiple generic errors',
            1,
            0,
            2,
            '<?xml version="1.0" encoding="UTF-8"?>
<testsuite failures="2" name="phpstan" tests="2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://raw.githubusercontent.com/junit-team/junit5/r5.5.1/platform-tests/src/test/resources/jenkins-junit.xsd">
  <testcase name="Generic error">
    <failure message="first generic error" />
  </testcase>
  <testcase name="Generic error">
    <failure message="second generic error"/>
  </testcase>
</testsuite>
',
        ];

        yield [
            'Multiple file, multiple generic errors',
            1,
            4,
            2,
            '<?xml version="1.0" encoding="UTF-8"?>
<testsuite failures="6" name="phpstan" tests="6" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://raw.githubusercontent.com/junit-team/junit5/r5.5.1/platform-tests/src/test/resources/jenkins-junit.xsd">
  <testcase name="folder with unicode &#x1F603;/file name with &quot;spaces&quot; and unicode &#x1F603;.php:2">
    <failure message="Bar" />
  </testcase>
  <testcase name="folder with unicode &#x1F603;/file name with &quot;spaces&quot; and unicode &#x1F603;.php:4">
    <failure message="Foo" />
  </testcase>
  <testcase name="foo.php:1">
    <failure message="Foo"/>
  </testcase>
  <testcase name="foo.php:5">
    <failure message="Bar"/>
  </testcase>
  <testcase name="Generic error">
    <failure message="first generic error" />
  </testcase>
  <testcase name="Generic error">
    <failure message="second generic error"/>
  </testcase>
</testsuite>
',
        ];
    }

    /**
     * Test generated use cases for JUnit output format.
     *
     * @dataProvider dataFormatterOutputProvider
     * @param string $message
     * @param int    $exitCode
     * @param int    $numFileErrors
     * @param int    $numGenericErrors
     * @param string $expected
     */
    public function testFormatErrors(
        string $message,
        int $exitCode,
        int $numFileErrors,
        int $numGenericErrors,
        string $expected
    ): void {
        $formatter = new JunitErrorFormatter(new SimpleRelativePathHelper(self::DIRECTORY_PATH));

        $this->assertSame($exitCode, $formatter->formatErrors(
            $this->getAnalysisResult($numFileErrors, $numGenericErrors),
            $this->getOutput()
        ), sprintf('%s: response code do not match', $message));

        $xml = new DOMDocument();
        $xml->loadXML($this->getOutputContent());

        $this->assertTrue($xml->schemaValidate('https://raw.githubusercontent.com/junit-team/junit5/r5.5.1/platform-tests/src/test/resources/jenkins-junit.xsd'));

        $this->assertXmlStringEqualsXmlString($expected, $this->getOutputContent(), sprintf('%s: XML do not match', $message));
    }
}

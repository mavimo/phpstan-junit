<?php

declare(strict_types=1);

namespace Mavimo\Tests\PHPStan\ErrorFormatter;

use Generator;
use Mavimo\PHPStan\ErrorFormatter\JunitErrorFormatter;
use PHPStan\Command\ErrorFormatter\TestBaseFormatter;
use PHPStan\File\SimpleRelativePathHelper;

class JunitErrorFormatterTest extends TestBaseFormatter
{
    /**
     * [dataFormatterOutputProvider description]
     *
     * @return \Generator<array<int, string|int>>
     */
    public function dataFormatterOutputProvider(): Generator
    {
        yield [
            'No errors',
            0,
            0,
            0,
            '<?xml version="1.0" encoding="UTF-8"?>
<testsuites name="static analysis">
  <testsuite failures="0" name="phpstan" tests="0">
    <testcase errors="0" failures="0" name="phpstan" tests="0"/>
  </testsuite>
</testsuites>
',
        ];

        yield [
            'One file error',
            1,
            1,
            0,
            '<?xml version="1.0" encoding="UTF-8"?>
<testsuites name="static analysis">
  <testsuite failures="1" name="phpstan" tests="1">
    <testcase errors="0" failures="1" name="folder with unicode &#x1F603;/file name with &quot;spaces&quot; and unicode &#x1F603;.php" tests="1">
      <failure message="Line 4: Foo" type="error" />
    </testcase>
  </testsuite>
</testsuites>
',
        ];

        yield [
            'One generic error',
            1,
            0,
            1,
            '<?xml version="1.0" encoding="UTF-8"?>
<testsuites name="static analysis">
  <testsuite failures="1" name="phpstan" tests="1">
    <testcase errors="0" failures="1" name="Generic errors" tests="1">
      <failure message="first generic error" type="error" />
    </testcase>
  </testsuite>
</testsuites>
',
        ];

        yield [
            'Multiple file errors',
            1,
            4,
            0,
            '<?xml version="1.0" encoding="UTF-8"?>
<testsuites name="static analysis">
  <testsuite failures="4" name="phpstan" tests="4">
    <testcase errors="0" failures="2" name="folder with unicode &#x1F603;/file name with &quot;spaces&quot; and unicode &#x1F603;.php" tests="2">
      <failure message="Line 2: Bar" type="error" />
      <failure message="Line 4: Foo" type="error" />
    </testcase>
    <testcase errors="0" failures="2" name="foo.php" tests="2">
      <failure message="Line 1: Foo" type="error"/>
      <failure message="Line 5: Bar" type="error"/>
    </testcase>
  </testsuite>
</testsuites>
',
        ];

        yield [
            'Multiple generic errors',
            1,
            0,
            2,
            '<?xml version="1.0" encoding="UTF-8"?>
<testsuites name="static analysis">
  <testsuite failures="2" name="phpstan" tests="2">
    <testcase errors="0" failures="2" name="Generic errors" tests="2">
      <failure message="first generic error" type="error" />
      <failure message="second generic error" type="error"/>
    </testcase>
  </testsuite>
</testsuites>
',
        ];

        yield [
            'Multiple file, multiple generic errors',
            1,
            4,
            2,
            '<?xml version="1.0" encoding="UTF-8"?>
<testsuites name="static analysis">
  <testsuite failures="6" name="phpstan" tests="6">
    <testcase errors="0" failures="2" name="folder with unicode &#x1F603;/file name with &quot;spaces&quot; and unicode &#x1F603;.php" tests="2">
      <failure message="Line 2: Bar" type="error" />
      <failure message="Line 4: Foo" type="error" />
    </testcase>
    <testcase errors="0" failures="2" name="foo.php" tests="2">
      <failure message="Line 1: Foo" type="error"/>
      <failure message="Line 5: Bar" type="error"/>
    </testcase>
    <testcase errors="0" failures="2" name="Generic errors" tests="2">
      <failure message="first generic error" type="error" />
      <failure message="second generic error" type="error"/>
    </testcase>
  </testsuite>
</testsuites>
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
            $this->getErrorConsoleStyle()
        ), sprintf('%s: response code do not match', $message));

        $this->assertXmlStringEqualsXmlString($expected, $this->getOutputContent(), sprintf('%s: XML do not match', $message));
    }
}

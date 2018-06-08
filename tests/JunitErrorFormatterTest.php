<?php

declare(strict_types = 1);

namespace Mavimo\PHPStan\ErrorFormatter;

class JunitErrorFormatterTest extends TestBaseFormatter
{

    public function dataFormatterOutputProvider(): iterable
    {
        yield [
            'No errors',
            0,
            0,
            0,
            '<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite failures="0" name="phpstan" tests="1">
    <testcase/>
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
<testsuites>
  <testsuite name="phpstan">
    <testcase name="folder with unicode 😃/file name with &quot;spaces&quot; and unicode 😃.php" errors="0" failures="1" tests="1" file="folder with unicode 😃/file name with &quot;spaces&quot; and unicode 😃.php">
      <failure message="Foo" type="error">line 4</failure>
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
<testsuites>
  <testsuite name="phpstan">
    <testcase errors="0" failures="1" tests="1">
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
<testsuites>
  <testsuite name="phpstan">
    <testcase name="folder with unicode 😃/file name with &quot;spaces&quot; and unicode 😃.php" errors="0" failures="2" tests="2" file="folder with unicode 😃/file name with &quot;spaces&quot; and unicode 😃.php">
      <failure message="Foo" type="error">line 4</failure>
      <failure message="Bar" type="error">line 2</failure>
    </testcase>
    <testcase name="foo.php" errors="0" failures="2" tests="2" file="foo.php">
      <failure message="Foo" type="error">line 1</failure>
      <failure message="Bar" type="error">line 5</failure>
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
<testsuites>
  <testsuite name="phpstan">
    <testcase errors="0" failures="2" tests="2">
      <failure message="first generic error" type="error" />
      <failure message="second generic error" type="error" />
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
<testsuites>
  <testsuite name="phpstan">
    <testcase name="folder with unicode 😃/file name with &quot;spaces&quot; and unicode 😃.php" errors="0" failures="2" tests="2" file="folder with unicode 😃/file name with &quot;spaces&quot; and unicode 😃.php">
      <failure message="Foo" type="error">line 4</failure>
      <failure message="Bar" type="error">line 2</failure>
    </testcase>
    <testcase name="foo.php" errors="0" failures="2" tests="2" file="foo.php">
      <failure message="Foo" type="error">line 1</failure>
      <failure message="Bar" type="error">line 5</failure>
    </testcase>
    <testcase errors="0" failures="2" tests="2">
      <failure message="first generic error" type="error"/>
      <failure message="second generic error" type="error"/>
    </testcase>
  </testsuite>
</testsuites>
',
        ];
    }

    /**
     * @dataProvider dataFormatterOutputProvider
     *
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
    ): void
    {
        $formatter = new JunitErrorFormatter();

        $this->assertSame($exitCode, $formatter->formatErrors(
            $this->getAnalysisResult($numFileErrors, $numGenericErrors),
            $this->getErrorConsoleStyle()
        ), sprintf('%s: response code do not match', $message));

        $this->assertXmlStringEqualsXmlString($expected, $this->getOutputContent(), sprintf('%s: XML do not match', $message));
    }

}

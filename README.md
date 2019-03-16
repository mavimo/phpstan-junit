# PHPStan JUnit error reporter

[![License](https://img.shields.io/packagist/l/mavimo/phpstan-junit.svg)](http://opensource.org/licenses/MIT)
[![Coverage Status](https://img.shields.io/codecov/c/github/mavimo/phpstan-junit/master.svg)](https://codecov.io/gh/mavimo/phpstan-junit?branch=master)

[![Packagist](http://img.shields.io/packagist/v/mavimo/phpstan-junit.svg)](https://packagist.org/packages/mavimo/phpstan-junit)
[![Packagist](http://img.shields.io/packagist/dt/mavimo/phpstan-junit.svg)](https://packagist.org/packages/mavimo/phpstan-junit)
[![Packagist](http://img.shields.io/packagist/dm/mavimo/phpstan-junit.svg)](https://packagist.org/packages/mavimo/phpstan-junit)
[![Packagist](http://img.shields.io/packagist/dd/mavimo/phpstan-junit.svg)](https://packagist.org/packages/mavimo/phpstan-junit)

The main scope for this project is to create error report in **JUnit** format that can be easly integrated in *Jenkins* or other tools that use this information.

## How to use it

### Install

You need to include this library in your project as dev-dependency, it dependes on the version of phpstan you're using you should use a different version of `mavimo/phpstan-junit` library.

#### PHPStan 0.10

You need to require the version `0.1.0` of this package:
```
composer require --dev mavimo/phpstan-junit:~0.1.0
```

You should require this extension on `phpstan.neon` file in the root of your project or the file you specify to phpstan using the `--config` flag by referencing `extension.neon` file:

```
includes:
    - vendor/mavimo/phpstan-junit/phpstan.neon
```
or declaring the service via:
```
services:
    errorFormatter.junit:
        class: Mavimo\PHPStan\ErrorFormatter\JunitErrorFormatter
```

#### PHPStan 0.11

The current version is not marked as stable (should be in some week), so you need to pull the version from master:
```
composer require --dev mavimo/phpstan-junit:dev-master
```

You should require this extension on `phpstan.neon` file in the root of your project or the file you specify to phpstan using the `--config` flag by referencing `extension.neon` file:

```
includes:
    - vendor/mavimo/phpstan-junit/extension.neon
```
or declaring the service via:
```
services:
    errorFormatter.junit:
        class: Mavimo\PHPStan\ErrorFormatter\JunitErrorFormatter
```

### Generate JUnit report

You should gnerate JUnit report with the flag `--error-format=junit`, eg:

```
vendor/bin/phpstan --configuration=phpstan.neon --error-format=junit --level=7 --no-progress --no-interaction analyse SOURCE_CODE_DIR
```

## Contributing

Contributions are welcome!

PR's will be merged only if:

 - *phpunit* is :white_check_mark:, you can run it using `vendor/bin/phpunit`
 - *phpstan* is :white_check_mark:, you can run it using `vendor/bin/phpstan analyse`
 - *phpcs* is :white_check_mark:, you can run it using `vendor/bin/phpcs`
 - *code coverage* will not decrease (or there are good reason to decrease it), you can check the current coverage using `phpdbg -qrr ./vendor/bin/phpunit --coverage-text`

If you have any question feel free to open a issue or contact me!

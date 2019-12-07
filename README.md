# PHPStan JUnit error reporter

[![License](https://img.shields.io/packagist/l/mavimo/phpstan-junit.svg)](http://opensource.org/licenses/MIT)
[![Coverage Status](https://img.shields.io/codecov/c/github/mavimo/phpstan-junit/master.svg)](https://codecov.io/gh/mavimo/phpstan-junit?branch=master)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/ce52632640e74313b03862890e9990fc)](https://www.codacy.com/manual/mavimo/phpstan-junit)

[![Packagist](http://img.shields.io/packagist/v/mavimo/phpstan-junit.svg)](https://packagist.org/packages/mavimo/phpstan-junit)
[![Packagist](http://img.shields.io/packagist/dt/mavimo/phpstan-junit.svg)](https://packagist.org/packages/mavimo/phpstan-junit)
[![Packagist](http://img.shields.io/packagist/dm/mavimo/phpstan-junit.svg)](https://packagist.org/packages/mavimo/phpstan-junit)
[![Packagist](http://img.shields.io/packagist/dd/mavimo/phpstan-junit.svg)](https://packagist.org/packages/mavimo/phpstan-junit)

The main scope for this project is to create error report in **JUnit** format that can be easly integrated in *Jenkins* or other tools that use this information.

## How to use it

### Install

You need to include this library in your project as dev-dependency, it dependes on the version of phpstan you're using you should use a different version of `mavimo/phpstan-junit` library, this table will give you a dependency map:

| `phpstan/phpstan` version | `mavimo/phpstan-junit` version |
|---------------------------|--------------------------------|
| `0.10.x`                  | `0.1.x`                        |
| `0.11.x`                  | `0.2.x`                        |
| `0.12.x`                  | `0.3.x`                        |

But if alredy specified the `phpstan/phpstan` version you can just use:

```bash
composer require --dev mavimo/phpstan-junit
```

If you also install [phpstan/extension-installer](https://github.com/phpstan/extension-installer) then you're all set, otherwise take a look to *manual setup* section below.

<details>
  <summary><strong>Manual setup</strong></summary>
  if you don't want to use `phpstan/extension-installer`, you should require the `extension.neon` file on your `phpstan.neon.dist` file in the root of your project (or on the file you specify to phpstan using the `--config` flag):

  ```yaml
  includes:
      - vendor/mavimo/phpstan-junit/extension.neon
  ```
  or declaring the service via:
  ```yaml
  services:
      errorFormatter.junit:
          class: Mavimo\PHPStan\ErrorFormatter\JunitErrorFormatter
  ```
</details>

<details>
  <summary><strong>PHPStan 0.10</strong></summary>
  <br />
  You should require this extension on `phpstan.neon` file in the root of your project or the file you specify to phpstan using the `--config` flag by referencing `extension.neon` file:

  ```yaml
  includes:
      - vendor/mavimo/phpstan-junit/phpstan.neon
  ```

  or declaring the service via:

  ```yaml
  services:
      errorFormatter.junit:
          class: Mavimo\PHPStan\ErrorFormatter\JunitErrorFormatter
  ```
</details>

### Generate JUnit report

You should gnerate JUnit report with the flag `--error-format=junit`, eg:

```bash
vendor/bin/phpstan --error-format=junit --no-progress --no-interaction analyse src
```

## Contributing

Contributions are welcome!

PR's will be merged only if:

- *phpunit* is :white_check_mark:, you can run it using `vendor/bin/phpunit`
- *phpstan* is :white_check_mark:, you can run it using `vendor/bin/phpstan analyse`
- *phpcs* is :white_check_mark:, you can run it using `vendor/bin/phpcs`
- *code coverage* will not decrease (or there are good reason to decrease it), you can check the current coverage using `phpdbg -qrr ./vendor/bin/phpunit --coverage-text`

If you have any question feel free to open a issue or contact me!

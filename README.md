## PHPStan JUnit error reporter

Generate error reporter used to create JUnit output

Include as dependency by:

```
composer require --dev mavimo/phpstan-junit
```

And enable on your `phpstan.neon` config file by including

```
services:
    errorFormatter.junit:
        class: Mavimo\PHPStan\ErrorFormatter\JunitErrorFormatter
```

than execute it by running:

```
vendor/bin/phpstan --configuration=phpstan.neon --errorFormat=junit --level=7 analyse SOURCE_CODE_DIR
```

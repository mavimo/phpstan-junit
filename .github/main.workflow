workflow "Testing" {
  on = "push"
  resolves = [
    "phpunit",
    "phpcs",
    "phpstan",
    "phpstan-integration",
  ]
}

action "dependency" {
  uses = "docker://composer"
  args = "install --prefer-source --no-progress"
}

action "phpunit" {
  uses = "docker://php:7.2"
  needs = ["dependency"]
  args = "vendor/bin/phpunit"
}

action "phpcs" {
  uses = "docker://php:7.2"
  needs = ["dependency"]
  args = "vendor/bin/phpcs"
}

action "phpstan" {
  uses = "docker://php:7.2"
  needs = ["dependency"]
  args = "vendor/bin/phpstan analyse --level=7 --no-progress ./src ./tests"
}

action "phpstan-integration" {
  uses = "docker://php:7.2"
  needs = ["dependency"]
  args = "vendor/bin/phpstan analyse --level=7 --no-progress --error-format=junit ./src ./tests"
}

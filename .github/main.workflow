workflow "Testing" {
  on = "push"
  resolves = [
    "phpcs",
    "phpstan",
    "phpstan-integration",
    "codecoverage",
  ]
}

action "dependency" {
  uses = "docker://composer"
  args = "install --prefer-source --no-progress"
}

action "phpunit" {
  uses = "docker://php:7.2"
  needs = ["dependency"]
  args = "phpdbg -qrr ./vendor/bin/phpunit"
}

action "phpcs" {
  uses = "docker://php:7.2"
  needs = ["dependency"]
  args = "vendor/bin/phpcs"
}

action "phpstan" {
  uses = "docker://php:7.2"
  needs = ["dependency"]
  args = "vendor/bin/phpstan analyse"
}

action "phpstan-integration" {
  uses = "docker://php:7.2"
  needs = ["dependency"]
  args = "vendor/bin/phpstan analyse --no-progress --error-format=junit"
}

action "codecoverage" {
  uses = "pleo-io/actions/codecov@master"
  needs = ["phpunit"]
  secrets = ["CODECOV_TOKEN"]
  args = "-f clover-report.xml"
}

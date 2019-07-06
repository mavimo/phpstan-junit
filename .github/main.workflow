workflow "Testing" {
  on = "push"
  resolves = [
    "phpcs",
    "phpstan",
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

action "codecoverage" {
  uses = "pleo-io/actions/codecov@master"
  needs = ["phpunit"]
  secrets = ["CODECOV_TOKEN"]
  args = "-f clover-report.xml"
}

workflow "Integration test" {
  resolves = [
    "test",
  ]
  on = "push"
}

action "prepare" {
  uses = "docker://bash"
  runs = "sh -l -c"
  args = ["sed -i 's/{GITHUB_SHA}/'\"$GITHUB_SHA\"'/' $GITHUB_WORKSPACE/tests-integration/composer.json"]
}

action "composer-install" {
  uses = "docker://composer"
  needs = ["prepare"]
  args = "install --working-dir tests-integration"
}

action "test" {
  uses = "docker://php:7.2"
  needs = ["composer-install"]
  args = "tests-integration/vendor/bin/phpstan analyse --configuration tests-integration/phpstan.neon.dist --no-progress --error-format=junit src"
}

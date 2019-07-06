workflow "Testing" {
  on = "push"
  resolves = [
    "phpcs",
    "phpstan",
    "codecoverage",
    "integration-test-run",
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

action "integration-test-prepare" {
  uses = "docker://bash"
  runs = "sh -l -c"
  args = ["sed -i 's/{GITHUB_SHA}/'\"$GITHUB_SHA\"'/' $GITHUB_WORKSPACE/tests-integration/composer.json"]
  #args = ["REPLACEMENT=$(echo '/{GITHUB_SHA}/'$GITHUB_SHA'/g') && sed $REPLACEMENT tests-integration/composer.json"]
}

action "integration-test-composer-install" {
  uses = "docker://composer"
  needs = ["integration-test-prepare"]
  args = "install --working-dir tests-integration"
}

action "integration-test-run" {
  uses = "docker://php:7.2"
  needs = ["integration-test-composer-install"]
  args = "tests-integration/vendor/bin/phpstan analyse --no-progress --error-format=junit src"
}

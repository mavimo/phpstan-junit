workflow "Integration tests" {
  on = "push"
  resolves = [
    "test",
  ]
}

action "prepare" {
  uses = "docker://bash"
  runs = "sh -l -c"
  args = ["sed -i 's/{GITHUB_SHA}/'\"$GITHUB_SHA\"'/' $GITHUB_WORKSPACE/tests-integration/composer.json"]
  #args = ["REPLACEMENT=$(echo '/{GITHUB_SHA}/'$GITHUB_SHA'/g') && sed $REPLACEMENT tests-integration/composer.json"]
}

action "composer-install" {
  uses = "docker://composer"
  needs = ["prepare"]
  args = "install --working-dir tests-integration"
}

action "test" {
  uses = "docker://php:7.2"
  needs = ["composer-install"]
  args = "tests-integration/vendor/bin/phpstan analyse --no-progress --error-format=junit src"
}

# Contributing

Your input and contributions are very welcome! Please open issues with
improvements, feature requests or bug reports.

If you want to contribute source code, add documentation or fix spelling
mistakes try this:

1. [Fork](http://help.github.com/forking/) the project.
1. Install vendor libraries needed for testing etc. via `make install-dependencies-dev`.
1. Make your changes and additions (e.g. in a new branch).
1. Verify your changes by making sure that `make tests` and `make code-sniffer-cli` do not fail.
1. Add, commit, squash and push the changes to your forked repository.
1. Send a [pull request](http://help.github.com/pull-requests/) with a well written issue describing the change and why it is necessary.

Please note, that the code tries to adhere to the [PSR-2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md).
Commits are continously integrated via
[TravisCI](https://travis-ci.org/shrink0r/workflux)
and failing the PHPUnit or PHP CodeSniffer tests will fail the builds. Usually
the build status will be shown on your pull request by Github. If something
fails please try to fix your changes as otherwise integrating them is harder.

There is no Contributor License Agreement (CLA) to sign, but you have to accept
and agree to the [license](LICENSE.md) to get your patches included.

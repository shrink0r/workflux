# Contributing

Your input and contributions are very welcome! Please open issues with improvements, feature requests or bug reports.

If you want to contribute source code, add documentation or fix spelling mistakes try this:

1. [Fork](http://help.github.com/forking/) the project.
1. Install vendor libraries needed for testing etc. via `make install-dependencies-dev`.
1. Make your changes and additions (e.g. in a new branch).
1. Verify your changes by making sure that `make tests` and `make code-sniffer-cli` do not fail.
1. Add, commit, squash and push the changes to your forked repository.
1. Send a [pull request](http://help.github.com/pull-requests/) with a well written issue describing the change and why it is necessary.

Please note, that the code tries to adhere to the [PSR-2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md). Commits are continously integrated via [TravisCI](https://travis-ci.org/shrink0r/workflux) and failing the PHPUnit or PHP CodeSniffer tests will fail the builds. Usually the build status will be shown on your pull request by Github. If something fails please try to fix your changes as otherwise integrating them is harder.

There is no Contributor License Agreement (CLA) to sign, but you have to accept and agree to the [license](LICENSE.md) and the Contributor Code of Conduct (CoC) to get your patches included.

## Contributor Code of Conduct

As contributors and maintainers of this project, we pledge to respect all people who contribute through reporting issues, posting feature requests, updating documentation, submitting pull requests or patches, and other activities.

We are committed to making participation in this project a harassment-free experience for everyone, regardless of level of experience, gender, gender identity and expression, sexual orientation, disability, personal appearance, body size, race, age, or religion.

Examples of unacceptable behavior by participants include the use of sexual language or imagery, derogatory comments or personal attacks, trolling, public or private harassment, insults, or other unprofessional conduct.

Project maintainers have the right and responsibility to remove, edit, or reject comments, commits, code, wiki edits, issues, and other contributions that are not aligned to this Code of Conduct. Project maintainers who do not follow the Code of Conduct may be removed from the project team.

Instances of abusive, harassing, or otherwise unacceptable behavior may be reported by opening an issue or contacting one or more of the project maintainers.

This Code of Conduct is adapted from the [Contributor Covenant](http://contributor-covenant.org), version 1.0.0, available at [http://contributor-covenant.org/version/1/0/0/](http://contributor-covenant.org/version/1/0/0/)

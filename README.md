# workflux

[![Latest-Stable-Version](https://poser.pugx.org/shrink0r/workflux/v/stable.svg)][1]
[![License](https://poser.pugx.org/shrink0r/workflux/license.svg)][10]
[![Latest Unstable Version](https://poser.pugx.org/shrink0r/workflux/v/unstable.svg)][1]
[![Build Status](https://secure.travis-ci.org/shrink0r/workflux.png)][2]
[![Coverage Status](https://coveralls.io/repos/shrink0r/workflux/badge.png)][3]
[![Code Climate](https://codeclimate.com/github/shrink0r/workflux/badges/gpa.svg)](https://codeclimate.com/github/shrink0r/workflux)
[![Dependency Status](https://www.versioneye.com/user/projects/542da521fc3f5ca427000299/badge.svg?style=flat-square)][4]
[![Stories in Ready](https://badge.waffle.io/shrink0r/workflux.png?label=ready&title=Ready)][9]
[![Total Composer Downloads](https://poser.pugx.org/shrink0r/workflux/d/total.png)][1] 

## Purpose

`Workflux` is a [Finite State Machine(FSM)](http://en.wikipedia.org/wiki/Finite-state_machine) for php.

Good reads on the topic:

* [Why developers should be force-fed state machines](http://www.shopify.com/technology/3383012-why-developers-should-be-force-fed-state-machines)
* [Why Developers Never Use State Machines](http://www.skorks.com/2011/09/why-developers-never-use-state-machines/)

## Requirements and installation

- PHP 7.0+

Install the library via [Composer](http://getcomposer.org/):

```./composer.phar require shrink0r/worklfux [optional version]```

Adding it manually as a vendor library requirement to the `composer.json` file of your project works as well:

```json
{
    "require": {
        "shrink0r/worklfux": "^1.0"
    }
}
```

Alternatively, you can download a release archive from the [available releases](https://github.com/shrink0r/worklfux/releases) page.

## Documentation

tbd.

## Community

None, but you may join the freenode IRC [`#honeybee`](irc://irc.freenode.org/honeybee) channel or https://gitter.im/honeybee/Lobby anytime. :-)

## Contributors

Please contribute by [forking](http://help.github.com/forking/) and sending a [pull request](http://help.github.com/pull-requests/). More information can be found in the [`CONTRIBUTING.md`](CONTRIBUTING.md) file. The authors and contributors are mentioned in the [github contributors graph](https://github.com/shrink0r/workflux/graphs/contributors) of this repository.

The code tries to adhere to the following PHP-FIG standards: [PSR-4][6], [PSR-1][7] and [PSR-2][8].

## Changelog

See [`CHANGELOG.md`](CHANGELOG.md) for more information about changes.

## License

This project is MIT licensed. See the [linked license](LICENSE.md) for details.

[1]: https://packagist.org/packages/shrink0r/workflux "shrink0r/workflux on packagist"
[2]: http://travis-ci.org/shrink0r/workflux "shrink0r/workflux on travis-ci"
[3]: https://coveralls.io/r/shrink0r/workflux "shrink0r/workflux on coveralls"
[4]: https://www.versioneye.com/user/projects/576dcc347bc681004a3f9b68 "shrink0r/workflux on versioneye"
[6]: http://www.php-fig.org/psr/psr-4/ "PSR-4 Autoloading Standard"
[7]: http://www.php-fig.org/psr/psr-1/ "PSR-1 Basic Coding Standard"
[8]: http://www.php-fig.org/psr/psr-2/ "PSR-2 Coding Style Guide"
[9]: https://waffle.io/shrink0r/workflux "shrink0r/workflux on waffle"
[10]: LICENSE.md "license file with full text of the license"

# Workflux

[![Latest Stable Version](https://poser.pugx.org/shrink0r/workflux/version.png)](https://packagist.org/packages/shrink0r/workflux)
[![Latest Unstable Version](https://poser.pugx.org/shrink0r/workflux/v/unstable.png)](https://packagist.org/packages/shrink0r/workflux)
[![Build Status](https://secure.travis-ci.org/shrink0r/workflux.png)](http://travis-ci.org/shrink0r/workflux)
[![Coverage Status](https://coveralls.io/repos/shrink0r/workflux/badge.png?branch=master)](https://coveralls.io/r/shrink0r/workflux?branch=master)
[![Dependency Status](https://www.versioneye.com/user/projects/542da521fc3f5ca427000299/badge.svg?style=flat)](https://www.versioneye.com/user/projects/542da521fc3f5ca427000299)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/shrink0r/workflux/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/shrink0r/workflux/?branch=master)
[![Code Climate](https://codeclimate.com/github/shrink0r/workflux/badges/gpa.svg)](https://codeclimate.com/github/shrink0r/workflux)
[![License](https://poser.pugx.org/shrink0r/workflux/license.svg)](http://creativecommons.org/licenses/by-sa/3.0/deed.en_US)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/c9d87c4a-f2f6-4f10-847e-8a9162d306d9/mini.png)](https://insight.sensiolabs.com/projects/c9d87c4a-f2f6-4f10-847e-8a9162d306d9)
[![Stories in Ready](https://badge.waffle.io/shrink0r/workflux.png?label=ready&title=Issues%20Open)](https://waffle.io/shrink0r/workflux)

## Purpose

`Workflux` is a [Finite State Machine(FSM)](http://en.wikipedia.org/wiki/Finite-state_machine) for php.
It's supposed to be useful for implementing all kinds of FSM suiting scenarios like e.g. workflows or matching.
Here are two reads on the topic, dealing with why/where you might want to use a FSM.

* [Why developers should be force-fed state machines](http://www.shopify.com/technology/3383012-why-developers-should-be-force-fed-state-machines)
* [Why Developers Never Use State Machines](http://www.skorks.com/2011/09/why-developers-never-use-state-machines/)

Below is an example of a state machine instance rendered by the library's DotGraphRenderer + [Graphviz](http://www.graphviz.org).

![Screenshot of a rendered example state machine](https://raw.githubusercontent.com/shrink0r/workflux/master/docs/state_machine.png)

## Requirements and installation

- PHP v5.5+

Install the library via [Composer](http://getcomposer.org/):

```./composer.phar require shrink0r/workflux [optional version]```

Adding it manually as a vendor library requirement to the `composer.json` file
of your project works as well:

```json
{
    "require": {
        "shrink0r/workflux": "~0.5"
    }
}
```

Alternatively, you can download a release archive from the [github releases](releases).

## Documentation

At the moment there are some basic usage examples and a completed API doc.  
The brave may look into the `tests` folder for more insights. ^^  
More documentation is up the pipeline.  
Until then, feel free to ask via irc or issues tracker.

* [API Doc](http://shrink0r.github.io/workflux/api/index.html)
* [Usage](https://github.com/shrink0r/workflux/blob/master/docs/usage.md)

## Community

None, but you may join the freenode IRC
[`irc://irc.freenode.org/honeybee`](irc://irc.freenode.org/honeybee) channel anytime. :-)

## Contributors

Please contribute by [forking](http://help.github.com/forking/) and sending a
[pull request](http://help.github.com/pull-requests/). More information can be
found in the [`CONTRIBUTING.md`](CONTRIBUTING.md) file.

## Changelog

See [`CHANGELOG.md`](CHANGELOG.md) for more information about changes.

## License

<a rel="license"
href="http://creativecommons.org/licenses/by-sa/3.0/deed.en_US"><img
alt="Creative Commons License" style="border-width:0"
src="http://i.creativecommons.org/l/by-sa/3.0/88x31.png" /></a><br /><span
xmlns:dct="http://purl.org/dc/terms/" property="dct:title">Workflux</span>
is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/deed.en_US">Creative Commons Attribution-ShareAlike 3.0 Unported License</a>.

CC-BY-SA-3.0 means, you are free to share, remix and make commercial use of the
work as long as you attribute and share alike. See [linked license](LICENSE.md) for details.

* Total Composer Downloads: [![Composer
  Downloads](https://poser.pugx.org/shrink0r/workflux/d/total.png)](https://packagist.org/packages/shrink0r/workflux)

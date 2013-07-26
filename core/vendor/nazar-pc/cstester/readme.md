# What is this?

CleverStyle Tester is simple tool to test your PHP applications

At first it was small script for testing of [CleverStyle CMS](https://github.com/nazar-pc/CleverStyle-CMS), but I found it can be helpful for others helpful,
and decided to maintain it as separate autonomous package. Hope, you'll like it)

Author – Nazar Mokrynskyi <nazar@mokrynskyi.com>

Copyright (c) 2013, Nazar Mokrynskyi

# Features

* Testing of PHP applications through browser
* Testing of PHP applications through terminal
* Interactive displaying of testing progress

# Requirements:

* Unix-like operating system
* PHP 5.4+

# How to install?

## Composer

Simply add dependency on `nazarpc/cstester` to your project's `composer.json`:

```json
{
    "require": {
        "nazar-pc/cstester": "*"
    }
}
```

## Git

Run `git clone https://github.com/nazar-pc/CSTester.git` inside project directory.

## Manual

Download zip/tarball from GitHub repository and extract to project directory (actually, only `src/nazarpc/CSTester.php` file is needed,
so, you can extract only this one file.

# How to use?

Put into root directory of the project `test.php` with such content (assumed composer is used, otherwise include `src/nazarpc/CSTester.php` file in any possible way):

```php
<?php
/**
 * Include CSTester class
 */
require __DIR__.'/vendor/autoload.php';
use nazarpc\CSTester;
/**
 * Create class instance and run testing
 */
(new CSTester(__DIR__.'/tests'))->run();
```
`tests` directory should contain tests of the project.

Open `http://website/test.php` through browser, or run `php test.php` from terminal.

Full example of usage in `example` directory, it is really very simple.

# Travis CI

Sample of `.travis.yml` for [Travic CI](https://travis-ci.org):

```yml
language: php
php:
  - 5.5
  - 5.4
script: php test.php
```

# Contributing

Feel free to report issues and send pull requests, all this is highly appreciated.
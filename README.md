# Elder Brother

[![Build Status](https://travis-ci.org/uuf6429/elder-brother.svg?branch=master)](https://travis-ci.org/uuf6429/elder-brother)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.5-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/uuf6429/elder-brother/master/LICENSE)
[![Coverage](https://codecov.io/gh/uuf6429/elder-brother/branch/master/graph/badge.svg?token=Bu2nK2Kq77)](https://codecov.io/github/uuf6429/elder-brother?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/uuf6429/elder-brother/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/uuf6429/elder-brother/?branch=master)
[![Packagist](https://img.shields.io/packagist/v/uuf6429/elder-brother.svg)](https://packagist.org/packages/uuf6429/elder-brother)

Elder Brother automatically regulates contributions before reaching your project.

Using events such as [Git-hooks](http://githooks.com/), one can ease development in various ways, such as:
- ensure code style conformity before committing changes
- run tools whose output should be a part of the commit
- performing roll-backs when checking-out old commits
- compiling sources or performing migrations when checking-out newer commits

This tools makes it easy to set up these tasks, as well as putting such policies under version control.

## Table Of Contents

- [Elder Brother](#elder-brother)
  - [Table Of Contents](#table-of-contents)
  - [Installation](#installation)
  - [Usage](#usage)
  - [Available Actions](#available-actions)
    - [Execute Custom Code (ExecuteCode)](#execute-custom-code-executecode)
    - [Execute External Program (ExecuteProgram)](#execute-external-program-executeprogram)
    - [Disallow Files (ForbiddenFiles)](#disallow-files-forbiddenfiles)
    - [PHP Code Style Fixer (PhpCsFixer)](#php-code-style-fixer-phpcsfixer)
    - [PHP Code Style Fixer (PhpCsFixerOld)](#php-code-style-fixer-phpcsfixerold)
    - [PHP Syntax Check (PhpLinter)](#php-syntax-check-phplinter)
    - [Show Warning For Files (RiskyFiles)](#show-warning-for-files-riskyfiles)
  - [FAQ](#faq)

## Installation

1. Add the library to your project with [Composer](https://getcomposer.org/):
   ```bash
   $ composer require neronmoon/scriptsdev
   $ composer require uuf6429/elder-brother "~1.0" --dev
   ```
   **Notes:**
   - in this way, *Elder Brother* will only be installed during development (Composer should be run with `--no-dev` in production).
   - `scriptsdev` package will make installation work during development and not break during production.
   - you may still have to install additional packages (detailed below) to use some particular actions.

2. Add the following entry to your `composer.json`:
   ```json
   {
       "scripts-dev": {
           "post-install-cmd": "vendor/bin/elder-brother install",
           "post-update-cmd": "vendor/bin/elder-brother install"
       }
   }
   ```
   
3. Create a `.brother.php` config file (as described below) and add `.brother.local.php` to your `.gitignore` file (this allows for user-level config).

## Usage

Elder Brother by default reads configuration from two files, `.brother.php` and `.brother.local.php` (which should be ignored by your VCS).

A typical configuration file will be structured like this:
```php
<?php

use uuf6429\ElderBrother\Action;
use uuf6429\ElderBrother\Event;
use uuf6429\ElderBrother\Change\GitChangeSet;

return [
    Event\Git::PRE_COMMIT => [
        1 => new Action\PhpLinter(
                GitChangeSet::getAddedCopiedModified()
                    ->name('/\\.php$/')
            ),
        2 => new Action\PhpCsFixer(
                GitChangeSet::getAddedCopiedModified()
                    ->name('/\\.php$/')
            ),
    ]
];
```
Basically, the configuration is an array of actions grouped by event.
In the above example, `PhpLinter` and `PhpCsFixer` actions will check all files (`GitChangeSet::getAddedCopiedModified()`) in the commit before it takes place (`Event\Git::PRE_COMMIT`).
It is recommended that you give each action a defined numeric index, so that they can be easily overridden by user config.
Note that no matter how the items look like in the array, action execution starts from the smallest index.

## Available Actions

### [Execute Custom Code (ExecuteCode)](https://github.com/uuf6429/elder-brother/blob/master/src/ElderBrother/Action/ExecuteCode.php)

```php
new ExecuteCode(
    string $description, // Description of the intention of the callback
    callable $callback // The callback to execute. It will receive $config, $input and $output as parameters
)
```
Executes the passed callback, function or static method.

### [Execute External Program (ExecuteProgram)](https://github.com/uuf6429/elder-brother/blob/master/src/ElderBrother/Action/ExecuteProgram.php)

```php
new ExecuteProgram(
    string $description, // Description of the intention of the program
    string $command, // Program command line (with parameters)
    bool $breakOnFailure, // (Optional, default is true) Stop execution if program returns non-0 exit code
    array|null $environment, // (Optional, default is null / current vars) Environment variables to pass to program
    string|null $currentDir, // The current directory to use for program
    int $timeout // (Optional, default is 60) The time to wait for program to finish (in seconds)
)
```
Executes an external program.

### [Disallow Files (ForbiddenFiles)](https://github.com/uuf6429/elder-brother/blob/master/src/ElderBrother/Action/ForbiddenFiles.php)

```php
new ForbiddenFiles(
    \FileList $files,
    string $reason
)
```
Will stop process if `$files` is not empty, for the reason specified in `$reason`.

### [PHP Code Style Fixer (PhpCsFixer)](https://github.com/uuf6429/elder-brother/blob/master/src/ElderBrother/Action/PhpCsFixer.php)

```php
new PhpCsFixer(
    \FileList $files, // The files to check
    int|null $level, // (Optional, defaults to NONE_LEVEL) Fixer level to use
    string[] $fixers, // (Optional, defaults to null) Set the fixers to use
    bool $addAutomatically // (Optional, default is true) Whether to add modified files to commit or not
)
```
Runs all the provided files through PHP-CS-Fixer, fixing any code style issues.

### [PHP Code Style Fixer (PhpCsFixerOld)](https://github.com/uuf6429/elder-brother/blob/master/src/ElderBrother/Action/PhpCsFixerOld.php)

```php
new PhpCsFixerOld(
    \FileList $files, // The files to check
    string|null $binFile, // (Optional, default is from vendor) File path to PHP-CS-Fixer binary
    string|null $configFile, // (Optional, default is project root) File path to PHP-CS-Fixer config
    bool $addAutomatically // (Optional, default is true) Whether to add modified files to commit or not
)
```
Runs all the provided files through PHP-CS-Fixer, fixing any code style issues.

### [PHP Syntax Check (PhpLinter)](https://github.com/uuf6429/elder-brother/blob/master/src/ElderBrother/Action/PhpLinter.php)

```php
new PhpLinter(
    \FileList $files // The files to check
)
```
Ensures that all the provided files are valid PHP files, terminating the
process with an error and non-zero exit code, if not.

### [Show Warning For Files (RiskyFiles)](https://github.com/uuf6429/elder-brother/blob/master/src/ElderBrother/Action/RiskyFiles.php)

```php
new RiskyFiles(
    \FileList $files,
    string $reason
)
```
Will show a warning if `$files` is not empty, for the reason specified in `$reason`.


## FAQ

### What is the main motivation behind this tool?

Projects with a considerable amount of collaborators usually ends up with policies of what kind of patches are accepted.
This is usually enforced through code-reviews (which can be very time-consuming), custom tools (difficult to set up) or shell scripts in pre-commit hooks (difficult to maintain and not really scalable).

The main idea here is to have a framework on the client side to enforce project contribution policies even before the source code reaches the main repository - saving developer time and maintenance costs.

### Why in PHP?

Why not? There are two alternatives: shell scripts and other languages.
Shell scripts have many disadvantages for this use - they're not cross-platform compatible, nor easily understandable.
Other languages might be suitable, but it means requiring your collaborators to know them.
We're basically using what's already available in the ecosystem.

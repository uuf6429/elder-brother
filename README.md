# Elder Brother

[![Build Status](https://travis-ci.org/uuf6429/elder-brother.svg?branch=master)](https://travis-ci.org/uuf6429/elder-brother)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.5-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/uuf6429/elder-brother/master/LICENSE)
[![Coverage](https://codecov.io/gh/uuf6429/elder-brother/branch/master/graph/badge.svg?token=Bu2nK2Kq77)](https://codecov.io/github/uuf6429/elder-brother?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/uuf6429/elder-brother/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/uuf6429/elder-brother/?branch=master)
[![Packagist](https://img.shields.io/packagist/v/uuf6429/ElderBrother.svg)](https://packagist.org/packages/uuf6429/ElderBrother)

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
    - [Disallow Files (ForbiddenFiles)](#disallow-files-forbiddenfiles)
    - [PHP Code Style Fixer (PhpCsFixer)](#php-code-style-fixer-phpcsfixer)
    - [PHP Syntax Check (PhpLinter)](#php-syntax-check-phplinter)
    - [Show Warning For Files (RiskyFiles)](#show-warning-for-files-riskyfiles)
  - [FAQ](#faq)

## Installation

First add the library to your project with [Composer](https://getcomposer.org/):
```bash
composer require uuf6429/elder-brother "~1.0"
```

Next, add the following entry to your `composer.json`:
```json
{
    "scripts": {
        "post-install-cmd": "vendor/bin/elder-brother install",
        "post-update-cmd": "vendor/bin/elder-brother install"
    }
}
```

**Note:** unfortunately, Composer scripts cannot be [disabled for non-dev runs](http://stackoverflow.com/q/13087088/314056), which is why Elder Brother cannot be loaded from `require-dev` only.
On the bright side, all the extra modules (such as PHP-CS-Fixer) can be loaded with `require-dev`.

## Usage

### TODO
- **DESCRIBE CREATION OF PROJECT-LEVEL CONFIG**
- **DESCRIBE CREATION OF USER-LEVEL CONFIG**
- **DESCRIBE AVAILABLE POLICIES/ACTIONS**

## Available Actions

### Execute Custom Code (ExecuteCode)

| Parameter  | Type | Description |
|------------|------|-------------|
| `$description` | `string` | Description of what this code does |
| `$callback` | `callable` | The callback to execute. The callback will receive $config, $input and $output as parameters |
*No Summary*

### Disallow Files (ForbiddenFiles)

| Parameter  | Type | Description |
|------------|------|-------------|
| `$files` | `\FileList` | *None* |
| `$reason` | `string` | *None* |
Will stop process if $files is not empty, for the reason specified in $reason.

### PHP Code Style Fixer (PhpCsFixer)

| Parameter  | Type | Description |
|------------|------|-------------|
| `$files` | `\FileList` | The files to check |
| `$binFile` | `string|null` | (Optional, default is from vendor) File path to PHP-CS-Fixer binary |
| `$configFile` | `string|null` | (Optional, default is project root) File path to PHP-CS-Fixer config |
| `$addAutomatically` | `bool` | (Optional, default is true) Whether to add modified files to commit or not |
Runs all the provided files through PHP-CS-Fixer, fixing any code style issues.

### PHP Syntax Check (PhpLinter)

| Parameter  | Type | Description |
|------------|------|-------------|
| `$files` | `\FileList` | The files to check |
Ensures that all the provided files are valid PHP files, terminating the
process with an error and non-zero exit code, if not.

### Show Warning For Files (RiskyFiles)

| Parameter  | Type | Description |
|------------|------|-------------|
| `$files` | `\FileList` | *None* |
| `$reason` | `string` | *None* |
Will show a warning if $files is not empty, for the reason specified in $reason.


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

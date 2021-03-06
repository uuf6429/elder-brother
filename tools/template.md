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
{{TOC_ACTIONS}}
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

{{SECTION_ACTIONS}}

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

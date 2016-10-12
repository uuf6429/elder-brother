# Git Project Control

[![Build Status](https://travis-ci.org/uuf6429/git-project-control.svg?branch=master)](https://travis-ci.org/uuf6429/git-project-control)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.5-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/uuf6429/git-project-control/master/LICENSE)
[![Coverage](https://codecov.io/gh/uuf6429/git-project-control/branch/master/graph/badge.svg?token=Bu2nK2Kq77)](https://codecov.io/github/uuf6429/git-project-control?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/uuf6429/git-project-control/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/uuf6429/git-project-control/?branch=master)
[![Packagist](https://img.shields.io/packagist/v/uuf6429/GitProjectControl.svg)](https://packagist.org/packages/uuf6429/GitProjectControl)

Set up and manage contribution policies for your PHP-based projects.

Using the power of [Git-hooks](http://githooks.com/), one can ease development in various ways, such as:
- ensure code style conformity before committing changes
- run tools whose output should be a part of the commit
- performing roll-backs when checking-out old commits
- generating css out of scss/sass or performing migrations when checking-out newer commits

This tools makes it easy to set up these tasks, as well as putting such policies under version control.

## Table Of Contents

- [Git Project Control](#git-project-control)
  - [Table Of Contents](#table-of-contents)
  - [Installation](#installation)
  - [Usage](#usage)
  - [FAQ](#faq)

## Installation

Ideally, this should be installed with [Composer](https://getcomposer.org/):

```bash
composer require uuf6429/git-project-control "~1.0"
```

## Usage

### TODO
- describe installation using composer install/update hooks
- describe creation of project-level config
- describe creation of user-level config
- describe available policies/actions

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

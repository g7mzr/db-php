---
layout: page
title: Install
---
## Introduction
**db-php** is installed into your project using composer.  Currently it is not on Packagist
which is the main Composer repository so is installed from the GitHib repository. Please
follow the instructions below to ad **db-php** to your project.

## Requirements

Please see below for the minimum requirements to use **db-php**:
* php 7.2.0
* php PDO
* php pdo_pgsql

## Install Via Composer

### Install Latest Release

To add the latest release to your project add the following to your composer.json
file.
```json
{
    "require": {
        "g7mzr/db-php": "*"
    },
    "repositories": [
        {
            "type": "vcs",
            "url":  "git@github.com:g7mzr/db-php.git"
        }
    ]
}
```

### Install Current Development Version
To add the latest development version to your project add the following to your
composer.json file.

```json
{
    "require": {
        "g7mzr/db-php": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url":  "git@github.com:g7mzr/db-php.git"
        }
    ]
}
```

### Update Project Dependencies
Once you have edited your composer.json file user composer to update this package or
all packages

```console
## update only db-php
$ /path/to/composer update g7mzr/db-php

## update all packages
$ /path/to/composer update
```
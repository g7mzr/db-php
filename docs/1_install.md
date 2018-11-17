---
layout: page
title: Install
---
DB is installed using composer.

To load the latest release add the following to your compose.json file

```
    {
        "require": {
            "g7mzr/db": "*"
        },
        "repositories": [
            {
                "type": "vcs",
                "url":  "git@github.com:g7mzr/db.git"
            }
        ]
    }
```
To load the development version add the following to your compose.json file
```
    {
        "require": {
            "g7mzr/db": "dev-master"
        },
        "repositories": [
            {
                "type": "vcs",
                "url":  "git@github.com:g7mzr/db.git"
            }
        ]
    }
```

# Database Interface for PHP Applications

The purpose of this module is to provide database access for my php applications.

It was originally part of WEBTEMPLATE but has been pulled out as a composer module.
It is currently installed via github.

# Installation

To load the latest release add the following to your compose.json file

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

To load the development version add the following to your compose.json file

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

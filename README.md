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
                "url":  "https://github.com/g7mzr/db-php.git"
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
                "url":  "https://github.com/g7mzr/db-php.git"
            }
        ]
    }

# Install Database

This module can now create and modify PostgreSQL databases.  An example schema can be
found at schema_example.json.  Example of how schemas are created and modified can be
found in the *tests\schema.classTest.php* and example for creating and dropping users
and databases can be found in the *tests\sqladmin.classTest.php* file.
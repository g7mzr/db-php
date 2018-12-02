---
layout: page
title: Configure
---
## Introduction
**db-php** can use a mixture of *php* and *json* files.  The PHP Data Source Name (DSN)
is a *php* array and can be stored as a *php* file or *json* file as long as it is
converted to an array before it is passed to **db-php**.  The database schema must be
held in *json* files.  Examples of both types of files are included in the package and
shown below.

## PHP Data Source Name (DSN)

A *php* DSN is shown below.  It uses a *php* array to hold the database connection
information. The "adminuser" and "adminpasswd" items are used when the database is initially
created or updated. They can be the same as "username" and "password" if you have manually
created the user and given them database creation rights.
```php
<?php
/**
 * This file is part of g7mzr/db-php
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$dsn["dbtype"] = "pgsql";
$dsn["hostspec"]  = "";
$dsn["databasename"] = "";
$dsn["username"] = "";
$dsn["password"] = "";
$dsn['adminuser'] = "";  // Only used to create a database user and blank database.
$dsn['adminpasswd'] = "";  // Only used to create a database user and blank database.
```


## Schema File

The example below shows a database schema containing two tables, one called "users2 with
five columns including a primary key and index and one called "items" with 5 columns
including a primary key, foreign key and index.
```json
{
    "version": "1",
    "tables": {
        "users": {
            "columns": {
                "user_id": {"type": "serial", "primary": true},
                "user_name": {"type": "varchar(50)", "unique": true, "notnull": true},
                "realname": {"type": "varchar(100)", "notnull": true},
                "email": {"type": "varchar(255)", "unique": true, "notnull": true},
                "passwd": {"type": "varchar(50)", "notnull": true}
            },
            "index": {
                "users_user_name_idx": {"column": "user_name"}
            }
        },
        "items": {
            "columns": {
                "id": {"type": "serial", "primary": true},
                "name": {"type": "varchar(100)", "notnull": true, "unique": true},
                "description": {"type": "varchar(255)"},
                "price": {"type": "numeric(3,2)", "notnull": true},
                "customer": {"type": "integer"},
                "flag": {"type": "char", "notnull": true, "default": "Y"},
                "date": {"type": "DATETIME"}
            },
            "fk": {
                "fk_items_customer": {"columnname": "customer", "linktable": "users", "linkcolumn": "user_id"}
            },
            "index": {
                "items_name_idx": {"column": "name", "unique": true}
            }
        }
    }
}
```
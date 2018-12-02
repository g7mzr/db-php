---
layout: page
title: Database Manager
---
## Introduction
*DBManager* is the main interface to *db-php* and is the class that your application
should call.  The API for the *DBManager* can be found at [Annex A](07_API_DBManager.md)

See the example code shown below for creating the class in your application:
```php
try {
    $dbmanager = new \g7mzr\db\DBManager($dsn, $dsn["adminuser"], $dsn["adminpasswd"]);
} catch (Exception $ex) {
    echo $ex->getMessage();
    exit(1);
}
```
This example assumes that the RDMS superuser's login details are part of the DSN.  This
need not be the case.  The superuser's login details are only needed to create a database
user for the application and the blank database; they are not needed to run the application
or maintain the database schema.  It is up to you on how the administrator's details are
passed to *DBManager*.

## Set Mode Function
Once you have successfully connected to *DBManager* you then need to select a function.
This is when you connect to the database.  There are three functions you can choose from:
* admin - used to create and delete database users, and databases.
* schema - used to add a schema to a blank database or update an existing one.
* dataaccess - used to insert, update and delete records in the database.

To select the required function issue one of the following commands:
```php
// Admin Function
$result = $dbmanager->setMode('admin');

// Schema Management Function
$result = $dbmanager->setMode('schema');

// Data Access Function
$result = $dbmanager->setMode('dataaccess');
```
If you have successfully connected to the database then ```$result``` will be true.
If the command has failed then result will be an object of type *\g7mzr\db\common\Error*.


## Access function
Once the mode has been set you can access the individual modules as follows:

### Admin
The example below shows the admin module being used to check if a database user exists.
Instructions for using the Admin Driver can be found [here](04_Admin.md).  The Admin
Driver API can be found at [Annex B](08_API_Admin.md).

```php
$result = $dbmanager->getAdminDriver()->userExists("username");
if (\g7mzr\db\common\Common::isError($result)) {

    //Deal with error

}
```

### Schema
The schema module is not accessed directly but via the schema manager.  Instructions
for using the schema Manager can be found [here](05_Schema.md).

### Data
The example below shows the DataDriver being used to obtain the Version of database
being used by the application.Instructions for using the DataDriver can be found
[here](06_DataAccess.md).  The DataDriver API can be found at [Annex D](10_API_DataAccess.md).
```php
$result = $dbmanager->getDataDriver()->getDBVersion();
```
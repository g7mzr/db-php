---
layout: page
title: Admin
---
## Introduction
The purpose of the *Admin* module is to manage the the creation of the database user
and the blank database to be used by the application.

*Admin* can be used to:
* Check if a database user exists.
* Create a new database user.
* Drop an existing database user.
* Check if a database exists.
* Create a new blank database
* Drop an existing database

The API for the **Admin** module can be found at [Annex B](31_API_Admin.md).

## Initialise Admin
The *Admin* interface is initialised as describes in [DBManager](03_DBManager.md)
using the **setMode()** command.
```php
$result = $dbmanager->setMode('admin');
```
## Generic Commands
The *Admin* interface provides the following generic commands.

### getVersion
The **getVersion()** command returns a string containing the DBMS version.  It is
called as follows:
```php
$result = $dbmanager->getAdminDriver()->getDBVersion();
```
In this case $result should be a php string containing the version information of the
connected database. If the command has failed then $result will be an object of type
*\g7mzr\db\common\Error*.

## User Commands
The *Admin* interface provides the following command used to manipulate database
users:

### userExists
The **userExists()** command is used to test if the user that is to own the database
exists or not.  It has the following parameter:
* username:  A string containing the name of database user being checked.

The **userExists()** function is called as follows:
```php
$result = $dbmanager->getAdminDriver()->userExists("username");
```

In this case $result should be **true** if the user exists or **false** if the user does
not exist. If the command has failed then $result will be an object of type
*\g7mzr\db\common\Error*.

### createUser
Users are created using the **createUser()** command.  It has the following parameters:
* username: A string containing the name of the database user to b created
* password: A string containing the new user's password
* unittestdb: A boolean variable which is true if the new user is to be used for testing.

**Note:** If $unittestdb is true then the created user has additional rights which allow
them to create and drop both databases and database users.

The **createUser()** function is called as follows:
```php
// Create a user for testing
$result = $dbmanager->getAdminDriver()->createUser($username, $password, true);

// Create a normal user
$result = $dbmanager->getAdminDriver()->createUser($username, $password);
```

In both cases $result should be **true** if the user is created. If the command has
failed then $result will be an object of type *\g7mzr\db\common\Error*.

### dropUser
The **dropUser()** command is used to delete a database user that is no longer required.
It has the following parameter:
* username A string containing the name of the database user to be dropped.

The **dropUser()** function is called as follows:
```php
$result = $dbmanager->getAdminDriver()->dropUser("username");
```

$result should be **true** if the user is deleted. If the command has
failed then $result will be an object of type *\g7mzr\db\common\Error*.

## Database Commands
The *Admin* interface provides the following command used to manipulate databases:

### databaseExists
The **databaseExists()** command is used to test if a database already exists. It has
the following parameter:
* databasename:  A string containing the name of database being checked.

The **databaseExists()** function is called as follows:
```php
$result = $dbmanager->getAdminDriver()->databaseExists("databasename");
```

In this case $result should be **true** if the database exists or **false** if the
database does not exist. If the command has failed then $result will be an object of type
*\g7mzr\db\common\Error*.

### createDatabase
The **createDatabase()** command is used to create a new database. It has the following
parameters:
* databasename:  A string containing the name of database being created.
* username:      The name of the database owner.

The **createDatabase()** function is called as follows:
```php
$result = $dbmanager->getAdminDriver()->createDatabase("databasename", "username");
```
In this case $result should be **true** if the database is created or exists. If the
command has failed then $result will be an object of type *\g7mzr\db\common\Error*.

### dropDatabase
The **dropDatabase()** command is used to delete an existing database. It has the following
parameter:
* databasename:  A string containing the name of database being deleted.

The **dropDatabase()** function is called as follows:
```php
$result = $dbmanager->getAdminDriver()->dropDatabase("databasename");
```

In this case $result should be **true** if the database is deleted. If the command has
failed then $result will be an object of type *\g7mzr\db\common\Error*.

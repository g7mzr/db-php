---
layout: page
title: Annex A - Database Manager API
---
## Constructor
```php
/**
 * Database Manager Class Constructor
 *
 * Sets up the Database Manager Class
 *
 * @param array   $dsn          array containing the database connection details.
 * @param string  $adminuser    String containing the db adminuser name
 * @param string  $admipassword String containing the db adminuser password
 * @param boolean $persistent   Set true for persistent connection to database
 *
 * @access public
 */
public function __construct($dsn, $adminuser, $adminpasswd, $persistent = false)
```

## setMode
```php
/**
 * Database Manager setMode
 *
 * This function connects the dbManager to the correct function and database
 * driver.  The functions it can chose are "admin", "schema" or "dataaccess.
 * Any other options will throw an error
 *
 * @param string $function The function that is to be used.
 *
 * @return true If the selected function is enabled db error other wise
 */
public function setMode($function)
```

## getAdminDriver
```php
/**
 * Database Manager getAdminDriver
 *
 * This function returns the admin Driver pointer
 *
 * @return \g7mzr\db\interfaces\InterfaceDatabaseAdmin  Admin Driver
 */
public function getAdminDriver()
```

## getSchemaDriver
```php
/**
 * Database Manager getAdminDriver
 *
 * This function returns the schema Driver pointer
 *
 * @return \g7mzr\db\interfaces\InterfaceDatabaseSchema Schema Driver
 */
public function getSchemaDriver()
```

## getDataDriver
```php
/**
 * Database Manager getAdminDriver
 *
 * This function returns the schema Driver pointer
 *
 * @return \g7mzr\db\interfaces\InterfaceDatabaseDriver
 */
public function getDataDriver()
```

## errorMessage
```php
/**
 * Return a textual error message for a MDB2 error code
 *
 * @param int $value integer error code,
 *
 * @return string error message, or false if the error code was not recognised
 *
 * @access public
 */
public function errorMessage($value)
```
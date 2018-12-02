---
layout: page
title: Annex B - Admin Manager API
---
## getDBVersion
```php
/**
 * Function to get the database version
 *
 * This function gets the version of database currently being used.
 *
 * @return string database Version
 *
 * @access public
 */
public function getDBVersion();
```

## userExists
```php
/**
 * Function to check if a Database User Exist
 *
 * @param string $username The name of the database user
 *
 * @return boolean true if user exists, False if, or DB Error
 *
 * @access public
 */
public function userExists($username);
```

## createUser
```php
/**
 * Function to create the database user for the application
 *
 * @param string $username   The name of the database user
 * @param string $password   The password for the database user
 * @param string $unittestdb True if this is a test system
 *
 * @return boolean true if user Created or exists WEBTEMPLATE Error otherwise
 *
 * @access public
 */
public function createUser($username, $password, $unittestdb = false);
```

## dropUser
```php
/**
 * Function to drop the database user for the application
 *
 * @param string $username The name of the database user
 *
 * @return boolean true if user dropped WEBTEMPLATE Error otherwise
 *
 * @access public
 */
public function dropUser($username);
```

## databaseExists
```php
/**
 * Function to drop the database for the application
 *
 * @param string $database The name of the database
 *
 * @return boolean true if database exists WEBTEMPLATE Error otherwise
 *
 * @access public
 */
public function databaseExists($database);
```

## createDatabase
```php
/**
 * Function to create the database for the application
 *
 * @param string $database The name of the database
 * @param string $username The name of the database user
 *
 * @return boolean true if database Created or exists WEBTEMPLATE Error otherwise
 *
 * @access public
 */
public function createDatabase($database, $username);
```

## dropDatabase
```php
/**
 * Function to drop the database for the application
 *
 * @param string $database The name of the database
 *
 * @return boolean true if database Created or exists WEBTEMPLATE Error otherwise
 *
 * @access public
 */
public function dropDatabase($database);
```
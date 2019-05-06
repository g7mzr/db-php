---
layout: page
title: Annex C - Schema Manager API
---
## Constructor
```php
/**
 * Schema Class Constructor
 *
 * This class load and processed the Database Schema stores in json configuration
 * files.  It will either create a new schema or update and existing one.
 *
 * @param \g7mzr\db\DBManager $dbManager Pointer to Database Manager Class
 *
 * @access public
 */
public function __construct($dbManager)
```
## autoSchemaManagement
```php
/**
 * Automatic Schema Change
 *
 * This function automatically installs or updates a Schema using the main class
 * functions.  It has been added to simplify schema management
 *
 * @param string  $filename   The fully qualified filename for the schema file
 * @param boolean $newinstall If true this is new install
 * @param string  $tablename  The name of the Schema Table
 *
 * @return mixed True if the schema has been loaded okay.  DB Error otherwise
 *
 * @access public
 */
public function autoSchemaManagement($filename, $newinstall = false, $tablename = "schema")
```

## loadNewSchema
```php
/**
 * Load new Schema
 *
 * Load the new schema from the file system
 *
 * @param string $filename The fully qualified filename for the schema file
 *
 * @return boolean True if the schema has been loaded okay.  DB Error otherwise
 *
 * @access public
 */
public function loadNewSchema($filename)
```

## processNewSchema
```php
/**
 * Process new Schema
 *
 * This function processes the schema for a new database
 *
 * @return mixed True if schema processed okay.  DB Error other wise
 *
 * @access public
 */
public function processNewSchema()
```

## getNewSchemaVersion
```php
/*
 * Function to obtain the new Schema Version Number
 *
 * This function returns the new schema version number
 *
 * @return integer The new Schema Version Number
 *
 * @access public
 */
public function getNewSchemaVersion()
```

## getNewSchema
```php
/**
 * Function to return the new Schema array
 *
 * This function returns the new schema as a PHP Array
 *
 * @return array The New Database Schema
 *
 * @access public
 */
public function getNewSchema()
```

## processSchemaUpdate
```php
/**
 * Update Schema
 *
 * This function processes the schema update for an existing database
 *
 * @return mixed True if schema processed okay.  DB Error other wise
 *
 * @access public
 */
public function processSchemaUpdate()
```

## saveSchema
```php
/**
 * Function to save the New SCHEMA into the database
 *
 * @param string $tablename The name of the Schema Table
 *
 * @return True if schema processed okay.  DB Error other wise
 *
 * @access public
 */
public function saveSchema($tablename = "schema")
```

## getSchema
```php
/**
 * Function to get the New SCHEMA from the database
 *
 * @param string $tablename The name of the Schema Table
 *
 * @return True if schema processed okay.  DB Error other wise
 *
 * @access public
 */
public function getSchema($tablename = "schema")
```

## getcurrentSchema
```php
/**
 * Function to return the current Schema array
 *
 * This function returns the current schema as a PHP Array
 *
 * @return array The Current Database Schema
 *
 * @access public
 */
public function getcurrentSchema()
```

## schemaChanged
```php
/**
 * Function the check if Schema Versions are different
 *
 * @return boolean True if Schema Versions are different
 *
 * @access public
 */
public function schemaChanged()
```

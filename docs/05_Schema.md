---
layout: page
title: Schema Management
---
## Introduction
The **Schema Manager** Class is used to CREATE, UPDATE and if necessary DELETE your applications
database.  It has functions available so that you can make control how the changes are
made or a function to automate most of the process for you.  The documentation covers both
methods.

New Schema are held in **JSON** files at a location of your choosing while the installed
schema is stored in the database as a **serialised array**.  The default table name used to
store the schema is *schema*.  This can be changed when creating your database structure
and by including the new table name in the parameter list of **Schema Manager** functions.

**Schema Manager** requires [DBManager](03_DBManager.md) to access the database.

The API for the **Schema Manager** can be found at [Annex C](32_API_SchemaManager.md).

The API for the **Schema Driver** module which is used by **Schema Manager** can be found at
[Annex E](34_API_SchemaDriver.md).

## Create New Schema
The following steps, assuming that a database user exists, are required to create a new database:
1. Create blank database using [createDatabase()](04_Admin.md#createdatabase) function
in the *Admin* Module.
2. Load the new *schema* from the **JSON** file.
3. Process the *schema* using **processNewSchema**.

The *php* commands to add a schema to a blank database are shown below:
```php
// Create the Schema Manager using a previously initiated DBManager.
try {
    $schemaManager = new \g7mzr\db\SchemaManager($dbmanager);
} catch (throwable $e) {
    // Deal with error
}

// Load the new schema from the file system
$loadresult = $schemaManager->loadNewSchema('path\to\schema\file');
if (\g7mzr\db\common\Common::isError($loadresult)) {
    //Deal with error
}

// Process the new schema
$result = $schemaManger->processNewSchema();
if (\g7mzr\db\common\Common::isError($result)) {
    //Deal with error
}
```

## Update Existing Schema
The following steps are used to update an exiting database.  It assumes that a schema
already exists and the definition is stored in a table called *schema*.
1. Load the new *schema* from the **JSON** file.
2. Load the existing schema from the database.
3. Check if the schema has changed.
4. If the schema has changed update it using **processSchemaUpdate**.

The *php* commands to udate an existing schema are shown below:
```php
// Create the Schema Manager using a previously initiated DBManager.
try {
    $schemaManager = new \g7mzr\db\SchemaManager($dbmanager);
} catch (throwable $e) {
    // Deal with error
}

// Load the new schema from the file system
$loadresult = $schemaManager->loadNewSchema('path\to\schema\file');
if (\g7mzr\db\common\Common::isError($loadresult)) {
    //Deal with error
}

// Load the existing schema from the database
$gotexistingschema = $schemaManager->getSchema();
if (\g7mzr\db\common\Common::isError($gotexistingschema)) {
    //Deal with error
}

// Check for changes
if ($schemaManager->schemaChanged() === false) {
    return false;
}

//  Process the schema changed
$schemaupdated = $schemaManger->processSchemaUpdate();
if (\g7mzr\db\common\Common::isError($schemaupdated)) {
    //Deal with error
}

// Save the new schema to the database
$saveresult = $schemaManager->saveSchema();
if (\g7mzr\db\common\Common::isError($saveresult)) {
    //Deal with error
}
```

## Automatic Schema Management
In order to save users of **db-php** writing significant lines of code to create and
update schema the *schemaManager* of **db-php** has a function which runs the appropriate
code to either create or update a schema.

The **autoSchemaUpdate** function takes 3 parameters
1. $filename: The name of the json schema file.
2. $newinstall: If true the commands on [Create New Schema](#create-new-schema) are run.  The
default value is false which runs the [Update Existing Schema](#update-existing-schema) commands.
3. $tablename: The name of the table where the schema is saved.  It defaults to *schema*.

The *php* commands to create or udate an existing schema are shown below:
```php
// Create the Schema Manager using a previously initiated DBManager.
try {
    $schemaManager = new \g7mzr\db\SchemaManager($dbmanager);
} catch (throwable $e) {
    // Deal with error
}

// New Install
$result = $schemaManager->autoSchemaManagement('\path\to\schema\file', true);
if (\g7mzr\db\common\Common::isError($result)) {
    //Deal with error
}

// Update with filename argument only
$result = $schemaManager->autoSchemaManagement('\path\to\schema\file');
if (\g7mzr\db\common\Common::isError($result)) {
    //Deal with error
}
```
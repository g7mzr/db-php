---
layout: page
title: Data Access
---
## Introduction
The **Data Access** class is used to manipulate data stored in the database.  It can be used
to INSERT, SELECT, UPDATE and DELETE one or more records in a table.  It has the ability
to use TRANSACTIONS to ensure that the probability of data corruption is kept as low as
possible.

**Data Access** requires access to [DBManager](03_DBManager.md) to access the database.

The API for the **Data Access** can be found at [Annex D](33_API_DataAccess.md).

## Generic Commands
The *Data Access* interface provides the following generic commands.

### getVersion
The **getVersion()** command returns a string containing the DBMS version.  It is
called as follows:
```php
$result = $dbmanager->getDataDriver()->getDBVersion();
```
In this case $result should be a php string containing the version information of the
connected database. If the command has failed then $result will be an object of type
*\g7mzr\db\common\Error*.

### startTransaction
The **startTransaction()** command initiates a database transaction.
```php
$result = $dbmanager->getDataDriver()->startTransaction();
```
$result is true if the transaction is started, false otherwise.

### endTransaction
The **endTransaction()** command will either, depending on the value of the *$commit* variable,
commit *(true)* or rollback *(false)* the database commands issued since the last
**startTransaction()** command.
```php
$commit = true; // To commit the changes to the database
$commit = false; // To discard the changes.
$result = $dbmanager->getDataDriver()->endTransaction($commit);
```
$result is true if the end transaction command runs successfully, false otherwise.

### rowCount
The **rowCount()** command returns the number rows affected by the last command.
```php
$rows = $dbmanager->getDataDriver()->rowCount();
```
$rows is an integer containg the number of rows affected by the last command.  It is zero if
no rows are affected.

### disconnect
The **disconnect()** function disconnects the script from the database by destroying the
PDOStatement object and PDO object.
```php
$dbManager->getDataDriver()->disconnect();
```
The **disconnect()** command always returns true.

## Insert Commands
The *Data Access* interface provides the following commands when inserting information
into the database.

### dbinsert
The **dbinsert()** command is used to add new records to the database.  It takes 2 parameters:
1. $tableName:  A string containing the name of the table in the database the new record is to be inserted into.
2. $insertData: An array containing the data for the new record.  The array elements are
structured as *"fieldname" => "data"*.

```php
$tableName = "table";
$insertData = array (
    "field1" => "data",
    "field2" => "data",
    "field3" => "data",
    "field4" => "data"
);

$result = $dbManager->getDataDriver()->dbinsert($tableName, $insertData);
```

$result is true if the record is inserted into the database.  If the command has failed
then $result will be an object of type *\g7mzr\db\common\Error*.

### dbinsertid
The **dbinsertid** returns the record number of the last record inserted by searching for
information unique to that record.  It takes 4 parameters all of which are strings:
1. $tablename: The name of the table the record was inserted into.
2. $idfield: The name of the field in the $tablename which holds the unique id number
3. $srchfield: The name of the field to be searched.
4. $srchdata: The data to be searched for.  It should be unique.

```php
$tablename = "tablename";
$idfield = "idfield";
$srchfield = "srchfieldname";
$srchdata = "SearchData";

$result = $dbManager->getDataDriver()->dbinsertid($tableName, $idfield, $srchfield, $srchdata);
```

If the command is successful then $result contains the record number.  If the command has failed
then $result will be an object of type *\g7mzr\db\common\Error*.

## Update Commands
The *Data Access* interface provides the following commands to update information
that is stored in the database.

### dbupdate
The **dbupdate()** command is used to update the information stored in existing database
records.  It takes 3 parameters:
1. $tableName:  A string containing the name of the table in the database the new record is to be inserted into.
2. $insertData: An array containing the data for the new record.  The array elements are
structured as *"fieldname" => "data"*.
3. $searchdata:  An array containing the information needed to identify the record to be updated.
The array elements are structured as *"fieldname" => "data"*.

```php
$tableName = "table";
$insertData = array (
    "field1" => "data",
    "field2" => "data",
    "field3" => "data",
    "field4" => "data"
);
$searchdata = array("searchfield" => "searchdata");

$result = $dbManager->getDataDriver()->dbupdate($tableName, $insertData, $searchdata);
```

$result is true if the record is updated.  If the command has failed then $result will
be an object of type *\g7mzr\db\common\Error*.

## Select Commands
The *Data Access* interface provides the following commands to retrieve information
that is stored in the database.

### dbselectsingle
The **dbselectsingle()** command is used to select one record from the database.  It takes 3
parameters:
1. $tableName:  A string containing the name of the table in the database the new record is to be inserted into.
2. $fieldNames: An array containing the list of fields to be returned by the search.
3. $searchdata:  An array containing the information needed to identify the record to be returned.
The array elements are structured as *"fieldname" => "data"*.

```php
$tablename = "table";
$fieldNames = array("field1", "field2", "field3" ...);
$searchdata = array("searchfield" => "searchdata");

$result = $dbManager->getDataDriver()->dbselectsingle($tableName, $fieldNames, $searchdata);
```

If the search was successful then $result will be an associated array containing the record.
If the search has failed then $result will be an object of type *\g7mzr\db\common\Error* which will
either indicate no records were found or an error occurred.

### dbselectmultiple
The **dbselectmultiple()** command is used to select one or more records from the database.
It takes 5 parameters:
1. $tableName:  A string containing the name of the table in the database the new record is to be inserted into.
2. $fieldNames: An array containing the list of fields to be returned by the search.
3. $searchdata:  An array containing the information needed to identify the record to be returned.
The array elements are structured as *"fieldname" => "data"*.
4. $order: A string containing the name of the field the search results have to sorted by.
5. $join: An array containing the name of the second table and fields to make the join with.

```php
$tablename = "table1name";
$fieldNames = array("field1", "field2", "field3" ...);
$searchdata = array("searchfield" => "searchdata");
$order = "field1";
$join = array (
    'table2' => 'table2name',
    'field1' => 'table1name.joinfieldname',
    'field2' => 'table2name.joinfieldname'
);

$result = $dbManager->getDataDriver()->dbselectmultiple(
    $tableName,
    $fieldNames,
    searchdata,
    $order,
    $join
);
```

If the search was successful then $result will be an multi-dimensional array containing the one or more records.
If the search has failed then $result will be an object of type *\g7mzr\db\common\Error* which will
either indicate no records were found or an error occurred.

## Delete Commands
The *Data Access* interface provides the following commands to delete information
that is stored in the database.

### dbdelete
The **dbdelete()** command is used to delete single records from the database.  It takes
2 parameters:
1. $tableName:  A string containing the name of the table in the database the new record is to be inserted into.
2. $deletedata:  An array containing the information needed to identify the record to be returned.
The array elements are structured as *"fieldname" => "data"*.

```php
$tablename = "table";
$deletedata = array('fieldname' => 'data');

$result = $dbManager->getDataDriver()->dbdelete($tablename, $deletedata);
```

If the delete command was successful then $result will be boolean true.  If the delete
command has failed then $result will be an object of type *\g7mzr\db\common\Error*

### dbdeletemultiple
The **dbdeletemultiple()** command is used to delete single records from the database.  It takes
2 parameters:
1. $tableName:  A string containing the name of the table in the database the new record is to be inserted into.
2. $deletedata:  An multi dimensional array containing the information needed to identify the record to be returned.
The array elements are structured as *"fieldname" => array("type" => "=", "data" => "data"*.

```php
    $tablename = "table";
    $deletedata = array();
    $deletedata["fieldname"] = array("type" => "=", "data" => 'data');

    $result = $dbManager->getDataDriver()->dbdeletemultiple($tablename, $deletedata);
```

If the delete command was successful then $result will be boolean true.  If the delete
command has failed then $result will be an object of type *\g7mzr\db\common\Error*

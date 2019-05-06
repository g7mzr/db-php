---
layout: page
title: Common Code
---
## Introduction
The purpose of the **Common Code** module is to provide functions which are used by all
modules in *db-php* and are also required by the application using it.  Currently it contains
two functions.

The API for the **Common Code** module can be found at [Annex F](35_API_Common.md).

## isError
The function **isError()** is used to check if the returned value from a *db-php* function is
of type *\g7mzr\db\common\Error* which indicates that the called function encountered an error of
some type.  Function **isError()** has one parameter:
1. $data: The result of a *db-php* command that needs to be checked

```php
$result = $dbmanager->getAdminDriver()->userExists("username");

// Check if $result is an error object.
if (\g7mzr\db\common\Common::isError($result)) {
    //Deal with error
}
```

The function *\g7mzr\db\common\Common::isError($data)* returns true if $data is an error
object of type *\g7mzr\db\common\Error* and false if it is not.

## raiseError
The purpose of the **raiseError** function is to create an object of type *\g7mzr\db\common\Error*.
It has 3 parameters, all of which are optional, are:
1. $message:  A string containing the message that needs to get passed back to the user
and logged if necessary.
2. $code: An integer containing an error code.  This can also be used to match to an error message.
3. $dbmessage:  A string containing the error message from the PDO or PDOStatement.  This is
used for debugging.

```php
// An error condition has been encountered
$message = "This is an error Message";
$code = -1;

// Return the error object
return raiseError($message, $code);
```
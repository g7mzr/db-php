---
layout: page
title: Annex F - Common API
---
## Notes
This is a static class.  Applications should use **isError** to check
that no errors were encountered when accessing the database which are ceated by
**raiseError**.

## isError
```php
/**
 * This method is used to check if the supplied variable is an DB error type.
 *
 * This method is used to check if the variable $data is of type
 * \g7mzr\db\common\Error which is the DB error object.  It will return true if
 * $data is of type \g7mzr\db\common\Error.
 *
 * @param mixed $data The value to test
 *
 * @return boolean True if $data is an error object
 *
 * @access public
 */
public static function isError($data)
```

## raiseError
```php
/**
 * This method is used to create an error object of type \g7mzr\db\common\Error.
 *
 * This function is used to create an object of type \g7mzr\db\common\Error which
 * is a error object for the DB access module.
 *
 * @param string  $message   a text message or error object
 * @param integer $code      The error code
 * @param array  $dbmessage The error message from the PDO Driver.
 *
 * @return object A DB Access Module Error object
 *
 * @access public
 */
public static function raiseError($message, $code = 0, $dbmessage = array())
```
<?php
/**
 * This file is part of g7mzr\db.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace g7mzr\db;

/**
 * The list below contains the error codes for the DB Module
 *
 * If you add a code here make sure you add it to the textual version
 * in DB::errorMessage()
 */

/**
 * No Error
 */
define('DB_OK', true);

/**
 * Unspecified error
 */
define('DB_ERROR', -1);

/**
 * Search Parameters not found
 */
define('DB_ERROR_NOT_FOUND', -2);

/**
 * User or User:Password not found in database.
 */
define('DB_USER_NOT_FOUND', -3);

/**
 * Unable to connect to the database
 */
define('DB_CANNOT_CONNECT', -4);

/**
 * Error running DB Query
 */
define('DB_ERROR_QUERY', -5);

/**
 * Error Entering a Transaction
 */
define('DB_ERROR_TRANSACTION', -6);

/**
 * Error Saving Data
 */
define('DB_ERROR_SAVE', -7);

/**
 * Error Not Implemented
 */
define('DB_NOT_IMPLEMENTED', -8);

/**
 * DB Class is a static used to load the DB abstraction layer
 *
 * @category Webtemplate
 * @package  Database
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
**/

class DB
{
    /**
     * Checks if a driver exists without triggering __autoload
     *
     * @param string $drivername Name of Database driver psql, mysql
     * @param string $phpversion Minimum version of PHP to use
     *
     * @return bool true success and false on error
     * @static
     * @access public
     */
    public function driverExists($drivername, $phpversion = "5.0.0")
    {
        if (version_compare(phpversion(), $phpversion, "<=")) {
            return false;
        }
        $fileName = dirname(__FILE__). '/drivers/' . $drivername . '.php';
        return file_exists($fileName);
    }


    /**
     * Loads the Database Driver module
     *
     * @param string $drivername to load
     *
     * @return mixed true success or g7mzr\db\common\Error on failure
     *
     * @access public
     */
    public function loaddriver($drivername)
    {
        $fileName = dirname(__FILE__) . '/drivers/' . $drivername . '.php';
        $include =  @include_once $fileName ;
        if (!$include) {
            $msg = gettext('Unable to load database driver') . " " .$drivername;
            $err = \g7mzr\db\common\Common::raiseError(
                $msg,
                DB_ERROR_NOT_FOUND
            );
            return $err;
        }
        return DB_OK;
    }

    /**
     * Load the database driver
     *
     * @param array   $dsn        Data Source Name
     * @param boolean $persistent Set true for persistent connection to database
     *
     * @return mixed a newly created PDO object, or false on error
     *
     * @access public
     */
    public function load($dsn, $persistent = false)
    {
        if (empty($dsn['dbtype'])) {
            $msg = gettext("No RDBMS driver specified");
            $err = \g7mzr\db\common\Common::raiseError(
                $msg,
                DB_ERROR_NOT_FOUND
            );
            return $err;
        }
        $driver = $dsn['dbtype'];
        $className = '\g7mzr\db\drivers\DatabaseDriver'.strtolower($driver);
        $err = DB::loaddriver($driver);
        if (\g7mzr\db\common\Common::isError($err)) {
            return $err;
        }
        try {
            $dbDriver = new $className($dsn, $persistent);
            return $dbDriver;
        } catch (\Throwable $e) {
            $err = \g7mzr\db\common\Common::raiseError(
                $e->getMessage(),
                DB_CANNOT_CONNECT,
                $e->getDBMessage()
            );
            return $err;
        }
    }

    /**
     * Return a textual error message for a MDB2 error code
     *
     * @param int $value integer error code,
     *
     * @return string error message, or false if the error code was
     *                  not recognised
     *
     * @access public
     */
    public function errorMessage($value)
    {
        $errorMessages = array(
            DB_OK                => "no error",
            DB_ERROR             => "unknown error",
            DB_ERROR_NOT_FOUND   => "not found",
            DB_USER_NOT_FOUND    => "user not found",
            DB_CANNOT_CONNECT    => "unable to connect to the database",
            DB_ERROR_QUERY       => "sql query failed",
            DB_ERROR_TRANSACTION => "Transaction Error",
            DB_ERROR_SAVE        => "unable to save data",
            DB_NOT_IMPLEMENTED   => "function not implemented"
        );
        return isset($errorMessages[$value]) ?
           $errorMessages[$value] : $errorMessages[DB_ERROR];
    }
}

<?php
/**
 * This file is part of PHP_Database_Client.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package db-php
 * @subpackage Access Module
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/db-php/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\db;

use \g7mzr\db\common\Common;

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
 * dbManger Class is the class for managing RDMS users, databases and schema.  It is
 * also used to access the database for manipulating data stored in it.
 **/
class DBManager
{
    /**
     * Property:Database PDO Data Source Name
     *
     * @var    array
     * @access protected
     */
    protected $dsn  = array();

    /**
     * Property:Admin User's Name
     *
     * @var    string
     * @access protected
     */
    protected $adminuser = '';

    /**
     * Property:Admin User's Password
     *
     * @var    string
     * @access protected
     */
    protected $adminpasswd = '';

    /**
     * Property:persistent connection to database
     *
     * @var    boolean
     * @access protected
     */
    protected $persistent = false;

    /**
     * Property: Admin Driver
     *
     * @var \g7mzr\db\interfaces\InterfaceDatabaseAdmin
     * @access protected
     */
    protected $admindriver;

    /**
     * Property: SchemaDriver
     *
     * @var \g7mzr\db\interfaces\InterfaceDatabaseSchema
     * @access protected
     */
    protected $schemadriver;

    /**
     * Property: DataDriver
     *
     * @var \g7mzr\db\interfaves\InterfaceDatabaseDriver
     * @access protected
     */
    protected $datadriver;

    /**
     * Database Manager Class Constructor
     *
     * Sets up the Database Manager Class
     *
     * @param array   $dsn         Array containing the database connection details.
     * @param string  $adminuser   String containing the db adminuser name.
     * @param string  $adminpasswd String containing the db adminuser password.
     * @param boolean $persistent  Set true for persistent connection to database.
     *
     * @access public
     */
    public function __construct(
        array $dsn,
        string $adminuser,
        string $adminpasswd,
        bool $persistent = false
    ) {
        $this->dsn = $dsn;
        $this->adminuser = $adminuser;
        $this->adminpasswd = $adminpasswd;
        $this->persistent = $persistent;
        $this->admindriver = null;
        $this->schemadriver = null;
        $this->datadriver = null;
    }

    /**
     * Destroy the DB Manager Class
     *
     */
    public function __destruct()
    {
        $this->dsn = array();
        $this->adminuser = '';
        $this->adminpasswd = '';
        $this->persistent = false;
        $this->admindriver = null;
        $this->schemadriver = null;
        $this->datadriver = null;
    }

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
    public function setMode(string $function)
    {
        // Set up flag for function selected;
        $functionselected = true;

        // Reset the database access variables
        $this->admindriver = null;
        $this->schemadriver = null;
        $this->datadriver = null;
        $driverError = "";

        $errorMsg = gettext("Invalid Database Manager function selected");

        if ($function == "admin") {
            $dsn["dbtype"] = $this->dsn['dbtype'];
            $dsn["hostspec"]  = $this->dsn['hostspec'];
            $dsn["databasename"] = "template1";
            $dsn["username"] = $this->adminuser;
            $dsn["password"] = $this->adminpasswd;
            $classname = '\g7mzr\\db\\drivers\\'. strtolower($dsn['dbtype']) . '\Admin';
            if (class_exists($classname)) {
                try {
                    $this->admindriver = new $classname(
                        $dsn,
                        $this->persistent
                    );
                } catch (\Throwable $ex) {
                    $errorMsg = gettext(
                        "Admin: Unable to connect to database as administrator"
                    );
                    $functionselected = false;
                    $this->admindriver = null;
                    $driverError = $ex->getMessage();
                }
            } else {
                $errorMsg = gettext(
                    "Admin: Unable to connect to database as administrator"
                );
                $functionselected = false;
                $this->admindriver = null;
            }
        } elseif ($function == "schema") {
            $classname = 'g7mzr\\db\\drivers\\'. strtolower($this->dsn['dbtype']) . '\Schema';
            if (class_exists($classname)) {
                try {
                    $this->schemadriver = new $classname(
                        $this->dsn,
                        $this->persistent
                     );
                } catch (\Throwable $ex) {
                    $errorMsg = gettext(
                        "Admin: Unable to connect to database as administrator"
                    );
                    $functionselected = false;
                    $this->schemadriver = null;
                    $driverError = $ex->getMessage();
                }
            } else {
                $errorMsg = gettext(
                    "Schema: Unable to connect to database as administrator"
                );
                $functionselected = false;
                $this->admindriver = null;
            }
        } elseif ($function == "datadriver") {
            $classname = 'g7mzr\\db\\drivers\\'. strtolower($this->dsn['dbtype']) . '\DatabaseDriver';
            if (class_exists($classname)) {
                try {
                    $this->datadriver = new $classname(
                        $this->dsn,
                        $this->persistent
                     );
                } catch (\Throwable $ex) {
                    $errorMsg = gettext(
                        "Admin: Unable to connect to database for data access"
                    );
                    $functionselected = false;
                    $this->datadriver = null;
                    $driverError = $ex->getMessage();
                }
            } else {
                $errorMsg = gettext(
                    "Admin: Unable to connect to database for data access"
                );
                $functionselected = false;
                $this->datadriver = null;
            }
        } else {
            $errorMsg = gettext("Invalid Database Manager function selected");
            $functionselected = false;
        }

        if ($functionselected === true) {
            return true;
        } else {
            return Common::raiseError($errorMsg, 1, array("ErrMsg" => $driverError));
        }
    }

    /**
     * Database Manager getAdminDriver
     *
     * This function returns the admin Driver pointer
     *
     * @throws \g7mzr\db\common\DBException If the Admin driver is not initialised.
     *
     * @return \g7mzr\db\interfaces\InterfaceDatabaseAdmin  Admin Driver
     */
    public function getAdminDriver()
    {
        if ($this->admindriver === null) {
            throw new \g7mzr\db\common\DBException(
                "Admin Driver not initalised",
                DB_ERROR
            );
        }
        return $this->admindriver;
    }

    /**
     * Database Manager getAdminDriver
     *
     * This function returns the schema Driver pointer
     *
     * @throws \g7mzr\db\common\DBException If the schema driver is not initialised.
     *
     * @return \g7mzr\db\interfaces\InterfaceDatabaseSchema Schema Driver
     */
    public function getSchemaDriver()
    {
        if ($this->schemadriver === null) {
            throw new \g7mzr\db\common\DBException(
                "Schema Driver not initalised",
                DB_ERROR
            );
        }
        return $this->schemadriver;
    }

    /**
     * Database Manager getAdminDriver
     *
     * This function returns the schema Driver pointer
     *
     * @throws \g7mzr\db\common\DBException If the Data driver is not initialised.
     *
     * @return \g7mzr\db\interfaces\InterfaceDatabaseDriver
     */
    public function getDataDriver()
    {
        if ($this->datadriver === null) {
            throw new \g7mzr\db\common\DBException(
                "Data Driver not initalised",
                DB_ERROR
            );
        }
        return $this->datadriver;
    }

    /**
     * Return a textual error message for a MDB2 error code
     *
     * @param integer $value Integer error code.
     *
     * @return string error message, or false if the error code was
     *                  not recognised
     *
     * @access public
     */
    public function errorMessage(int $value)
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

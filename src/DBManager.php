<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace g7mzr\db;

require_once __DIR__ . '/common/errorCodes.php';
/**
 * dbManger Class is the class for accessing for managing RDMS users, databases and
 * schemas
 *
 * @category Webtemplate
 * @package  Database
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
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
     * @var \g7mzr\db\manager\InterfaceDatabaseAdmin
     * @access protected
     */
    protected $admindriver;

    /**
     * Property: SchemaDriver
     *
     * @var \g7mzr\db\manager\InterfaceDatabaseSchema
     * @access protected
     */
    protected $schemadriver;

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
    {
        $this->dsn = $dsn;
        $this->adminuser = $adminuser;
        $this->adminpasswd = $adminpasswd;
        $this->persistent = $persistent;
        $this->admindriver = null;
        $this->schemadriver = null;
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
    }

    /**
     * Database Manager setMode
     *
     * This function connects the dbManager to the correct function and database
     * driver.  The functions it can chose are "admin" or "schema".  Any other
     * options will throw an error
     *
     * @param string $function The function that is to be used.
     *
     * @return true If the selected function is enabled db error other wise
     */
    public function setMode($function)
    {
        // Set up flag for function selected;
        $functionselected = true;

        // Reset the database access variables
        $this->admindriver = null;
        $this->schemadriver = null;
        $driverError = "";

        $errorMsg = gettext("Invalid Database Manager function selected");
        if ($function == "admin") {
            $dsn["dbtype"] = $this->dsn['dbtype'];
            $dsn["hostspec"]  = $this->dsn['hostspec'];
            $dsn["databasename"] = "template1";
            $dsn["username"] = $this->adminuser;
            $dsn["password"] = $this->adminpasswd;
            $classname = 'g7mzr\\db\\manager\\'. strtoupper($dsn['dbtype']) . 'Admin';
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
            $classname = 'g7mzr\\db\\manager\\'. strtoupper($this->dsn['dbtype']) . 'Schema';
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
        } else {
            $errorMsg = gettext("Invalid Database Manager function selected");
            $functionselected = false;
        }

        if ($functionselected === true) {
            return true;
        } else {
            return \g7mzr\db\common\Common::raiseError($errorMsg, 1, $driverError);
        }
    }

    /**
     * Database Manager getAdminDriver
     *
     * This function returns the admin Driver pointer
     *
     * @return \g7mzr\db\manager\InterfaceDatabaseAdmin  Admin Driver
     */
    public function getAdminDriver()
    {
        return $this->admindriver;
    }

    /**
     * Database Manager getAdminDriver
     *
     * This function returns the sechema Driver pointer
     *
     * @return \g7mzr\db\manager\InterfaceDatabaseSchema Schema Driver
     */
    public function getSchemaDriver()
    {
        return $this->schemadriver;
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

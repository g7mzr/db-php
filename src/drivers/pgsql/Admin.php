<?php
/**
 * This file is part of PHP_Database_Client.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package db-php
 * @subpackage Drivers
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/db-php/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\db\drivers\pgsql;

use g7mzr\db\interfaces\InterfaceDatabaseAdmin;

/**
 * Admin Class is the class for the pgsql database drivers.  It implements
 * the Admin interface to provide access to the PGSQL database via PDO
 **/
class Admin implements InterfaceDatabaseAdmin
{
    /**
     * Database MDB2 Data Source Name
     *
     * @var    array
     * @access protected
     */
    protected $dsn  = array();

    /**
     * An associative array of MDB2 option names and their values.
     *
     * @var    array
     * @access protected
     */
    protected $dsnOptions = array();

    /**
     * A PDO Database Object.
     *
     * @var    \PDO
     * @access protected
     */
    protected $pdo;

    /**
     * PDO Statement object.  USed when preparing SQL scripts
     *
     * @var    \STMP
     * @access protected
     */
    protected $stmt;

    /**
     *  SQL text variable used when preparing SQL scripts
     *
     * @var    string
     * @access protected
     */
    protected $sql;

    /**
     * property: rowcount
     * @var integer
     * @access protected
     */
    protected $rowcount = 0;

    /**
     * PGSQL Driver Class Constructor
     *
     * Sets up the PGSQL Driver dsn from the calling function
     * and any PDO specific options.
     *
     * @param array   $dsn        An array containing the database connection details.
     * @param boolean $persistent Set true for persistent connection to database.
     *
     * @throws \g7mzr\db\common\DBException If unable to connect to the database.
     *
     * @access public
     */
    public function __construct(array $dsn, bool $persistent = false)
    {
        $conStr = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s",
            $dsn["hostspec"],
            '5432',
            $dsn["databasename"]
        );

        // Create the PDO object and Connect to the database
        try {
            $this->pdo = new \PDO(
                $conStr,
                $dsn["username"],
                $dsn["password"],
                array(\PDO::ATTR_PERSISTENT => $persistent)
            );
        } catch (\Exception $e) {
            throw new \g7mzr\db\common\DBException(
                'Unable to connect to the database',
                1,
                $e->getMessage()
            );
        }

        //$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
    } // end constructor


    /**
     * DB Driver Destructor
     *
     * Disconnect the MDB2 data object from the database
     */
    public function __destruct()
    {
        $this->stmt = null;
        $this->pdo = null;
    }


    /**
     **************************************************************************
     **********  THIS SECTION OF THE FILE CONTAINS GENERAL FUNCTIONS  *********
     **************************************************************************
     */

    /**
     * Function to get the database version
     *
     * This function starts a Database Transaction
     *
     * @return string database Version
     *
     * @access public
     */
    public function getDBVersion()
    {
        $databaseversion = gettext("Error Getting Database Version");
        $this->sql = "SELECT version()";
        $this->stmt = $this->pdo->prepare($this->sql);
        $resultId = $this->stmt->execute();
        if ($resultId !== false) {
            // The version sql statement has run okay
            if ($this->stmt->rowCount() > 0) {
                $uao = $this->stmt->fetch(\PDO::FETCH_ASSOC, 0);
                $versiontext = chop($uao['version']);
                $versionarray = explode(" ", $versiontext);
                $databaseversion = $versionarray[0] . " " . $versionarray[1];
            }
        }

        return $databaseversion;
    }
     /**
     * Function to check if a Database User Exist
     *
     * @param string $username The name of the database user.
     *
     * @return boolean true if user exists, False if, or DB Error
     *
     * @access public
     */
    public function userExists(string $username)
    {
        $checkuserOk = false;
        $userExists = false;
        $this->sql = "select rolname from pg_roles where rolname = '$username'";
        $this->stmt = $this->pdo->prepare($this->sql);
        $sqlresult = $this->stmt->execute();
        if ($sqlresult !== false) {
            if ($this->stmt->rowCount() == 1) {
                $userExists = true;
            }
            $checkuserOk = true;
        }
        if ($checkuserOk == true) {
            return $userExists;
        } else {
            $msg = gettext("Error Testing if user exists in the database");
            return \g7mzr\db\common\Common::raiseError(
                $msg,
                DB_ERROR,
                $this->stmt->errorInfo()
            );
        }
    }

    /**
     * Function to create the database user for the application
     *
     * @param string  $username   The name of the database user.
     * @param string  $password   The password for the database user.
     * @param boolean $unittestdb True if this is a test system.
     *
     * @return boolean true if user Created or exists DB Error otherwise
     *
     * @access public
     */
    public function createUser(string $username, string $password, bool $unittestdb = false)
    {
        $checkuserOk = false;
        $this->sql = " Create User $username with";
        if ($unittestdb == true) {
            $this->sql .= " CREATEDB CREATEROLE";
        }
        $this->sql .= " encrypted password '$password'";
        $affected = $this->pdo->exec($this->sql);
        if ($affected !== false) {
            $checkuserOk = true;
        }
        if ($checkuserOk == true) {
            return true;
        } else {
            $msg = gettext("Error Creating Database User");
            return \g7mzr\db\common\Common::raiseError(
                $msg,
                DB_ERROR,
                $this->pdo->errorInfo()
            );
        }
    }

    /**
     * Function to drop the database user for the application
     *
     * @param string $username The name of the database user.
     *
     * @return boolean true if user does not exist or is dropped otherwise DB Error
     *
     * @access public
     */
    public function dropUser(string $username)
    {
        $checkuserOk = false;
        $this->sql = "Drop Role $username ";
        $affected = $this->pdo->exec($this->sql);
        if ($affected !== false) {
            $checkuserOk = true;
        }
        if ($checkuserOk == true) {
            return true;
        } else {
            $msg = gettext("Error Dropping Database User");
            return \g7mzr\db\common\Common::raiseError(
                $msg,
                DB_ERROR,
                $this->pdo->errorInfo()
            );
        }
    }
    /**
     * Function to drop the database for the application
     *
     * @param string $database The name of the database.
     *
     * @return boolean true if database exists DB Error otherwise
     *
     * @access public
     */
    public function databaseExists(string $database)
    {
        $msg = '';
        $databaseExists = false;
        $databaseExistsOk = false;
        $this->sql = "select pg_database.datname from pg_database";
        $this->sql .= " where pg_database.datname = '$database'";

        $this->stmt = $this->pdo->prepare($this->sql);
        $result = $this->stmt->execute();
        if ($result !== false) {
            if ($this->stmt->rowCount() > 0) {
                $databaseExists = true;
            }
            $databaseExistsOk = true;
        }
        if ($databaseExistsOk === true) {
            return $databaseExists;
        } else {
            $msg = gettext("Error checking the database exists") . "\n";
            return \g7mzr\db\common\Common::raiseError(
                $msg,
                DB_ERROR,
                $this->stmt->errorInfo()
            );
        }
    }


     /**
     * Function to create the database for the application
     *
     * @param string $database The name of the database.
     * @param string $username The name of the database user.
     *
     * @return boolean true if database Created or exists DB Error otherwise
     *
     * @access public
     */
    public function createDatabase(string $database, string $username)
    {

        $dbcheckok = true;
        $databaseCreated = false;

        $this->sql = "CREATE DATABASE " . $database;
        $this->sql .= " with owner = " .  $username;
        $this->stmt = $this->pdo->prepare($this->sql);
        $createDataBase = $this->stmt->execute();
        if ($createDataBase === false) {
            $dbcheckok = false;
        } else {
            $databaseCreated = true;
        }
        if ($dbcheckok == true) {
            return $databaseCreated;
        } else {
            $msg = gettext("Error Creating the database");
            return \g7mzr\db\common\Common::raiseError(
                $msg,
                DB_ERROR,
                $this->stmt->errorInfo()
            );
        }
    }

     /**
     * Function to drop the database for the application
     *
     * @param string $database The name of the database.
     *
     * @return boolean true if database Created or exists DB Error otherwise
     *
     * @access public
     */
    public function dropDatabase(string $database)
    {

        $msg = '';
        $dbcheckok = false;

        $this->sql = "DROP DATABASE $database";
        $this->stmt = $this->pdo->prepare($this->sql);
        $createtable = $this->stmt->execute();
        if ($createtable !== false) {
            $dbcheckok = true;
        }
        if ($dbcheckok == true) {
            return true;
        } else {
            $msg .= gettext("Error Dropping Database");
            return \g7mzr\db\common\Common::raiseError(
                $msg,
                DB_ERROR,
                $this->stmt->errorInfo()
            );
        }
    }
}

<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace g7mzr\db\dbphpunitsupport;

use g7mzr\db\dbphpunitsupport\InterfaceDBUnitTestSupport;

/**
 * DB_DRIVER_PGSQL Class is the class for the pgsql database drivers.  It implements
 * the InterfaceDatabaseSchema interface to provide access to the PGSQL database via
 * the PHP DO
 *
 * @category DB
 * @package  Database
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/

class PGSQLDBUnitTestSupport implements InterfaceDBUnitTestSupport
{
    /**
     * A PDO Database Object.
     *
     * @var   \g7mzr\db\DBManager
     * @access protected
     */
    protected $dbmanager;

    /**
     *  SQL text variable used when preparing SQL scripts
     *
     * @var    string
     * @access protected
     */
    protected $sql;

    /**
     * PGSQL Driver Class Constructor
     *
     * Sets up the PGSQL Driver dsn from the calling function
     * and any PDO specific options.
     *
     * @param \g7mzr\db\DBManager $dbmanager A dbmanager object
     *
     * @access public
     */
    public function __construct($dbmanager)
    {
        $this->dbmanager = $dbmanager;
    } // end constructor


    /**
     * DB Driver Destructor
     *
     * Disconnect the MDB2 data object from the database
     */
    public function __destruct()
    {
    }

    /**************************************************************************
     * FUNCTIONS TO BE USED BY PHPUNIT TO CHECK DATABASE CHANGES HAVE BEEN MADE
     **************************************************************************/

    /**
     * Function to send plain SQL Query to the Database
     *
     * @param string $table The name of the table to be checked for existance
     *
     * @return boolean True if the table exists, false if table does not exist  or
     *                 DB Error if an error is encountered
     */
    public function tableExists($table)
    {
        $tablefound  = false;
        $this->sql = "SELECT table_name FROM information_schema.tables ";
        $this->sql .= "WHERE table_schema='public' AND table_name = '$table'";
        $result = $this->dbmanager->getSchemaDriver()->sqlQuery($this->sql);
        if (\g7mzr\db\common\Common::isError($result)) {
            if ($result->getCode() != DB_ERROR_NOT_FOUND) {
                return $result;
            }
        } else {
            foreach ($result as $name) {
                if ($name['table_name'] == $table) {
                    $tablefound = true;
                }
            }
        }
        return $tablefound;
    }


    /**
     * Function to check if a column exist in the database
     *
     * @param string $table The name of the table the column is in
     * @param string $column The name of the column to be checked for existance
     *
     * @return boolean True if the column exists, false if column does not exist  or
     *                 DB Error if an error is encountered
     */
    public function columnExists($table, $column)
    {
        $columnfound  = false;
        $this->sql = "SELECT column_name FROM information_schema.columns ";
        $this->sql .= "WHERE table_schema='public' ";
        $this->sql .= "AND table_name = '$table' ";
        $this->sql .= "AND column_name = '$column'";
        $result = $this->dbmanager->getSchemaDriver()->sqlQuery($this->sql);
        if (\g7mzr\db\common\Common::isError($result)) {
            if ($result->getCode() != DB_ERROR_NOT_FOUND) {
                return $result;
            }
        } else {
            foreach ($result as $name) {
                if ($name['column_name'] == $column) {
                    $columnfound = true;
                }
            }
        }
        return $columnfound;
    }

    /**
     * Function to check if a column is nullable
     *
     * @param string $table The name of the table the column is in
     * @param string $column The name of the column to be checked
     *
     * @return boolean True if the column is nullable, false if not or
     *                 DB Error if an error is encountered
     */
    public function columnIsNullable($table, $column)
    {
        $isnullable  = false;
        $this->sql = "SELECT is_nullable FROM information_schema.columns ";
        $this->sql .= "WHERE table_schema='public' ";
        $this->sql .= "AND table_name = '$table' ";
        $this->sql .= "AND column_name = '$column'";
        $result = $this->dbmanager->getSchemaDriver()->sqlQuery($this->sql);
        if (\g7mzr\db\common\Common::isError($result)) {
            return $result;
        } else {
            foreach ($result as $name) {
                if ($name['is_nullable'] == "NO") {
                    $isnullable = true;
                }
            }
        }
        return $isnullable;
    }

    /**
     * Function to check the column type
     *
     * @param string $table The name of the table the column is in
     * @param string $column The name of the column to be checked
     * @param string $type   The type of column using RDMS type description
     *
     * @return boolean True if the column type matches, false if not or
     *                 DB Error if an error is encountered
     */
    public function columnType($table, $column, $type)
    {
        $columntype = false;
        $this->sql = "SELECT data_type FROM information_schema.columns ";
        $this->sql .= "WHERE table_schema='public' ";
        $this->sql .= "AND table_name = '$table' ";
        $this->sql .= "AND column_name = '$column'";
        $result = $this->dbmanager->getSchemaDriver()->sqlQuery($this->sql);
        if (\g7mzr\db\common\Common::isError($result)) {
            return $result;
        } else {
            foreach ($result as $datatype) {
                if ($datatype['data_type'] == $type) {
                    $columntype = true;
                }
            }
        }
        return $columntype;
    }

    /**
     * Function to check the default value of the column
     *
     * @param string $table  The name of the table the column is in
     * @param string $column The name of the column to be checked
     * @param string $value  The default value for the column
     *
     * @return boolean True if the column default value matches, false if not or
     *                 DB Error if an error is encountered
     */
    public function columnDefault($table, $column, $value)
    {
        $defaultmatch = false;
        $this->sql = "SELECT column_default FROM information_schema.columns ";
        $this->sql .= "WHERE table_schema='public' ";
        $this->sql .= "AND table_name = '$table' ";
        $this->sql .= "AND column_name = '$column'";
        $result = $this->dbmanager->getSchemaDriver()->sqlQuery($this->sql);
        if (\g7mzr\db\common\Common::isError($result)) {
            return $result;
        } else {
            foreach ($result as $columndefault) {
                if ($columndefault['column_default'] == $value) {
                    $defaultmatch = true;
                }
            }
        }
        return $defaultmatch;
    }
}

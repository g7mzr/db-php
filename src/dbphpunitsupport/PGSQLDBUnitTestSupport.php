<?php
/**
 * This file is part of PHP_Database_Client.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package db-php
 * @subpackage UnitTest
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/db-php/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\db\dbphpunitsupport;

use g7mzr\db\dbphpunitsupport\InterfaceDBUnitTestSupport;

/**
 * PGSQLDBUnitTestSupport implements InterfaceDBUnitTestSupport and is used by phpunit
 * to check if changes made to the database during testing were implemented.
 */
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
     * @param \g7mzr\db\DBManager $dbmanager A dbmanager object.
     *
     * @access public
     */
    public function __construct(\g7mzr\db\DBManager $dbmanager)
    {
        $this->dbmanager = $dbmanager;
    }


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
     * @param string $table The name of the table to be checked for existence.
     *
     * @return boolean True if the table exists, false if table does not exist  or
     *                 DB Error if an error is encountered
     */
    public function tableExists(string $table)
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
     * @param string $table  The name of the table the column is in.
     * @param string $column The name of the column to be checked for existence.
     *
     * @return boolean True if the column exists, false if column does not exist  or
     *                 DB Error if an error is encountered
     */
    public function columnExists(string $table, string $column)
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
     * Function to check if the value of a column can be set to null
     *
     * @param string $table  The name of the table the column is in.
     * @param string $column The name of the column to be checked.
     *
     * @return boolean True if the column value can be set to null, false if not or
     *                 DB Error if an error is encountered
     */
    public function columnIsNullable(string $table, string $column)
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
     * @param string $table  The name of the table the column is in.
     * @param string $column The name of the column to be checked.
     * @param string $type   The type of column using RDMS type description.
     *
     * @return boolean True if the column type matches, false if not or
     *                 DB Error if an error is encountered
     */
    public function columnType(string $table, string $column, string $type)
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
     * @param string $table  The name of the table the column is in.
     * @param string $column The name of the column to be checked.
     * @param string $value  The default value for the column.
     *
     * @return boolean True if the column default value matches, false if not or
     *                 DB Error if an error is encountered
     */
    public function columnDefault(string $table, string $column, string $value)
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

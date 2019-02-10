<?php
/**
 * This file is part of g7mzr/db
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace g7mzr\db\drivers\pgsql;

use g7mzr\db\interfaces\InterfaceDatabaseDriver;

/**
 * DatabaseDriverpgsql is the class for the pgsql database drivers.  It implements
 * the InterfaceDatabaseDriver interface to provide access to the PGSQL database
 * via PDO
 *
 * @category Webtemplate
 * @package  Database
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/

class DatabaseDriver implements InterfaceDatabaseDriver
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
     * @var    \PDOStatement
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
     * @param array $dsn an array containing the database connection details.
     * @param boolean $persistent Set true for persistent connection to database
     *
     * @access public
     */
    public function __construct($dsn, $persistent = false)
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
     * Function to start a database transaction
     *
     * This function starts a Database Transaction
     *
     * @return boolean true if transaction is started
     *
     * @access public
     */
    public function startTransaction()
    {
        $this->stmt = $this->pdo->prepare("BEGIN TRANSACTION");
        $resultID = $this->stmt->execute();
        return $resultID;
    }

    /**
     * Function to end a database transaction
     *
     * This function ends a Database Transaction by eithe committing or rolling
     * back the transaction based on the value of $commit
     *
     * @param boolean $commit Commmit transiaction if true, rollback otherwise.
     *
     * @return boolean true if transaction is started
     *
     * @access public
     */
    public function endTransaction($commit)
    {
        if ($commit == true) {
            $this->stmt = $this->pdo->prepare("COMMIT");
            $resultID = $this->stmt->execute();
        } else {
            $this->stmt = $this->pdo->prepare("ROLLBACK");
            $resultID = $this->stmt->execute();
        }
        return $resultID;
    }

    /**
     * This function returns the last insert id for the selected table
     *
     * @param string $tableName The name of the table data was inserted to
     * @param string $idfield   The name of the id field the table
     * @param string $srchfield The name of the field where the sreach data is saved
     * @param string $srchdata  The unique name entered in to the field
     *
     * @return integer The id of the last record inserted or WEBTEMPLATE error type
     * @access public
     */
    public function dbinsertid($tableName, $idfield, $srchfield, $srchdata)
    {
        $result = -1;
        $this->sql = "SELECT $idfield FROM $tableName WHERE ";
        $this->sql .= "$srchfield = '$srchdata'";
        $this->stmt = $this->pdo->prepare($this->sql);
        $sqlresult = $this->stmt->execute();
        if ($sqlresult !== false) {
            if ($this->stmt->rowCount() == '1') {
                $uao = $this->stmt->fetch(\PDO::FETCH_ASSOC, 0);
                $result = $uao[$idfield];
            } else {
                $msg = gettext("Record ID Not Found.");
                $err = \g7mzr\db\common\Common::raiseError(
                    $msg,
                    DB_ERROR_NOT_FOUND
                );
                return $err;
            }
        } else {
            $msg = gettext("Error getting record ID.");
            $err = \g7mzr\db\common\Common::raiseError($msg, DB_ERROR);
            return $err;
        }
        return $result;
    }

    /**
     * This function disconnects from the database
     *
     * @return boolean True
     */
    public function disconnect()
    {
        $this->stmt = null;
        $this->pdo = null;
        return true;
    }

    /**
     **************************************************************************
     ******************  END OF THE GENERAL FUNCTIONS SECTION   ***************
     **************************************************************************
     */

    /**
     * ****************************************************************************
     * ** START OF THE SECTION OF THE FILE CONTAINING GENERIC DATABASE FUNCTIONS **
     * ****************************************************************************
     */


    /**
     * This function inserts a single record to the database
     *
     * @param string $tableName  The name of the table data is to be inserted to
     * @param array  $insertData The name of the fields and data to be inserted
     *
     * @return boolean True if insert is ok or WEBTEMPLATE error type
     * @access public
     */
    public function dbinsert($tableName, $insertData)
    {

        // Initialise the data array
        $data = array();
        //Build the SQL INSERT Statement
        $this->sql = "INSERT INTO " . $tableName . " (";

        // Add the field names

        $arr_length = count($insertData);
        $current_element = 1;
        foreach ($insertData as $key => $elementData) {
            $this->sql .= $key;
            $paramname = ":". $key;
            $data[$paramname] = $elementData;
            if ($current_element < $arr_length) {
                $this->sql .= ", ";
            }
            $current_element = $current_element + 1;
        }
        $this->sql .= ") VALUES (";

        $arr_length = count($data);
        $current_element = 1;
        foreach ($data as $paramname => $elementdata) {
            $this->sql .= $paramname;
            if ($current_element < $arr_length) {
                $this->sql .= ',';
            }
            $current_element = $current_element + 1;
        }
        $this->sql .= ")";

        $this->stmt = $this->pdo->prepare($this->sql);

        foreach ($data as $paramname => $value) {
            $this->bind($paramname, $value);
        }
        $saveok = true;

        // Run the Update Command
        $affectedrows =  $this->stmt->execute();

        // Check the INSERT command run okay.
        if ($affectedrows === false) {
            $saveok = false;
            $msg = gettext("Error running the Database INSERT Statement");
        }

        //If all went okay return true.  If not return an error
        if ($saveok) {
            return true;
            ;
        } else {
            return \g7mzr\db\common\Common::raiseError($msg, 1);
        }
    }

    /**
     * This function updates a single record to the database
     *
     * @param string $tableName  The name of the table data is to be inserted to
     * @param array  $insertData The name of the fields and data to be inserted
     * @param array  $searchdata The field and data to be used in the "WHERE" clause
     *
     * @return boolean True if insert is okay or WEBTEMPLATE error type
     * @access public
     */
    public function dbupdate($tableName, $insertData, $searchdata)
    {

        // Initialise the data array
        $data = array();
        //Build the SQL INSERT Statement
        $this->sql = "UPDATE " . $tableName . " SET ";

        // Add the field names
        $arr_length = count($insertData);
        $current_element = 1;
        foreach ($insertData as $key => $elementData) {
            $paramname = ':' . $key;
            $this->sql .= $key . " = " . $paramname;
            $data[$paramname] = $elementData;
            if ($current_element < $arr_length) {
                $this->sql .= ", ";
            }
            $current_element = $current_element + 1;
        }

        $this->sql .= $this->processSearchData($searchdata);

        $this->stmt = $this->pdo->prepare($this->sql);

        foreach ($data as $paramname => $value) {
            $this->bind($paramname, $value);
        }

        $saveok = true;

        // Run the Update Command
        $affectedrows = $this->stmt->execute();

        // Check the UPDATE command run okay.
        if ($affectedrows === false) {
            $saveok = false;
            $msg = gettext("Error running the Database UPDATE Statement");
        } else {
            if ($this->stmt->rowCount() == 0) {
                $saveok = false;
                $msg = gettext("Record not found");
            }
        }

        //If all went okay return true.  If not return a WEBTEMPLATE error
        if ($saveok) {
            return true;
        } else {
            return \g7mzr\db\common\Common::raiseError($msg, 1);
        }
    }


    /**
     * This function selects a single record from the database
     *
     * @param string $tableName  The name of the table data is to be selected from
     * @param array  $fieldNames The name of the fields to select from the database
     * @param array  $searchdata The field and data to be used in the "WHERE" clause
     *
     * @return array Search data if search is ok or WEBTEMPLATE error type
     * @access public
     */
    public function dbselectsingle($tableName, $fieldNames, $searchdata)
    {

        // Build SQL statement
        $this->sql = "SELECT ";
        $this->sql .= $this->processSearchFields($fieldNames);
        $this->sql .= " from " . $tableName;
        $this->sql .= $this->processSearchData($searchdata);

        // Run the statement and check for errors
        $this->stmt = $this->pdo->prepare($this->sql);
        $resultID = $this->stmt->execute();
        if ($resultID !== false) {
            // No errors
            if ($this->stmt->rowCount() == 1) {
                // Found one record.
                // retrieve the record and populate the result array
                $this->rowcount = $this->stmt->rowCount();
                $gao = $this->stmt->fetch(\PDO::FETCH_ASSOC, 0);
            } elseif ($this->stmt->rowCount() > 1) {
                $msg = gettext('Found More than One Record');
                $err = \g7mzr\db\common\Common::raiseError(
                    $msg,
                    DB_ERROR_NOT_FOUND
                );
                return $err;
            } else {
                $msg = gettext('Not Found');
                $err = \g7mzr\db\common\Common::raiseError(
                    $msg,
                    DB_ERROR_NOT_FOUND
                );
                return $err;
            }
        } else {
            $msg = gettext('SQL Query Error');
            $err = \g7mzr\db\common\Common::raiseError(
                $msg,
                DB_ERROR,
                $this->stmt->errorInfo()
            );
            return $err;
        }
        return $gao;
    }



    /**
     * This function returns a search from the database
     *
     * @param string $tableName  Name of the table data is to be selected from
     * @param array  $fieldNames Name of the fields to select from the database
     * @param array  $searchdata Field and data to be used in the "WHERE" clause
     * @param string $order      Field used to order the selected data
     * @param array  $join       Data used to join tables for the search
     *
     * @return array Search data if search is ok or WEBTEMPLATE error type
     * @access public
     */
    public function dbselectmultiple(
        $tableName,
        $fieldNames,
        $searchdata,
        $order = null,
        $join = null
    ) {

        // Build SQL statement
        $this->sql = "SELECT ";
        $this->sql .= $this->processSearchFields($fieldNames);
        $this->sql .= " from " . $tableName;
        $this->sql .= $this->processJoin($join);
        $this->sql .= $this->processSearchData($searchdata);
        $this->sql .= $this->processSearchOrder($order);

        // Run the statement and check for errors
        $this->stmt = $this->pdo->prepare($this->sql);
        $resultID = $this->stmt->execute();
        if ($resultID !== false) {
            // No errors
            $this->rowcount = $this->stmt->rowCount();
            if ($this->stmt->rowCount() > 0) {
                /*  The search has found at least one group.
                   Create the output array */
                $resultarray = array();

                /* Populate the output array with the records */
                while ($uao = $this->stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $resultarray[] = $uao;
                }
            } else {
                $msg = gettext('Not Found');
                $err = \g7mzr\db\common\Common::raiseError(
                    $msg,
                    DB_ERROR_NOT_FOUND
                );
                return $err;
            }
        } else {
            $msg = gettext('SQL Query Error');
            $err = \g7mzr\db\common\Common::raiseError($msg, DB_ERROR);
            return $err;
        }
        return $resultarray;
    }

    /**
     * This function deletes single records from the database.
     *
     * The data to be used for the where clause is in an array called $searchdata
     * in format "columnname" => "search data".  It only deletes data which matches
     * exactly
     *
     * @param string $tableName  The name of the table data is to be deleted from
     * @param array  $searchdata The field and data to be used in the "WHERE" clause
     *
     * @return boolean if rows deleted or WEBTEMPLATE error type
     * @access public
     */
    public function dbdelete($tableName, $searchdata)
    {

        $this->sql = "DELETE FROM ". $tableName . " WHERE ";
        while ($data = current($searchdata)) {
            $this->sql .= key($searchdata) . " = '" . $data . "'";
            if (next($searchdata) !== false) {
                $this->sql .= " AND ";
            }
        }

        $this->stmt = $this->pdo->prepare($this->sql);
        $resultID = $this->stmt->execute();
        if ($resultID === false) {
            $msg = gettext('SQL Query Error');
            $err = \g7mzr\db\common\Common::raiseError($msg, DB_ERROR);
            return $err;
        }
        $this->rowcount = $this->stmt->rowCount();
        return true;
    }


    /**
     * This function can delete multiple records from the database.
     *
     * The data to be used for the where clause is in an array called $searchdata
     * in format "columnname" => array("type" => "<,> or =", "data" => "search data")
     *
     * @param string $tableName  The name of the table data is to be deleted from
     * @param array  $searchdata The field and data to be used in the "WHERE" clause
     *
     * @return boolean true if search is ok or WEBTEMPLATE error type
     * @access public
     */
    public function dbdeletemultiple($tableName, $searchdata)
    {

        $this->sql = "DELETE FROM ". $tableName . " WHERE ";
        while ($data = current($searchdata)) {
            $this->sql .= key($searchdata) . " ";
            $this->sql .= $data['type'] . " '" . $data['data'] . "'";
            if (next($searchdata) !== false) {
                $this->sql .= " AND ";
            }
        }

        $this->stmt = $this->pdo->prepare($this->sql);
        $resultID = $this->stmt->execute();
        if ($resultID === false) {
            $msg = gettext('SQL Query Error');
            $err = \g7mzr\db\common\Common::raiseError($msg, DB_ERROR);
            return $err;
        }
        $this->rowcount = $this->stmt->rowCount();
        return true;
    }

    /**
     * Get the rowcount of the last activity
     *
     * @return integer
     * @access public
     */
    public function rowCount()
    {
        return $this->rowcount;
    }

    /**
     * ****************************************************************************
     * **   END OF THE SECTION OF THE FILE CONTAINING GENERIC DATABASE FUNCTIONS **
     * ****************************************************************************
     */

    /**
     * ****************************************************************************
     * **                 START OF THE CLASS PRIVATE FUNCTIONS                   **
     * ****************************************************************************
     */


    /**
     * This function processes the field to be returned into  a SQL string
     *
     * @param array $fieldNames The fields to be returned as part of the search
     *
     * @return string The combined join statements in SQL Format
     *
     * @access private
     */
    private function processSearchFields($fieldNames)
    {

        $sql = '';
        while ($field = current($fieldNames)) {
            $sql .= $field ;
            if (\next($fieldNames) !== false) {
                $sql .= ", ";
            }
        }
        return $sql;
    }


    /**
     * This function processes join statements into a SQL string for searching
     *
     * @param array $join The field to be returned as part of the search
     *
     * @return string The combined join statements in SQL Format
     *
     * @access private
     */
    private function processJoin($join)
    {

        $sql = '';
        if ($join != null) {
            $sql .= ' INNER JOIN';
            $sql .= ' ' . $join['table2'];
            $sql .= ' ON (';
            $sql .= $join['field1'];
            $sql .= ' = ';
            $sql .= $join['field2'];
            $sql .= ')';
        }
        return $sql;
    }



    /**
     * This function processes search data nto a SQL string for searching
     *
     * @param array $searchdata The field to be returned as part of the search
     *
     * @return string The  search data in SQL Format
     *
     * @access private
     */
    private function processSearchData($searchdata)
    {

        $sql = '';
        if ($searchdata != null) {
            $sql .= " WHERE ";
            while ($data = \current($searchdata)) {
                if (($data[0] =='%') or ($data[strlen($data)-1] == '%')) {
                    $sql .= \key($searchdata) . " like '" . $data . "'";
                } else {
                    $sql .= \key($searchdata) . " = '" . $data . "'";
                }
                if (\next($searchdata) !== false) {
                    $sql .= " AND ";
                }
            }
        }
        return $sql;
    }

    /**
     * This function processes search data nto a SQL string for searching
     *
     * @param string $order Thefield the search is to be ordered by
     *
     * @return string The  search data in SQL Format
     *
     * @access private
     */
    private function processSearchOrder($order)
    {

        $sql= '';
        if ($order != null) {
            $sql .= " ORDER BY " . $order . " ASC";
        }

        return $sql;
    }

    /**
     * Function to print out messages.  If class is under unit test output is aborted
     *
     * @param string $msg The message to be displayed
     *
     * @return boolean Always true
     *
     * @access private
     *
    private function dbEcho($msg = "")
    {
        if (!isset($GLOBALS['unittest'])) {
            echo $msg. "\n";
        }
        return true;
    }
    */
    /**
     * Bind inputs to place holders
     *
     * This function binds the inputs to the place holders we put in place in the
     * sql statement prepared using the query function.
     *
     * @param string $param The name of the placeholder the variable is to be bound.
     * @param mixed  $value The value to be bound to the placeholder
     * @param int    $type  The type of variable defined using PDO Constants.
     *
     * @return boolean always true
     *
     * @access private
     */
    private function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = \PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = \PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = \PDO::PARAM_NULL;
                    break;
                default:
                    $type = \PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);

        return true;
    }

    /**
     * ****************************************************************************
     * **                   END OF THE CLASS PRIVATE FUNCTIONS                   **
     * ****************************************************************************
     */
}

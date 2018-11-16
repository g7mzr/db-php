<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace g7mzr\db\drivers\mock;

use g7mzr\db\interfaces\InterfaceDatabaseDriver;

/**
 * DB_DRIVER_MOCK Class is the mock class for unit testing.  It implements
 * the DBDRIVERIF interface but returns errors for every function
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
     * An PDO Database Object.
     *
     * @var    \PDO
     * @access protected
     */
    protected $PDO;

    /**
     * property: rowcount
     * @var integer
     * @access protected
     */

    protected $rowcount = 0;
    /**
     * Name of test being run
     *
     * @var    string
     * @access protected
     */
    protected $functions = array();

    /**
     * Data to be used in test
     *
     * @var    array
     * @access protected
     */
    protected $data = array();

    /**
     * Mock Database Driver Class Constructor
     *     *
     * @param array $dsn PDO Data Source Name
     *
     * @access public
     */
    public function __construct($dsn)
    {
        $this->dsn  = $dsn ;
    } // end constructor

    /**
     * DB Driver Destructor
     *
     * Disconnect the dummy data object
     */
    public function __destruct()
    {
        $this->dsn = '';
    }

    /****************************************************************************
     * This is a control function for the MOCK Interface only
     ****************************************************************************/
    /**
     * Function to control the MOCK Database Interface.
     *
     * This function is uesed to control wither the MOCK database interface return
     * sucessful values or failures.
     *
     * @param array $functions The name of the test being run
     * @param array $data      The data being sent to or returned by the database
     *
     * @return boolean Return true if control function sucessful
     *
     * @access public
     */
    public function control($functions, $data)
    {
        $this->functions = $functions;
        $this->data      = $data;
        return true;
    }

    /*****************************************************************************
     * This section contains all the functions used to manipulate the data held
     * within the database
     *****************************************************************************/
    /**
     * Function to get the database version
     *
     * This function gets the version of database currently being used.
     *
     * @return string database Version
     *
     * @access public
     */
    public function getDBVersion()
    {
        $callers = debug_backtrace();
        $calling = $callers[1]['function'];
        $databaseversion = gettext("Error Getting Database Version");

        if (!array_key_exists($calling, $this->functions)) {
            return $databaseversion;
        }
        if (!array_key_exists('pass', $this->functions[$calling])) {
            return $databaseversion;
        }

        if ($this->functions[$calling]['pass'] == true) {
            $databaseversion = $this->data['version'];
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
        $callers = debug_backtrace();
        $calling = $callers[1]['function'];

        if (!array_key_exists($calling, $this->functions)) {
            return false;
        }
        if (!array_key_exists('starttransaction', $this->functions[$calling])) {
            return false;
        }
        if ($this->functions[$calling]['starttransaction'] == false) {
            return false;
        }
        return true;
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
        $callers = debug_backtrace();
        $calling = $callers[1]['function'];
        $result = false;

        if (array_key_exists($calling, $this->functions)) {
            if (array_key_exists(
                'endtransaction',
                $this->functions[$calling]
            )
            ) {
                if ($this->functions[$calling]['endtransaction'] == true) {
                    $result = $commit;
                }
            }
        }
        return $result;
    }

    /**
     * This function inserts a new record to the database
     *
     * The data to be inserted in to $tableName is places in an array called
     * $field name.  The data is stored in the array in the following format
     * "columnname" => "data to be inserted".
     *
     * @param string $tableName  The name of the table data is to be inserted to
     * @param array  $insertData The name of the fields and data to be inserted
     *
     * @return boolean True if insert is ok or WEBTEMPLATE error type
     *
     * @access public
     */
    public function dbinsert($tableName, $insertData)
    {
        $callers = debug_backtrace();
        $calling = $callers[1]['function'];

        if (array_key_exists($calling, $this->functions)) {
            if (array_key_exists('pass', $this->functions[$calling])) {
                if ($this->functions[$calling]['pass'] == true) {
                    return true;
                }
            }
        }

        // The test should fail as
        $msg = gettext('SQL Query Error');
        $err = \g7mzr\db\common\Common::raiseError($msg, DB_ERROR);
        return $err;
    }

    /**
     * This function returns the last insert id for the selected table
     *
     * @param string $tableName The name of the table data was inserted to
     * @param string $idfield   The name of the id field the table
     * @param string $srchfield The name of the field where the search data is saved
     * @param string $srchdata  The unique name entered in to the field
     *
     * @return integer The id of the last record inserted or WEBTEMPLATE error type
     * @access public
     */
    public function dbinsertid($tableName, $idfield, $srchfield, $srchdata)
    {
        $callers = debug_backtrace();
        $calling = $callers[1]['function'];
        $result = false;

        if (array_key_exists($calling, $this->functions)) {
            if (array_key_exists('id', $this->functions[$calling])) {
                if ($this->functions[$calling]['id'] == true) {
                    $result = true;
                }
            }
        }

        if ($result == true) {
            if (array_key_exists($srchdata, $this->data)) {
                return $this->data[$srchdata];
            } else {
                return 1;
            }
        } else {
            $msg = gettext('Error Getting record ID.');
            $err = \g7mzr\db\common\Common::raiseError($msg, DB_ERROR);
            return $err;
        }
    }

    /**
     * This function updates an existing record to the database
     *
     * The data to be inserted in to $tableName is places in an array called
     * $field name.  The data is stored in the array in the following format
     * "columnname" => "data to be inserted".
     *
     * The data to be used for the where clause is again in an array in the same
     * format "columnname" => "search data".
     *
     * @param string $tableName  The name of the table data is to be inserted to
     * @param array  $insertData The name of the fields and data to be inserted
     * @param array  $searchdata The field and data to be used in the "WHERE" clause
     *
     * @return boolean True if insert is ok or WEBTEMPLATE error type
     *
     * @access public
     */
    public function dbupdate($tableName, $insertData, $searchdata)
    {
        $callers = debug_backtrace();
        $calling = $callers[1]['function'];

        if (array_key_exists($calling, $this->functions)) {
            if (array_key_exists('update', $this->functions[$calling])) {
                if ($this->functions[$calling]['update'] === true) {
                    return true;
                }
            }
        }

        // The test should fail as
        $msg = gettext('SQL Query Error');
        $err = \g7mzr\db\common\Common::raiseError($msg, DB_ERROR);
        return $err;
    }

    /**
     * This function selects a single record from the database
     *
     * The columns to be returned from the search are in an array called  $fieldNames
     * This is an unindexed array, array=("Col1", "col2" etc).
     *
     * The data to be usedfor the where clause is in an array called $searchdata in
     * format"columnname" => "search data".
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
        return $this->dbselect($tableName, $fieldNames, $searchdata);
    }

    /**
     * This function returns a search from the database
     *
     * The columns to be returned from the search are in an array called  $fieldNames
     * This is an unindexed array, array=("Col1", "col2" etc).
     *
     * The data to be used for the where clause is in an array called $searchdata in
     * format "columnname" => "search data".
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
        return $this->dbselect($tableName, $fieldNames, $searchdata);
    }

    /**
     * This function deletes single from the database.
     *
     * The data to be used for the where clause is in an array called $searchdata
     * in format "columnname" => "search data".  It only deletes data which matches
     * exactly
     *
     * @param string $tableName  The name of the table data is to be deleted from
     * @param array  $searchdata The field and data to be used in the "WHERE" clause
     *
     * @return boolean true if search is ok or WEBTEMPLATE error type
     * @access public
     */
    public function dbdelete($tableName, $searchdata)
    {
        $callers = debug_backtrace();
        $calling = $callers[1]['function'];

        if (array_key_exists($calling, $this->functions)) {
            if (array_key_exists('delete', $this->functions[$calling])) {
                if ($this->functions[$calling]['delete'] == $tableName) {
                    return true;
                }
            }
        }

        $msg = gettext('SQL Query Error');
        $err = \g7mzr\db\common\Common::raiseError($msg, DB_ERROR);
        return $err;
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
        $msg = gettext('SQL Query Error');
        $err = \g7mzr\db\common\Common::raiseError($msg, DB_ERROR);
        return $err;
    }

    /**
     * This function implements the Select function in the mock driver.
     *
     * This function is the comon select function in the mock driver for both
     * dbselectsingle and dbselectmultiple
     *
     * The columns to be returned from the search are in an array called  $fieldNames
     * This is an unindexed array, array=("Col1", "col2" etc).
     *
     * The data to be usedfor the where clause is in an array called $searchdata in
     * format"columnname" => "search data".
     *
     * @param string $tableName  The name of the table data is to be selected from
     * @param array  $fieldNames The name of the fields to select from the database
     * @param array  $searchdata The field and data to be used in the "WHERE" clause
     *
     * @return array Search data if search is ok or WEBTEMPLATE error type
     * @access private
     */
    private function dbselect($tableName, $fieldNames, $searchdata)
    {
        $callers = debug_backtrace();
        $calling = $callers[2]['function'];
        if (array_key_exists($calling, $this->functions)) {
            if (array_key_exists('notfound', $this->functions[$calling])) {
                if ($this->functions[$calling]['notfound'] == true) {
                    $msg = gettext('Not Found');
                    $err = \g7mzr\db\common\Common::raiseError(
                        $msg,
                        DB_ERROR_NOT_FOUND
                    );
                    return $err;
                }
            }
            if (array_key_exists('pass', $this->functions[$calling])) {
                if ($this->functions[$calling]['pass'] == true) {
                    return $this->data[$calling];
                }
            }
        }

        // The test should fail as
        $msg = gettext('SQL Query Error');
        $err = \g7mzr\db\common\Common::raiseError($msg, DB_ERROR);
        return $err;
    }

    /**
     * This function disconnects from the database
     *
     * @return boolean True
     */
    public function disconnect()
    {
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
        $callers = debug_backtrace();
        $calling = $callers[1]['function'];

        if (!array_key_exists($calling, $this->functions)) {
            return $this->rowcount;
        }
        if (array_key_exists('rowcount', $this->functions[$calling])) {
            if ($this->functions[$calling]['rowcount'] == true) {
                $this->rowcount = $this->data['rowcount'];
            }
        }
        return $this->rowcount;
    }
}

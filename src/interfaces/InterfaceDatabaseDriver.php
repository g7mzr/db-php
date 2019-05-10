<?php
/**
 * This file is part of PHP_Database_Client.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package db-php
 * @subpackage Drivers Interfaces
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/db-php/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\db\interfaces;

/**
 * InterfaceDatabaseDriver defines the public interface to provide access to the selected
 * database via PDO
 *
 */
interface InterfaceDatabaseDriver
{

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
    public function getDBVersion();

     /**
     * Function to start a database transaction
     *
     * This function starts a Database Transaction
     *
     * @return boolean true if transaction is started
     *
     * @access public
     */
    public function startTransaction();

    /**
     * Function to end a database transaction
     *
     * This function ends a Database Transaction by either committing or rolling
     * back the transaction based on the value of $commit
     *
     * @param boolean $commit Commit transaction if true, rollback otherwise.
     *
     * @return boolean true if transaction end command is successful.
     *
     * @access public
     */
    public function endTransaction(bool $commit);

    /**
     * This function inserts a new record to the database
     *
     * The data to be inserted in to $tableName is places in an array called
     * $field name.  The data is stored in the array in the following format
     * "columnname" => "data to be inserted".
     *
     * @param string $tableName  The name of the table data is to be inserted to.
     * @param array  $insertData The name of the fields and data to be inserted.
     *
     * @return boolean True if insert is ok or DB error type
     *
     * @access public
     */
    public function dbinsert(string $tableName, array $insertData);

    /**
     * This function returns the last insert id for the selected table
     *
     * @param string $tableName The name of the table data was inserted to.
     * @param string $idfield   The name of the id field the table.
     * @param string $srchfield The name of the field where the search data is saved.
     * @param string $srchdata  The unique name entered in to the field.
     *
     * @return integer The id of the last record inserted or DB error type
     * @access public
     */
    public function dbinsertid(string $tableName, string $idfield, string $srchfield, string $srchdata);

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
     * @param string $tableName  The name of the table data is to be inserted to.
     * @param array  $insertData The name of the fields and data to be inserted.
     * @param array  $searchdata The field and data to be used in the "WHERE" clause.
     *
     * @return boolean True if insert is ok or DB error type
     *
     * @access public
     */
    public function dbupdate(string $tableName, array $insertData, array $searchdata);

    /**
     * This function selects a single record from the database
     *
     * The columns to be returned from the search are in an array called  $fieldNames
     * This is a non-associated array, array=("Col1", "col2" etc).
     *
     * The data to be usedfor the where clause is in an array called $searchdata in
     * format"columnname" => "search data".
     *
     * @param string $tableName  The name of the table data is to be selected from.
     * @param array  $fieldNames The name of the fields to select from the database.
     * @param array  $searchdata The field and data to be used in the "WHERE" clause.
     *
     * @return array Search data if search is ok or DB error type
     * @access public
     */
    public function dbselectsingle(string $tableName, array $fieldNames, array $searchdata);

    /**
     * This function returns a search from the database
     *
     * The columns to be returned from the search are in an array called  $fieldNames
     * This is an non-associated array, array=("Col1", "col2" etc).
     *
     * The data to be used for the where clause is in an array called $searchdata in
     * format "columnname" => "search data".
     *
     * @param string $tableName  Name of the table data is to be selected from.
     * @param array  $fieldNames Name of the fields to select from the database.
     * @param array  $searchdata Field and data to be used in the "WHERE" clause.
     * @param string $order      Field used to order the selected data.
     * @param array  $join       Data used to join tables for the search.
     *
     * @return array Search data if search is ok or DB error type
     * @access public
     */
    public function dbselectmultiple(
        string $tableName,
        array $fieldNames,
        array $searchdata,
        string $order = '',
        array $join = array()
    );

    /**
     * This function deletes single from the database.
     *
     * The data to be used for the where clause is in an array called $searchdata
     * in format "columnname" => "search data".  It only deletes data which matches
     * exactly
     *
     * @param string $tableName  The name of the table data is to be deleted from.
     * @param array  $searchdata The field and data to be used in the "WHERE" clause.
     *
     * @return boolean true if search is OK or DB error type
     * @access public
     */
    public function dbdelete(string $tableName, array $searchdata);

    /**
     * This function can delete multiple records from the database.
     *
     * The data to be used for the where clause is in an array called $searchdata
     * in format "columnname" => array("type" => "<,> or =", "data" => "search data")
     *
     * @param string $tableName  The name of the table data is to be deleted from.
     * @param array  $searchdata The field and data to be used in the "WHERE" clause.
     *
     * @return boolean true if search is ok or DB error type
     * @access public
     */
    public function dbdeletemultiple(string $tableName, array $searchdata);

    /**
     * Get the number of rows affected by the last command
     *
     * @return integer
     * @access public
     */
    public function rowCount();

    /**
     * This function disconnects from the database
     *
     * @return boolean True
     */
    public function disconnect();
}

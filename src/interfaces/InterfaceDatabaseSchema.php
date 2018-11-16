<?php
/**
 * This file is part of g7mzr\db
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace g7mzr\db\interfaces;

/**
 * Defines the public interface of the Schema manager for database access.  This
 * interface needs to be implemented for each of the RMDB systems to be accessed.
 *
 * @category g7mzr\db
 * @package  Database
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/

interface InterfaceDatabaseSchema
{

    /****************************************************************************
     * The functions in the section below are all used to create and modify the
     * database.  They are called from The Schemafunctions Class
     ****************************************************************************/

    /**
     **************************************************************************
     **********  THIS SECTION OF THE FILE CONTAINS GENERAL FUNCTIONS  *********
     **************************************************************************
     */

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
     * This function ends a Database Transaction by eithe committing or rolling
     * back the transaction based on the value of $commit
     *
     * @param boolean $commit Commmit transaction if true, rollback otherwise.
     *
     * @return boolean true if transaction is started
     *
     * @access public
     */
    public function endTransaction($commit);

    /**
     * Function to translate Column types from default schema
     *
     * @param string $columntype Type of database column to be translated
     *
     * @return string translated column type
     *
     * @access public
     */
    public function translateColumn($columntype);

    /**************************************************************************
     *    FUNCTIONS FOR CREATING AND DROPPING TABLES
     **************************************************************************/

    /**
     * Function to create table SQL
     *
     * @param string $tableName The name of the table being created
     *
     * @return mixed true if table created or DB error
     *
     * @access public
     */
    public function createtable($tableName);

    /**
     * Function to create SQL to drop a table
     *
     * @param string $tableName The name of the table being dropped
     *
     * @return mixed true if table created or DB error
     *
     * @access public
     */
    public function dropTable($tableName);

    /**************************************************************************
     *           FUNCTIONS FOR CREATING, DROPPING, AMMENDING COLUMNS
     **************************************************************************/

    /**
     * Function to create the SQL to add a column to a table
     *
     * @param string  $tableName   The name of the table being altered
     * @param string  $columnName  The Name of the column being created
     * @param string  $columnType  The SQL type for the column
     * @param boolean $primary     If true this is the primary index for the table
     * @param boolean $notnull     If true the column must contain data
     * @param boolean $unique      If true the data in the column must be unique
     * @param string  $default     The default value for the column
     *
     * @return mixed true if table created or DB error
     *
     * @access public
     */
    public function addColumn(
        $tableName,
        $columnName,
        $columnType,
        $primary = false,
        $notnull = false,
        $unique = false,
        $default = ""
    );

    /**
     * Function to create the SQL to drop a column from a table
     *
     * @param string $tableName  The name of the table being altered
     * @param string $columnName The Name of the column being dropped
     *
     * @return mixed true if table created or DB error
     *
     * @access public
     */
    public function dropColumn($tableName, $columnName);

    /**
     * Function to create the SQL to alter a column in a table
     *
     * @param string $tableName  The name of the table being altered
     * @param string $columnName The Name of the column being created
     * @param string $attribute  The attribute being changed
     * @param string $setdrop    SET or DROP the Attribute
     * @param string $value      The value of the attribute if it has one.
     *
     * @return mixed true if table created or WEBTEMPLATE error
     *
     * @access public
     */
    public function alterColumn(
        $tableName,
        $columnName,
        $attribute,
        $setdrop,
        $value = null
    );

    /**************************************************************************
     *             FUNCTIONS FOR CREATING AND DROPPING FOREGIN KEYS
     **************************************************************************/

    /**
     * Function to create a Foreign Key
     *
     * @param string $tablename  The name of the table the FK is to be created on
     * @param string $fkname     the name of the foreign key
     * @param string $columnname The name of the column which has the FK attached
     * @param string $linktable  The name of the table which is the FK Source
     * @param string $linkcolumn The name of the column which is the FK Source
     *
     * @return boolean true if Foreign Key Created DB Error otherwise
     *
     * @access public
     */
    public function createFK(
        $tablename,
        $fkname,
        $columnname,
        $linktable,
        $linkcolumn
    );


     /**
     * Function to drop a Foreign Key
     *
     * @param string $tableName The name of the table being worked on
     * @param string $keyName   The name of the Foreign Key being dropped
     *
     * @return boolean true if Foreign Key dropped DB Error otherwise
     *
     * @access public
     */
    public function dropFK($tableName, $keyName);


    /**************************************************************************
     *                  FUNCTIONS FOR CREATING AND DROPPING INDEXES
     **************************************************************************/

    /**
     * Function to create an index
     *
     * @param string  $tablename The name of the table being indexed
     * @param string  $indexname The Name of the Index.
     * @param string  $column    The Name of the Index Column
     * @PARAM boolean $unique    Set true if the unique key is added to index
     *
     * @return boolean true if index Created DB Error otherwise
     *
     * @access public
     */
    public function createIndex($tablename, $indexname, $column, $unique);

     /**
     * Function to drop indexes for a table
     *
     * @param string $tableName The name of the table being changed
     * @param string $indexName The name of the index being dropped
     *
     * @return boolean true if index Created DB Error otherwise
     *
     * @access public
     */
    public function dropIndex($tableName, $indexName);



    /**************************************************************************
     *                  FUNCTIONS FOR SCHEMA MANAGEMENT
     **************************************************************************/

    /**
     * Function to create the SQL to save the current schema
     *
     * @param integer $version The Version of the schema being saved
     * @param array   $schema  The array containing the schema
     * @param string  $table   The name of the Schema Table in the database
     *
     * @return mixed true if schema saved or DB error
     *
     * @access public
     */
    public function saveSchema($version, $schema, $table = "schema");

    /**
     * Function to retrieve the current schema from the database.
     *
     * @param string $table The table the schema is stored in.  Default is schema
     *
     * @return mixed array containing schema version and data or DB error
     *
     * @access public
     */
    public function getSchema($table = 'schema');

    /**
     * Function to send plain SQL Query to the Database
     *
     * @param string $sql SQL expression to send to database
     *
     * @return mixed array with results of DB Error
     */
    public function sqlQuery($sql);

    /*****************************************************************************
     * End of the section which deals with Database Maintenance
     *****************************************************************************/
}

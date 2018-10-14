<?php
/**
 * This file is part of g7mzr\db
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace g7mzr\db\dbphpunitsupport;

/**
 * Defines the public interface of the Schema manager for database access.  This
 * interface needs to be implemented for each of the RMDB systems to be accessed.
 *
 * @category g7mzr\db
 * @package  UNITTEST
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/

interface InterfaceDBUnitTestSupport
{
    /**************************************************************************
     * FUNCTIONS TO BE USED BY PHPUNIT TO CHECK DATABASE CHANGES HAVE BEEN MADE
     **************************************************************************/

    /**
     * Function to check if a table exist in the database
     *
     * @param string $table The name of the table to be checked for existance
     *
     * @return boolean True if the table exists, false if table does not exist  or
     *                 DB Error if an error is encountered
     */
    public function tableExists($table);

    /**
     * Function to check if a column exist in the database
     *
     * @param string $table The name of the table the column is in
     * @param string $column The name of the column to be checked for existance
     *
     * @return boolean True if the column exists, false if column does not exist  or
     *                 DB Error if an error is encountered
     */
    public function columnExists($table, $column);

    /**
     * Function to check if a column is nullable
     *
     * @param string $table  The name of the table the column is in
     * @param string $column The name of the column to be checked
     *
     * @return boolean True if the column is nullable, false if not or
     *                 DB Error if an error is encountered
     */
    public function columnIsNullable($table, $column);

    /**
     * Function to check the column type
     *
     * @param string $table  The name of the table the column is in
     * @param string $column The name of the column to be checked
     * @param string $type   The type of column using RDMS type description
     *
     * @return boolean True if the column type matches, false if not or
     *                 DB Error if an error is encountered
     */
    public function columnType($table, $column, $type);

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
    public function columnDefault($table, $column, $value);

    /*****************************************************************************
     * End of the section which deals with Database Maintenance
     *****************************************************************************/
}

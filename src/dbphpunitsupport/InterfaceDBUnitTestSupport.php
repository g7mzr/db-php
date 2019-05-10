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

/**
 * InterfaceDBUnitTestSupport defines the functions used by phpunit to check if
 * changes made to the database during testing were implemented.
 */
interface InterfaceDBUnitTestSupport
{
    /**************************************************************************
     * FUNCTIONS TO BE USED BY PHPUNIT TO CHECK DATABASE CHANGES HAVE BEEN MADE
     **************************************************************************/

    /**
     * Function to check if a table exist in the database
     *
     * @param string $table The name of the table to be checked for existence.
     *
     * @return boolean True if the table exists, false if table does not exist  or
     *                 DB Error if an error is encountered
     */
    public function tableExists(string $table);

    /**
     * Function to check if a column exist in the database
     *
     * @param string $table  The name of the table the column is in.
     * @param string $column The name of the column to be checked for existence.
     *
     * @return boolean True if the column exists, false if column does not exist  or
     *                 DB Error if an error is encountered
     */
    public function columnExists(string $table, string $column);

    /**
     * Function to check if the value of a column can be set to null
     *
     * @param string $table  The name of the table the column is in.
     * @param string $column The name of the column to be checked.
     *
     * @return boolean True if the column value can be set to null, false if not or
     *                 DB Error if an error is encountered
     */
    public function columnIsNullable(string $table, string $column);

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
    public function columnType(string $table, string $column, string $type);

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
    public function columnDefault(string $table, string $column, string $value);

    /*****************************************************************************
     * End of the section which deals with Database Maintenance
     *****************************************************************************/
}

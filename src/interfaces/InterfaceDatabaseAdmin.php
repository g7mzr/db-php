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
 * Defines the public interface of administrating the RMDB .  This
 * interface needs to be implemented for each of the RMDB systems to be accessed.
 */

interface InterfaceDatabaseAdmin
{

    /****************************************************************************
     * The functions in the section below are all used to Manage Database users,
     * create blank databases and users, drop databases and users
     ****************************************************************************/

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
     * Function to check if a Database User Exist
     *
     * @param string $username The name of the database user.
     *
     * @return boolean true if user exists, False if, or DB Error
     *
     * @access public
     */
    public function userExists(string $username);

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
    public function createUser(string $username, string $password, bool $unittestdb = false);

     /**
     * Function to drop the database user for the application
     *
     * @param string $username The name of the database user.
     *
     * @return boolean true if user dropped DB Error otherwise
     *
     * @access public
     */
    public function dropUser(string $username);

    /**
     * Function to drop the database for the application
     *
     * @param string $database The name of the database.
     *
     * @return boolean true if database exists DB Error otherwise
     *
     * @access public
     */
    public function databaseExists(string $database);

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
    public function createDatabase(string $database, string $username);

     /**
     * Function to drop the database for the application
     *
     * @param string $database The name of the database.
     *
     * @return boolean true if database Created or exists DB Error otherwise
     *
     * @access public
     */
    public function dropDatabase(string $database);

    /*****************************************************************************
     * End of the section which deals with Database Maintenance
     *****************************************************************************/
}

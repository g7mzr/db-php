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
 * Defines the public interface of administrating the RMDB .  This
 * interface needs to be implemented for each of the RMDB systems to be accessed.
 *
 * @category g7mzr\db
 * @package  Database
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/

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
     * @param string $username The name of the database user
     *
     * @return boolean true if user exists, False if, or DB Error
     *
     * @access public
     */
    public function userExists($username);

    /**
     * Function to create the database user for the application
     *
     * @param string $username   The name of the database user
     * @param string $password   The password for the database user
     * @param string $unittestdb True if this is a test system
     *
     * @return boolean true if user Created or exists WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function createUser($username, $password, $unittestdb = false);

     /**
     * Function to drop the database user for the application
     *
     * @param string $username The name of the database user
     *
     * @return boolean true if user dropped WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function dropUser($username);

    /**
     * Function to drop the database for the application
     *
     * @param string $database The name of the database
     *
     * @return boolean true if database exists WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function databaseExists($database);

    /**
     * Function to create the database for the application
     *
     * @param string $database The name of the database
     * @param string $username The name of the database user
     *
     * @return boolean true if database Created or exists WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function createDatabase($database, $username);

     /**
     * Function to drop the database for the application
     *
     * @param string $database The name of the database
     *
     * @return boolean true if database Created or exists WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function dropDatabase($database);

    /*****************************************************************************
     * End of the section which deals with Database Maintenance
     *****************************************************************************/
}

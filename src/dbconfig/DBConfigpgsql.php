<?php
/**
 * This file is part of g7mzr/db
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace g7mzr\db\dbconfig;

/**
 * PGSQL Configuration Class for Unit Testing
 *
 * @category g7mzr/db
 * @package  UnitTesting
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class DBConfigpgsql
{
    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
    }

    /**
     * This function configures the database for unit testing
     *
     * @param array $dsn Database configuration details
     *
     * @return boolean True if the database is configured
     */
    public function configdb($dsn)
    {

        $createnewdatabase = $this->createdb($dsn);
        if ($createnewdatabase === false) {
            return false;
        }

        $createnewschema = $this->createschema($dsn);
        if ($createnewschema === false) {
            return false;
        }

        return true;
    }

    /**
     * This function deletes existing test databases and creates
     * a new blank one
     *
     * @param array $dsn The database connection details
     *
     * @return boolean True if database is created false otherwise
     *
     * @access private
     */
    private function createdb($dsn)
    {
        $conStr = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
            $dsn["hostspec"],
            '5432',
            'template1',
            $dsn["username"],
            $dsn["password"]
        );

        // Create the PDO object and Connect to the database
        try {
            $localconn = new \PDO($conStr);
        } catch (\Exception $e) {
            print_r($e->getMessage());
            return false;
        }
        //$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $localconn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);

        // DROP THE EXISTING TEST DATABASE
        $sql =  "DROP DATABASE IF EXISTS " . $dsn['databasename'];
        $dropresult = $localconn->query($sql);
        if ($dropresult === false) {
            echo "Error dropping database\n";
            return false;
        }

        // CREATE THE TEST DATABASE
        $sql =  "CREATE DATABASE " . $dsn['databasename'];
        $createresult = $localconn->query($sql);
        if ($createresult === false) {
            echo "Error creating database\n";
            return false;
        }

        return true;
    }

    /**
     * This function creates the test schema and data
     *
     * @param array $dsn The database connection details
     *
     * @return boolean True if schema is created false otherwise
     *
     * @access private
     */
    private function createschema($dsn)
    {
        // SWITCH TO THE NEW DATABASE
        $conStr = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
            $dsn["hostspec"],
            '5432',
            $dsn["databasename"],
            $dsn["username"],
            $dsn["password"]
        );

        // Create the PDO object and Connect to the database
        try {
            $localconn = new \PDO($conStr);
        } catch (\Exception $e) {
            print_r($e->getMessage());
            return false;
        }
        //$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $localconn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);

        // Create the Schema Table
        $sql = "CREATE TABLE schema(";
        $sql .= "name VARCHAR(50) UNIQUE NOT NULL,";
        $sql .= "version integer NOT NULL,";
        $sql .= "schema text NOT NULL";
        $sql .= ")";
        $tableschemaresult = $localconn->query($sql);
        if ($tableschemaresult === false) {
            echo "Error creating Schema Table\n";
            return false;
        }


        // Create an Empty Schema Table for testing
        $sql = "CREATE TABLE emptyschema(";
        $sql .= "name VARCHAR(50) UNIQUE NOT NULL,";
        $sql .= "version integer NOT NULL,";
        $sql .= "schema text NOT NULL";
        $sql .= ")";
        $tableemptyschemaresult = $localconn->query($sql);
        if ($tableemptyschemaresult === false) {
            echo "Error creating empty Schema Table\n";
            return false;
        }

        // CREATE THE FIRST TEST TABLE
        $sql = "CREATE TABLE users (";
        $sql .= "user_id serial PRIMARY KEY, ";
        $sql .= "username VARCHAR(50) UNIQUE NOT NULL,";
        $sql .= "password VARCHAR(50) NOT NULL,";
        $sql .= "email VARCHAR(355) UNIQUE NOT NULL";
        $sql .= ")";
        $table1result = $localconn->query($sql);
        if ($table1result === false) {
            echo "Error creating Table1\n";
            return false;
        }

        // CREATE THE SECOND TEST TABLE
        $sql = "CREATE TABLE items (";
        $sql .= "item_id serial PRIMARY KEY, ";
        $sql .= "itemname VARCHAR(50) UNIQUE NOT NULL,";
        $sql .= "itemdescription VARCHAR(355),";
        $sql .= "available BOOLEAN NOT NULL,";
        $sql .= "owner integer REFERENCES users";
        $sql .= ")";
        $table1result = $localconn->query($sql);
        if ($table1result === false) {
            echo "Error creating Table2\n";
            return false;
        }

        // ADD USER ONE
        $sql = "INSERT INTO users ";
        $sql .= "(username, password, email) ";
        $sql .= "VALUES ";
        $sql .= "('user1', 'passwd', 'user1@example.com')";
        $user1result = $localconn->query($sql);
        if ($user1result === false) {
            echo "Error creating User1\n";
            return false;
        }

        // ADD USER TWO
        $sql = "INSERT INTO users ";
        $sql .= "(username, password, email) ";
        $sql .= "VALUES ";
        $sql .= "('user2', 'passwd', 'user2@example.com')";
        $user2result = $localconn->query($sql);
        if ($user2result === false) {
            echo "Error creating User2\n";
            return false;
        }

       // ADD ITEM ONE
        $sql = "INSERT INTO items";
        $sql .= "(itemname, itemdescription, available, owner) ";
        $sql .= "VALUES ";
        $sql .= "('item1', 'This is the first test item.', true, '1')";
        $item1result = $localconn->query($sql);
        if ($item1result === false) {
            echo "Error creating Item1\n";
            return false;
        }

       // ADD ITEM YWO
        $sql = "INSERT INTO items";
        $sql .= "(itemname, itemdescription, available, owner) ";
        $sql .= "VALUES ";
        $sql .= "('item2', 'This is the second test item.', false, '2')";
        $item2result = $localconn->query($sql);
        if ($item2result === false) {
            echo "Error creating Item2\n";
            return false;
        }

       // ADD ITEM THREE
        $sql = "INSERT INTO items";
        $sql .= "(itemname, itemdescription, available, owner) ";
        $sql .= "VALUES ";
        $sql .= "('item3', 'This is the third test item.', true, '2')";
        $item3result = $localconn->query($sql);
        if ($item3result === false) {
            echo "Error creating Item3\n";
            return false;
        }

        // All okay
        return true;
    }
}

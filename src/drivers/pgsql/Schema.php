<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace g7mzr\db\drivers\pgsql;

use g7mzr\db\interfaces\InterfaceDatabaseSchema;

/**
 * DB_DRIVER_PGSQL Class is the class for the pgsql database drivers.  It implements
 * the InterfaceDatabaseSchema interface to provide access to the PGSQL database via
 * the PHP PDO
 *
 * @category DB
 * @package  Database
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/

class Schema implements InterfaceDatabaseSchema
{
    /**
     * Database Data Source Name
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
     * @var   \PDO
     * @access protected
     */
    protected $pdo;

    /**
     * PDO Statement object.  USed when preparing SQL scripts
     *
     * @var    \STMP
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
        if ($resultID !== false) {
            return true;
        } else {
            return false;
        }
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
            return true;
        } else {
            $this->stmt = $this->pdo->prepare("ROLLBACK");
            $resultID = $this->stmt->execute();
            return false;
        }
    }


    /**
     * Function to translate Column types from default schema
     *
     * @param string $columntype Type of database column to be translated
     *
     * @return string translated column type
     *
     * @access public
     */
    public function translateColumn($columntype)
    {
        switch ($columntype) {
            case "DATETIME":
                $newcolumntype = "timestamp(0) without time zone";
                break;
            default:
                $newcolumntype = $columntype;
                break;
        }
        return $newcolumntype;
    }

    /**
     **************************************************************************
     *****  THIS SECTION OF THE FILE CONTAINS DATABASE CREATION FUNCTIONS *****
     **************************************************************************
     */

    /**************************************************************************
     *                  FUNCTIONS FOR CREATING AND DROPPING TABLES
     **************************************************************************/


    /***   TABLE FUNCTIONS       ***/
    /**
     * Function to create table SQL
     *
     * @param string $tableName The name of the table being created
     *
     * @return mixed true if table created or WEBTEMPLATE error
     *
     * @access public
     */
    public function createtable($tableName)
    {

        $this->dbEcho("Creating Table $tableName");
        $errorMsg = '';
        $this->sql = "CREATE TABLE " . $tableName . " ()";
        $affected = $this->pdo->exec($this->sql);
        if ($affected  === false) {
            $errorMsg .= "Error Creating the Table\n";
            $err = \g7mzr\db\common\Common::raiseError($errorMsg, DB_ERROR);
            return $err;
        }
        return true;
    }

    /**
     * Function to create SQL to drop a table
     *
     * @param string $tableName The name of the table being droped
     *
     * @return mixed true if table created or WEBTEMPLATE error
     *
     * @access public
     */
    public function dropTable($tableName)
    {
        $this->dbEcho("Dropping Table $tableName");
        $this->sql = "DROP TABLE $tableName CASCADE";
        $affected = $this->pdo->exec($this->sql);

        if ($affected === false) {
            $errorMsg = "Error Dropping Table $tableName\n";
            return \g7mzr\db\common\Common::raiseError($errorMsg, DB_ERROR);
        }
            return true;
    }


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
    ) {
        $this->dbEcho("Adding Column $tableName:$columnName");
        $errorMsg = '';
        $this->sql = "ALTER TABLE $tableName ADD COLUMN ";
        $this->sql .= "$columnName ";
        $this->sql .= $this->translateColumn($columnType) . " ";
        if ($primary == true) {
            $this->sql .= "PRIMARY KEY ";
        }
        if ($notnull == true) {
            $this->sql .= "NOT NULL ";
        }
        if ($unique == true) {
            $this->sql .= "UNIQUE ";
        }
        if ($default != "") {
            $this->sql .= "DEFAULT '" . $default ."' ";
        }

        $affected = $this->pdo->exec($this->sql);

        if ($affected === false) {
            $errorMsg .= "Error adding column to $tableName\n";
            return \g7mzr\db\common\Common::raiseError($errorMsg, DB_ERROR);
        }
        return true;
    }


    /**
     * Function to create the SQL to drop a column from a table
     *
     * @param string $tableName  The name of the table being altered
     * @param string $columnName The Name of the column being dropped
     *
     * @return mixed true if table created or WEBTEMPLATE error
     *
     * @access public
     */
    public function dropColumn($tableName, $columnName)
    {
        $this->dbEcho("Dropping Column $tableName:$columnName");
        $dataerror = false;
        $this->sql = "ALTER TABLE $tableName DROP COLUMN $columnName CASCADE";

        $affected = $this->pdo->exec($this->sql);

        if ($affected === false) {
            $errorMsg = "Error dropping column on $tableName\n";
            return \g7mzr\db\common\Common::raiseError($errorMsg, DB_ERROR);
        }
        return true;
    }

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
    ) {
        $this->dbEcho("Altering Column $tableName:$columnName");
        $dataerror = false;
        $gotconstraints = false;

        $this->sql = "ALTER TABLE ONLY $tableName ";

        //Alter Column Type
        if ($attribute == 'type') {
            $newvalue = $this->translateColumn($value);
            $this->sql .= "ALTER COLUMN $columnName TYPE $newvalue";
        }

        // Change NOT NULL
        if ($attribute == 'notnull') {
            if ($setdrop == "set") {
                $this->sql .= "ALTER COLUMN $columnName SET NOT NULL";
            } else {
                $this->sql .= "ALTER COLUMN $columnName DROP NOT NULL";
            }
        }

        //Change DEFAULT Value
        if ($attribute == 'default') {
            if ($setdrop == 'set') {
                $this->sql .= "ALTER COLUMN $columnName SET DEFAULT '$value'";
            } else {
                $this->sql .= "ALTER COLUMN $columnName DROP DEFAULT";
            }
        }
        $affected = $this->pdo->exec($this->sql);

        if ($affected === false) {
            //print_r($this->pdo->errorInfo());
            $errorMsg = "Error amending column $columnName on $tableName\n";
            return \g7mzr\db\common\Common::raiseError($errorMsg, DB_ERROR);
        }
        return true;
    }
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
     * @return boolean true if Foreign Key Created WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function createFK(
        $tablename,
        $fkname,
        $columnname,
        $linktable,
        $linkcolumn
    ) {
        $this->dbEcho("Creating Foreign Key " . $fkname);
        $this->sql = "ALTER TABLE ONLY " . $tablename . " ADD CONSTRAINT";
        $this->sql .=  " " . $fkname . " FOREIGN KEY (" . $columnname . ")";
        $this->sql .= " REFERENCES " . $linktable . "(" . $linkcolumn . ")";
        $this->sql .= " ON DELETE CASCADE";
        $result = $this->pdo->exec($this->sql);

        // Always check that result is not an error
        if ($result === false) {
            $msg = gettext("Error Creating Foreign Keys.");
            return \g7mzr\db\common\Common::raiseError($msg, DB_ERROR);
        }
        return true;
    }

    /**
     * Function to drop a Foreign Key
     *
     * @param string $tableName The name of the table being worked on
     * @param string $keyName   The name of the Foreign Key being dropped
     *
     * @return boolean true if Forigen Key dropped WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function dropFK($tableName, $keyName)
    {

        $this->dbEcho("Dropping Foreign Key $keyName");
        $this->sql = "ALTER TABLE ONLY $tableName DROP CONSTRAINT $keyName CASCADE";
        $affected = $this->pdo->exec($this->sql);

        if ($affected === false) {
            $errorMsg = "Error deleteting Foreign Key $keyName on $tableName\n";
            return \g7mzr\db\common\Common::raiseError($errorMsg, DB_ERROR);
        }
        return true;
    }


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
    public function createIndex($tablename, $indexname, $column, $unique)
    {
        $this->dbEcho("Creating Index $indexname");
        $indexsaved = true;
        $this->sql = "CREATE ";
        if ($unique === true) {
            $this->sql .= " UNIQUE";
        }
        $this->sql .= " INDEX " . $indexname . " ON " . $tablename;
        $this->sql .= " USING btree (" . $column . ")";

        $result = $this->pdo->exec($this->sql);

        // Always check that result is not an error
        if ($result === false) {
            $indexsaved = false;
        }
        if ($indexsaved == true) {
            return true;
        } else {
            $msg = gettext("Error Creating Index.");
            return \g7mzr\db\common\Common::raiseError($msg, DB_ERROR);
        }
    }

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
    public function dropIndex($tableName, $indexName)
    {
        $this->dbEcho("Dropping Index $indexName");

        $this->sql = "DROP INDEX IF EXISTS " . $indexName . " CASCADE";

        $result = $this->pdo->exec($this->sql);

        // Always check that result is not an error
        if ($result === false) {
            $msg = gettext("Error Dropping Index ");
            $msg .= $tableName .":" .$indexName . "\n";
            return \g7mzr\db\common\Common::raiseError($msg, DB_ERROR);
        }
        return true;
    }


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
    public function saveSchema($version, $schema, $table = "schema")
    {
        $schemaSaved = false;

        $this->sql = "DELETE FROM $table";
        $this->stmt = $this->pdo->prepare($this->sql);
        $resultId = $this->stmt->execute();
        if ($resultId !== false) {
            $serial_schema = serialize($schema);
            $this->sql = "INSERT INTO $table ";
            $this->sql .= "(version, schema) values (?,?)";
            $this->stmt = $this->pdo->prepare(
                $this->sql,
                array('decimal', 'text')
            );
            if (!\g7mzr\db\common\Common::isError($this->stmt)) {
                $data = array($version, $serial_schema);
                $result = $this->stmt->execute($data);
                if ($result !== false) {
                    $schemaSaved = true;
                } else {
                    $msg = gettext("Error EXECUTING Schema INSERT");
                }
            } else {
                $msg = gettext("Error PREPARING Schema INSERT SQL");
            }
        } else {
            $msg = gettext("Error Deleteting Previous Schema. ");
            //$msg .= $resultId->getMessage();
        }
        if ($schemaSaved == true) {
            return true;
        } else {
            return \g7mzr\db\common\Common::raiseError($msg, DB_ERROR);
        }
    }


    /**
     * Function to retrieve the current schema from the database.
     *
     * @param string $table The table the schema is stored in.  Default is schema
     *
     * @return mixed array containing schema version and data or DB error
     *
     * @access public
     */
    public function getSchema($table = 'schema')
    {

        // Set Local Variables
        $resultArray = array();
        // Set the SQL to get the Schema
        $this->sql = "SELECT version, schema from " . $table;
        $this->stmt = $this->pdo->prepare($this->sql);
        $resultId = $this->stmt->execute();
        if ($resultId !== false) {
            if ($this->stmt->rowCount() > 0) {
                // Found a Schema Entry.  Get the data.
                $uao = $this->stmt->fetch(\PDO::FETCH_ASSOC, 0);
                $resultArray['version'] = $uao['version'];
                $resultArray['schema'] = unserialize($uao['schema']);
            } else {
                $msg = gettext('Schema Not Found');
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
        return $resultArray;
    }

    /**
     **************************************************************************
     ******************  END OF THE DATABASE CREATION SECTION   ***************
     **************************************************************************
     */


    /**************************************************************************
     *                  FUNCTIONS TO SENT STRAIGHT SQL to DATABASE
     **************************************************************************/

    /**
     * Function to send plain SQL Query to the Database
     *
     * @param string $sql SQL expression to send to database
     *
     * @return mixed array with results of DB Error
     */
    public function sqlQuery($sql)
    {
        $this->stmt = $this->pdo->prepare($sql);
        $resultID = $this->stmt->execute();
        if ($resultID !== false) {
            // No errors
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
     * ****************************************************************************
     * **                 START OF THE CLASS PRIVATE FUNCTIONS                   **
     * ****************************************************************************
     */

    /**
     * Function to print out messages.  If class is under unit test output is aborted
     *
     * @param string $msg The message to be displayed
     *
     * @return boolean Always true
     *
     * @access private
     */
    private function dbEcho($msg = "")
    {
        if (!isset($GLOBALS['unittest'])) {
            echo $msg. "\n";
        }
        return true;
    }

    /**
     * ****************************************************************************
     * **                   END OF THE CLASS PRIVATE FUNCTIONS                   **
     * ****************************************************************************
     */
}

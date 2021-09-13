<?php
/**
 * This file is part of PHP_Database_Client.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package db-php
 * @subpackage Schema Management
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/db-php/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\db;
/**
 * This Class is used to process the Database Schema Stored in json Files.
 *
 **/
class SchemaManager
{
    /**
     * property: newSchema
     * @var array
     * @access protected
     */
    protected $newSchema = null;

    /**
     * property: newSchemaVersion
     * @var integer
     * @access protected
     */
    protected $newSchemaversion = 0;

    /**
     * property: newSchemaName
     * @var String
     * @access protected
     */
    protected $newSchemaname = '';

    /**
     * property: currentSchema
     * @var array
     * @access protected
     */
    protected $currentSchema = null;

    /**
     * property: currentSchemaVersion
     * @var array
     * @access protected
     */
    protected $currentSchemaversion = 0;

    /**
     * property: currentSchemaName
     * @var String
     * @access protected
     */

    protected $currentSchemaname = 'main';
    /**
     * Property dbManager
     * @var \g7mzr\db\DBManager
     * @access protected
     */
    protected $dbManager = null;

    /**
     * Schema Class Constructor
     *
     * This class load and processed the Database Schema stores in json configuration
     * files.  It will either create a new schema or update and existing one.
     *
     * @param \g7mzr\db\DBManager $dbManager Pointer to Database Manager Class.
     *
     * @throws \g7mzr\db\common\DBException If unable to set Schema Mode.
     *
     * @access public
     */
    public function __construct(\g7mzr\db\DBManager $dbManager)
    {
        $this->dbManager = $dbManager;
        $result = $this->dbManager->setMode('schema');
        if (\g7mzr\db\common\Common::isError($result)) {
            throw new \g7mzr\db\common\DBException(
                'Unable to set dbManger Mode',
                1,
                $result->getMessage()
            );
        }
    }

    /**
     * Schema Class Destructor
     *
     * Carry out clean up operations for the class
     *
     * @access public
     */
    public function __destruct()
    {
    }

    /******************************************************************************
     *                           PUBLIC FUNCTIONS
     ******************************************************************************/

    /******************************************************************************
     *                 Function to Automatically Install or Update Schema
     ******************************************************************************/


    /**
     * Automatic Schema Change
     *
     * This function automatically installs or updates a Schema using the main class
     * functions.  It has been added to simplify schema management
     *
     * @param string  $filename   The fully qualified filename for the schema file.
     * @param boolean $newinstall If true this is new install.
     * @param string  $tablename  The name of the Schema Table.
     *
     * @return mixed True if the schema has been loaded okay.  DB Error otherwise
     *
     * @access public
     */
    public function autoSchemaManagement(string $filename, bool $newinstall = false, string $tablename = "schema")
    {
        $newSchemaLoaded = $this->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($newSchemaLoaded)) {
            return $newSchemaLoaded;
        }

        if ($newinstall === true) {
            $installResult = $this->processNewSchema();
        } else {
            $currentSchemaLoaded = $this->getSchema($tablename);
            if (\g7mzr\db\common\Common::isError($currentSchemaLoaded)) {
                return $currentSchemaLoaded;
            }

            if ($this->schemaChanged() === true) {
                $installResult = $this->processSchemaUpdate();
            } else {
                $installResult = true;
            }
        }

        if (\g7mzr\db\common\Common::isError($installResult)) {
            return $installResult;
        }

        $saveResult = $this->saveSchema($tablename);
        if (\g7mzr\db\common\Common::isError($saveResult)) {
            return $saveResult;
        }

        return true;
    }
    /******************************************************************************
     *                           FUNCTIONS FOR DEALING WITH NEW SCHEMA
     ******************************************************************************/
    /**
     * Load new Schema
     *
     * Load the new schema from the file system
     *
     * @param string $filename The fully qualified filename for the schema file.
     *
     * @return boolean True if the schema has been loaded okay.  DB Error otherwise
     *
     * @access public
     */
    public function loadNewSchema(string $filename)
    {
        // Set the checkvariables
        $fileloaded = true;
        $errorMsg = "";
        $jsonstr = false;
        $dataarray = null;

        //Load the json file
        $jsonfile = @fopen($filename, 'r');
        if ($jsonfile !== false) {
            $jsonstr = fread($jsonfile, filesize($filename));
            fclose($jsonfile);
        }
        if ($jsonstr === false) {
            $fileloaded = false;
            $errorMsg = gettext("Unable to load database schema file");
        } else {
            // Convert the json string to an array
            $dataarray = json_decode($jsonstr, true);
            if ($dataarray === null) {
                $fileloaded = false;
                $errorMsg = gettext("Unable to convert database schema file");
            }
        }
        if ($fileloaded == true) {
            $this->newSchema = $dataarray["tables"];
            $this->newSchemaversion = $dataarray["version"];
            $this->newSchemaname = $dataarray['name'];

            // Save the current Schema Name.  Needed to get the schema from the database
            $this->currentSchemaname = $dataarray['name'];
        }

        if ($fileloaded === true) {
            return true;
        } else {
            $err = \g7mzr\db\common\Common::raiseError($errorMsg);
            return $err;
        }
    }

    /**
     * Process new Schema
     *
     * This function processes the schema for a new database
     *
     * @return mixed True if schema processed okay.  DB Error other wise
     *
     * @access public
     */
    public function processNewSchema()
    {
        $errorMsg = '';
        $schemaResult = true;
        $this->dbManager->getSchemaDriver()->startTransaction();
        foreach ($this->newSchema as $tablename => $tabledata) {
            $tableresult = $this->processNewTable($tablename, $tabledata);
            if (\g7mzr\db\common\Common::isError($tableresult)) {
                $schemaResult = false;
                $errorMsg .= $tableresult->getMessage();
            }
        }
        $this->dbManager->getSchemaDriver()->endTransaction($schemaResult);
        if ($schemaResult === true) {
            return $schemaResult;
        } else {
            $err = \g7mzr\db\common\Common::raiseError($errorMsg);
            return $err;
        }
    }


    /**
     * Function to obtain the new Schema Version Number
     *
     * This function returns the new schema version number
     *
     * @return integer The new Schema Version Number
     *
     * @access public
     */
    public function getNewSchemaVersion()
    {
        return $this->newSchemaversion;
    }

    /**
     * Function to return the new Schema array
     *
     * This function returns the new schema as a PHP Array
     *
     * @return array The New Database Schema
     *
     * @access public
     */
    public function getNewSchema()
    {
        return $this->newSchema;
    }

    /**
     * Function to return the new Schema name
     *
     * This function returns the new schema name as a string
     *
     * @return string New Schema Name
     *
     * @access public
     */
    public function getNewSchemaName()
    {
        return $this->newSchemaname;
    }

    /******************************************************************************
     *                  FUNCTIONS FOR DEALING WITH A SCHEMA UPDATE
     ******************************************************************************/

    /**
     * Update Schema
     *
     * This function processes the schema update for an existing database
     *
     * @return mixed True if schema processed okay.  DB Error other wise
     *
     * @access public
     */
    public function processSchemaUpdate()
    {
        $errorMsg = '';
        $schemaResult = true;
        if ($this->currentSchema == null) {
            $errorMsg .= "Current Schema not initalised\n";
            $err = \g7mzr\db\common\Common::raiseError($errorMsg);
            return $err;
        }
        if ($this->newSchema == null) {
            $errorMsg .= "New Schema not initalised\n";
            $err = \g7mzr\db\common\Common::raiseError($errorMsg);
            return $err;
        }
        // Do the database changes from here
        $this->dbManager->getSchemaDriver()->startTransaction();

        // Check for changes to the tables
        $tableChangeResult = $this->processElementChange(
            $this->currentSchema,
            $this->newSchema
        );

        // Drop Tables to be dropped
        foreach ($tableChangeResult['DROP'] as $tablename) {
            $dropresult = $this->dbManager->getSchemaDriver()->dropTable($tablename);
            if (\g7mzr\db\common\Common::isError($dropresult)) {
                $schemaResult = false;
                $errorMsg .= $dropresult->getMessage();
            }
        }

        // CREATE THE NEW TABLES
        foreach ($tableChangeResult['CREATE'] as $tablename) {
            $createresult = $this->processNewTable(
                $tablename,
                $this->newSchema[$tablename]
            );
            if (\g7mzr\db\common\Common::isError($createresult)) {
                $schemaResult = false;
                $errorMsg .= $createresult->getMessage();
            }
        }

        // Walk Through the Tables to be Changed
        foreach ($tableChangeResult['CHANGE'] as $tablename) {
            $tablechange = $this->processTableChange($tablename);

            if (\g7mzr\db\common\Common::isError($tablechange)) {
                $schemaResult = false;
                $errorMsg .= $tablechange->getMessage();
            }
        }

        $this->dbManager->getSchemaDriver()->endTransaction($schemaResult);
        if ($schemaResult === true) {
            return $schemaResult;
        } else {
            $err = \g7mzr\db\common\Common::raiseError($errorMsg);
            return $err;
        }
    }


    /******************************************************************************
     *                  FUNCTIONS FOR DEALING WITH CURRENT SCHEMA
     ******************************************************************************/

    /**
     * Function to save the New SCHEMA into the database
     *
     * @param string $tablename The name of the Schema Table.
     *
     * @return True if schema processed okay.  DB Error other wise
     *
     * @access public
     */
    public function saveSchema(string $tablename = "schema")
    {
        $result = $this->dbManager->getSchemaDriver()->saveSchema(
            $this->newSchemaversion,
            $this->newSchema,
            $this->newSchemaname,
            $tablename
        );
        if (\g7mzr\db\common\Common::isError($result)) {
            $errorMsg = $result->getMessage();
            $err = \g7mzr\db\common\Common::raiseError($errorMsg);
            return $err;
        }
        return true;
    }

    /**
     * Function to get the New SCHEMA from the database
     *
     * @param string $tablename The name of the Schema Table.
     *
     * @return True if schema processed okay.  DB Error other wise
     *
     * @access public
     */
    public function getSchema(string $tablename = "schema")
    {
        $result = $this->dbManager->getSchemaDriver()->getSchema(
            $this->currentSchemaname,
            $tablename
        );
        if (\g7mzr\db\common\Common::isError($result)) {
            return $result;
        }
        $this->currentSchemaversion = $result['version'];
        $this->currentSchema = $result['schema'];
        $this->currentSchemaname = $result['name'];

        return true;
    }




    /**
     * Function to obtain the current Schema Version Number
     *
     * This function returns the current schema version number
     *
     * @return integer The new Schema Version Number
     *
     * @access public
     */
    public function getCurrentSchemaVersion()
    {
        return $this->currentSchemaversion;
    }

    /**
     * Function to return the current Schema array
     *
     * This function returns the current schema as a PHP Array
     *
     * @return array The Current Database Schema
     *
     * @access public
     */
    public function getcurrentSchema()
    {
        return $this->currentSchema;
    }
    /**
     * Function to return the current Schema name
     *
     * This function returns the current schema name as a string
     *
     * @return string Current Schema Name
     *
     * @access public
     */
    public function getCurrentSchemaName()
    {
        return $this->currentSchemaname;
    }

    /******************************************************************************
     *                          COMMON SCHEMA FUNCTIONS
     ******************************************************************************/

    /**
     * Function the check if Schema Versions are different
     *
     * @return boolean True if Schema Versions are different
     *
     * @throws \g7mzr\db\common\DBException If the two schema names do not match.
     *
     * @access public
     */
    public function schemaChanged()
    {
        $schemachanged = false;

        if ($this->currentSchemaname != $this->newSchemaname) {
            throw \g7mzr\db\common\DBException("Schema Names do not match", 1);
        }

        // Check the Version Numbers
        if ($this->currentSchemaversion != $this->newSchemaversion) {
            $schemachanged = true;
        }

        // Check the actual Schemas
        $newSchemaString = serialize($this->newSchema);
        $currentSchemaString = serialize($this->currentSchema);
        if (strcmp($newSchemaString, $currentSchemaString) != 0) {
            $schemachanged = true;
        }
        return $schemachanged;
    }



    /******************************************************************************
     *                           PRIVATE FUNCTIONS
     ******************************************************************************/

    /**
     * Process New Table
     *
     * Process a table and allow it to create a new table in the database
     *
     * @param string $tablename The name of the table.
     * @param array  $tabledef  The definition of the table.
     *
     * @return mixed True if table processed okay.  DB Error other wise
     *
     * @access private
     */
    private function processNewTable(string $tablename, array $tabledef)
    {
        $errorMsg = '';

        // Create the empty table
        $createTable = $this->dbManager->getSchemaDriver()->createtable($tablename);
        if (\g7mzr\db\common\Common::isError($createTable)) {
            $errorMsg = $createTable->getMessage();
            $err = \g7mzr\db\common\Common::raiseError($errorMsg);
            return $err;
        }

        // Add the colums to the new table
        if (array_key_exists('columns', $tabledef)) {
            $columnresult = $this->processNewColumns(
                $tablename,
                $tabledef['columns']
            );
            if (\g7mzr\db\common\Common::isError($columnresult)) {
                $errorMsg = $columnresult->getMessage();
                $err = \g7mzr\db\common\Common::raiseError($errorMsg);
                return $err;
            }
        } else {
            $errorMsg = gettext("No columns have been defined for table ");
            $errorMsg .= $tablename;
            $err = \g7mzr\db\common\Common::raiseError($errorMsg);
            return $err;
        }

        // Add the constraints to the table
        if (array_key_exists('fk', $tabledef)) {
            $fkresult = $this->processFK(
                $tablename,
                $tabledef['fk']
            );
            if (\g7mzr\db\common\Common::isError($fkresult)) {
                $errorMsg = $fkresult->getMessage();
                $err = \g7mzr\db\common\Common::raiseError($errorMsg);
                return $err;
            }
        }

        // Add the indexes to the table
        if (array_key_exists('index', $tabledef)) {
            $indexresult = $this->processIndex(
                $tablename,
                $tabledef['index']
            );
            if (\g7mzr\db\common\Common::isError($indexresult)) {
                $errorMsg = $indexresult->getMessage();
                $err = \g7mzr\db\common\Common::raiseError($errorMsg);
                return $err;
            }
        }


        // All passed okay so return true
        return true;
    }

    /**
     * Process new Columns
     *
     * Process the columns for a new table
     *
     * @param string $tablename  The name of the table being updated.
     * @param array  $columndata Array containing the column data.
     *
     * @return mixed True if table processed okay.  DB Error other wise
     *
     * @access private
     */
    private function processNewColumns(string $tablename, array $columndata)
    {
        foreach ($columndata as $columnname => $data) {
            $columntype = null;
            $notnull = false;
            $unique = false;
            $primary = false;
            $default = "";

            if (array_key_exists('type', $data)) {
                $columntype = $data['type'];
            }
            if (array_key_exists('primary', $data)) {
                $primary = $data['primary'];
            }
            if (array_key_exists('notnull', $data)) {
                $notnull = $data['notnull'];
            }
            if (array_key_exists('unique', $data)) {
                $unique = $data['unique'];
            }
            if (array_key_exists('default', $data)) {
                $default = $data['default'];
            }
            $columnresult = $this->dbManager->getSchemaDriver()->addColumn(
                $tablename,
                $columnname,
                $columntype,
                $primary,
                $notnull,
                $unique,
                $default
            );
            if (\g7mzr\db\common\Common::isError($columnresult)) {
                $errorMsg = $columnresult->getMessage();
                $err = \g7mzr\db\common\Common::raiseError($errorMsg);
                return $err;
            }
        }
        return true;
    }

    /**
     * Process Foreign Keys
     *
     * @param string $tablename The name of the table being updated.
     * @param array  $fkdata    Array containing the constraint data.
     *
     * @return mixed True if table processed okay.  DB Error other wise
     *
     * @access private
     */
    private function processFK(string $tablename, array $fkdata)
    {
        foreach ($fkdata as $fkname => $data) {
            $columnname = $data['columnname'];
            $linktable = $data['linktable'];
            $linkcolumn = $data['linkcolumn'];
            $fkresult = $this->dbManager->getSchemaDriver()->createFK(
                $tablename,
                $fkname,
                $columnname,
                $linktable,
                $linkcolumn
            );
            if (\g7mzr\db\common\Common::isError($fkresult)) {
                $errorMsg = $fkresult->getMessage();
                $err = \g7mzr\db\common\Common::raiseError($errorMsg);
                return $err;
            }
        }
        return true;
    }

    /**
     * Process Constraints
     *
     * @param string $tablename The name of the table being updated.
     * @param array  $indexdata Array containing the index data.
     *
     * @return mixed True if table processed okay.  DB Error other wise
     *
     * @access private
     */
    private function processIndex(string $tablename, array $indexdata)
    {
        foreach ($indexdata as $indexname => $data) {
            $indexresult = $this->dbManager->getSchemaDriver()->createIndex(
                $tablename,
                $indexname,
                $data["column"],
                $data['unique']
            );
            if (\g7mzr\db\common\Common::isError($indexresult)) {
                $errorMsg = $indexresult->getMessage();
                $err = \g7mzr\db\common\Common::raiseError($errorMsg);
                return $err;
            }
        }
        return true;
    }

    /**
     * Process Schema to identify Existing, New and Dropped tables
     *
     * @param array $currentElement The Current Schema element (Table, Column etc).
     * @param array $newElement     The new Schema element (Table, Column etc).
     *
     * @return mixed Multi dimensional Array of table names.  DB Error other wise
     *
     * @access private
     */
    private function processElementChange(array $currentElement, array $newElement)
    {
        $resultArray = array();
        $changearray = array();

        $newelementlist = array();
        $currentelementlist = array();
        foreach ($newElement as $elementname => $elementdata) {
            $newelementlist[] = $elementname;
        }
        foreach ($currentElement as $elementname => $elementdata) {
            $currentelementlist[] = $elementname;
        }
        $createarray = array_diff($newelementlist, $currentelementlist);
        $droparray = array_diff($currentelementlist, $newelementlist);
        $temparray = array_intersect($currentelementlist, $newelementlist);
        foreach ($temparray as $elementname) {
            $currentstring = \serialize($currentElement[$elementname]);
            $newstring = \serialize($newElement[$elementname]);
            if ($newstring != $currentstring) {
                $changearray[] = $elementname;
            }
        }
        $resultArray['DROP'] = $droparray;
        $resultArray['CREATE'] = $createarray;
        $resultArray['CHANGE'] = $changearray;
        return $resultArray;
    }


    /**
     * Process Table Update
     *
     * @param string $tablename The name of the table being updated.
     *
     * @return mixed True if table processed okay.  DB Error other wise
     *
     * @access private
     */
    private function processTableChange(string $tablename)
    {
        // Drop the Foreign Keys Associated with this table prior to making the
        // changes to the table
        if (array_key_exists('fk', $this->currentSchema[$tablename])) {
            foreach ($this->currentSchema[$tablename]['fk'] as $name => $data) {
                $fkdropresult = $this->dbManager->getSchemaDriver()->dropFK(
                    $tablename,
                    $name
                );
                if (\g7mzr\db\common\Common::isError($fkdropresult)) {
                    $errorMsg = $fkdropresult->getMessage();
                    $err = \g7mzr\db\common\Common::raiseError($errorMsg);
                    return $err;
                }
            }
        }

        // Process the Changes to the COLUMNS
        $columnChangeresult = $this->processElementChange(
            $this->currentSchema[$tablename]['columns'],
            $this->newSchema[$tablename]['columns']
        );

        // Drop the Columns
        foreach ($columnChangeresult['DROP'] as $columnName) {
            $dropresult = $this->dbManager->getSchemaDriver()->dropColumn(
                $tablename,
                $columnName
            );
            if (\g7mzr\db\common\Common::isError($dropresult)) {
                $errorMsg = $dropresult->getMessage();
                $err = \g7mzr\db\common\Common::raiseError($errorMsg);
                return $err;
            }
        }

        // Create New Columns
        foreach ($columnChangeresult['CREATE'] as $columnName) {
            $columndata = $this->newSchema[$tablename]['columns'][$columnName];
            $createresult = $this->processNewColumns(
                $tablename,
                array($columnName => $columndata)
            );
            if (\g7mzr\db\common\Common::isError($createresult)) {
                $errorMsg = $createresult->getMessage();
                $err = \g7mzr\db\common\Common::raiseError($errorMsg);
                return $err;
            }
        }

        // Modify existing Columns
        foreach ($columnChangeresult['CHANGE'] as $columnName) {
            $newdata = $this->newSchema[$tablename]['columns'][$columnName];
            $currentdata = $this->currentSchema[$tablename]['columns'][$columnName];
            $createresult = $this->processUpdateColumn(
                $tablename,
                $columnName,
                $newdata,
                $currentdata
            );
            if (\g7mzr\db\common\Common::isError($createresult)) {
                $errorMsg = $createresult->getMessage();
                $err = \g7mzr\db\common\Common::raiseError($errorMsg);
                return $err;
            }
        }


        //  RESTORE THE FOREIGN KEYS FOR THE TABLE
        if (array_key_exists('fk', $this->newSchema[$tablename])) {
            $fkresult = $this->processFK(
                $tablename,
                $this->newSchema[$tablename]['fk']
            );
            if (\g7mzr\db\common\Common::isError($fkresult)) {
                $errorMsg = $fkresult->getMessage();
                $err = \g7mzr\db\common\Common::raiseError($errorMsg);
                return $err;
            }
        }
        return true;
    }



    /**
     * Process new Columns
     *
     * Process the columns for a new table
     *
     * @param string $tablename   The name of the table being updated.
     * @param string $columnname  The name of the column being updated.
     * @param array  $newdata     Array containing the new column data.
     * @param array  $currentdata Array containing the current column data.
     *
     * @return mixed True if table processed okay.  DB Error other wise
     *
     * @access private
     */
    private function processUpdateColumn(
        string $tablename,
        string $columnname,
        array $newdata,
        array $currentdata
    ) {
        $changeddata = $this->processElementChange($currentdata, $newdata);
        foreach ($changeddata['DROP'] as $attribute) {
            $setdrop = 'drop';
            $columnresult = $this->dbManager->getSchemaDriver()->alterColumn(
                $tablename,
                $columnname,
                $attribute,
                $setdrop
            );
            if (\g7mzr\db\common\Common::isError($columnresult)) {
                $errorMsg = $columnresult->getMessage();
                $err = \g7mzr\db\common\Common::raiseError($errorMsg);
                return $err;
            }
        }

        foreach ($changeddata['CREATE'] as $attribute) {
            $setdrop = 'set';
            $value = null;
            if (($attribute == 'default') and ($newdata[$attribute] != "")) {
                $value = $newdata[$attribute];
            } elseif (($attribute == 'default') and ($newdata[$attribute] == "")) {
                $setdrop = "drop";
            } elseif ($newdata[$attribute] === false) {
                $setdrop = "drop";
            }
            if ($setdrop == "set") {
                $columnresult = $this->dbManager->getSchemaDriver()->alterColumn(
                    $tablename,
                    $columnname,
                    $attribute,
                    $setdrop,
                    $value
                );
                if (\g7mzr\db\common\Common::isError($columnresult)) {
                    $errorMsg = $columnresult->getMessage();
                    $err = \g7mzr\db\common\Common::raiseError($errorMsg);
                    return $err;
                }
            }
        }

        foreach ($changeddata['CHANGE'] as $attribute) {
            $setdrop = 'set';
            $value = null;
            if (($attribute == 'default') and ($newdata[$attribute] != "")) {
                $value = $newdata[$attribute];
            } elseif (($attribute == 'default') and ($newdata[$attribute] == "")) {
                $setdrop = "drop";
            } elseif ($attribute == 'type') {
                $value = $newdata[$attribute];
            } elseif ($newdata[$attribute] === false) {
                $setdrop = "drop";
            }

            $columnresult = $this->dbManager->getSchemaDriver()->alterColumn(
                $tablename,
                $columnname,
                $attribute,
                $setdrop,
                $value
            );
            if (\g7mzr\db\common\Common::isError($columnresult)) {
                $errorMsg = $columnresult->getMessage();
                $err = \g7mzr\db\common\Common::raiseError($errorMsg);
                return $err;
            }
        }
        return true;
    }
}

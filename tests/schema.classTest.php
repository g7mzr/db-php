<?php
/**
 * This file is part of DB
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace g7mzr\db\phpunit;

// Include the Class Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Include the Database DSN
require_once __DIR__ . '/../testconfig.php';

use PHPUnit\Framework\TestCase;

/**
 * Error Class Unit Tests
 *
 * @category g7mzr\db
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class SchemaTest extends TestCase
{
    /**
     * Schema Manager CLASS
     *
     * @var \g7mzr\db\SchemaManager
     */
    protected $schemamanager;

    /**
     * Database Manager
     *
     * @var \g7mzr\db\DBManager
     */
    protected $dbmanager;

    /**
     * PHPUNIT Database Support Functions
     *
     * @var \g7mzr\db\dbphpunitsupport\InterfaceDBUnitTestSupport
     */
    protected $dbphpunitsupport;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return null No return data
     */
    protected function setUp()
    {
        global $dsn;
        try {
            $this->dbmanager = new \g7mzr\db\DBManager(
                $dsn,
                $dsn["adminuser"],
                $dsn["adminpasswd"],
                true
            );
        } catch (\Throwable $ex) {
            echo $ex->getMessage();
            exit(1);
        }
        try {
            $this->schemamanager = new \g7mzr\db\SchemaManager($this->dbmanager);
        } catch (\Throwable $ex) {
            echo $ex->getMessage();
            exit(1);
        }
        $classname = 'g7mzr\\db\\dbphpunitsupport\\'. strtoupper($dsn['dbtype']);
        $classname .= 'DBUnitTestSupport';

        if (class_exists($classname)) {
            $this->dbphpunitsupport = new $classname($this->dbmanager);
        } else {
            echo $classname . "\n\n";
            echo "Unable to load DB Unitest Support for " . $dsn['dbtype'] ."\n\n";
            exit(1);
        }
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return null No return data
     */
    protected function tearDown()
    {
        global $dsn;
        $conStr = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
            $dsn["hostspec"],
            '5432',
            $dsn["databasename"],
            $dsn["adminuser"],
            $dsn["adminpasswd"]
        );

        // Create the PDO object and Connect to the database
        try {
            $localconn = new \PDO($conStr);
        } catch (\Exception $e) {
            //print_r($e->getMessage());
            throw new \Exception('Unable to connect to the database');
        }
        //$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $localconn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);

        // Drop all the created Tables
        $localconn->query("DROP TABLE IF EXISTS table1 CASCADE");
        $localconn->query("DROP TABLE IF EXISTS table2 CASCADE");
        $localconn->query("DROP TABLE IF EXISTS table3 CASCADE");
        $localconn->query("DROP TABLE IF EXISTS table4 CASCADE");

        unset($localconn);
        $this->dbmanager = null;
        $this->schemamanager = null;
    }

    /**
     * This function prepares the test schema for a number of the tests
     *
     * @param string $scheme The test Schema
     *
     * @return boolean True if the schema is loaded okay DB Error otherwise
     */
    private function prepareTestSchema($schema = "/testdata/schema.json")
    {
        $schemaprepared = true;

        // Install the Test Schema to the database
        $filename = __DIR__ . $schema;
        $loadnewresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadnewresult)) {
            $schemaprepared = false;
            $errorMsg = $loadnewresult->getMessage();
        }

        $schemaresult = $this->schemamanager->processNewSchema();
        if (\g7mzr\db\common\Common::isError($schemaresult)) {
            $schemaprepared = false;
            $errorMsg = $schemaresult->getMessage();
        }

        $schemaSaved = $this->schemamanager->saveSchema();
        if (is_a($schemaSaved, '\g7mzr\db\common\Error')) {
            $schemaprepared = false;
            $errorMsg = $schemaSaved->getMessage();
        }

        // Get the schema from the database
        $getResult = $this->schemamanager->getSchema();
        if (is_a($getResult, '\g7mzr\db\common\Error')) {
            $schemaprepared = false;
            $errorMsg = $getResult->getMessage();
        }

        if ($schemaprepared === true) {
            return true;
        } else {
            $err = \g7mzr\db\common\Common::raiseError($errorMsg);
            return $err;
        }
    }

    /**
     * This function tests the correct error is returned when the json schema file
     * is missing
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return null
     */
    public function testLoadNewSchemaFailFileNotFound()
    {
        $filename = __DIR__ . "/missingfile.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        $this->assertTrue(is_a($loadresult, "\g7mzr\db\common\Error"));
        $this->assertContains(
            "Unable to load database schema file",
            $loadresult->getMessage()
        );
    }

    /**
     * This function tests the correct error is returned when the json schema file
     * is not in the correct format
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return null
     */
    public function testLoadNewSchemaFailInvalidFormat()
    {
        $filename = __DIR__ . "/error.classTest.php";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        $this->assertTrue(is_a($loadresult, "\g7mzr\db\common\Error"));
        $this->assertContains(
            "Unable to convert database schema file",
            $loadresult->getMessage()
        );
    }

    /**
     * This function tests the json schema file can be loaded
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return null
     */
    public function testLoadNewSchemaPass()
    {
        $filename = __DIR__ . "/testdata/schema.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }
        $this->assertTrue($loadresult);
        $schema = $this->schemamanager->getNewSchema();
        $this->assertTrue($schema['table1']['columns']['name']['notnull']);
        $this->assertEquals(
            'users',
            $schema['table1']['fk']['fk_table1_customer']['linktable']
        );
    }

    /**
     * This function tests the json schema file can be loaded
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends  testLoadNewSchemaPass
     *
     * @return null
     */
    public function testCreateTablePass()
    {
        $filename = __DIR__ . "/testdata/schema.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }
        $this->assertTrue($loadresult);
        $schemaresult = $this->schemamanager->processNewSchema();
        if (\g7mzr\db\common\Common::isError($schemaresult)) {
            $this->fail($schemaresult->getMessage());
        }
        $this->assertTrue($schemaresult);
    }

    /**
     * This function tests that the schema module fails if no columns have been
     * defined
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends  testLoadNewSchemaPass
     *
     * @return null
     */
    public function testCreateTableFailNoColumns()
    {
        $filename = __DIR__ . "/testdata/nocolumnsschema.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        $schemaresult = $this->schemamanager->processNewSchema();
        $this->assertTrue(is_a($schemaresult, "\g7mzr\db\common\Error"));
        $msg = $schemaresult->getMessage();
        $this->assertContains("No columns have been defined for table", $msg);
    }

    /**
     * This function tests that the schema module fails if a duplicate table name is
     * used
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends  testLoadNewSchemaPass
     *
     * @return null
     */
    public function testCreateTableFailDuplicatetable()
    {
        $filename = __DIR__ . "/testdata/duplicatetableschema.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        $schemaresult = $this->schemamanager->processNewSchema();
        $this->assertTrue(is_a($schemaresult, "\g7mzr\db\common\Error"));
        $msg = $schemaresult->getMessage();
        $this->assertContains("Error Creating the Table", $msg);
    }

    /**
     * This function tests that the schema module fails if there is an error with
     * the column data being used.
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends  testLoadNewSchemaPass
     *
     * @return null
     */
    public function testCreateTableFailDuplicateColumn()
    {
        $filename = __DIR__ . "/testdata/errorcolumnschema.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        $schemaresult = $this->schemamanager->processNewSchema();
        $this->assertTrue(is_a($schemaresult, "\g7mzr\db\common\Error"));
        $msg = $schemaresult->getMessage();
        $this->assertContains("Error adding column to table1", $msg);
    }


    /**
     * This function tests that the schema module fails if there is an error with
     * the Foreign Key data being used.
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends  testLoadNewSchemaPass
     *
     * @return null
     */
    public function testCreateTableFailForeignKey()
    {
        $filename = __DIR__ . "/testdata/errorfkschema.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        $schemaresult = $this->schemamanager->processNewSchema();
        $this->assertTrue(is_a($schemaresult, "\g7mzr\db\common\Error"));
        $msg = $schemaresult->getMessage();
        $this->assertContains("Error Creating Foreign Keys", $msg);
    }

    /**
     * This function tests that the schema module fails if there is an error with
     * the index data being used.
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends  testLoadNewSchemaPass
     *
     * @return null
     */
    public function testCreateTableFailIndex()
    {
        $filename = __DIR__ . "/testdata/errorindexschema.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        $schemaresult = $this->schemamanager->processNewSchema();
        $this->assertTrue(is_a($schemaresult, "\g7mzr\db\common\Error"));
        $msg = $schemaresult->getMessage();
        $this->assertContains("Error Creating Index", $msg);
    }

    /*****************************************************************************
     *     SCHEMA MANAGEMENT TESTS
     ****************************************************************************/

    /**
     * This function tests Saving the Schema to the database
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return null
     */
    public function testsaveSchemaPass()
    {
        $filename = __DIR__ . "/testdata/schema.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }
        $saveresult = $this->schemamanager->saveSchema();
        if (is_a($saveresult, '\g7mzr\db\common\Error')) {
            $this->fail("Error Saving schema");
        }
        $this->assertTrue($saveresult);
    }

    /**
     * This function tests Saving the Schema to the database with an invalid table
     * name so that it fails
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return null
     */
    public function testsaveSchemaFail()
    {
        $filename = __DIR__ . "/testdata/schema.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }
        $saveresult = $this->schemamanager->saveSchema("dummy");
        $this->assertTrue(is_a($saveresult, '\g7mzr\db\common\Error'));
        $this->assertContains(
            "Error Deleteting Previous Schema",
            $saveresult->getMessage()
        );
    }

    /**
     * This function tests to see that an error is returned if the schema table is
     * empty
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testGetEmptySchema()
    {
        // Get the schema from the database
        $getResult = $this->schemamanager->getSchema('emptyschema');
        $this->assertTrue(is_a($getResult, '\g7mzr\db\common\Error'));
        $this->assertContains(
            "Schema Not Found",
            $getResult->getMessage()
        );
    }

    /**
     * This function tests to see that an error is returned if the schema table does
     * not exist
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testGetSchemaFailSQL()
    {
        // Get the schema from the database
        $getResult = $this->schemamanager->getSchema('dummy');
        $this->assertTrue(is_a($getResult, '\g7mzr\db\common\Error'));
        $this->assertContains(
            "SQL Query Error",
            $getResult->getMessage()
        );
    }


    /**
     * This function tests to see that the schema can be loaded from the database
     * into the SchemaManager Class
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testGetSchema()
    {
        // Load up the current test Schema for comparrison
        $filename = __DIR__ . "/testdata/schema.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        // Get the sche,a from the database
        $getResult = $this->schemamanager->getSchema();
        if (is_a($getResult, '\g7mzr\db\common\Error')) {
            $this->fail("Error Getting schema");
        }
        $this->assertTrue($getResult);
        $this->assertEquals(
            $this->schemamanager->getCurrentSchemaVersion(),
            $this->schemamanager->getNewSchemaVersion()
        );
        $this->assertEquals(
            serialize($this->schemamanager->getCurrentSchema()),
            serialize($this->schemamanager->getNewSchema())
        );
    }

    /**
     * This function tests to see if there are changes between the two schema
     * versions.  No Change
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testSchemaNoChange()
    {
        // Load up the current test Schema for comparrison
        $filename = __DIR__ . "/testdata/schema.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        // Get the sche,a from the database
        $getResult = $this->schemamanager->getSchema();
        if (is_a($getResult, '\g7mzr\db\common\Error')) {
            $this->fail("Error Getting schema");
        }
        $testResult = $this->schemamanager->schemaChanged();
        $this->assertFalse($testResult);
    }

    /**
     * This function tests to see if there are changes between the two schema
     * versions.  Schema Changed
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testSchemaChanged()
    {
        // Load up the current test Schema for comparrison
        $filename = __DIR__ . "/testdata/schemav2.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        // Get the schema from the database
        $getResult = $this->schemamanager->getSchema();
        if (is_a($getResult, '\g7mzr\db\common\Error')) {
            $this->fail("Error Getting schema");
        }
        $testResult = $this->schemamanager->schemaChanged();
        $this->assertTrue($testResult);
    }

    /******************************************************************************
     *          SCHEMA CHANGE TESTS
     ******************************************************************************/

    /**
     * This function tests to see if an error returms if there is no current
     * schema
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testProcessSchemaUpdateFAilNoCurrentSchema()
    {
        // Process the update to Fail No Current Schema
        $testResult = $this->schemamanager->processSchemaUpdate();
        $this->assertTrue(is_a($testResult, '\g7mzr\db\common\Error'));
        $this->assertContains(
            "Current Schema not initalised",
            $testResult->getMessage()
        );
    }

    /**
     * This function tests to see if an error returms if there is no current
     * schema
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testProcessSchemaUpdateFAilNoNewchema()
    {
        // Get the schema from the database
        $getResult = $this->schemamanager->getSchema();
        if (is_a($getResult, '\g7mzr\db\common\Error')) {
            $this->fail("Error Getting schema");
        }

        // Process the update to Fail No New Schema
        $testResult = $this->schemamanager->processSchemaUpdate();
        $this->assertTrue(is_a($testResult, '\g7mzr\db\common\Error'));
        $this->assertContains(
            "New Schema not initalised",
            $testResult->getMessage()
        );
    }


    /**
     * This function tests to see if Table 3 can be dropped
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testProcessSchemaDropTable3Pass()
    {
        // Load the Current Schema for Testing
        $prepareResult = $this->prepareTestSchema();
        if (\g7mzr\db\common\Common::isError($prepareResult)) {
            $this->Fail($prepareResult->getMessage());
        }

        // Load up the New test Schema for comparrison
        $filename = __DIR__ . "/testdata/schema-droptable.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        // Check That Table 3 exists

        $tableexists = $this->dbphpunitsupport->tableExists('table3');
        if (\g7mzr\db\common\Common::isError($tableexists)) {
            $this->fail($tableexists->getMessage());
        }
        $this->assertTrue($tableexists);

        // Process the update
        $testResult = $this->schemamanager->processSchemaUpdate();
        if (is_a($testResult, '\g7mzr\db\common\Error')) {
            $this->fail($testResult->getMessage());
        }
        $this->assertTrue($testResult);

        // Check that Table 3 has gone
        $tableexists = $this->dbphpunitsupport->tableExists('table3');
        if (\g7mzr\db\common\Common::isError($tableexists)) {
            $this->fail($tableexists->getMessage());
        }
        $this->assertFalse($tableexists);
    }


    /**
     * This function tests to check that an error Occurs if Table 3 is dropped twice
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testProcessSchemaDropTable3Twice()
    {
        // Load the Current Schema for Testing
        $prepareResult = $this->prepareTestSchema();
        if (\g7mzr\db\common\Common::isError($prepareResult)) {
            $this->Fail($prepareResult->getMessage());
        }

        // Load up the New test Schema for comparrison
        $filename = __DIR__ . "/testdata/schema-droptable.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        // Check That Table 3 exists
        $tableexists = $this->dbphpunitsupport->tableExists('table3');
        if (\g7mzr\db\common\Common::isError($tableexists)) {
            $this->fail($tableexists->getMessage());
        }
        $this->assertTrue($tableexists);

        // Process the update
        $testResult = $this->schemamanager->processSchemaUpdate();
        if (is_a($testResult, '\g7mzr\db\common\Error')) {
            $this->fail($testResult->getMessage());
        }
        $this->assertTrue($testResult);

        // Check that Table 3 has gone
        $tableexists = $this->dbphpunitsupport->tableExists('table3');
        if (\g7mzr\db\common\Common::isError($tableexists)) {
            $this->fail($tableexists->getMessage());
        }
        $this->assertFalse($tableexists);

        // Process the update for a second time.
        $testResult = $this->schemamanager->processSchemaUpdate();
        $this->assertTrue(is_a($testResult, '\g7mzr\db\common\Error'));
        $this->assertEquals(
            "Error Dropping Table table3\n",
            $testResult->getMessage()
        );
    }

    /**
     * This function tests to see if Table 4 can be created
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testProcessSchemaCreateTable4Pass()
    {
        // Load the Current Schema for Testing
        $prepareResult = $this->prepareTestSchema();
        if (\g7mzr\db\common\Common::isError($prepareResult)) {
            $this->Fail($prepareResult->getMessage());
        }

        // Load up the New test Schema for comparrison
        $filename = __DIR__ . "/testdata/schema-createtable.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        $tableexists = $this->dbphpunitsupport->tableExists('table4');
        if (\g7mzr\db\common\Common::isError($tableexists)) {
            $this->fail($tableexists->getMessage());
        }
        $this->assertFalse($tableexists);

        // Process the update
        $testResult = $this->schemamanager->processSchemaUpdate();
        if (is_a($testResult, '\g7mzr\db\common\Error')) {
            $this->fail($testResult->getMessage());
        }
        $this->assertTrue($testResult);

        // Check That Table 4 exists
        $tableexists = $this->dbphpunitsupport->tableExists('table4');
        if (\g7mzr\db\common\Common::isError($tableexists)) {
            $this->fail($tableexists->getMessage());
        }
        $this->assertTrue($tableexists);
    }

    /**
     * This function tests to check an error occurs if table 4 is created twice
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testProcessSchemaCreateTable4Duplicate()
    {
        // Load the Current Schema for Testing
        $prepareResult = $this->prepareTestSchema();
        if (\g7mzr\db\common\Common::isError($prepareResult)) {
            $this->Fail($prepareResult->getMessage());
        }

        // Load up the New test Schema for comparrison
        $filename = __DIR__ . "/testdata/schema-createtable.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        // Check that Table 4 Does Not Exist
        $tableexists = $this->dbphpunitsupport->tableExists('table4');
        if (\g7mzr\db\common\Common::isError($tableexists)) {
            $this->fail($tableexists->getMessage());
        }
        $this->assertFalse($tableexists);

        // Process the update
        $testResult = $this->schemamanager->processSchemaUpdate();
        if (is_a($testResult, '\g7mzr\db\common\Error')) {
            $this->fail($testResult->getMessage());
        }
        $this->assertTrue($testResult);

        // Check That Table 4 exists
        $tableexists = $this->dbphpunitsupport->tableExists('table4');
        if (\g7mzr\db\common\Common::isError($tableexists)) {
            $this->fail($tableexists->getMessage());
        }
        $this->assertTrue($tableexists);

        // Process the update for a second time
        $testResult = $this->schemamanager->processSchemaUpdate();
        $this->assertTrue(is_a($testResult, '\g7mzr\db\common\Error'));
        $this->assertEquals("Error Creating the Table\n", $testResult->getMessage());
    }


    /**
     * This function tests to see if Table 3 column flag can be dropped
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testProcessSchemaDropTable3ColumnFlagPass()
    {
        // Load the Current Schema for Testing
        $prepareResult = $this->prepareTestSchema();
        if (\g7mzr\db\common\Common::isError($prepareResult)) {
            $this->Fail($prepareResult->getMessage());
        }

        // Load up the New test Schema for comparrison
        $filename = __DIR__ . "/testdata/schema-dropcolumn.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        // Check That Table 3 column customer exists

        $columnexists = $this->dbphpunitsupport->columnExists('table3', 'flag');
        if (\g7mzr\db\common\Common::isError($columnexists)) {
            $this->fail($columnexists->getMessage());
        }
        $this->assertTrue($columnexists);

        // Process the update
        $testResult = $this->schemamanager->processSchemaUpdate();
        if (is_a($testResult, '\g7mzr\db\common\Error')) {
            $this->fail($testResult->getMessage());
        }
        $this->assertTrue($testResult);

        // Check that Table 3 Column Customer has gone
        $columnexists = $this->dbphpunitsupport->columnExists('table3', 'flag');
        if (\g7mzr\db\common\Common::isError($columnexists)) {
            $this->fail($columnexists->getMessage());
        }
        $this->assertFalse($columnexists);
    }


    /**
     * This function tests to see if dropping Table 3 column flag twice causes an
     * error
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testProcessSchemaDropTable3ColumnFlagDropTwiceFail()
    {
        // Load the Current Schema for Testing
        $prepareResult = $this->prepareTestSchema();
        if (\g7mzr\db\common\Common::isError($prepareResult)) {
            $this->Fail($prepareResult->getMessage());
        }

        // Load up the New test Schema for comparrison
        $filename = __DIR__ . "/testdata/schema-dropcolumn.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        // Check That Table 3 column customer exists

        $columnexists = $this->dbphpunitsupport->columnExists('table3', 'flag');
        if (\g7mzr\db\common\Common::isError($columnexists)) {
            $this->fail($columnexists->getMessage());
        }
        $this->assertTrue($columnexists);

        // Process the update
        $testResult = $this->schemamanager->processSchemaUpdate();
        if (is_a($testResult, '\g7mzr\db\common\Error')) {
            $this->fail($testResult->getMessage());
        }
        $this->assertTrue($testResult);

        // Check that Table 3 Column Customer has gone
        $columnexists = $this->dbphpunitsupport->columnExists('table3', 'flag');
        if (\g7mzr\db\common\Common::isError($columnexists)) {
            $this->fail($columnexists->getMessage());
        }
        $this->assertFalse($columnexists);

        // Process the update for the second time
        $testResult = $this->schemamanager->processSchemaUpdate();
        $this->assertTrue(is_a($testResult, '\g7mzr\db\common\Error'));

        $this->assertEquals(
            "Error dropping column on table3\n",
            $testResult->getMessage()
        );
    }

    /**
     * This function tests to see if column phpunit can be created on table 3
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testProcessSchemaCreateTable3ColumnPhpunit()
    {
        // Load the Current Schema for Testing
        $prepareResult = $this->prepareTestSchema();
        if (\g7mzr\db\common\Common::isError($prepareResult)) {
            $this->Fail($prepareResult->getMessage());
        }

        // Load up the New test Schema for comparrison
        $filename = __DIR__ . "/testdata/schema-createcolumn.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        // Check That Table 3 column customer exists

        $columnexists = $this->dbphpunitsupport->columnExists('table3', 'phpunit');
        if (\g7mzr\db\common\Common::isError($columnexists)) {
            $this->fail($columnexists->getMessage());
        }
        $this->assertFalse($columnexists);

        // Process the update
        $testResult = $this->schemamanager->processSchemaUpdate();
        if (is_a($testResult, '\g7mzr\db\common\Error')) {
            $this->fail($testResult->getMessage());
        }
        $this->assertTrue($testResult);

        // Check that Table 3 Column Customer has gone
        $columnexists = $this->dbphpunitsupport->columnExists('table3', 'phpunit');
        if (\g7mzr\db\common\Common::isError($columnexists)) {
            $this->fail($columnexists->getMessage());
        }
        $this->assertTrue($columnexists);
    }


    /**
     * This function tests to see if column phpunit can be created on table 3
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testProcessSchemaCreateTable3ColumnPhpunitFail()
    {
        // Load the Current Schema for Testing
        $prepareResult = $this->prepareTestSchema();
        if (\g7mzr\db\common\Common::isError($prepareResult)) {
            $this->Fail($prepareResult->getMessage());
        }

        // Load up the New test Schema for comparrison
        $filename = __DIR__ . "/testdata/schema-createcolumn.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        // Check That Table 3 column customer exists

        $columnexists = $this->dbphpunitsupport->columnExists('table3', 'phpunit');
        if (\g7mzr\db\common\Common::isError($columnexists)) {
            $this->fail($columnexists->getMessage());
        }
        $this->assertFalse($columnexists);

        // Process the update
        $testResult = $this->schemamanager->processSchemaUpdate();
        if (is_a($testResult, '\g7mzr\db\common\Error')) {
            $this->fail($testResult->getMessage());
        }
        $this->assertTrue($testResult);

        // Check that Table 3 Column Customer has gone
        $columnexists = $this->dbphpunitsupport->columnExists('table3', 'phpunit');
        if (\g7mzr\db\common\Common::isError($columnexists)) {
            $this->fail($columnexists->getMessage());
        }
        $this->assertTrue($columnexists);

        // Process the update for the second time
        $testResult = $this->schemamanager->processSchemaUpdate();
        $this->assertTrue(is_a($testResult, '\g7mzr\db\common\Error'));

        $this->assertEquals(
            "Error adding column to table3\n",
            $testResult->getMessage()
        );
    }


    /**
     * This function tests to see if column description can be set not null
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testProcessSchemaIsNullableYes()
    {
        // Load the Current Schema for Testing
        $prepareResult = $this->prepareTestSchema();
        if (\g7mzr\db\common\Common::isError($prepareResult)) {
            $this->Fail($prepareResult->getMessage());
        }

        // Load up the New test Schema for comparrison
        $filename = __DIR__ . "/testdata/schema-isnullableyes.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        // Check That Table 3 column customer exists

        $columnisnullable = $this->dbphpunitsupport->columnIsNullable(
            'table3',
            'description'
        );
        if (\g7mzr\db\common\Common::isError($columnisnullable)) {
            $this->fail($columnisnullable->getMessage());
        }
        $this->assertFalse($columnisnullable);

        // Process the update
        $testResult = $this->schemamanager->processSchemaUpdate();
        if (is_a($testResult, '\g7mzr\db\common\Error')) {
            $this->fail($testResult->getMessage());
        }
        $this->assertTrue($testResult);

        // Check that Table 3 Column Customer has gone
        $columnisnullable = $this->dbphpunitsupport->columnIsNullable(
            'table3',
            'description'
        );
        if (\g7mzr\db\common\Common::isError($columnisnullable)) {
            $this->fail($columnisnullable->getMessage());
        }
        $this->assertTrue($columnisnullable);
    }

    /**
     * This function tests to see if column price can have not null removed
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testProcessSchemaIsNullableNO()
    {
        // Load the Current Schema for Testing
        $prepareResult = $this->prepareTestSchema();
        if (\g7mzr\db\common\Common::isError($prepareResult)) {
            $this->Fail($prepareResult->getMessage());
        }

        // Load up the New test Schema for comparrison
        $filename = __DIR__ . "/testdata/schema-isnullableno.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        // Check That Table 3 column customer exists

        $columnisnullable = $this->dbphpunitsupport->columnIsNullable(
            'table3',
            'price'
        );
        if (\g7mzr\db\common\Common::isError($columnisnullable)) {
            $this->fail($columnisnullable->getMessage());
        }
        $this->assertTrue($columnisnullable);

        // Process the update
        $testResult = $this->schemamanager->processSchemaUpdate();
        if (is_a($testResult, '\g7mzr\db\common\Error')) {
            $this->fail($testResult->getMessage());
        }
        $this->assertTrue($testResult);

        // Check that Table 3 Column Customer has gone
        $columnisnullable = $this->dbphpunitsupport->columnIsNullable(
            'table3',
            'price'
        );
        if (\g7mzr\db\common\Common::isError($columnisnullable)) {
            $this->fail($columnisnullable->getMessage());
        }
        $this->assertFalse($columnisnullable);
    }


    /**
     * This function tests to see if column description type can be changed
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testProcessSchemaColumnTYpe()
    {
        // Load the Current Schema for Testing
        $prepareResult = $this->prepareTestSchema();
        if (\g7mzr\db\common\Common::isError($prepareResult)) {
            $this->Fail($prepareResult->getMessage());
        }

        // Load up the New test Schema for comparrison
        $filename = __DIR__ . "/testdata/schema-type.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        // Check That Table 3 column customer exists

        $columntype = $this->dbphpunitsupport->columnType(
            'table3',
            'description',
            'character varying'
        );
        if (\g7mzr\db\common\Common::isError($columntype)) {
            $this->fail($columntype->getMessage());
        }
        $this->assertTrue($columntype);

        // Process the update
        $testResult = $this->schemamanager->processSchemaUpdate();
        if (is_a($testResult, '\g7mzr\db\common\Error')) {
            $this->fail($testResult->getMessage());
        }
        $this->assertTrue($testResult);

        // Check that Table 3 Column Customer has gone
        $columnisnullable = $this->dbphpunitsupport->columntype(
            'table3',
            'price',
            'text'
        );
        if (\g7mzr\db\common\Common::isError($columnisnullable)) {
            $this->fail($columnisnullable->getMessage());
        }
        $this->assertFalse($columnisnullable);
    }

    /**
     * This function tests to see if a default Value can be removed from a column
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testProcessSchemaRemoveDefault()
    {
        // Load the Current Schema for Testing
        $prepareResult = $this->prepareTestSchema();
        if (\g7mzr\db\common\Common::isError($prepareResult)) {
            $this->Fail($prepareResult->getMessage());
        }

        // Load up the New test Schema for comparrison
        $filename = __DIR__ . "/testdata/schema-removedefault.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        // Check That Table 3 column customer exists

        $defaultmatch = $this->dbphpunitsupport->columnDefault(
            'table3',
            'flag',
            "'Y'::bpchar"
        );
        if (\g7mzr\db\common\Common::isError($defaultmatch)) {
            $this->fail($defaultmatch->getMessage());
        }
        $this->assertTrue($defaultmatch);

        // Process the update
        $testResult = $this->schemamanager->processSchemaUpdate();
        if (is_a($testResult, '\g7mzr\db\common\Error')) {
            $this->fail($testResult->getMessage());
        }
        $this->assertTrue($testResult);

        // Check that Table 3 Column Customer has gone
        $defaultmatch = $this->dbphpunitsupport->columnDefault(
            'table3',
            'flag',
            ''
        );
        if (\g7mzr\db\common\Common::isError($defaultmatch)) {
            $this->fail($defaultmatch->getMessage());
        }
        $this->assertTrue($defaultmatch);
    }


    /**
     * This function tests to see if a default Value can be removed from a column
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testProcessSchemaSetDefault()
    {
        // Load the Current Schema for Testing
        $prepareResult = $this->prepareTestSchema();
        if (\g7mzr\db\common\Common::isError($prepareResult)) {
            $this->Fail($prepareResult->getMessage());
        }

        // Load up the New test Schema for comparrison
        $filename = __DIR__ . "/testdata/schema-adddefault.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        // Check That Table 3 column customer exists

        $defaultmatch = $this->dbphpunitsupport->columnDefault(
            'table3',
            'flag',
            "'Y'::bpchar"
        );
        if (\g7mzr\db\common\Common::isError($defaultmatch)) {
            $this->fail($defaultmatch->getMessage());
        }
        $this->assertTrue($defaultmatch);

        // Process the update
        $testResult = $this->schemamanager->processSchemaUpdate();
        if (is_a($testResult, '\g7mzr\db\common\Error')) {
            $this->fail($testResult->getMessage());
        }
        $this->assertTrue($testResult);

        // Check that Table 3 Column Customer has gone
        $defaultmatch = $this->dbphpunitsupport->columnDefault(
            'table3',
            'flag',
            "'T'::bpchar"
        );
        if (\g7mzr\db\common\Common::isError($defaultmatch)) {
            $this->fail($defaultmatch->getMessage());
        }
        $this->assertTrue($defaultmatch);
    }


    /**
     * This function tests to see that an error is reported if a FK fails to delete
     * during a schema update
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testProcessSchemaFKDeleteFail()
    {
        // Load the Current Schema for Testing
        $prepareResult = $this->prepareTestSchema();
        if (\g7mzr\db\common\Common::isError($prepareResult)) {
            $this->Fail($prepareResult->getMessage());
        }

        // Load the Schem with the dummy FK
        $filename = __DIR__ . "/testdata/schema-fkdeletefail.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }

        $schemaSaved = $this->schemamanager->saveSchema();
        if (is_a($schemaSaved, '\g7mzr\db\common\Error')) {
            $this->fail($schemaSaved->getMessage());
        }

        $getschema = $this->schemamanager->getSchema();
        if (is_a($getschema, '\g7mzr\db\common\Error')) {
            $this->fail($getschema->getMessage());
        }

        // Load up the New test Schema for comparrison
        $filename = __DIR__ . "/testdata/schema.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }
        // Process the update
        $testResult = $this->schemamanager->processSchemaUpdate();
        $this->assertTrue(is_a($testResult, '\g7mzr\db\common\Error'));
        $this->assertContains(
            "Error deleteting Foreign Key fk_table1_dummy on table1",
            $testResult->getMessage()
        );
    }

    /**
     * This function tests to see that an error is reported if a FK fails to create
     * during a schema update
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @depends testsaveSchemaPass
     *
     * @return null
     */
    public function testProcessSchemaFKCreateFail()
    {
        // Load the Current Schema for Testing
        $prepareResult = $this->prepareTestSchema();
        if (\g7mzr\db\common\Common::isError($prepareResult)) {
            $this->Fail($prepareResult->getMessage());
        }

        // Load up the New test Schema for comparrison
        $filename = __DIR__ . "/testdata/schema-fkcreatefail.json";
        $loadresult = $this->schemamanager->loadNewSchema($filename);
        if (\g7mzr\db\common\Common::isError($loadresult)) {
            $this->fail($loadresult->getMessage());
        }
        // Process the update
        $testResult = $this->schemamanager->processSchemaUpdate();
        $this->assertTrue(is_a($testResult, '\g7mzr\db\common\Error'));
        $this->assertContains(
            "Error Creating Foreign Keys",
            $testResult->getMessage()
        );
    }
}

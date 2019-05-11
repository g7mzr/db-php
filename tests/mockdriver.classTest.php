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

namespace g7mzr\db\phpunit;

// Include the Class Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Include the Database DSN
require_once __DIR__ . '/../testconfig.php';

use PHPUnit\Framework\TestCase;

/**
 * Mock Database Driver Class Unit Tests
 *
 * This module is used to test the functionality of the MOCK database driver.
 *
 */
class MockDriverTest extends TestCase
{
    /**
     * DB CLASS
     *
     * @var \g7mzr\db\DB
     */
    protected $object;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @throws \Exception If unable to connect to the database.
     *
     * @return void No return data
     */
    protected function setUp(): void
    {
        global $dsn;
        $localdsn = $dsn;
        $localdsn['dbtype'] = 'mock';

        try {
            $this->object = new \g7mzr\db\DBManager($localdsn, "", "", true);
        } catch (\throwable $e) {
            throw new \Exception('Unable to connect to the database');
        }

        $result = $this->object->setMode("datadriver");
        if (\g7mzr\db\common\Common::isError($result)) {
            print_r($result);
            exit(1);
        }
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void No return data
     */
    protected function tearDown(): void
    {
        $this->object->getDataDriver()->disconnect();
    }

    /**
     * This function tests the class Destructor
     *
     * @group unittest
     * @group error
     *
     * @throws \Exception If unable to connect to the database.
     *
     * @return void No return data
     */
    public function testDestruct()
    {
        global $dsn;
        $localdsn = $dsn;
        $localdsn['dbtype'] = 'mock';
        try {
            $this->object = new \g7mzr\db\DBManager($dsn, "", "", true);
        } catch (\throwable $e) {
            throw new \Exception('Unable to connect to the database');
        }

        $result = $this->object->setMode("datadriver");
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail('Unable to create Class');
        }
        $this->object->getDataDriver()->disconnect();
        $this->assertTrue(true);
    }

    /**
     * This function tests that the DBVersion function passes
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testDBVersionPass()
    {
        $functions = array(
            'testDBVersionPass' => array(
                'pass' => true
            )
        );
        $data = array('version' => 'Mock 1.0.0');
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->getDBVersion();
        $this->assertStringContainsString('Mock 1.0.0', $result);
    }

    /**
     * This function tests that the DBVersion function failse
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testDBVersionFail()
    {
        // Test with no Control Data
        $result = $this->object->getDataDriver()->getDBVersion();
        $this->assertStringContainsString('Error Getting Database Version', $result);

        //test with pass element missing from control data
        $functions = array(
            'testDBVersionFail' => array(
                'passed' => false
            )
        );
        $data = array('version' => 'Mock 1.0.0');
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->getDBVersion();
        $this->assertStringContainsString('Error Getting Database Version', $result);

        //test with control data set to fail
        $functions = array(
            'testDBVersionFail' => array(
                'pass' => false
            )
        );
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->getDBVersion();
        $this->assertStringContainsString('Error Getting Database Version', $result);
    }

    /**
     * This function tests that the startTransaction Passes
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function teststartTransactionPass()
    {
        $functions = array(
            'teststartTransactionPass' => array(
                'starttransaction' => true
            )
        );
        $data = array();
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->startTransaction();
        $this->assertTrue($result);
    }

    /**
     * This function tests that the startTransaction Fails
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function teststartTransactionFail()
    {
        // No Control Data
        $result = $this->object->getDataDriver()->startTransaction();
        $this->assertFalse($result);

         // starttransaction missing from control data
        $functions = array(
            'teststartTransactionFail' => array(
                'starttransactions' => false
            )
        );
        $data = array();
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->startTransaction();
        $this->assertFalse($result);

       // Control Data to Fail
        $functions = array(
            'teststartTransactionFail' => array(
                'starttransaction' => false
            )
        );
        $data = array();
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->startTransaction();
        $this->assertFalse($result);
    }

    /**
     * This function tests that the endTransaction Passes
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testendTransactionPass()
    {
        $functions = array(
            'testendTransactionPass' => array(
                'endtransaction' => true
            )
        );
        $data = array();
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->endTransaction(true);
        $this->assertTrue($result);
    }

    /**
     * This function tests that the endTransaction Fails
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testendTransactionFail()
    {
        // No Control Data
        $result = $this->object->getDataDriver()->endTransaction(true);
        $this->assertFalse($result);

         // starttransaction missing from control data
        $functions = array(
            'testendTransactionFail' => array(
                'endtransactions' => false
            )
        );
        $data = array();
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->startTransaction();
        $this->assertFalse($result);

       // Control Data to Fail
        $functions = array(
            'teststartTransactionFail' => array(
                'endtransaction' => false
            )
        );
        $data = array();
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->startTransaction();
        $this->assertFalse($result);
    }

    /**
     * This function tests that the dbInsert Passes
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testdbInsertPass()
    {
        $functions = array(
            'testdbInsertPass' => array(
                'pass' => true
            )
        );
        $data = array();
        $this->object->getDataDriver()->control($functions, $data);
        $insertdata = array("user" => "phpunit");
        $result = $this->object->getDataDriver()->dbInsert('users', $insertdata);
        $this->assertTrue($result);
    }

    /**
     * This function tests that the dbInsert Passes
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testdbInsertFail()
    {
        // No Control Data
        $insertdata = array("user" => "phpunit");
        $result = $this->object->getDataDriver()->dbInsert('users', $insertdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertStringContainsString("SQL Query Error", $result->getMessage());

        // pass key missing from control data
        $functions = array(
            'testdbInsertPass' => array(
                'passed' => true
            )
        );
        $data = array();
        $this->object->getDataDriver()->control($functions, $data);
        $insertdata = array("user" => "phpunit");
        $result = $this->object->getDataDriver()->dbInsert('users', $insertdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertStringContainsString("SQL Query Error", $result->getMessage());

        // pass key set to false
        $functions = array(
            'testdbInsertPass' => array(
                'pass' => false
            )
        );
        $data = array();
        $this->object->getDataDriver()->control($functions, $data);
        $insertdata = array("user" => "phpunit");
        $result = $this->object->getDataDriver()->dbInsert('users', $insertdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertStringContainsString("SQL Query Error", $result->getMessage());
    }

    /**
     * This function tests that the dbInsertID Passes
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testdbInsertIDPass()
    {
        // Test pass with no record id passed via control
        $functions = array(
            'testdbInsertIDPass' => array(
                'id' => true
            )
        );
        $data = array();
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->dbInsertid(
            'tableName',
            'idfield',
            'srchfield',
            'srchdata'
        );
        $this->assertEquals(1, $result);

        // Test pass with record id passed via control
        $functions = array(
            'testdbInsertIDPass' => array(
                'id' => true
            )
        );
        $data = array('srchdata' => 6);
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->dbInsertid(
            'tableName',
            'idfield',
            'srchfield',
            'srchdata'
        );
        $this->assertEquals(6, $result);
    }

    /**
     * This function tests that the dbInsertID Fail
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testdbInsertIDFail()
    {
        // Test fail with no control data
            $result = $this->object->getDataDriver()->dbInsertid(
                'tableName',
                'idfield',
                'srchfield',
                'srchdata'
            );
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertStringContainsString("Error Getting record ID.", $result->getMessage());

        // Test fail with missinf id key
        $functions = array(
            'testdbInsertIDFail' => array(
                'ids' => true
            )
        );
        $data = array();
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->dbInsertid(
            'tableName',
            'idfield',
            'srchfield',
            'srchdata'
        );
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertStringContainsString("Error Getting record ID.", $result->getMessage());

        // Test with id key set to false
        $functions = array(
            'testdbInsertIDFail' => array(
                'id' => false
            )
        );
        $data = array('srchdata' => 6);
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->dbInsertid(
            'tableName',
            'idfield',
            'srchfield',
            'srchdata'
        );
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertStringContainsString("Error Getting record ID.", $result->getMessage());
    }

    /**
     * This function tests that dbUpdate Passes
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testdbUpdatePass()
    {
        $functions = array(
            'testdbUpdatePass' => array(
                'update' => true
            )
        );
        $data = array();
        $this->object->getDataDriver()->control($functions, $data);
        $updatedata = array("user" => "phpunit");
        $searchdata = array("user_id" => 4);
        $result = $this->object->getDataDriver()->dbUpdate('users', $updatedata, $searchdata);
        $this->assertTrue($result);
    }

    /**
     * This function tests that dbUpdate fails
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testdbUpdateFail()
    {
        // Common Data for tests
        $updatedata = array("user" => "phpunit");
        $searchdata = array("user_id" => 4);

        // No control data
        $result = $this->object->getDataDriver()->dbUpdate('users', $updatedata, $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertStringContainsString("SQL Query Error", $result->getMessage());

        // Contol data key 'pass' missing
        $functions = array(
            'testdbUpdateFail' => array(
                'updated' => false
            )
        );
        $data = array();
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->dbUpdate('users', $updatedata, $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertStringContainsString("SQL Query Error", $result->getMessage());

        // Contol data key 'pass' set to false
        $functions = array(
            'testdbUpdateFail' => array(
                'update' => false
            )
        );
        $data = array();
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->dbUpdate('users', $updatedata, $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertStringContainsString("SQL Query Error", $result->getMessage());
    }

    /**
     * This function tests that dbSElectSingle Passes
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testdbSelectSinglePass()
    {
        $functions = array(
            'testdbSelectSinglePass' => array(
                'pass' => true
            )
        );
        $data = array(
            'testdbSelectSinglePass' => array(
                'user_id' => 1,
                'username' => 'phpunit',
                'passwd' => 'passwd'
            )
        );
        $this->object->getDataDriver()->control($functions, $data);
        $fields = array("user_id", "username", "passwd");
        $searchdata = array("user_id" => 1);
        $result = $this->object->getDataDriver()->dbSelectSingle('users', $fields, $searchdata);
        $this->assertTrue(is_array($result));
        $this->assertEquals('phpunit', $result['username']);
    }


    /**
     * This function tests that dbSElectSingle returns NOTFOUND
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testdbSelectSingleNotFound()
    {
        $functions = array(
            'testdbSelectSingleNotFound' => array(
                'notfound' => true
            )
        );
        $data = array(
            'testdbSelectSingleNotFound' => array(
                'user_id' => 1,
                'username' => 'phpunit',
                'passwd' => 'passwd'
            )
        );
        $this->object->getDataDriver()->control($functions, $data);
        $fields = array("user_id", "username", "passwd");
        $searchdata = array("user_id" => 1);
        $result = $this->object->getDataDriver()->dbSelectSingle('users', $fields, $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertStringContainsString("Not Found", $result->getMessage());
    }

    /**
     * This function tests that dbSElectSingle returns Failled
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testdbSelectSingleFailed()
    {
        // Common Search Data
        $fields = array("user_id", "username", "passwd");
        $searchdata = array("user_id" => 1);

        // No Control Data
        $result = $this->object->getDataDriver()->dbSelectSingle('users', $fields, $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertStringContainsString("SQL Query Error", $result->getMessage());

        // Missing pass key from functions array
        $functions = array(
            'testdbSelectSingleFailed' => array(
                'passed' => true
            )
        );
        $data = array(
            'testdbSelectSingleFailed' => array(
                'user_id' => 1,
                'username' => 'phpunit',
                'passwd' => 'passwd'
            )
        );
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->dbSelectSingle('users', $fields, $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertStringContainsString("SQL Query Error", $result->getMessage());

        //  pass key set to false in functions array
        $functions = array(
            'testdbSelectSingleFailed' => array(
                'pass' => false
            )
        );
        $data = array(
            'testdbSelectSingleFailed' => array(
                'user_id' => 1,
                'username' => 'phpunit',
                'passwd' => 'passwd'
            )
        );
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->dbSelectSingle('users', $fields, $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertStringContainsString("SQL Query Error", $result->getMessage());
    }

    /**
     * This function tests that dbSElectMultiple Passes
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testdbSelectMultiplePass()
    {
        $functions = array(
            'testdbSelectMultiplePass' => array(
                'pass' => true
            )
        );
        $data = array(
            'testdbSelectMultiplePass' => array(
                'user_id' => 1,
                'username' => 'phpunit',
                'passwd' => 'passwd'
            )
        );
        $this->object->getDataDriver()->control($functions, $data);
        $fields = array("user_id", "username", "passwd");
        $searchdata = array("user_id" => 1);
        $result = $this->object->getDataDriver()->dbSelectMultiple('users', $fields, $searchdata);
        $this->assertTrue(is_array($result));
        $this->assertEquals('phpunit', $result['username']);
    }

    /**
     * This function tests that dbDelete Passes
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testdbDeletePass()
    {
        $functions = array(
            'testdbDeletePass' => array(
                'delete' => 'users'
            )
        );
        $data = array();
        $this->object->getDataDriver()->control($functions, $data);
        $searchdata = array("user_id" => 1);
        $result = $this->object->getDataDriver()->dbDelete('users', $searchdata);
        $this->assertTrue($result);
    }

    /**
     * This function tests that dbDelete Fails
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testdbDeleteFail()
    {
        // Common Search data
        $searchdata = array("user_id" => 1);

        //  No Control Data
        $result = $this->object->getDataDriver()->dbDelete('users', $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertStringContainsString("SQL Query Error", $result->getMessage());

        //  'delete' key missing fron functions array
        $functions = array(
            'testdbDeletePass' => array(
                'deleted' => 'users'
            )
        );
        $data = array();
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->dbDelete('users', $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertStringContainsString("SQL Query Error", $result->getMessage());

        //  'delete' key set to wron table name
        $functions = array(
            'testdbDeletePass' => array(
                'delete' => 'groups'
            )
        );
        $data = array();
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->dbDelete('users', $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertStringContainsString("SQL Query Error", $result->getMessage());
    }

    /**
     * This function tests that dbDelete Fails
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testdbDeleteMultipleFail()
    {
        // Common Search data
        $searchdata = array("user_id" => 1);

        //  No Control Data implemented
        $result = $this->object->getDataDriver()->dbDeleteMultiple('users', $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertStringContainsString("SQL Query Error", $result->getMessage());
    }

    /**
     * This function tests rowCount.  If no test data is passed via the control,
     * other wise it returns the control data
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testrowCount()
    {
        $result = $this->object->getDataDriver()->rowCount();
        $this->assertEquals(0, $result);


        $functions = array(
            'testrowCount' => array(
                'rowcount' => true
            )
        );
        $data = array('rowcount' => 6);
        $this->object->getDataDriver()->control($functions, $data);
        $result = $this->object->getDataDriver()->rowCount();
        $this->assertEquals(6, $result);
    }
}

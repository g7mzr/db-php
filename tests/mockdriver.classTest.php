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
     * @return null No return data
     */
    protected function setUp()
    {
        global $dsn;
        $localdsn = $dsn;
        $localdsn['dbtype'] = 'mock';
        $this->object = \g7mzr\db\DB::load($localdsn, true);
        if (\g7mzr\db\common\Common::isError($this->object)) {
            print_r($this->object);
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
        $this->object->disconnect();
    }

    /**
     * This function tests the class Destructor
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testDestruct()
    {
        global $dsn;
        $localdsn = $dsn;
        $localdsn['dbtype'] = 'mock';
        $db = \g7mzr\db\DB::load($localdsn, true);
        if (\g7mzr\db\common\Common::isError($db)) {
            $this->fail("Unable to create mock DB Object");
        }
        unset($db);
        $this->assertTrue(true);
    }

    /**
     * This function tests that the DBVersion function passes
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testDBVersionPass()
    {
        $functions = array(
            'testDBVersionPass' => array(
                'pass' => true
            )
        );
        $data = array('version' => 'Mock 1.0.0');
        $this->object->control($functions, $data);
        $result = $this->object->getDBVersion();
        $this->assertContains('Mock 1.0.0', $result);
    }

    /**
     * This function tests that the DBVersion function failse
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testDBVersionFail()
    {
        // Test with no Control Data
        $result = $this->object->getDBVersion();
        $this->assertContains('Error Getting Database Version', $result);

        //test with pass element missing from control data
        $functions = array(
            'testDBVersionFail' => array(
                'passed' => false
            )
        );
        $data = array('version' => 'Mock 1.0.0');
        $this->object->control($functions, $data);
        $result = $this->object->getDBVersion();
        $this->assertContains('Error Getting Database Version', $result);

        //test with control data set to fail
        $functions = array(
            'testDBVersionFail' => array(
                'pass' => false
            )
        );
        $this->object->control($functions, $data);
        $result = $this->object->getDBVersion();
        $this->assertContains('Error Getting Database Version', $result);
    }

    /**
     * This function tests that the startTransaction Passes
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function teststartTransactionPass()
    {
        $functions = array(
            'teststartTransactionPass' => array(
                'starttransaction' => true
            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $result = $this->object->startTransaction();
        $this->assertTrue($result);
    }

    /**
     * This function tests that the startTransaction Fails
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function teststartTransactionFail()
    {
        // No Control Data
        $result = $this->object->startTransaction();
        $this->assertFalse($result);

         // starttransaction missing from control data
        $functions = array(
            'teststartTransactionFail' => array(
                'starttransactions' => false
            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $result = $this->object->startTransaction();
        $this->assertFalse($result);

       // Control Data to Fail
        $functions = array(
            'teststartTransactionFail' => array(
                'starttransaction' => false
            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $result = $this->object->startTransaction();
        $this->assertFalse($result);
    }

    /**
     * This function tests that the endTransaction Passes
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testendTransactionPass()
    {
        $functions = array(
            'testendTransactionPass' => array(
                'endtransaction' => true
            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $result = $this->object->endTransaction(true);
        $this->assertTrue($result);
    }

    /**
     * This function tests that the endTransaction Fails
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testendTransactionFail()
    {
        // No Control Data
        $result = $this->object->endTransaction(true);
        $this->assertFalse($result);

         // starttransaction missing from control data
        $functions = array(
            'testendTransactionFail' => array(
                'endtransactions' => false
            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $result = $this->object->startTransaction();
        $this->assertFalse($result);

       // Control Data to Fail
        $functions = array(
            'teststartTransactionFail' => array(
                'endtransaction' => false
            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $result = $this->object->startTransaction();
        $this->assertFalse($result);
    }

    /**
     * This function tests that the dbInsert Passes
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testdbInsertPass()
    {
        $functions = array(
            'testdbInsertPass' => array(
                'pass' => true
            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $insertdata = array("user" => "phpunit");
        $result = $this->object->dbInsert('users', $insertdata);
        $this->assertTrue($result);
    }

    /**
     * This function tests that the dbInsert Passes
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testdbInsertFail()
    {
        // No Control Data
        $insertdata = array("user" => "phpunit");
        $result = $this->object->dbInsert('users', $insertdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertContains("SQL Query Error", $result->getMessage());

        // pass key missing from control data
        $functions = array(
            'testdbInsertPass' => array(
                'passed' => true
            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $insertdata = array("user" => "phpunit");
        $result = $this->object->dbInsert('users', $insertdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertContains("SQL Query Error", $result->getMessage());

        // pass key set to false
        $functions = array(
            'testdbInsertPass' => array(
                'pass' => false
            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $insertdata = array("user" => "phpunit");
        $result = $this->object->dbInsert('users', $insertdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertContains("SQL Query Error", $result->getMessage());
    }

    /**
     * This function tests that the dbInsertID Passes
     *
     * @group unittest
     * @group error
     *
     * @return null
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
        $this->object->control($functions, $data);
        $result = $this->object->dbInsertid(
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
        $this->object->control($functions, $data);
        $result = $this->object->dbInsertid(
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
     * @return null
     */
    public function testdbInsertIDFail()
    {
        // Test fail with no control data
            $result = $this->object->dbInsertid(
                'tableName',
                'idfield',
                'srchfield',
                'srchdata'
            );
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertContains("Error Getting record ID.", $result->getMessage());

        // Test fail with missinf id key
        $functions = array(
            'testdbInsertIDFail' => array(
                'ids' => true
            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $result = $this->object->dbInsertid(
            'tableName',
            'idfield',
            'srchfield',
            'srchdata'
        );
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertContains("Error Getting record ID.", $result->getMessage());

        // Test with id key set to false
        $functions = array(
            'testdbInsertIDFail' => array(
                'id' => false
            )
        );
        $data = array('srchdata' => 6);
        $this->object->control($functions, $data);
        $result = $this->object->dbInsertid(
            'tableName',
            'idfield',
            'srchfield',
            'srchdata'
        );
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertContains("Error Getting record ID.", $result->getMessage());
    }

    /**
     * This function tests that dbUpdate Passes
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testdbUpdatePass()
    {
        $functions = array(
            'testdbUpdatePass' => array(
                'update' => true
            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $updatedata = array("user" => "phpunit");
        $searchdata = array("user_id" => 4);
        $result = $this->object->dbUpdate('users', $updatedata, $searchdata);
        $this->assertTrue($result);
    }

    /**
     * This function tests that dbUpdate fails
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testdbUpdateFail()
    {
        // Common Data for tests
        $updatedata = array("user" => "phpunit");
        $searchdata = array("user_id" => 4);

        // No control data
        $result = $this->object->dbUpdate('users', $updatedata, $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertContains("SQL Query Error", $result->getMessage());

        // Contol data key 'pass' missing
        $functions = array(
            'testdbUpdateFail' => array(
                'updated' => false
            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $result = $this->object->dbUpdate('users', $updatedata, $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertContains("SQL Query Error", $result->getMessage());

        // Contol data key 'pass' set to false
        $functions = array(
            'testdbUpdateFail' => array(
                'update' => false
            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $result = $this->object->dbUpdate('users', $updatedata, $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertContains("SQL Query Error", $result->getMessage());
    }

    /**
     * This function tests that dbSElectSingle Passes
     *
     * @group unittest
     * @group error
     *
     * @return null
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
        $this->object->control($functions, $data);
        $fields = array("user_id", "username", "passwd");
        $searchdata = array("user_id" => 1);
        $result = $this->object->dbSelectSingle('users', $fields, $searchdata);
        $this->assertTrue(is_array($result));
        $this->assertEquals('phpunit', $result['username']);
    }


    /**
     * This function tests that dbSElectSingle returns NOTFOUND
     *
     * @group unittest
     * @group error
     *
     * @return null
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
        $this->object->control($functions, $data);
        $fields = array("user_id", "username", "passwd");
        $searchdata = array("user_id" => 1);
        $result = $this->object->dbSelectSingle('users', $fields, $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertContains("Not Found", $result->getMessage());
    }

    /**
     * This function tests that dbSElectSingle returns Failled
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testdbSelectSingleFailed()
    {
        // Common Search Data
        $fields = array("user_id", "username", "passwd");
        $searchdata = array("user_id" => 1);

        // No Control Data
        $result = $this->object->dbSelectSingle('users', $fields, $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertContains("SQL Query Error", $result->getMessage());

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
        $this->object->control($functions, $data);
        $result = $this->object->dbSelectSingle('users', $fields, $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertContains("SQL Query Error", $result->getMessage());

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
        $this->object->control($functions, $data);
        $result = $this->object->dbSelectSingle('users', $fields, $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertContains("SQL Query Error", $result->getMessage());
    }

    /**
     * This function tests that dbSElectMultiple Passes
     *
     * @group unittest
     * @group error
     *
     * @return null
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
        $this->object->control($functions, $data);
        $fields = array("user_id", "username", "passwd");
        $searchdata = array("user_id" => 1);
        $result = $this->object->dbSelectMultiple('users', $fields, $searchdata);
        $this->assertTrue(is_array($result));
        $this->assertEquals('phpunit', $result['username']);
    }

    /**
     * This function tests that dbDelete Passes
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testdbDeletePass()
    {
        $functions = array(
            'testdbDeletePass' => array(
                'delete' => 'users'
            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $searchdata = array("user_id" => 1);
        $result = $this->object->dbDelete('users', $searchdata);
        $this->assertTrue($result);
    }

    /**
     * This function tests that dbDelete Fails
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testdbDeleteFail()
    {
        // Common Search data
        $searchdata = array("user_id" => 1);

        //  No Control Data
        $result = $this->object->dbDelete('users', $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertContains("SQL Query Error", $result->getMessage());

        //  'delete' key missing fron functions array
        $functions = array(
            'testdbDeletePass' => array(
                'deleted' => 'users'
            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $result = $this->object->dbDelete('users', $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertContains("SQL Query Error", $result->getMessage());

        //  'delete' key set to wron table name
        $functions = array(
            'testdbDeletePass' => array(
                'delete' => 'groups'
            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $result = $this->object->dbDelete('users', $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertContains("SQL Query Error", $result->getMessage());
    }

    /**
     * This function tests that dbDelete Fails
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testdbDeleteMultipleFail()
    {
        // Common Search data
        $searchdata = array("user_id" => 1);

        //  No Control Data implemented
        $result = $this->object->dbDeleteMultiple('users', $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertContains("SQL Query Error", $result->getMessage());
    }

    /**
     * This function tests rowCount.  If no test data is passed via the control,
     * other wise it returns the control data
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testrowCount()
    {
        $result = $this->object->rowCount();
        $this->assertEquals(0, $result);


        $functions = array(
            'testrowCount' => array(
                'rowcount' => true
            )
        );
        $data = array('rowcount' => 6);
        $this->object->control($functions, $data);
        $result = $this->object->rowCount();
        $this->assertEquals(6, $result);
    }
}

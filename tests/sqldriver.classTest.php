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
class DriverTest extends TestCase
{
    /**
     * DB CLASS
     *
     * @var \g7mzr\db\DB
     */
    protected $object;

    /**
     * SQL Database Driver Type
     *
     * @var string
     */
    protected $drivertype;

    /**
     * Boolean Flag if test user is created
     *
     * @var boolean
     */
    protected $userCreated;

    /**
     * Boolean Flag if test item is created
     *
     * @var boolean
     */
    protected $itemCreated;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return null No return data
     */
    protected function setUp()
    {
        global $dsn;
        $this->object = \g7mzr\db\DB::load($dsn, true);
        if (\g7mzr\db\common\Common::isError($this->object)) {
            print_r($this->object);
            exit(1);
        }

        // Set expected answers for specific database types
        switch ($dsn['dbtype']) {
            case 'pgsql':
                $this->drivertype = 'PostgreSQL'; // Version
                break;
            default:
                $this->drivertype = 'Unknown';
                break;
        }
        $this->userCreated = false;
        $this->itemCreated = false;
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
        if (($this->userCreated === true) or ($this->itemCreated === true)) {
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
                //print_r($e->getMessage());
                throw new \Exception('Unable to connect to the database');
            }
            //$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $localconn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);

            if ($this->userCreated === true) {
                $sql =  "delete from users where username = 'unittest'";
                $localconn->query($sql);
            }

            if ($this->itemCreated === true) {
                $sql =  "delete from items where itemname = 'testitem'";
                $localconn->query($sql);
            }

            unset($localconn);
        }
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
        $class = \g7mzr\db\DB::load($dsn, true);
        if (\g7mzr\db\common\Common::isError($class)) {
            $this->fail('Unable to create Class');
        }
        $class = null;
        $this->assertTrue(true);
    }

    /**
     * This function tests that the DB Version String can be returned
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testDBVersion()
    {
        $result = $this->object->getDBVersion();
        $this->assertContains($this->drivertype, $result);
    }

    /**
     * This function tests a record can be returned from the database
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testSelectSingleRecord()
    {
        // Data fields to be returned
        $fields = array('user_id', 'username', 'password', 'email');

        // Search using iser_id
        $searchone = array('user_id' => '1');
        $resultone = $this->object->dbselectsingle('users', $fields, $searchone);
        $this->assertEquals('1', $resultone['user_id']);
        $this->assertEquals('user1', $resultone['username']);

        // search using username
        $searchtwo = array('username' => 'user2');
        $resulttwo = $this->object->dbselectsingle('users', $fields, $searchtwo);
        $this->assertEquals('2', $resulttwo['user_id']);
        $this->assertEquals('user2', $resulttwo['username']);

        // Search the items table
        $itemfields = array(
            'item_id',
            'itemname',
            'itemdescription',
            'available',
            'owner'
        );
        $itemsearch = array('item_id' => 1);
        $itemresult = $this->object->dbselectsingle(
            'items',
            $itemfields,
            $itemsearch
        );
        $this->assertEquals('1', $itemresult['item_id']);
        $this->assertEquals('item1', $itemresult['itemname']);
        $this->assertTrue($itemresult['available']);
    }

    /**
     * This function tests a Not Found error is returned if no record is found
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testSelectSingleRecordNotFound()
    {
        // Data fields to be returned
        $fields = array('user_id', 'username', 'password', 'email');

        // search using username
        $search = array('username' => 'user6');
        $result = $this->object->dbselectsingle('users', $fields, $search);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertEquals('Not Found', $result->getMessage());
    }

    /**
     * This function tests database error is returned if an query is used
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testSelectSingleInvalidQuery()
    {
        // Test for an invalid field
        $fields = array('user_id', 'usermame', 'password', 'email');
        $search = array('username' => 'user1');
        $result = $this->object->dbselectsingle('users', $fields, $search);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertEquals('SQL Query Error', $result->getMessage());

        // Test Invalid table
        $fields = array('user_id', 'username', 'password', 'email');
        $search = array('username' => 'user1');
        $result = $this->object->dbselectsingle('user', $fields, $search);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertEquals('SQL Query Error', $result->getMessage());
    }

    /**
     * This function tests for more than 1 item being returned with SelectSingle
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testSelectSingleMultipleRecords()
    {
        // Test for multiple records
        $fields = array('item_id', 'itemname', 'itemdescription', 'owner');
        $search = array('owner' => '2');
        $result = $this->object->dbselectsingle('items', $fields, $search);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertEquals('Found More than One Record', $result->getMessage());
    }

    /**
     * This function tests for more than 1 item using SelectMultiple
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testSelectMultiple()
    {
        // Test for multiple records
        $fields = array('item_id', 'itemname', 'itemdescription', 'owner');
        $search = array('owner' => '2');
        $result = $this->object->dbselectmultiple('items', $fields, $search);
        $this->assertEquals(2, count($result));
        $this->assertEquals('item2', $result[0]['itemname']);
        $this->assertEquals('item3', $result[1]['itemname']);
    }

    /**
     * This function tests for more than 1 item using SelectMultiple and orders them
     * by id
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testSelectMultipleOrderby()
    {
        // Test for multiple records ordered by item_id
        $fields = array('item_id', 'itemname', 'itemdescription', 'owner');
        $search = array('owner' => '2');
        $orderby = 'item_id';
        $result = $this->object->dbselectmultiple(
            'items',
            $fields,
            $search,
            $orderby
        );
        $this->assertEquals(2, count($result));
        $this->assertEquals('item2', $result[0]['itemname']);
        $this->assertEquals('item3', $result[1]['itemname']);
    }

    /**
     * This function tests for more than 1 item using SelectMultiple and orders them
     * by id
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testSelectMultipleNotFound()
    {
        // Test for No Records Found
        $fields = array('item_id', 'itemname', 'itemdescription', 'owner');
        $search = array('owner' => '10');
        $orderby = 'item_id';
        $result = $this->object->dbselectmultiple(
            'items',
            $fields,
            $search,
            $orderby
        );
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertEquals('Not Found', $result->getMessage());
    }

    /**
     * This function tests for error message if an invalid query is used
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testSelectMultipleInvalidQuery()
    {
        // Invalid Field Name
        $fields = array('item_id', 'itemname', 'description', 'owner');
        $search = array('owner' => '10');
        $orderby = 'item_id';
        $result = $this->object->dbselectmultiple(
            'items',
            $fields,
            $search,
            $orderby
        );
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertEquals('SQL Query Error', $result->getMessage());

        // Invalid table Name
        $fields = array('item_id', 'itemname', 'itemdescription', 'owner');
        $search = array('owner' => '10');
        $orderby = 'item_id';
        $result = $this->object->dbselectmultiple(
            'item',
            $fields,
            $search,
            $orderby
        );
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertEquals('SQL Query Error', $result->getMessage());
    }

    /**
     * This function tests for more than 1 item using SelectMultiple and wildcards
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testSelectMultipleLike()
    {
        // Test for multiple records ordered by item_id
        $fields = array('user_id', 'username', 'password', 'email');
        $search = array('username' => 'user%');
         $orderby = 'user_id';
        $result = $this->object->dbselectmultiple(
            'users',
            $fields,
            $search,
            $orderby
        );
        $this->assertEquals(2, count($result));
        $this->assertEquals('user1', $result[0]['username']);
        $this->assertEquals('user2', $result[1]['username']);
    }

    /**
     * This function tests for more than 1 item using SelectMultiple, wildcards and
     * multiple fields
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testSelectMultipleLikeAND()
    {
        // Test for multiple records ordered by item_id
        $fields = array('user_id', 'username', 'password', 'email');
        $search = array('username' => 'user%', 'email' => 'user1@example.com');
        $orderby = 'user_id';
        $result = $this->object->dbselectmultiple(
            'users',
            $fields,
            $search,
            $orderby
        );
        $this->assertEquals(1, count($result));
        $this->assertEquals('user1', $result[0]['username']);
        //$this->assertEquals('user2', $result[1]['username']);
    }


    /**
     * This function tests for joining two tables
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testSelectMultipleJOIN()
    {
        // Test for multiple records ordered by item_id
        $fields = array(
            'username',
            'password',
            'email',
            'itemname',
            'itemdescription'
        );
        $search = array('username' => 'user2');
        $orderby = 'user_id';
        $join = array(
            'table2' => 'items',
            'field1' =>
            'users.user_id',
            'field2' => 'items.owner'
        );
        $result = $this->object->dbselectmultiple(
            'users',
            $fields,
            $search,
            $orderby,
            $join
        );
        $this->assertEquals(2, count($result));
        $this->assertEquals('item2', $result[0]['itemname']);
        $this->assertEquals('item3', $result[1]['itemname']);
    }

    /**
     * This function tests for inserting user data to the database
     *
     * @group unittest
     * @group error
     * @depends testSelectSingleRecord
     *
     * @return null
     */
    public function testInsert()
    {
        //Set up data
        $fields = array(
            'username' => 'unittest',
            'password' => 'unittest',
            'email' => 'unittest@example.com'
        );
        $result = $this->object->dbinsert('users', $fields);
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail($result->getMessage());
        } else {
            $this->assertTrue(true);
            $this->userCreated = true;
        }
    }

    /**
     * This function tests for inserting user data to the database
     *
     * @group unittest
     * @group error
     * @depends testSelectSingleRecord
     *
     * @return null
     */
    public function testInsertItem()
    {
        //Set up data
        $fields = array(
            'itemname' => 'testitem',
            'itemdescription' => null,
            'available' => true,
            'owner' => 1
        );
        $result = $this->object->dbinsert('items', $fields);
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail($result->getMessage());
        } else {
            $this->assertTrue(true);
            $this->itemCreated = true;
        }
    }


   /**
     * This function tests that a database insert fails with invalid table name
     *
     * @group unittest
     * @group error
     * @depends testSelectSingleRecord
     *
     * @return null
     */
    public function testInsertInvalidSql()
    {
        //Set up data
        $fields = array(
            'username' => 'unittest',
            'password' => 'unittest',
            'email' => 'unittest@example.com'
        );
        $result = $this->object->dbinsert('user', $fields);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertEquals(
            'Error running the Database INSERT Statement',
            $result->getMessage()
        );
    }

    /**
     * This function tests that the id of a record can be returned.  It is normally
     * run after a new record is inserted.  For the purpose of testing we are using
     * an existing record
     *
     * @group unittest
     * @group error
     * @depends testSelectSingleRecord
     *
     * @return null
     */
    public function testInsertID()
    {
        $result = $this->object->dbinsertid('users', 'user_id', 'username', 'user1');
        $this->assertEquals('1', $result);
    }

    /**
     * This function tests that the id of a record can be returned.  It is normally
     * run after a new record is inserted.  This test checks an error is returned if
     * the record is not found.
     *
     * @group unittest
     * @group error
     * @depends testSelectSingleRecord
     *
     * @return null
     */
    public function testInsertIDNotFound()
    {
        $result = $this->object->dbinsertid(
            'users',
            'user_id',
            'username',
            'user10'
        );
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertEquals(
            'Record ID Not Found.',
            $result->getMessage()
        );
    }

    /**
     * This function tests that the id of a record can be returned.  It is normally
     * run after a new record is inserted.  This test checks an error is returned if
     * sql statement is wrong
     *
     * @group unittest
     * @group error
     * @depends testSelectSingleRecord
     *
     * @return null
     */
    public function testInsertIDSQLError()
    {
        $result = $this->object->dbinsertid(
            'users',
            'userid',
            'username',
            'user1'
        );
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertEquals(
            'Error getting record ID.',
            $result->getMessage()
        );
    }

    /**
     * This function tests the Update function
     *
     * @group unittest
     * @group error
     * @depends testSelectSingleRecord
     * @depends testInsert
     *
     * @return null
     */
    public function testUpdate()
    {
        //Set up data
        $fields = array(
            'username' => 'unittest',
            'password' => 'unittest',
            'email' => 'unittest@example.com'
        );
        $result = $this->object->dbinsert('users', $fields);
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail($result->getMessage());
        }

        $this->userCreated = true;
        $updatefields = array(
            'password' => 'test',
            'email' => 'test@example.com'
        );
        $searchdata = array('username' => 'unittest');
        $result = $this->object->dbupdate('users', $updatefields, $searchdata);
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail("Error updating record");
        }
        $selectfields = array('user_id', 'username', 'password', 'email');
        $selectresult = $this->object->dbselectsingle(
            'users',
            $selectfields,
            $searchdata
        );
        $this->assertEquals('unittest', $selectresult['username']);
        $this->assertEquals('test', $selectresult['password']);
        $this->assertEquals('test@example.com', $selectresult['email']);
    }

    /**
     * This function tests the Update function with an AND Statement
     *
     * @group unittest
     * @group error
     * @depends testSelectSingleRecord
     * @depends testInsert
     *
     * @return null
     */
    public function testUpdatewithAND()
    {
        //Set up data
        $fields = array(
            'username' => 'unittest',
            'password' => 'unittest',
            'email' => 'unittest@example.com'
        );
        $result = $this->object->dbinsert('users', $fields);
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail($result->getMessage());
        }

        $this->userCreated = true;
        $updatefields = array(
            'email' => 'test@example.com'
        );
        $searchdata = array('username' => 'unittest', 'Password' => 'unittest');
        $result = $this->object->dbupdate('users', $updatefields, $searchdata);
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail("Error updating record");
        }
        $selectfields = array('user_id', 'username', 'password', 'email');
        $selectresult = $this->object->dbselectsingle(
            'users',
            $selectfields,
            $searchdata
        );
        $this->assertEquals('unittest', $selectresult['username']);
        $this->assertEquals('unittest', $selectresult['password']);
        $this->assertEquals('test@example.com', $selectresult['email']);
    }

    /**
     * This function tests the Update function Record not found
     *
     * @group unittest
     * @group error
     * @depends testSelectSingleRecord
     * @depends testInsert
     *
     * @return null
     */
    public function testUpdateRecordNotFound()
    {
        $updatefields = array(
            'password' => 'test',
            'email' => 'test@example.com'
        );
        $searchdata = array('username' => 'unittest');
        $result = $this->object->dbupdate('users', $updatefields, $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertEquals(
            'Record not found',
            $result->getMessage()
        );
    }

    /**
     * This function tests the Update function SQL Error
     *
     * @group unittest
     * @group error
     * @depends testSelectSingleRecord
     * @depends testInsert
     *
     * @return null
     */
    public function testUpdateSQLError()
    {
        $updatefields = array(
            'passwd' => 'test',
            'email' => 'test@example.com'
        );
        $searchdata = array('username' => 'unittest');
        $result = $this->object->dbupdate('users', $updatefields, $searchdata);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertEquals(
            'Error running the Database UPDATE Statement',
            $result->getMessage()
        );
    }


    /**
     * This function tests the Transaction Capability.  Commit
     *
     * @group unittest
     * @group error
     * @depends testSelectSingleRecord
     * @depends testInsert
     *
     * @return null
     */
    public function testTransactionCommit()
    {
        $transactionstart = $this->object->startTransaction();
        $this->assertTrue($transactionstart);

        $insertfields = array(
            'username' => 'unittest',
            'password' => 'unittest',
            'email' => 'unittest@example.com'
        );
        $result = $this->object->dbinsert('users', $insertfields);
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail($result->getMessage());
        }

        $transactionend =$this->object->endTransaction(true);
        $this->assertTrue($transactionend);

        $this->userCreated = true;

        // Data fields to be returned
        $searchfields = array('user_id', 'username', 'password', 'email');

        // Search using iser_id
        $searchdata = array('username' => 'unittest');
        $searchresult = $this->object->dbselectsingle(
            'users',
            $searchfields,
            $searchdata
        );
        $this->assertEquals('unittest', $searchresult['username']);
    }

    /**
     * This function tests the Transaction Capability.  Rollback
     *
     * @group unittest
     * @group error
     * @depends testSelectSingleRecord
     * @depends testInsert
     *
     * @return null
     */
    public function testTransactionRollback()
    {
        $transactionstart = $this->object->startTransaction();
        $this->assertTrue($transactionstart);

        $insertfields = array(
            'username' => 'unittest',
            'password' => 'unittest',
            'email' => 'unittest@example.com'
        );
        $result = $this->object->dbinsert('users', $insertfields);
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail($result->getMessage());
        }

        $transactionend =$this->object->endTransaction(false);
        $this->assertFalse($transactionend);

        // Data fields to be returned
        $searchfields = array('user_id', 'username', 'password', 'email');

        // Search using iser_id
        $searchdata = array('username' => 'unittest');
        $searchresult = $this->object->dbselectsingle(
            'users',
            $searchfields,
            $searchdata
        );
        $this->assertTrue(is_a($searchresult, '\g7mzr\db\common\Error'));
        $this->assertEquals(
            'Not Found',
            $searchresult->getMessage()
        );
    }


    /**
     * This function tests the Delete Capability
     *
     * @group unittest
     * @group error
     * @depends testSelectSingleRecord
     * @depends testInsert
     *
     * @return null
     */
    public function testDeleteRecord()
    {
        $insertfields = array(
            'username' => 'unittest',
            'password' => 'unittest',
            'email' => 'unittest@example.com'
        );
        $result = $this->object->dbinsert('users', $insertfields);
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail($result->getMessage());
        }

        $this->userCreated = true;

        // Delete using username
        $deletedata = array('username' => 'unittest');
        $deleteresult = $this->object->dbdelete('users', $deletedata);
        if (\g7mzr\db\common\Common::isError($deleteresult)) {
            $this->fail($deleteresult->getMessage());
        }


        // Data fields to be returned
        $searchfields = array('user_id', 'username', 'password', 'email');

        // Search using iser_id
        $searchdata = array('username' => 'unittest');
        $searchresult = $this->object->dbselectsingle(
            'users',
            $searchfields,
            $searchdata
        );
        $this->assertTrue(is_a($searchresult, '\g7mzr\db\common\Error'));
        $this->assertEquals(
            'Not Found',
            $searchresult->getMessage()
        );
    }

    /**
     * This function tests the Delete Capability using multiple search fields
     *
     * @group unittest
     * @group error
     * @depends testSelectSingleRecord
     * @depends testInsert
     *
     * @return null
     */
    public function testDeleteRecordMultipleSearchFields()
    {
        $insertfields = array(
            'username' => 'unittest',
            'password' => 'unittest',
            'email' => 'unittest@example.com'
        );
        $result = $this->object->dbinsert('users', $insertfields);
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail($result->getMessage());
        }

        $this->userCreated = true;

        // Search using multiple fields
        $deletedata = array('username' => 'unittest', 'password' => 'unittest');
        $deleteresult = $this->object->dbdelete('users', $deletedata);
        if (\g7mzr\db\common\Common::isError($deleteresult)) {
            $this->fail($deleteresult->getMessage());
        }

        // Data fields to be returned
        $searchfields = array('user_id', 'username', 'password', 'email');

        // Search using iser_id
        $searchdata = array('username' => 'unittest');
        $searchresult = $this->object->dbselectsingle(
            'users',
            $searchfields,
            $searchdata
        );
        $this->assertTrue(is_a($searchresult, '\g7mzr\db\common\Error'));
        $this->assertEquals(
            'Not Found',
            $searchresult->getMessage()
        );
    }
    /**
     * This function tests the Delete Capability invalid SQL
     *
     * @group unittest
     * @group error
     * @depends testSelectSingleRecord
     * @depends testInsert
     *
     * @return null
     */
    public function testDeleteRecordInvalidSQL()
    {

        // Search using iser_id
        $deletedata = array('name' => 'unittest');
        $deleteresult = $this->object->dbdelete('users', $deletedata);

        $this->assertTrue(is_a($deleteresult, '\g7mzr\db\common\Error'));
        $this->assertEquals(
            'SQL Query Error',
            $deleteresult->getMessage()
        );
    }

    /**
     * This function tests the Delete Multiple Capability
     *
     * @group unittest
     * @group error
     * @depends testSelectSingleRecord
     * @depends testInsert
     *
     * @return null
     */
    public function testDeleteMultipleRecord()
    {
        $insertfields = array(
            'username' => 'unittest',
            'password' => 'unittest',
            'email' => 'unittest@example.com'
        );
        $result = $this->object->dbinsert('users', $insertfields);
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail($result->getMessage());
        }

        $this->userCreated = true;

        // Delete using username
        $deletedata = array();
        $deletedata['username'] = array('type' => "=", 'data' => 'unittest');
        $deleteresult = $this->object->dbdeletemultiple('users', $deletedata);
        if (\g7mzr\db\common\Common::isError($deleteresult)) {
            $this->fail($deleteresult->getMessage());
        }


        // Data fields to be returned
        $searchfields = array('user_id', 'username', 'password', 'email');

        // Search using iser_id
        $searchdata = array('username' => 'unittest');
        $searchresult = $this->object->dbselectsingle(
            'users',
            $searchfields,
            $searchdata
        );
        $this->assertTrue(is_a($searchresult, '\g7mzr\db\common\Error'));
        $this->assertEquals(
            'Not Found',
            $searchresult->getMessage()
        );
    }

    /**
     * This function tests the Delete Multiple Capability
     *
     * @group unittest
     * @group error
     * @depends testSelectSingleRecord
     * @depends testInsert
     *
     * @return null
     */
    public function testDeleteMultipleRecordsMultipleSearchfields()
    {
        $insertfields = array(
            'username' => 'unittest',
            'password' => 'unittest',
            'email' => 'unittest@example.com'
        );
        $result = $this->object->dbinsert('users', $insertfields);
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail($result->getMessage());
        }

        $this->userCreated = true;

        // Delete using username
        $deletedata = array();
        $deletedata['username'] = array('type' => "=", 'data' => 'unittest');
        $deletedata['password'] = array('type' => "=", 'data' => 'unittest');
        $deleteresult = $this->object->dbdeletemultiple('users', $deletedata);
        if (\g7mzr\db\common\Common::isError($deleteresult)) {
            $this->fail($deleteresult->getMessage());
        }


        // Data fields to be returned
        $searchfields = array('user_id', 'username', 'password', 'email');

        // Search using iser_id
        $searchdata = array('username' => 'unittest');
        $searchresult = $this->object->dbselectsingle(
            'users',
            $searchfields,
            $searchdata
        );
        $this->assertTrue(is_a($searchresult, '\g7mzr\db\common\Error'));
        $this->assertEquals(
            'Not Found',
            $searchresult->getMessage()
        );
    }

   /**
     * This function tests the Delete Multiple Capability
     *
     * @group unittest
     * @group error
     * @depends testSelectSingleRecord
     * @depends testInsert
     *
     * @return null
     */
    public function testDeleteMultipleRecordsInvalidSQL()
    {

        // Delete using username
        $deletedata = array();
        $deletedata['name'] = array('type' => "=", 'data' => 'unittest');
        $deleteresult = $this->object->dbdeletemultiple('users', $deletedata);

        $this->assertTrue(is_a($deleteresult, '\g7mzr\db\common\Error'));
        $this->assertEquals(
            'SQL Query Error',
            $deleteresult->getMessage()
        );
    }

    /**
     * This function tests the row count
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testRowCount()
    {
        // Data fields to be returned
        $fields = array('user_id', 'username', 'password', 'email');

        // Search using iser_id
        $searchdata = array('user_id' => '1');
        $searchresult = $this->object->dbselectsingle('users', $fields, $searchdata);
        if (\g7mzr\db\common\Common::isError($searchresult)) {
            $this->fail($searchresult->getMessage());
        }

        $rowcount = $this->object->rowCount();
        $this->assertEquals('1', $rowcount);
    }
}

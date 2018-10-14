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
class SQLAdminTest extends TestCase
{
    /**
     * DB CLASS
     *
     * @var \g7mzr\db\dbManager
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
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return null No return data
     */
    protected function setUp()
    {
        global $dsn;
        try {
            $this->object = new \g7mzr\db\DBManager(
                $dsn,
                $dsn["adminuser"],
                $dsn["adminpasswd"],
                true
            );
        } catch (Exception $ex) {
            echo $ex->getMessage();
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
        $mode = $this->object->setMode('admin');
        if (\g7mzr\db\common\Common::isError($mode)) {
            echo $mode->getMessage();
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
            'template1',
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
        $sql =  "DROP ROLE IF EXISTS unittest";
        $localconn->query($sql);
        $sql =  "DROP DATABASE IF EXISTS unittest";
        $localconn->query($sql);
        unset($localconn);
        $this->object = null;
    }


    /**
     * This function tests the result when the database connection fails as Admin
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return null
     */
    public function testFailtoConnectAdmin()
    {
        global $dsn;
        try {
            $dbobject = new \g7mzr\db\DBManager(
                $dsn,
                $dsn["adminuser"],
                "fakepassword",
                true
            );
        } catch (Exception $ex) {
            $this->fail($ex->getMessage());
        }
        $result = $dbobject->setMode('admin');
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertContains(
            "Admin: Unable to connect to database as administrator",
            $result->getMessage()
        );
    }

    /**
     * This function tests that the DB Version String can be returned
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return null
     */
    public function testDBVersion()
    {
        $result = $this->object->getAdminDriver()->getDBVersion();
        $this->assertContains($this->drivertype, $result);
    }


    /**
     * This function tests if an user already exists when they do
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return null
     */
    public function testUserExistsExistingUser()
    {
        global $dsn;
        $result = $this->object->getAdminDriver()->userExists($dsn['adminuser']);
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail("Error testing if a user exists in the database");
        }
        $this->assertTrue($result);
    }

    /**
     * This function tests if an user already exists when they don't
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return null
     */
    public function testUserExistsNoUser()
    {
        global $dsn;
        $result = $this->object->getAdminDriver()->userExists('unittest');
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail("Error testing if a user exists in the database");
        }
        $this->assertFalse($result);
    }

    /**
     * This function tests creating a user when they already exist
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return null
     */
    public function testCreateUserExistingUser()
    {
        global $dsn;
        $result = $this->object->getAdminDriver()->createUser(
            $dsn['adminuser'],
            $dsn['adminpasswd']
        );
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertContains(
            "Error Creating Database User",
            $result->getMessage()
        );
        $dbmessage = $result->getDBMessage();
    }

    /**
     * This function tests creating a new user
     *
     * @group unittest
     * @group DatabaseAccess
     * @depends testUserExistsExistingUser
     *
     * @return null
     */
    public function testCreateUserNewUser()
    {
        $result = $this->object->getAdminDriver()->createUser(
            'unittest',
            'password',
            true
        );
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail("Error Creating new user");
        }
        $this->assertTrue($result);
        $existsresult = $this->object->getAdminDriver()->userExists('unittest');
        if (\g7mzr\db\common\Common::isError($existsresult)) {
            $this->fail("Error Creating new user");
        }
        $this->assertTrue($existsresult);
        $this->userCreated = true;
    }

    /**
     * This function tests dropping an existing user
     *
     * @group unittest
     * @group DatabaseAccess
     * @depends testCreateUserNewUser
     *
     * @return null
     */
    public function testDropExistingUser()
    {
        // Create the Test User
        $result = $this->object->getAdminDriver()->createUser(
            'unittest',
            'password',
            true
        );
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail("Error Creating new user");
        }

        // Check the TEST user has been created
        $existsresult = $this->object->getAdminDriver()->userExists('unittest');
        if (\g7mzr\db\common\Common::isError($existsresult)) {
            $this->fail("Error Creating new user");
        }
        $this->assertTrue($existsresult);

        // Drop the Test Users
        $dropresult = $this->object->getAdminDriver()->dropUser('unittest');
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail("Error Dropping user");
        }

        // Check the TEST user has been dropped
        $existsresult = $this->object->getAdminDriver()->userExists('unittest');
        if (\g7mzr\db\common\Common::isError($existsresult)) {
            $this->fail("Error Dropping user");
        }
        $this->assertFalse($existsresult);
    }


    /**
     * This function tests dropping an non-existing user
     *
     * @group unittest
     * @group DatabaseAccess
     * @depends testCreateUserNewUser
     *
     * @return null
     */
    public function testDropNonExistingUser()
    {
        // Drop the Test Users
        $dropresult = $this->object->getAdminDriver()->dropUser('fakeuser');
        $this->assertTrue(is_a($dropresult, "\g7mzr\db\common\Error"));
        $this->assertEquals(
            "Error Dropping Database User",
            $dropresult->getMessage()
        );
    }

    /**
     * This function tests if a database already exists in the RDMS
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return null
     */
    public function testDatabaseExistsExistingDataBase()
    {
        global $dsn;
        $result = $this->object->getAdminDriver()->databaseExists("template1");
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail("Error testing if a database exists on " . $dsn['hostspec']);
        }
        $this->assertTrue($result);
    }


    /**
     * This function tests if a database does not exist on  the RDMS
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return null
     */
    public function testDatabaseExistsNoDataBase()
    {
        global $dsn;
        $result = $this->object->getAdminDriver()->databaseExists("fakedb");
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail("Error testing if a database exists on " . $dsn['hostspec']);
        }
        $this->assertFalse($result);
    }


    /**
     * This function tests that a new database can be created
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return null
     */
    public function testCreateDataBase()
    {
        global $dsn;
        $result = $this->object->getAdminDriver()->createDatabase(
            "unittest",
            $dsn['adminuser']
        );
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail(
                "Error creating a tests database exists on " . $dsn['hostspec']
            );
        }
        $this->assertTrue($result);
    }

    /**
     * This function tests that a new database cannot be created if it already exists
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return null
     */
    public function testCreateDataBaseExistingDatabase()
    {
        global $dsn;
        $result = $this->object->getAdminDriver()->createDatabase(
            "unittest",
            $dsn['adminuser']
        );
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail(
                "Error creating a tests database exists on " . $dsn['hostspec']
            );
        }
        $this->assertTrue($result);
        $createresult = $this->object->getAdminDriver()->createDatabase(
            "unittest",
            $dsn['adminuser']
        );
        $this->assertTrue(is_a($createresult, "\g7mzr\db\common\Error"));
        $this->assertEquals(
            "Error Creating the database",
            $createresult->getMessage()
        );
    }


    /**
     * This function tests that an existing database can be dropped
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return null
     */
    public function testDropDataBaseExistingDatabase()
    {
        global $dsn;
        $result = $this->object->getAdminDriver()->createDatabase(
            "unittest",
            $dsn['adminuser']
        );
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->fail(
                "Error creating a tests database exists on " . $dsn['hostspec']
            );
        }
        $this->assertTrue($result);
        $dropresult = $this->object->getAdminDriver()->dropDatabase('unittest');
        if (\g7mzr\db\common\Common::isError($dropresult)) {
            $this->fail(
                "Error dropping test database on " . $dsn['hostspec']
            );
        }
        $this->assertTrue($dropresult);
    }

    /**
     * This function tests that an existing database can be dropped
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return null
     */
    public function testDropDataBaseNonExistingDatabase()
    {
        global $dsn;
        $dropresult = $this->object->getAdminDriver()->dropDatabase('fakedb');
        $this->assertTrue(is_a($dropresult, "\g7mzr\db\common\Error"));
        $this->assertEquals(
            "Error Dropping Database",
            $dropresult->getMessage()
        );
    }
}

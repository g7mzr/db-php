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
 * DBManager Class Unit Tests
 *
 * This module contains the UNITTESTS for the DBManager module.
 */
class DBManagerTest extends TestCase
{
    /**
     * DB CLASS
     *
     * @var \g7mzr\db\DBManager
     */
    protected $object;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return void No return data
     */
    protected function setUp(): void
    {
        global $dsn;
        try {
            $this->object = new \g7mzr\db\DBManager(
                $dsn,
                $dsn["username"],
                $dsn["password"],
                true
            );
        } catch (Exception $ex) {
            echo $ex->getMessage();
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
        $this->object = null;
    }

    /**
     * This function tests the ErrorMessageFunction
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testErrorMessage()
    {
        // OK
        $okMsg = $this->object->errorMessage(DB_OK);
        $this->assertEquals("no error", $okMsg);

        // NotFound
        $notfoundMsg = $this->object->errorMessage(DB_ERROR_NOT_FOUND);
        $this->assertEquals("not found", $notfoundMsg);

        // Invalid Error Code
        $errorMsg = $this->object->errorMessage(-200);
        $this->assertEquals("unknown error", $errorMsg);
    }

    /**
     * This function tests the admin function can be selected using setMode
     *
     * @group unittest
     * @group SQLManager
     *
     * @return void No return data
     */
    public function testsetModeAdmin()
    {
        $result = $this->object->setMode('admin');
        $this->assertTrue($result);
        $this->assertTrue(is_a(
            $this->object->getAdminDriver(),
            '\g7mzr\db\interfaces\InterfaceDatabaseAdmin'
        ));
        //$this->assertNull($this->object->getSchemaDriver());
    }

    /**
     * This function tests the scehema function can be selected using setMode
     *
     * @group unittest
     * @group SQLManager
     *
     * @return void No return data
     */
    public function testsetModeSchema()
    {
        $result = $this->object->setMode('schema');
        $this->assertTrue($result);
        $this->assertTrue(is_a(
            $this->object->getSchemaDriver(),
            '\g7mzr\db\interfaces\InterfaceDatabaseSchema'
        ));
        //$this->assertNull($this->object->getAdminDriver());
    }

    /**
     * This function tests the dataaccess function can be selected using setMode
     *
     * @group unittest
     * @group SQLManager
     *
     * @return void No return data
     */
    public function testsetModeDataDriver()
    {
        $result = $this->object->setMode('datadriver');
        $this->assertTrue($result);
        $this->assertTrue(is_a(
            $this->object->getDataDriver(),
            '\g7mzr\db\interfaces\InterfaceDatabaseDriver'
        ));
        //$this->assertNull($this->object->getAdminDriver());
    }


    /**
     * This function tests an error is returned if the wrong function is selected
     * using setMode
     *
     * @group unittest
     * @group SQLManager
     *
      * @return void No return data
     */
    public function testsetModeInvalidFunction()
    {
        $result = $this->object->setMode('fail');
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertEquals(
            "Invalid Database Manager function selected",
            $result->getMessage()
        );
        //$this->assertNull($this->object->getAdminDriver());
        //$this->assertNull($this->object->getSchemaDriver());
    }

    /**
     * This function tests an error is returned if an invalid database driver is used
     * for the admin function
     *
     * @group unittest
     * @group SQLManager
     *
     * @return void No return data
     */
    public function testsetModeInvalidDBDriverAdmin()
    {
        global $dsn;

        // Set up invalid driver
        $localdsn = $dsn;
        $localdsn['dbtype'] = 'invalid';

        try {
            $db = new \g7mzr\db\DBManager(
                $localdsn,
                $localdsn["username"],
                $localdsn["password"],
                true
            );
        } catch (Exception $ex) {
            $this->fail($ex->getMessage());
        }

        $result = $db->setMode('admin');
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertEquals(
            "Admin: Unable to connect to database as administrator",
            $result->getMessage()
        );
        //$this->assertNull($db->getAdminDriver());
        //$this->assertNull($db->getSchemaDriver());
    }

    /**
     * This function tests an error is returned if an invalid database driver is used
     * for the schema function
     *
     * @group unittest
     * @group SQLManager
     *
     * @return void No return data
     */
    public function testsetModeInvalidDBDriverSchema()
    {
        global $dsn;

        // Set up invalid driver
        $localdsn = $dsn;
        $localdsn['dbtype'] = 'invalid';

        try {
            $db = new \g7mzr\db\DBManager(
                $localdsn,
                $localdsn["username"],
                $localdsn["password"],
                true
            );
        } catch (Exception $ex) {
            $this->fail($ex->getMessage());
        }

        $result = $db->setMode('schema');
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertEquals(
            "Schema: Unable to connect to database as administrator",
            $result->getMessage()
        );
        //$this->assertNull($db->getAdminDriver());
        //$this->assertNull($db->getSchemaDriver());
    }

    /**
     * This function tests an error is returned if an invalid database driver is used
     * for the datadriver function
     *
     * @group unittest
     * @group SQLManager
     *
     * @return void No return data
     */
    public function testsetModeInvalidDBDriverDataDriver()
    {
        global $dsn;

        // Set up invalid driver
        $localdsn = $dsn;
        $localdsn['dbtype'] = 'invalid';

        try {
            $db = new \g7mzr\db\DBManager(
                $localdsn,
                $localdsn["username"],
                $localdsn["password"],
                true
            );
        } catch (Exception $ex) {
            $this->fail($ex->getMessage());
        }

        $result = $db->setMode('datadriver');
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertEquals(
            "Admin: Unable to connect to database for data access",
            $result->getMessage()
        );
        //$this->assertNull($db->getAdminDriver());
        //$this->assertNull($db->getSchemaDriver());
    }


    /**
     * This function tests an error is returned if an $dsn is used for the admin
     * function
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return void No return data
     */
    public function testsetModeInvalidDSNAdmin()
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
        $this->assertStringContainsString(
            "Admin: Unable to connect to database as administrator",
            $result->getMessage()
        );
    }

    /**
     * This function tests an error is returned if an invalid $dsn is used for the
     * schema function
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return void No return data
     */
    public function testsetModeInvalidDSNSchema()
    {
        global $dsn;

        $localdsn = $dsn;
        $localdsn['password'] = "fakepassword";

        try {
            $dbobject = new \g7mzr\db\DBManager(
                $localdsn,
                $dsn["adminuser"],
                "fakepassword",
                true
            );
        } catch (Exception $ex) {
            $this->fail($ex->getMessage());
        }
        $result = $dbobject->setMode('schema');
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertStringContainsString(
            "Admin: Unable to connect to database as administrator",
            $result->getMessage()
        );
    }

    /**
     * This function tests an error is returned if an invalid $dsn is used for the
     * datadriver function
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return void No return data
     */
    public function testsetModeInvalidDSNDataDriver()
    {
        global $dsn;

        $localdsn = $dsn;
        $localdsn['password'] = "fakepassword";

        try {
            $dbobject = new \g7mzr\db\DBManager(
                $localdsn,
                $dsn["adminuser"],
                "fakepassword",
                true
            );
        } catch (Exception $ex) {
            $this->fail($ex->getMessage());
        }
        $result = $dbobject->setMode('datadriver');
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertStringContainsString(
            "Admin: Unable to connect to database for data access",
            $result->getMessage()
        );
    }

    /**
     * This function tests an exception is thrown if the Admin function is not set
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return void No return data
     */
    public function testInvalidAdminObject()
    {
        global $dsn;

        $localdsn = $dsn;

        try {
            $dbobject = new \g7mzr\db\DBManager(
                $localdsn,
                $dsn["adminuser"],
                $dsn['adminpasswd'],
                true
            );
        } catch (\Throwable $ex) {
            $this->fail($ex->getMessage());
        }

        try {
            $result = $this->object->getAdminDriver()->getDBVersion();
            $this->fail("Exception not raised for invalid Admin Driver");
        } catch (\Throwable $ex) {
            $this->assertStringContainsString(
                "Admin Driver not initalised",
                $ex->getMessage()
            );
            $this->assertEquals("", $ex->getDBMessage());
        }
    }

    /**
     * This function tests an exception is thrown if the Schema function is not set
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return void No return data
     */
    public function testInvalidSchemaObject()
    {
        global $dsn;

        $localdsn = $dsn;

        try {
            $dbobject = new \g7mzr\db\DBManager(
                $localdsn,
                $dsn["adminuser"],
                $dsn['adminpasswd'],
                true
            );
        } catch (\Throwable $ex) {
            $this->fail($ex->getMessage());
        }

        try {
            $result = $this->object->getSchemaDriver()->getDBVersion();
            $this->fail("Exception not raised for invalid Schema Driver");
        } catch (\Throwable $ex) {
            $this->assertStringContainsString(
                "Schema Driver not initalised",
                $ex->getMessage()
            );
        }
    }

   /**
     * This function tests an exception is thrown if the DataDriverfunction is not set
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return void No return data
     */
    public function testInvalidDataDriverObject()
    {
        global $dsn;

        $localdsn = $dsn;

        try {
            $dbobject = new \g7mzr\db\DBManager(
                $localdsn,
                $dsn["adminuser"],
                $dsn['adminpasswd'],
                true
            );
        } catch (\Throwable $ex) {
            $this->fail($ex->getMessage());
        }

        try {
            $result = $this->object->getDataDriver()->getDBVersion();
            $this->fail("Exception not raised for invalid Data Driver");
        } catch (\Throwable $ex) {
            $this->assertStringContainsString(
                "Data Driver not initalised",
                $ex->getMessage()
            );
        }
    }
}

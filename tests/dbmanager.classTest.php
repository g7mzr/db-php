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
     * @return null No return data
     */
    protected function setUp()
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
     * @return null No return data
     */
    protected function tearDown()
    {
        $this->object = null;
    }

    /**
     * This function tests the ErrorMessageFunction
     *
     * @group unittest
     * @group error
     *
     * @return null
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
     * @return null
     */
    public function testsetModeAdmin()
    {
        $result = $this->object->setMode('admin');
        $this->assertTrue($result);
        $this->assertTrue(is_a(
            $this->object->getAdminDriver(),
            '\g7mzr\db\interfaces\InterfaceDatabaseAdmin'
        ));
        $this->assertNull($this->object->getSchemaDriver());
    }

    /**
     * This function tests the scehema function can be selected using setMode
     *
     * @group unittest
     * @group SQLManager
     *
     * @return null
     */
    public function testsetModeSchema()
    {
        $result = $this->object->setMode('schema');
        $this->assertTrue($result);
        $this->assertTrue(is_a(
            $this->object->getSchemaDriver(),
            '\g7mzr\db\interfaces\InterfaceDatabaseSchema'
        ));
        $this->assertNull($this->object->getAdminDriver());
    }

    /**
     * This function tests an error is returned if the wrong function is selected
     * using setMode
     *
     * @group unittest
     * @group SQLManager
     *
     * @return null
     */
    public function testsetModeInvalidFunction()
    {
        $result = $this->object->setMode('fail');
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertEquals(
            "Invalid Database Manager function selected",
            $result->getMessage()
        );
        $this->assertNull($this->object->getAdminDriver());
        $this->assertNull($this->object->getSchemaDriver());
    }

    /**
     * This function tests an error is returned if an invalid database driver is used
     * for the admin function
     *
     * @group unittest
     * @group SQLManager
     *
     * @return null
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
        $this->assertNull($db->getAdminDriver());
        $this->assertNull($db->getSchemaDriver());
    }

    /**
     * This function tests an error is returned if an invalid database driver is used
     * for the schema function
     *
     * @group unittest
     * @group SQLManager
     *
     * @return null
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
        $this->assertNull($db->getAdminDriver());
        $this->assertNull($db->getSchemaDriver());
    }

    /**
     * This function tests an error is returned if an $dsn is used for the admin
     * function
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return null
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
        $this->assertContains(
            "Admin: Unable to connect to database as administrator",
            $result->getMessage()
        );
    }

    /**
     * This function tests an error is returned if an $dsn is used for the admin
     * function
     *
     * @group unittest
     * @group DatabaseAccess
     *
     * @return null
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
        $this->assertContains(
            "Admin: Unable to connect to database as administrator",
            $result->getMessage()
        );
    }
}

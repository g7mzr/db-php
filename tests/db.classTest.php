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
class DBTest extends TestCase
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
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return null No return data
     */
    protected function tearDown()
    {
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
        $okMsg = \g7mzr\db\DB::errorMessage(DB_OK);
        $this->assertEquals("no error", $okMsg);

        // NotFound
        $notfoundMsg = \g7mzr\db\DB::errorMessage(DB_ERROR_NOT_FOUND);
        $this->assertEquals("not found", $notfoundMsg);

        // Invalid Error Code
        $errorMsg = \g7mzr\db\DB::errorMessage(-200);
        $this->assertEquals("unknown error", $errorMsg);
    }

    /**
     * This function tests the DriverExists function with a valid driver
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testDriverExists()
    {
        $driverexists = \g7mzr\db\DB::driverexists('pgsql', '7.0.2');
        $this->assertTrue($driverexists);
    }

    /**
     * This function tests the DriverExists function with an invalid driver
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testDriverExistsInvalidDriver()
    {
        $driverexists = \g7mzr\db\DB::driverexists('nosql');
        $this->assertFalse($driverexists);
    }

    /**
     * This function tests the DriverExists function an invalid version of php
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testDriverExistsInvalidPHP()
    {
        $driverexists = \g7mzr\db\DB::driverexists('pgsql', '200.1.1');
        $this->assertFalse($driverexists);
    }

    /**
     * This function tests the loaddriver function with a valid driver
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testLoadDriver()
    {
        $driverloaded = \g7mzr\db\DB::loaddriver('pgsql');
        $this->assertTrue($driverloaded);
    }

    /**
     * This function tests the loaddriver function with an invalid driver
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testLoadDriverInvalidDriver()
    {
        $driverloaded = \g7mzr\db\DB::loaddriver('nosql');
        $this->assertTrue(is_a($driverloaded, '\g7mzr\db\common\Error'));
        $this->assertContains(
            'Unable to load database driver',
            $driverloaded->getMessage()
        );
    }

    /**
     * This function tests the load function with a valid driver
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testLoad()
    {
        global $dsn;
        $driverloaded = \g7mzr\db\DB::load($dsn, true);
        $result = is_a($driverloaded, '\g7mzr\db\drivers\DatabaseDriverpgsql');
        $this->assertTrue($result);
        $driverloaded = null;
    }

    /**
     * This function tests the load function with a empty db driver type
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testLoadNoDriver()
    {
        global $dsn;
        $localdsn = $dsn;
        $localdsn['dbtype'] = '';
        $driverloaded = \g7mzr\db\DB::load($localdsn, true);
        $result = is_a($driverloaded, '\g7mzr\db\common\Error');
        $this->assertTrue($result);
        $this->assertContains(
            'No RDBMS driver specified',
            $driverloaded->getMessage()
        );
    }

    /**
     * This function tests the loaddriver function with an invalid driver
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testLoadInvalidDriver()
    {
        global $dsn;
        $localdsn = $dsn;
        $localdsn['dbtype'] = 'nosql';
        $driverloaded = \g7mzr\db\DB::load($localdsn);
        $this->assertTrue(is_a($driverloaded, '\g7mzr\db\common\Error'));
        $this->assertContains(
            'Unable to load database driver',
            $driverloaded->getMessage()
        );
    }
    /**
     * This function tests that an exception is thrown if it cannot connect
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testFailedConnection()
    {
        global $dsn;
        $localdsn = $dsn;
        $localdsn["databasename"] = 'doesnotexist';
        $result = \g7mzr\db\DB::load($localdsn);
        $this->assertTrue(is_a($result, '\g7mzr\db\common\Error'));
        $this->assertEquals(
            'Unable to connect to the database',
            $result->getMessage()
        );
        $this->assertContains(
            'FATAL:  database "doesnotexist" does not exist',
            $result->getDBMessage()
        );
    }
}

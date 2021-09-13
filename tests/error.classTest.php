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

use PHPUnit\Framework\TestCase;

/**
 * Error Class Unit Tests
 *
 */
class ErrorTest extends TestCase
{
    /**
     * Error Class
     *
     * @var \g7mzr\db\common\Error
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
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void No return data
     */
    protected function tearDown(): void
    {
    }


    /**
     * This function tests thar an error object can be created
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testError()
    {
        $err = new \g7mzr\db\common\Error('Test Error', 1, array("ErrMsg" => 'DB Message'));
        $this->assertEquals("Test Error", $err->getMessage());
        $this->assertEquals(1, $err->getCode());
        $dbmsg = $err->getDBMessage();
        $this->assertEquals("DB Message", $dbmsg['ErrMsg']);
    }

    /**
     * This function tests that an error object can be raised
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testRaiseError()
    {
        $err = \g7mzr\db\common\Common::raiseError('Test Error', 1, array("ErrMsg" => 'DB Message'));
        $this->assertEquals("Test Error", $err->getMessage());
        $this->assertEquals(1, $err->getCode());
        $dbmsg = $err->getDBMessage();
        $this->assertEquals("DB Message", $dbmsg['ErrMsg']);
    }

    /**
     * This function tests that isError works
     *
     * @group unittest
     * @group error
     *
     * @return void No return data
     */
    public function testisError()
    {
        $err = \g7mzr\db\common\Common::raiseError('Test Error', 1);
        $this->assertTrue(\g7mzr\db\common\Common::isError($err));
    }
}

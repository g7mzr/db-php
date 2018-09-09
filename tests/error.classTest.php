<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace g7mzr\db\phpunit;

// Include the Class Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

/**
 * Error Class Unit Tests
 *
 * @category g7mzr\db
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
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
     * This function tests thar an error object can be created
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testError()
    {
        $err = new \g7mzr\db\common\Error('Test Error', 1, 'DB Message');
        $this->assertEquals("Test Error", $err->getMessage());
        $this->assertEquals(1, $err->getCode());
        $this->assertEquals("DB Message", $err->getDBMessage());
    }

    /**
     * This function tests that an error object can be raised
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testRaiseError()
    {
        $err = \g7mzr\db\common\Common::raiseError('Test Error', 1, 'DB Message');
        $this->assertEquals("Test Error", $err->getMessage());
        $this->assertEquals(1, $err->getCode());
        $this->assertEquals("DB Message", $err->getDBMessage());
    }

    /**
     * This function tests that isError works
     *
     * @group unittest
     * @group error
     *
     * @return null
     */
    public function testisError()
    {
        $err = \g7mzr\db\common\Common::raiseError('Test Error', 1);
        $this->assertTrue(\g7mzr\db\common\Common::isError($err));
    }
}

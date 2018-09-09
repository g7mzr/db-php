<?php
/**
 * This file is part of g7mzr/db
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace g7mzr\db\common;

/**
 * This module contains common code shared by the g7mzr\db class
 *
 * @category g7mzr\db
 * @package  common
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class Common
{

    /**
     * This method is used to check if the supplied variable is an error type
     *
     * @param mixed $data The value to test
     *
     * @return boolean True if $data is an error object
     *
     * @access public
     */
    public static function isError($data)
    {
        return is_a($data, '\g7mzr\db\common\Error', false);
    }

    /**
     * This method is raise an error
     *
     * @param mixed  $message   a text message or error object
     * @param int    $code      The error code
     * @param string $dbmessage The error message from the PDO Driver.
     *
     * @return object an Error object
     *
     * @access public
     */
    public static function raiseError(
        $message = null,
        $code = null,
        $dbmessage = null
    ) {
        return new \g7mzr\db\common\Error($message, $code, $dbmessage);
    }
}

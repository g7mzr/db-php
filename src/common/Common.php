<?php
/**
 * This file is part of PHP_Database_Client.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package db-php
 * @subpackage Common_Code
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/db-php/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\db\common;

/**
 * This module contains common code shared by the g7mzr\db class
 */
class Common
{

    /**
     * This method is used to check if the supplied variable is an DB error type.
     *
     * This method is used to check if the variable $data is of type
     * \g7mzr\db\common\Error which is the DB error object.  It will return true if
     * $data is of type \g7mzr\db\common\Error.
     *
     * @param mixed $data The value to test.
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
     * This method is used to create an error object of type \g7mzr\db\common\Error.
     *
     * This function is used to create an object of type \g7mzr\db\common\Error which
     * is a error object for the DB access module.
     *
     * @param string  $message   A text message or error object.
     * @param integer $code      The error code.
     * @param array   $dbmessage The error message from the PDO Driver.
     *
     * @return object A DB Access Module Error object
     *
     * @access public
     */
    public static function raiseError(
        string $message,
        int $code = 0,
        array $dbmessage = array()
    ) {
        return new \g7mzr\db\common\Error($message, $code, $dbmessage);
    }
}

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
 * This module is used to create the g7mzr\db error class
 */
class Error
{

    /*
     * Property Error Message
     * @var string
     * @acess protected
     */
    protected $errorMsg = '';

    /*
     * Property Error Code
     * @var integer
     * @access protected
     */
    protected $errorCode = 0;

    /*
     * Property Error Message from PDO Driver
     * @var string
     * @access protected
     */
    protected $dbmessage = '';

    /**
     * Constructor
     *
     * @param string  $errorMsg  The error message.
     * @param integer $errorCode The code of the error.
     * @param array   $dbmessage The error message from the PDO Driver.
     *
     * @access public
     */
    public function __construct(
        string $errorMsg,
        int $errorCode = 0,
        $dbmessage = array()
    ) {
        $this->errorMsg = $errorMsg;
        $this->errorCode = $errorCode;
        $this->dbmessage = $dbmessage;
    } // end constructor


    /**
     * This function returns the error message
     *
     * @return string Return the error message
     *
     * @since Method available since Release 1.0.0
     */
    public function getMessage()
    {
        return $this->errorMsg;
    }


    /**
     * This function returns the error code
     *
     * @return integer The error code
     *
     * @since Method available since Release 1.0.0
     */
    public function getCode()
    {
        return $this->errorCode;
    }

    /**
     * This function returns the error message
     *
     * @return string Return the error message
     *
     * @since Method available since Release 1.0.0
     */
    public function getDBMessage()
    {
        return $this->dbmessage;
    }
}

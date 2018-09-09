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
 * This module is used to create the g7mzr\db error class
 *
 * @category g7mzr\db
 * @package  common
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
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
     * @param string  $errorMsg  The error message the exception has thrown
     * @param integer $errorCode The code of the error
     * @param string  $dbmessage The error message from the PDO Driver.
     *
     * @access public
     */
    public function __construct(
        $errorMsg = null,
        $errorCode = null,
        $dbmessage = null
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

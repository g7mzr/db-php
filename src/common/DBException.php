<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace g7mzr\db\common;

/**
* Webtemplate Exception Class
*
* @category Webtemplate
* @package  Exception
* @author   Sandy McNeil <g7mzrdev@gmail.com>
* @license  View the license file distributed with this source code
**/
class DBException extends \Exception
{
    /**
     * Property Database Message
     *
     * @var string
     * @access protected
     */
    protected $dbmessage = '';
    /**
     * Constructor for AppException.
     *
     * AppException makes the message manditory unlike the PHP version
     *
     * @param string    $message   The DB Exception message to throw.
     * @param integer   $code      The Exception code.
     * @param string    $dbmessage The error message from the PDO Driver.
     * @param exception $previous  The previous exception used for chaining.
     *
     * @access public
     */
    public function __construct(
        $message,
        $code = 0,
        $dbmessage = '',
        Throwable $previous = null
    ) {
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
        $this->dbmessage = $dbmessage;
    }

    /**
     * Function to return the db message
     *
     * @return string Message from the PDO Driver
     */
    public function getDBMessage()
    {
        return $this->dbmessage;
    }
}

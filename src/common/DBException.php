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
 * PHP_Database_Client Exception Class
 */
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
     * AppException makes the message mandatory unlike the PHP version
     *
     * @param string    $message   The DB Exception message to throw.
     * @param integer   $code      The Exception code.
     * @param string    $dbmessage The error message from the PDO Driver.
     * @param exception $previous  The previous exception used for chaining.
     *
     * @access public
     */
    public function __construct(
        string $message,
        int $code = 0,
        string $dbmessage = '',
        exception $previous = null
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

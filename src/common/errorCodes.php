<?php
/**
 * This file is part of g7mzr\db.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace g7mzr\db;

/**
 * The list below contains the error codes for the DB Module
 *
 * If you add a code here make sure you add it to the textual version
 * in DB::errorMessage()
 */

/**
 * No Error
 */
define('DB_OK', true);

/**
 * Unspecified error
 */
define('DB_ERROR', -1);

/**
 * Search Parameters not found
 */
define('DB_ERROR_NOT_FOUND', -2);

/**
 * User or User:Password not found in database.
 */
define('DB_USER_NOT_FOUND', -3);

/**
 * Unable to connect to the database
 */
define('DB_CANNOT_CONNECT', -4);

/**
 * Error running DB Query
 */
define('DB_ERROR_QUERY', -5);

/**
 * Error Entering a Transaction
 */
define('DB_ERROR_TRANSACTION', -6);

/**
 * Error Saving Data
 */
define('DB_ERROR_SAVE', -7);

/**
 * Error Not Implemented
 */
define('DB_NOT_IMPLEMENTED', -8);

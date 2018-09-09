#!/usr/bin/env php
<?php

/**
 * This file is part of g7mzr/db
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__ . '/../testconfig.php';

require_once __DIR__ . '/../vendor/autoload.php';

switch ($dsn['dbtype']) {
    case 'pgsql':
        $classname = '\g7mzr\db\dbconfig\DBConfigpgsql';
        break;
    default:
        echo "\nError:  Database type '" . $dsn['dbtype'] . "' is unsupported.\n\n";
        exit(1);
        break;
}

$dbinstall = new $classname();

$result = $dbinstall->configdb($dsn);

if ($result === true) {
    echo "\nDatabase configured for testing\n\n";
    exit(0);
} else {
    echo "\nError: Unable to configure database for testing\n\n";
    exit(1);
}

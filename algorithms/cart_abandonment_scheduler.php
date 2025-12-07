<?php
/**
 * CRON Scheduler for Cart Abandonment Detection
 * Runs automatically every few minutes
 */

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/cart_abandonment_test.php'; // your algorithm file

// Turn debug OFF for cron execution
$DEBUG = false;

// Execute algorithm
detectAbandonedCartsDecisionTree(1); // cutoff (1 hour for real use or 1 min if testing)

file_put_contents(
    __DIR__ . "/cron_log.txt",
    "[" . date('Y-m-d H:i:s') . "] Abandonment check executed\n",
    FILE_APPEND
);

<?php
// Database configuration — XAMPP/MAMP defaults
define('DB_HOST', 'localhost');
define('DB_USER', 'rb_user');
define('DB_PASS', 'rb_pass_123');
define('DB_NAME', 'restaurant_booking_db');

function getDB(): mysqli {
    static $db = null;
    if ($db === null) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($db->connect_error) {
            die('DB connection failed: ' . $db->connect_error);
        }
        $db->set_charset('utf8mb4');
    }
    return $db;
}

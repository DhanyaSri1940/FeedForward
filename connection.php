<?php
// Database configuration
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'demo';

// Create connection
$connection = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$connection) {
    die('Connection failed: ' . mysqli_connect_error());
}

// Select database
if (!mysqli_select_db($connection, $database)) {
    die('Database selection failed: ' . mysqli_error($connection));
}

// Connection successful
// echo 'Connected successfully to the database.';
?> 
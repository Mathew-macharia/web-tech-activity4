<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); // TODO: Update with your MySQL root password if set
define('DB_NAME', 'web_tech_activity4'); // Define the database name here

// Establish database connection
// The database name will be created by db_init.php, so we connect without specifying it initially
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, 3307);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

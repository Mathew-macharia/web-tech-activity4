<?php
require_once __DIR__ . '/../includes/config.php';

// The database name is defined in config.php as DB_NAME
$dbname = DB_NAME;

// Create database if it doesn't exist
$sql_create_db = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql_create_db) === TRUE) {
    echo "database created successfully\n";
} else {
    echo "Error creating database: " . $conn->error . "\n";
}

// Select the database
$conn->select_db($dbname);

// SQL to create users table
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'faculty') NOT NULL,
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql_users) === TRUE) {
    echo "table 'users' created successfully or already exists\n";
} else {
    echo "Error creating table 'users': " . $conn->error . "\n";
}

// sql to create courses table
$sql_courses = "CREATE TABLE IF NOT EXISTS courses (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL UNIQUE,
    faculty_id INT(6) UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES users(id)
)";

if ($conn->query($sql_courses) === TRUE) {
    echo "Table 'courses' created successfully or already exists\n";
} else {
    echo "Error creating table 'courses': " . $conn->error . "\n";
}

// sql to create requests table
$sql_requests = "CREATE TABLE IF NOT EXISTS requests (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT(6) UNSIGNED NOT NULL,
    course_id INT(6) UNSIGNED NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    UNIQUE (student_id, course_id)
)";

if ($conn->query($sql_requests) === TRUE) {
    echo "Table 'requests' created successfully or already exists\n";
} else {
    echo "Error creating table 'requests': " . $conn->error . "\n";
}

$conn->close();
?>

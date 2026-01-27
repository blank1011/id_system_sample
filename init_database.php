<?php
// init_database.php - Create database and tables

require_once 'config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists.<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

$conn->select_db(DB_NAME);

// Update the table creation queries in init_database.php
$sql = "CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    student_number VARCHAR(50) UNIQUE NOT NULL,
    date_received DATE NOT NULL,
    expiration_date DATE NOT NULL,
    comments TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

$sql = "CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    employee_number VARCHAR(50) UNIQUE NOT NULL,
    date_received DATE NOT NULL,
    expiration_date DATE NOT NULL,
    comments TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Employees table created successfully.<br>";
} else {
    echo "Error creating employees table: " . $conn->error . "<br>";
}

// Create indexes for faster expiration date queries
$index_queries = [
    "CREATE INDEX IF NOT EXISTS idx_student_expiration ON students(expiration_date)",
    "CREATE INDEX IF NOT EXISTS idx_employee_expiration ON employees(expiration_date)"
];

foreach ($index_queries as $index_query) {
    if ($conn->query($index_query) === TRUE) {
        echo "Index created successfully.<br>";
    } else {
        echo "Error creating index: " . $conn->error . "<br>";
    }
}

$conn->close();
echo "<h3>Database setup completed!</h3>";
?>
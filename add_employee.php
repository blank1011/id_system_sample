<?php
// add_employee.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $employee_number = $_POST['employee_number'];
    $date_received = $_POST['date_received'];
    $expiration_date = $_POST['expiration_date'];
    
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("INSERT INTO employees (full_name, employee_number, date_received, expiration_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $full_name, $employee_number, $date_received, $expiration_date);
    
    if ($stmt->execute()) {
        header("Location: index.php?message=Employee added successfully");
    } else {
        header("Location: index.php?error=" . urlencode("Error: " . $stmt->error));
    }
    
    $stmt->close();
    $conn->close();
}
?>
<?php
// add_student.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $student_number = $_POST['student_number'];
    $date_received = $_POST['date_received'];
    $expiration_date = $_POST['expiration_date'];
    $comments = $_POST['comments'] ?? '';
    
    // Check if lost ID checkbox is checked
    if (isset($_POST['lost_id'])) {
        $comments = "Lost ID - Payment required";
    }
    
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("INSERT INTO students (full_name, student_number, date_received, expiration_date, comments) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $full_name, $student_number, $date_received, $expiration_date, $comments);
    
    if ($stmt->execute()) {
        header("Location: index.php?message=Student added successfully");
    } else {
        header("Location: index.php?error=" . urlencode("Error: " . $stmt->error));
    }
    
    $stmt->close();
    $conn->close();
}
?>
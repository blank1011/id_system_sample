<?php
// delete_student.php
require_once 'config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: index.php?message=Student deleted successfully");
    } else {
        header("Location: index.php?error=" . urlencode("Error deleting student: " . $stmt->error));
    }
    
    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php?error=No student ID provided");
}
?>
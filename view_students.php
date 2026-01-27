<?php
// view_students.php
require_once 'config.php';

$conn = getDBConnection();
$result = $conn->query("SELECT * FROM students ORDER BY expiration_date");

$today = date('Y-m-d');

echo "<table>";
echo "<tr><th>Full Name</th><th>Student Number</th><th>Date Received</th><th>Expiration Date</th><th>Days Remaining</th><th>Comments</th><th>Status</th><th>Actions</th></tr>";

while ($row = $result->fetch_assoc()) {
    $exp_date = new DateTime($row['expiration_date']);
    $today_date = new DateTime($today);
    $interval = $today_date->diff($exp_date);
    $days_remaining = $interval->days;
    
    if ($today > $row['expiration_date']) {
        $status = "Expired";
        $row_class = "expired";
        $days_remaining = -$days_remaining;
    } elseif ($days_remaining <= 30) {
        $status = "Expiring Soon";
        $row_class = "expiring-soon";
    } else {
        $status = "Active";
        $row_class = "";
    }
    
    echo "<tr class='$row_class'>";
    echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['student_number']) . "</td>";
    echo "<td>" . $row['date_received'] . "</td>";
    echo "<td>" . $row['expiration_date'] . "</td>";
    echo "<td>" . $days_remaining . " days</td>";
    echo "<td>" . ($row['comments'] ? htmlspecialchars($row['comments']) : 'No comments') . "</td>";
    echo "<td>" . $status . "</td>";
    echo "<td>
            <a href='edit_student.php?id=" . $row['id'] . "' style='background: #3498db; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; margin-right: 5px;'>Edit</a>
            <a href='delete_student.php?id=" . $row['id'] . "' onclick=\"return confirm('Are you sure you want to delete this student record?')\" style='background: #e74c3c; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px;'>Delete</a>
          </td>";
    echo "</tr>";
}

echo "</table>";

$conn->close();
?>
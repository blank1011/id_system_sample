<?php
// expiring_soon.php
require_once 'config.php';

$conn = getDBConnection();
$today = date('Y-m-d');
$next_month = date('Y-m-d', strtotime('+30 days'));

// Get expiring students
echo "<h3>Students</h3>";
$result = $conn->query("SELECT * FROM students WHERE expiration_date BETWEEN '$today' AND '$next_month' ORDER BY expiration_date");

if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Full Name</th><th>Student Number</th><th>Date Received</th><th>Expiration Date</th><th>Days Remaining</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $exp_date = new DateTime($row['expiration_date']);
        $today_date = new DateTime($today);
        $interval = $today_date->diff($exp_date);
        $days_remaining = $interval->days;
        
        echo "<tr class='expiring-soon'>";
        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['student_number']) . "</td>";
        echo "<td>" . $row['date_received'] . "</td>";
        echo "<td>" . $row['expiration_date'] . "</td>";
        echo "<td>" . $days_remaining . " days</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No students with IDs expiring soon.</p>";
}

// Get expiring employees
echo "<h3>Employees</h3>";
$result = $conn->query("SELECT * FROM employees WHERE expiration_date BETWEEN '$today' AND '$next_month' ORDER BY expiration_date");

if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Full Name</th><th>Employee Number</th><th>Date Received</th><th>Expiration Date</th><th>Days Remaining</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $exp_date = new DateTime($row['expiration_date']);
        $today_date = new DateTime($today);
        $interval = $today_date->diff($exp_date);
        $days_remaining = $interval->days;
        
        echo "<tr class='expiring-soon'>";
        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['employee_number']) . "</td>";
        echo "<td>" . $row['date_received'] . "</td>";
        echo "<td>" . $row['expiration_date'] . "</td>";
        echo "<td>" . $days_remaining . " days</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No employees with IDs expiring soon.</p>";
}

$conn->close();
?>
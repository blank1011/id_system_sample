<?php
// edit_student.php
require_once 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch student data
$student = null;
if ($id > 0) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
}

if (!$student) {
    header("Location: index.php?error=Student not found");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $student_number = $_POST['student_number'];
    $date_received = $_POST['date_received'];
    $expiration_date = $_POST['expiration_date'];
    $comments = $_POST['comments'];
    
    // Check if lost ID checkbox is checked
    if (isset($_POST['lost_id'])) {
        $comments = "Lost ID - Payment required";
    }
    
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("UPDATE students SET full_name = ?, student_number = ?, date_received = ?, expiration_date = ?, comments = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $full_name, $student_number, $date_received, $expiration_date, $comments, $id);
    
    if ($stmt->execute()) {
        header("Location: index.php?message=Student updated successfully");
    } else {
        header("Location: index.php?error=" . urlencode("Error: " . $stmt->error));
    }
    
    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="date"], textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        textarea { height: 100px; }
        .checkbox-group { display: flex; align-items: center; gap: 10px; margin-bottom: 15px; }
        button { padding: 10px 20px; background: #27ae60; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px; }
        button:hover { background: #219a52; }
        .btn-secondary { background: #7f8c8d; }
        .btn-secondary:hover { background: #6c7b7d; }
        .quick-actions { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .quick-actions h3 { margin-top: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Edit Student Record</h1>
        </div>
        
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <p><strong>Check this for lost ID:</strong></p>
            <div class="checkbox-group">
                <input type="checkbox" id="lostIdCheckbox" onchange="setLostIDComment()">
                <label for="lostIdCheckbox" style="font-weight: normal;">Lost ID - Needs Payment</label>
            </div>
            <button type="button" onclick="setRenewal()" style="background: #3498db; margin-bottom: 5px;">Set 1-Year Renewal</button>
            <button type="button" onclick="setSixMonths()" style="background: #9b59b6; margin-bottom: 5px;">Set 6-Month Renewal</button>
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Student Number:</label>
                <input type="text" name="student_number" value="<?php echo htmlspecialchars($student['student_number']); ?>" required>
            </div>
            <div class="form-group">
                <label>Date ID Received:</label>
                <input type="date" name="date_received" value="<?php echo $student['date_received']; ?>" required>
            </div>
            <div class="form-group">
                <label>Expiration Date:</label>
                <input type="date" name="expiration_date" id="expiration_date" value="<?php echo $student['expiration_date']; ?>" required>
            </div>
            <div class="form-group">
                <label>Comments:</label>
                <textarea name="comments" id="comments"><?php echo htmlspecialchars($student['comments'] ?? ''); ?></textarea>
            </div>
            
            <input type="checkbox" name="lost_id" id="lost_id" style="display: none;">
            
            <div class="form-group">
                <button type="submit">Update Student</button>
                <a href="index.php" class="btn-secondary" style="padding: 10px 20px; background: #7f8c8d; color: white; text-decoration: none; border-radius: 4px;">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        function setLostIDComment() {
            var checkbox = document.getElementById('lostIdCheckbox');
            var hiddenCheckbox = document.getElementById('lost_id');
            var commentsField = document.getElementById('comments');
            
            if (checkbox.checked) {
                commentsField.value = "Lost ID - Payment required";
                hiddenCheckbox.checked = true;
            } else {
                commentsField.value = commentsField.defaultValue;
                hiddenCheckbox.checked = false;
            }
        }
        
        function setRenewal() {
            var today = new Date();
            var oneYearLater = new Date();
            oneYearLater.setFullYear(today.getFullYear() + 1);
            
            var formattedDate = oneYearLater.toISOString().split('T')[0];
            document.getElementById('expiration_date').value = formattedDate;
        }
        
        function setSixMonths() {
            var today = new Date();
            var sixMonthsLater = new Date();
            sixMonthsLater.setMonth(today.getMonth() + 6);
            
            var formattedDate = sixMonthsLater.toISOString().split('T')[0];
            document.getElementById('expiration_date').value = formattedDate;
        }
        
        // Pre-check the lost ID checkbox if comments already contain the lost ID text
        window.onload = function() {
            var commentsField = document.getElementById('comments');
            if (commentsField.value.includes("Lost ID") || commentsField.value.includes("Payment required")) {
                document.getElementById('lostIdCheckbox').checked = true;
                document.getElementById('lost_id').checked = true;
            }
        };
    </script>
</body>
</html>
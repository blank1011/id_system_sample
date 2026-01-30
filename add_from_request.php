<?php
require_once 'config.php';

$conn = getDBConnection();
$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get request data
$request = null;
if ($request_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM photo_requests WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();
    $stmt->close();
}

if (!$request) {
    header("Location: manage_requests.php?error=Request not found");
    exit();
}

// Handle form submission to add to student records
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $student_number = $_POST['student_number'];
    $date_received = $_POST['date_received'];
    $expiration_date = $_POST['expiration_date'];
    $comments = $_POST['comments'];
    
    // Check if student already exists
    $check_stmt = $conn->prepare("SELECT id FROM students WHERE student_number = ?");
    $check_stmt->bind_param("s", $student_number);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Update existing student
        $stmt = $conn->prepare("UPDATE students SET full_name = ?, date_received = ?, expiration_date = ?, comments = ? WHERE student_number = ?");
        $stmt->bind_param("sssss", $full_name, $date_received, $expiration_date, $comments, $student_number);
    } else {
        // Insert new student
        $stmt = $conn->prepare("INSERT INTO students (full_name, student_number, date_received, expiration_date, comments) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $full_name, $student_number, $date_received, $expiration_date, $comments);
    }
    
    if ($stmt->execute()) {
        // Update request status to completed
        $update_stmt = $conn->prepare("UPDATE photo_requests SET status = 'completed', date_completed = CURDATE() WHERE id = ?");
        $update_stmt->bind_param("i", $request_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        header("Location: manage_requests.php?message=Student added/updated successfully");
    } else {
        header("Location: manage_requests.php?error=Error: " . urlencode($stmt->error));
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
    <title>Add Student from Photo Request</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="date"], textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        textarea { height: 100px; }
        button { padding: 10px 20px; background: #27ae60; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px; }
        button:hover { background: #219a52; }
        .btn-secondary { background: #7f8c8d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; }
        .photo-preview { text-align: center; margin-bottom: 20px; }
        .photo-preview img { max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd; }
        .quick-actions { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .quick-actions button { margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Add Student to Records</h1>
            <p>From Photo Request #<?php echo $request['id']; ?></p>
        </div>
        
        <?php if ($request['photo_filename']): ?>
        <div class="photo-preview">
            <img src="<?php echo UPLOAD_DIR . $request['photo_filename']; ?>" alt="Student Photo">
            <p>Uploaded Photo</p>
        </div>
        <?php endif; ?>
        
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <button type="button" onclick="setRenewal()">Set 1-Year Validity</button>
            <button type="button" onclick="setSixMonths()">Set 6-Month Validity</button>
            <button type="button" onclick="setLostID()">Mark as Lost ID</button>
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($request['full_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Student Number:</label>
                <input type="text" name="student_number" value="<?php echo htmlspecialchars($request['student_number']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Date ID Received:</label>
                <input type="date" name="date_received" id="date_received" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Expiration Date:</label>
                <input type="date" name="expiration_date" id="expiration_date" value="<?php echo date('Y-m-d', strtotime('+1 year')); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Comments:</label>
                <textarea name="comments" id="comments">From photo request #<?php echo $request['id']; ?> - <?php echo $request['request_type']; ?> request<?php echo $request['reason'] ? ': ' . $request['reason'] : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit">Add to Student Records</button>
                <a href="manage_requests.php" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <script>
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
        
        function setLostID() {
            document.getElementById('comments').value = "Lost ID - Payment required (From photo request #<?php echo $request['id']; ?>)";
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
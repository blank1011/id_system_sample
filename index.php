
<?php
// index.php - Main dashboard
require_once 'config.php';

// index.php - Add this at the top after the opening PHP tag
if (isset($_GET['message'])) {
    echo '<div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #c3e6cb;">';
    echo htmlspecialchars($_GET['message']);
    echo '</div>';
}

if (isset($_GET['error'])) {
    echo '<div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #f5c6cb;">';
    echo htmlspecialchars($_GET['error']);
    echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Expiration Tracking System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .nav { display: flex; gap: 10px; margin-bottom: 20px; }
        .nav button { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; background: #3498db; color: white; }
        .nav button:hover { background: #2980b9; }
        .section { display: none; }
        .active { display: block; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .expiring-soon { background-color: #fff3cd; }
        .expired { background-color: #f8d7da; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="date"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 8px 16px; background: #27ae60; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #219a52; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ID Expiration Tracking System</h1>
            <p>Track student and employee ID expiration dates</p>
        </div>
        
        <div class="nav">
            <button onclick="showSection('dashboard')">Dashboard</button>
            <button onclick="showSection('addStudent')">Add Student</button>
            <button onclick="showSection('addEmployee')">Add Employee</button>
            <button onclick="showSection('viewStudents')">View Students</button>
            <button onclick="showSection('viewEmployees')">View Employees</button>
            <button onclick="showSection('expiringSoon')">Expiring Soon</button>
        </div>
        
        <!-- Dashboard -->
        <div id="dashboard" class="section active">
            <h2>Dashboard</h2>
            <?php
            $conn = getDBConnection();
            
            // Get counts
            $student_count = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
            $employee_count = $conn->query("SELECT COUNT(*) as count FROM employees")->fetch_assoc()['count'];
            
            // Get expiring soon (within 30 days)
            $today = date('Y-m-d');
            $next_month = date('Y-m-d', strtotime('+30 days'));
            
            $expiring_students = $conn->query("SELECT COUNT(*) as count FROM students WHERE expiration_date BETWEEN '$today' AND '$next_month'")->fetch_assoc()['count'];
            $expiring_employees = $conn->query("SELECT COUNT(*) as count FROM employees WHERE expiration_date BETWEEN '$today' AND '$next_month'")->fetch_assoc()['count'];
            
            // Get expired
            $expired_students = $conn->query("SELECT COUNT(*) as count FROM students WHERE expiration_date < '$today'")->fetch_assoc()['count'];
            $expired_employees = $conn->query("SELECT COUNT(*) as count FROM employees WHERE expiration_date < '$today'")->fetch_assoc()['count'];
            
            $conn->close();
            ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div style="background: #3498db; color: white; padding: 20px; border-radius: 8px;">
                    <h3>Total Students</h3>
                    <h1 style="margin: 10px 0;"><?php echo $student_count; ?></h1>
                </div>
                <div style="background: #9b59b6; color: white; padding: 20px; border-radius: 8px;">
                    <h3>Total Employees</h3>
                    <h1 style="margin: 10px 0;"><?php echo $employee_count; ?></h1>
                </div>
                <div style="background: #f39c12; color: white; padding: 20px; border-radius: 8px;">
                    <h3>Expiring Soon (30 days)</h3>
                    <h1 style="margin: 10px 0;"><?php echo $expiring_students + $expiring_employees; ?></h1>
                </div>
                <div style="background: #e74c3c; color: white; padding: 20px; border-radius: 8px;">
                    <h3>Expired IDs</h3>
                    <h1 style="margin: 10px 0;"><?php echo $expired_students + $expired_employees; ?></h1>
                </div>
            </div>
        </div>
        
        <!-- Add Student Form -->
        <!-- Add Student Form -->
        <div id="addStudent" class="section">
            <h2>Add Student Record</h2>
            <form method="POST" action="add_student.php">
                <div class="form-group">
                    <label>Full Name:</label>
                    <input type="text" name="full_name" required>
                </div>
                <div class="form-group">
                    <label>Student Number:</label>
                    <input type="text" name="student_number" required>
                </div>
                <div class="form-group">
                    <label>Date ID Received:</label>
                    <input type="date" name="date_received" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Expiration Date:</label>
                    <input type="date" name="expiration_date" required>
                </div>
                <div class="form-group">
                    <label>Comments:</label>
                    <textarea name="comments"></textarea>
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" id="addLostStudent" name="lost_id">
                    <label for="addLostStudent" style="font-weight: normal;">Lost ID - Needs Payment</label>
                </div>
                <button type="submit">Add Student</button>
            </form>
        </div>
                
        <!-- Add Employee Form -->
        <div id="addEmployee" class="section">
            <h2>Add Employee Record</h2>
            <form method="POST" action="add_employee.php">
                <div class="form-group">
                    <label>Full Name:</label>
                    <input type="text" name="full_name" required>
                </div>
                <div class="form-group">
                    <label>Employee Number:</label>
                    <input type="text" name="employee_number" required>
                </div>
                <div class="form-group">
                    <label>Date ID Received:</label>
                    <input type="date" name="date_received" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Expiration Date:</label>
                    <input type="date" name="expiration_date" required>
                </div>
                <button type="submit">Add Employee</button>
            </form>
        </div>
        
        <!-- View Students -->
        <div id="viewStudents" class="section">
            <h2>Student Records</h2>
            <?php include 'view_students.php'; ?>
        </div>
        
        <!-- View Employees -->
        <div id="viewEmployees" class="section">
            <h2>Employee Records</h2>
            <?php include 'view_employees.php'; ?>
        </div>
        
        <!-- Expiring Soon -->
        <div id="expiringSoon" class="section">
            <h2>IDs Expiring Soon (Within 30 Days)</h2>
            <?php include 'expiring_soon.php'; ?>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Show selected section
            document.getElementById(sectionId).classList.add('active');
        }
                    // For Add Student form
            document.getElementById('addLostStudent')?.addEventListener('change', function() {
                if (this.checked) {
                    document.querySelector('#addStudent textarea[name="comments"]').value = "Lost ID - Payment required";
                } else {
                    document.querySelector('#addStudent textarea[name="comments"]').value = "";
                }
            });

            // For Add Employee form
            document.getElementById('addLostEmployee')?.addEventListener('change', function() {
                if (this.checked) {
                    document.querySelector('#addEmployee textarea[name="comments"]').value = "Lost ID - Payment required";
                } else {
                    document.querySelector('#addEmployee textarea[name="comments"]').value = "";
                }
            });

            // For Add Employee form
            document.getElementById('addLostEmployee')?.addEventListener('change', function() {
                if (this.checked) {
                    document.querySelector('#addEmployee textarea[name="comments"]').value = "Lost ID - Payment required";
                } else {
                    document.querySelector('#addEmployee textarea[name="comments"]').value = "";
                }
            });
    </script>
</body>
</html>
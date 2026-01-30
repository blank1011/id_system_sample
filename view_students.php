<?php
// view_students.php
require_once 'config.php';

$conn = getDBConnection();

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT * FROM students WHERE 1=1";

if ($search) {
    $query .= " AND (full_name LIKE '%$search%' OR student_number LIKE '%$search%')";
}

$query .= " ORDER BY expiration_date";
$result = $conn->query($query);

$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Students - ID System</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background-color: #f5f7fa; 
        }
        .container { 
            max-width: 1400px; 
            margin: 0 auto; 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 2px 20px rgba(0,0,0,0.1); 
        }
        .header { 
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); 
            color: white; 
            padding: 25px; 
            border-radius: 10px; 
            margin-bottom: 30px; 
        }
        .header h1 { 
            margin: 0; 
            font-size: 28px; 
            display: flex; 
            align-items: center; 
            gap: 15px; 
        }
        .header p { 
            margin: 10px 0 0; 
            opacity: 0.9; 
        }
        .controls { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px; 
            gap: 20px; 
        }
        .search-box { 
            flex: 1; 
            max-width: 400px; 
            position: relative; 
        }
        .search-box input { 
            width: 100%; 
            padding: 12px 15px 12px 45px; 
            border: 1px solid #e0e6ed; 
            border-radius: 6px; 
            font-size: 16px; 
        }
        .search-box i { 
            position: absolute; 
            left: 15px; 
            top: 50%; 
            transform: translateY(-50%); 
            color: #95a5a6; 
        }
        .action-buttons { 
            display: flex; 
            gap: 10px; 
        }
        .btn { 
            padding: 12px 24px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-size: 14px; 
            font-weight: 600; 
            text-decoration: none; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            transition: transform 0.3s; 
        }
        .btn-primary { 
            background: linear-gradient(135deg, #27ae60 0%, #219a52 100%); 
            color: white; 
        }
        .btn-secondary { 
            background: #95a5a6; 
            color: white; 
        }
        .btn:hover { 
            transform: translateY(-2px); 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        th, td { 
            padding: 15px; 
            text-align: left; 
            border-bottom: 1px solid #ecf0f1; 
        }
        th { 
            background-color: #f8f9fa; 
            font-weight: 600; 
            color: #2c3e50; 
            position: sticky; 
            top: 0; 
        }
        tr:hover { 
            background-color: #f8f9fa; 
        }
        .status-badge { 
            padding: 6px 12px; 
            border-radius: 20px; 
            font-size: 12px; 
            font-weight: bold; 
            display: inline-block; 
        }
        .status-active { background: #d4edda; color: #155724; }
        .status-expiring { background: #fff3cd; color: #856404; }
        .status-expired { background: #f8d7da; color: #721c24; }
        
        .action-cell { 
            display: flex; 
            gap: 8px; 
        }
        .action-btn { 
            padding: 6px 12px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 12px; 
            font-weight: 600; 
            text-decoration: none; 
            display: inline-flex; 
            align-items: center; 
            gap: 5px; 
        }
        .btn-edit { background: #3498db; color: white; }
        .btn-delete { background: #e74c3c; color: white; }
        
        .no-data { 
            text-align: center; 
            padding: 50px; 
            color: #7f8c8d; 
        }
        .no-data i { 
            font-size: 48px; 
            margin-bottom: 15px; 
            opacity: 0.3; 
            display: block; 
        }
        
        .stats-bar { 
            display: flex; 
            gap: 20px; 
            margin-bottom: 20px; 
            padding: 15px; 
            background: #f8f9fa; 
            border-radius: 8px; 
        }
        .stat-item { 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            flex: 1; 
        }
        .stat-number { 
            font-size: 24px; 
            font-weight: bold; 
            color: #2c3e50; 
        }
        .stat-label { 
            font-size: 12px; 
            color: #7f8c8d; 
            margin-top: 5px; 
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-user-graduate"></i> Student Records</h1>
            <p>View and manage all student ID records</p>
        </div>
        
        <?php
        // Calculate statistics
        $total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
        $active_students = $conn->query("SELECT COUNT(*) as count FROM students WHERE expiration_date >= CURDATE()")->fetch_assoc()['count'];
        $expired_students = $conn->query("SELECT COUNT(*) as count FROM students WHERE expiration_date < CURDATE()")->fetch_assoc()['count'];
        ?>
        
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-number"><?php echo $total_students; ?></div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $active_students; ?></div>
                <div class="stat-label">Active IDs</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $expired_students; ?></div>
                <div class="stat-label">Expired IDs</div>
            </div>
        </div>
        
        <div class="controls">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search students..." 
                       value="<?php echo htmlspecialchars($search); ?>"
                       onkeyup="searchTable()">
            </div>
            
            <div class="action-buttons">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <a href="add_student.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add New Student
                </a>
            </div>
        </div>
        
        <?php if ($result->num_rows > 0): ?>
        <div style="overflow-x: auto;">
            <table id="studentsTable">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Student Number</th>
                        <th>Date Received</th>
                        <th>Expiration Date</th>
                        <th>Days Remaining</th>
                        <th>Status</th>
                        <th>Comments</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): 
                        $exp_date = new DateTime($row['expiration_date']);
                        $today_date = new DateTime($today);
                        $interval = $today_date->diff($exp_date);
                        $days_remaining = $interval->days;
                        
                        if ($today > $row['expiration_date']) {
                            $status = "Expired";
                            $status_class = "status-expired";
                            $days_remaining = -$days_remaining;
                        } elseif ($days_remaining <= 30) {
                            $status = "Expiring Soon";
                            $status_class = "status-expiring";
                        } else {
                            $status = "Active";
                            $status_class = "status-active";
                        }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['student_number']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['date_received'])); ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['expiration_date'])); ?></td>
                        <td><?php echo $days_remaining; ?> days</td>
                        <td>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo $status; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($row['comments'] ?: 'N/A'); ?></td>
                        <td class="action-cell">
                            <a href="edit_student.php?id=<?php echo $row['id']; ?>" 
                               class="action-btn btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete_student.php?id=<?php echo $row['id']; ?>" 
                               onclick="return confirm('Delete this student record?')"
                               class="action-btn btn-delete">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="no-data">
                <i class="fas fa-user-graduate"></i>
                <h3>No Student Records Found</h3>
                <p>No student records have been added yet.</p>
                <a href="add_student.php" class="btn btn-primary" style="margin-top: 15px;">
                    <i class="fas fa-user-plus"></i> Add First Student
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function searchTable() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#studentsTable tbody tr');
            
            rows.forEach(row => {
                const name = row.cells[0].textContent.toLowerCase();
                const number = row.cells[1].textContent.toLowerCase();
                
                if (name.includes(search) || number.includes(search)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
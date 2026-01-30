<?php
// expiring_soon.php
require_once 'config.php';

$conn = getDBConnection();
$today = date('Y-m-d');
$next_month = date('Y-m-d', strtotime('+30 days'));

// Get expiring students
$expiring_students = $conn->query("
    SELECT * FROM students 
    WHERE expiration_date BETWEEN '$today' AND '$next_month' 
    ORDER BY expiration_date
");

// Get expiring employees
$expiring_employees = $conn->query("
    SELECT * FROM employees 
    WHERE expiration_date BETWEEN '$today' AND '$next_month' 
    ORDER BY expiration_date
");

// Count totals
$total_expiring = $expiring_students->num_rows + $expiring_employees->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expiring Soon - ID System</title>
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
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); 
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
        .stats-card { 
            background: #fff9e6; 
            padding: 25px; 
            border-radius: 10px; 
            margin-bottom: 30px; 
            border-left: 4px solid #f39c12; 
        }
        .stats-card h2 { 
            margin-top: 0; 
            color: #856404; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px; 
            margin-top: 20px; 
        }
        .stat-item { 
            text-align: center; 
        }
        .stat-number { 
            font-size: 36px; 
            font-weight: bold; 
            color: #f39c12; 
            margin-bottom: 5px; 
        }
        .stat-label { 
            font-size: 14px; 
            color: #856404; 
            font-weight: 600; 
        }
        .section { 
            margin-bottom: 40px; 
        }
        .section-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 20px; 
            padding-bottom: 15px; 
            border-bottom: 2px solid #ecf0f1; 
        }
        .section-header h2 { 
            margin: 0; 
            color: #2c3e50; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        .btn { 
            padding: 10px 20px; 
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
        }
        tr:hover { 
            background-color: #fff9e6; 
        }
        .days-cell { 
            font-weight: bold; 
            color: #f39c12; 
        }
        .urgency-high { 
            background: #fff3cd !important; 
            border-left: 4px solid #f39c12; 
        }
        .urgency-medium { 
            background: #fff9e6 !important; 
            border-left: 4px solid #ffc107; 
        }
        .urgency-low { 
            background: #fffdf6 !important; 
            border-left: 4px solid #ffeaa7; 
        }
        
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
        
        .empty-state { 
            text-align: center; 
            padding: 50px; 
            color: #7f8c8d; 
            background: #f8f9fa; 
            border-radius: 10px; 
            margin-top: 20px; 
        }
        .empty-state i { 
            font-size: 48px; 
            margin-bottom: 15px; 
            opacity: 0.3; 
            display: block; 
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-clock"></i> IDs Expiring Soon</h1>
            <p>Monitor IDs that will expire within the next 30 days</p>
        </div>
        
        <div class="stats-card">
            <h2><i class="fas fa-exclamation-triangle"></i> Expiration Alert Summary</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_expiring; ?></div>
                    <div class="stat-label">Total Expiring</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $expiring_students->num_rows; ?></div>
                    <div class="stat-label">Students</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $expiring_employees->num_rows; ?></div>
                    <div class="stat-label">Employees</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">30</div>
                    <div class="stat-label">Days Window</div>
                </div>
            </div>
        </div>
        
        <div class="section-header">
            <h2><i class="fas fa-user-graduate"></i> Expiring Students</h2>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <?php if ($expiring_students->num_rows > 0): ?>
        <div class="section">
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Student Number</th>
                            <th>Date Received</th>
                            <th>Expiration Date</th>
                            <th>Days Remaining</th>
                            <th>Urgency</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($student = $expiring_students->fetch_assoc()): 
                            $exp_date = new DateTime($student['expiration_date']);
                            $today_date = new DateTime($today);
                            $interval = $today_date->diff($exp_date);
                            $days_remaining = $interval->days;
                            
                            // Determine urgency level
                            if ($days_remaining <= 7) {
                                $urgency_class = "urgency-high";
                                $urgency_text = "High";
                            } elseif ($days_remaining <= 14) {
                                $urgency_class = "urgency-medium";
                                $urgency_text = "Medium";
                            } else {
                                $urgency_class = "urgency-low";
                                $urgency_text = "Low";
                            }
                        ?>
                        <tr class="<?php echo $urgency_class; ?>">
                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['student_number']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($student['date_received'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($student['expiration_date'])); ?></td>
                            <td class="days-cell"><?php echo $days_remaining; ?> days</td>
                            <td><?php echo $urgency_text; ?></td>
                            <td><?php echo htmlspecialchars($student['comments'] ?: 'N/A'); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-user-graduate"></i>
                <h3>No Students Expiring Soon</h3>
                <p>Great news! No student IDs are expiring within the next 30 days.</p>
            </div>
        <?php endif; ?>
        
        <div class="section-header" style="margin-top: 40px;">
            <h2><i class="fas fa-user-tie"></i> Expiring Employees</h2>
        </div>
        
        <?php if ($expiring_employees->num_rows > 0): ?>
        <div class="section">
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            <th>Employee Number</th>
                            <th>Date Received</th>
                            <th>Expiration Date</th>
                            <th>Days Remaining</th>
                            <th>Urgency</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($employee = $expiring_employees->fetch_assoc()): 
                            $exp_date = new DateTime($employee['expiration_date']);
                            $today_date = new DateTime($today);
                            $interval = $today_date->diff($exp_date);
                            $days_remaining = $interval->days;
                            
                            // Determine urgency level
                            if ($days_remaining <= 7) {
                                $urgency_class = "urgency-high";
                                $urgency_text = "High";
                            } elseif ($days_remaining <= 14) {
                                $urgency_class = "urgency-medium";
                                $urgency_text = "Medium";
                            } else {
                                $urgency_class = "urgency-low";
                                $urgency_text = "Low";
                            }
                        ?>
                        <tr class="<?php echo $urgency_class; ?>">
                            <td><?php echo htmlspecialchars($employee['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($employee['employee_number']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($employee['date_received'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($employee['expiration_date'])); ?></td>
                            <td class="days-cell"><?php echo $days_remaining; ?> days</td>
                            <td><?php echo $urgency_text; ?></td>
                            <td><?php echo htmlspecialchars($employee['comments'] ?: 'N/A'); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-user-tie"></i>
                <h3>No Employees Expiring Soon</h3>
                <p>Great news! No employee IDs are expiring within the next 30 days.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>
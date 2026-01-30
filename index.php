<?php
// index.php - Main dashboard
require_once 'config.php';

// Check for messages
$message = isset($_GET['message']) ? $_GET['message'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

$conn = getDBConnection();

// Get all statistics
$student_count = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$employee_count = $conn->query("SELECT COUNT(*) as count FROM employees")->fetch_assoc()['count'];

// Get request statistics
$total_requests = $conn->query("SELECT COUNT(*) as count FROM photo_requests")->fetch_assoc()['count'];
$pending_requests = $conn->query("SELECT COUNT(*) as count FROM photo_requests WHERE status = 'pending'")->fetch_assoc()['count'];
$approved_requests = $conn->query("SELECT COUNT(*) as count FROM photo_requests WHERE status = 'approved'")->fetch_assoc()['count'];
$completed_requests = $conn->query("SELECT COUNT(*) as count FROM photo_requests WHERE status = 'completed'")->fetch_assoc()['count'];
$rejected_requests = $conn->query("SELECT COUNT(*) as count FROM photo_requests WHERE status = 'rejected'")->fetch_assoc()['count'];

// Get expiring soon (within 30 days)
$today = date('Y-m-d');
$next_month = date('Y-m-d', strtotime('+30 days'));

$expiring_students = $conn->query("SELECT COUNT(*) as count FROM students WHERE expiration_date BETWEEN '$today' AND '$next_month'")->fetch_assoc()['count'];
$expiring_employees = $conn->query("SELECT COUNT(*) as count FROM employees WHERE expiration_date BETWEEN '$today' AND '$next_month'")->fetch_assoc()['count'];

// Get recent requests
$recent_requests = $conn->query("SELECT * FROM photo_requests ORDER BY created_at DESC LIMIT 5");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Expiration Tracking System</title>
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
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); 
            color: white; 
            padding: 30px; 
            border-radius: 10px; 
            margin-bottom: 30px; 
        }
        .header h1 { 
            margin: 0; 
            font-size: 32px; 
        }
        .header p { 
            margin: 10px 0 0; 
            opacity: 0.9; 
        }
        .nav { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 10px; 
            margin-bottom: 30px; 
        }
        .nav button, .nav a { 
            padding: 12px 24px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            background: #3498db; 
            color: white; 
            text-decoration: none; 
            display: inline-block; 
            font-size: 14px; 
            font-weight: 600; 
            transition: all 0.3s; 
        }
        .nav button:hover, .nav a:hover { 
            background: #2980b9; 
            transform: translateY(-2px); 
        }
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 20px; 
            margin-bottom: 40px; 
        }
        .stat-card { 
            background: white; 
            padding: 25px; 
            border-radius: 10px; 
            box-shadow: 0 3px 15px rgba(0,0,0,0.08); 
            transition: transform 0.3s; 
            border-top: 4px solid; 
        }
        .stat-card:hover { 
            transform: translateY(-5px); 
        }
        .stat-card.students { border-color: #3498db; }
        .stat-card.employees { border-color: #9b59b6; }
        .stat-card.expiring { border-color: #f39c12; }
        .stat-card.requests { border-color: #2ecc71; }
        .stat-card.pending { border-color: #e74c3c; }
        .stat-card.completed { border-color: #1abc9c; }
        
        .stat-icon { 
            font-size: 40px; 
            margin-bottom: 15px; 
            opacity: 0.8; 
        }
        .stat-number { 
            font-size: 36px; 
            font-weight: bold; 
            margin-bottom: 10px; 
            color: #2c3e50; 
        }
        .stat-title { 
            color: #7f8c8d; 
            font-size: 14px; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            font-weight: 600; 
        }
        .stat-subtitle { 
            color: #95a5a6; 
            font-size: 12px; 
            margin-top: 5px; 
        }
        .content-grid { 
            display: grid; 
            grid-template-columns: 2fr 1fr; 
            gap: 30px; 
            margin-top: 20px; 
        }
        @media (max-width: 1024px) {
            .content-grid { 
                grid-template-columns: 1fr; 
            }
        }
        .card { 
            background: white; 
            padding: 25px; 
            border-radius: 10px; 
            box-shadow: 0 3px 15px rgba(0,0,0,0.08); 
        }
        .card h2 { 
            margin-bottom: 20px; 
            padding-bottom: 15px; 
            border-bottom: 2px solid #ecf0f1; 
            color: #2c3e50; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        th, td { 
            padding: 12px 15px; 
            text-align: left; 
            border-bottom: 1px solid #ecf0f1; 
        }
        th { 
            background-color: #f8f9fa; 
            font-weight: 600; 
            color: #2c3e50; 
        }
        .status-badge { 
            padding: 5px 12px; 
            border-radius: 20px; 
            font-size: 12px; 
            font-weight: bold; 
            display: inline-block; 
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-completed { background: #cce5ff; color: #004085; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
        .request-item { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 15px; 
            margin-bottom: 10px; 
            background: #f8f9fa; 
            border-radius: 8px; 
            border-left: 4px solid #3498db; 
        }
        .request-info { 
            flex: 1; 
        }
        .request-name { 
            font-weight: 600; 
            color: #2c3e50; 
        }
        .request-number { 
            font-size: 12px; 
            color: #7f8c8d; 
            margin-top: 5px; 
        }
        .request-date { 
            font-size: 12px; 
            color: #95a5a6; 
        }
        
        .quick-stats { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 30px; 
            border-radius: 10px; 
            margin-bottom: 30px; 
        }
        .quick-stats h3 { 
            margin-top: 0; 
            margin-bottom: 20px; 
        }
        .quick-stat { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 10px; 
            padding-bottom: 10px; 
            border-bottom: 1px solid rgba(255,255,255,0.1); 
        }
        .quick-stat:last-child { 
            border-bottom: none; 
            margin-bottom: 0; 
        }
        
        .message { 
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
        }
        .success { 
            background-color: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
        }
        .error { 
            background-color: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb; 
        }
        
        .dashboard-title { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px; 
        }
        .view-all { 
            color: #3498db; 
            text-decoration: none; 
            font-size: 14px; 
            font-weight: 600; 
        }
        .view-all:hover { 
            text-decoration: underline; 
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-id-card"></i> ID Expiration Tracking System</h1>
            <p>Track student and employee ID expiration dates & photo requests</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="nav">
            <button onclick="window.location.href='index.php'">
                <i class="fas fa-home"></i> Dashboard
            </button>
            <button onclick="window.location.href='add_student.php'">
                <i class="fas fa-user-plus"></i> Add Student
            </button>
            <button onclick="window.location.href='add_employee.php'">
                <i class="fas fa-briefcase"></i> Add Employee
            </button>
            <button onclick="window.location.href='view_students.php'">
                <i class="fas fa-users"></i> View Students
            </button>
            <button onclick="window.location.href='view_employees.php'">
                <i class="fas fa-user-tie"></i> View Employees
            </button>
            <button onclick="window.location.href='expiring_soon.php'">
                <i class="fas fa-clock"></i> Expiring Soon
            </button>
            <button onclick="window.location.href='photo_request.php'" style="background: #2ecc71;">
                <i class="fas fa-camera"></i> Photo Request
            </button>
            <button onclick="window.location.href='manage_requests.php'" style="background: #e74c3c;">
                <i class="fas fa-tasks"></i> Manage Requests
            </button>
        </div>
        
        <!-- Quick Overview Stats -->
        <div class="quick-stats">
            <h3><i class="fas fa-chart-line"></i> System Overview</h3>
            <div class="quick-stat">
                <span>Total Records:</span>
                <strong><?php echo $student_count + $employee_count; ?></strong>
            </div>
            <div class="quick-stat">
                <span>Total Photo Requests:</span>
                <strong><?php echo $total_requests; ?></strong>
            </div>
            <div class="quick-stat">
                <span>Active Requests:</span>
                <strong><?php echo $pending_requests + $approved_requests; ?></strong>
            </div>
            <div class="quick-stat">
                <span>IDs Expiring Soon:</span>
                <strong><?php echo $expiring_students + $expiring_employees; ?></strong>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <!-- Student Card -->
            <div class="stat-card students">
                <div class="stat-icon">
                    <i class="fas fa-user-graduate" style="color: #3498db;"></i>
                </div>
                <div class="stat-number"><?php echo $student_count; ?></div>
                <div class="stat-title">Total Students</div>
                <div class="stat-subtitle"><?php echo $expiring_students; ?> expiring soon</div>
            </div>
            
            <!-- Employee Card -->
            <div class="stat-card employees">
                <div class="stat-icon">
                    <i class="fas fa-user-tie" style="color: #9b59b6;"></i>
                </div>
                <div class="stat-number"><?php echo $employee_count; ?></div>
                <div class="stat-title">Total Employees</div>
                <div class="stat-subtitle"><?php echo $expiring_employees; ?> expiring soon</div>
            </div>
            
            <!-- Expiring Card -->
            <div class="stat-card expiring">
                <div class="stat-icon">
                    <i class="fas fa-clock" style="color: #f39c12;"></i>
                </div>
                <div class="stat-number"><?php echo $expiring_students + $expiring_employees; ?></div>
                <div class="stat-title">IDs Expiring Soon</div>
                <div class="stat-subtitle">Within 30 days</div>
            </div>
            
            <!-- Total Requests Card -->
            <div class="stat-card requests">
                <div class="stat-icon">
                    <i class="fas fa-camera" style="color: #2ecc71;"></i>
                </div>
                <div class="stat-number"><?php echo $total_requests; ?></div>
                <div class="stat-title">Total Photo Requests</div>
                <div class="stat-subtitle">
                    <?php echo $completed_requests; ?> completed, <?php echo $rejected_requests; ?> rejected
                </div>
            </div>
            
            <!-- Pending Requests Card -->
            <div class="stat-card pending">
                <div class="stat-icon">
                    <i class="fas fa-hourglass-half" style="color: #e74c3c;"></i>
                </div>
                <div class="stat-number"><?php echo $pending_requests; ?></div>
                <div class="stat-title">Pending Requests</div>
                <div class="stat-subtitle">Awaiting approval</div>
            </div>
            
            <!-- Completed Requests Card -->
            <div class="stat-card completed">
                <div class="stat-icon">
                    <i class="fas fa-check-circle" style="color: #1abc9c;"></i>
                </div>
                <div class="stat-number"><?php echo $completed_requests; ?></div>
                <div class="stat-title">Completed Requests</div>
                <div class="stat-subtitle">Added to records</div>
            </div>
        </div>
        
        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Recent Requests Table -->
            <div class="card">
                <div class="dashboard-title">
                    <h2><i class="fas fa-history"></i> Recent Photo Requests</h2>
                    <a href="manage_requests.php" class="view-all">View All Requests →</a>
                </div>
                
                <?php if ($total_requests > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Student #</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($request = $recent_requests->fetch_assoc()): ?>
                        <tr onclick="window.location.href='manage_requests.php#request-<?php echo $request['id']; ?>'" 
                            style="cursor: pointer;">
                            <td>
                                <div class="request-name"><?php echo htmlspecialchars($request['full_name']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($request['student_number']); ?></td>
                            <td><?php echo ucfirst($request['request_type']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $request['status']; ?>">
                                    <?php echo ucfirst($request['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d', strtotime($request['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p style="text-align: center; color: #7f8c8d; padding: 30px;">
                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block; opacity: 0.3;"></i>
                        No photo requests yet
                    </p>
                <?php endif; ?>
            </div>
            
            <!-- Request Status Summary -->
            <div class="card">
                <h2><i class="fas fa-chart-pie"></i> Request Status</h2>
                
                <?php if ($total_requests > 0): ?>
                <div style="margin-bottom: 30px;">
                    <!-- Status Progress Bars -->
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <span>Pending</span>
                            <span><?php echo $pending_requests; ?> (<?php echo round(($pending_requests/$total_requests)*100); ?>%)</span>
                        </div>
                        <div style="height: 8px; background: #ecf0f1; border-radius: 4px; overflow: hidden;">
                            <div style="width: <?php echo ($pending_requests/$total_requests)*100; ?>%; height: 100%; background: #f39c12;"></div>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <span>Approved</span>
                            <span><?php echo $approved_requests; ?> (<?php echo round(($approved_requests/$total_requests)*100); ?>%)</span>
                        </div>
                        <div style="height: 8px; background: #ecf0f1; border-radius: 4px; overflow: hidden;">
                            <div style="width: <?php echo ($approved_requests/$total_requests)*100; ?>%; height: 100%; background: #2ecc71;"></div>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <span>Completed</span>
                            <span><?php echo $completed_requests; ?> (<?php echo round(($completed_requests/$total_requests)*100); ?>%)</span>
                        </div>
                        <div style="height: 8px; background: #ecf0f1; border-radius: 4px; overflow: hidden;">
                            <div style="width: <?php echo ($completed_requests/$total_requests)*100; ?>%; height: 100%; background: #3498db;"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <span>Rejected</span>
                            <span><?php echo $rejected_requests; ?> (<?php echo round(($rejected_requests/$total_requests)*100); ?>%)</span>
                        </div>
                        <div style="height: 8px; background: #ecf0f1; border-radius: 4px; overflow: hidden;">
                            <div style="width: <?php echo ($rejected_requests/$total_requests)*100; ?>%; height: 100%; background: #e74c3c;"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                    <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 16px;">Request Summary</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <div style="font-size: 12px; color: #7f8c8d;">Today</div>
                            <div style="font-weight: bold; font-size: 20px;">
                                <?php 
                                $conn = getDBConnection();
                                $today_requests = $conn->query("SELECT COUNT(*) as count FROM photo_requests WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
                                $conn->close();
                                echo $today_requests;
                                ?>
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #7f8c8d;">This Week</div>
                            <div style="font-weight: bold; font-size: 20px;">
                                <?php 
                                $conn = getDBConnection();
                                $week_requests = $conn->query("SELECT COUNT(*) as count FROM photo_requests WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())")->fetch_assoc()['count'];
                                $conn->close();
                                echo $week_requests;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                    <p style="text-align: center; color: #7f8c8d; padding: 30px;">
                        <i class="fas fa-chart-pie" style="font-size: 48px; margin-bottom: 15px; display: block; opacity: 0.3;"></i>
                        No requests to display
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
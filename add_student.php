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
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - ID System</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background-color: #f5f7fa; 
        }
        .container { 
            max-width: 600px; 
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
        .form-group { 
            margin-bottom: 25px; 
        }
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 600; 
            color: #2c3e50; 
        }
        input[type="text"], input[type="date"], textarea { 
            width: 95%; 
            padding: 12px 15px; 
            border: 1px solid #e0e6ed; 
            border-radius: 6px; 
            font-size: 16px; 
            transition: border-color 0.3s; 
        }
        input:focus, textarea:focus { 
            outline: none; 
            border-color: #3498db; 
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1); 
        }
        textarea { 
            height: 100px; 
            resize: vertical; 
        }
        .checkbox-group { 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            margin-bottom: 20px; 
        }
        .checkbox-group input { 
            width: auto; 
            transform: scale(1.2); 
        }
        .checkbox-group label { 
            font-weight: normal; 
            margin-bottom: 0; 
        }
        .form-actions { 
            display: flex; 
            gap: 15px; 
            margin-top: 30px; 
        }
        .btn-primary { 
            background: linear-gradient(135deg, #27ae60 0%, #219a52 100%); 
            color: white; 
            padding: 14px 30px; 
            border: none; 
            border-radius: 6px; 
            font-size: 16px; 
            font-weight: 600; 
            cursor: pointer; 
            flex: 1; 
            transition: transform 0.3s; 
        }
        .btn-primary:hover { 
            transform: translateY(-2px); 
        }
        .btn-secondary { 
            background: #95a5a6; 
            color: white; 
            padding: 14px 30px; 
            border: none; 
            border-radius: 6px; 
            font-size: 16px; 
            font-weight: 600; 
            cursor: pointer; 
            text-decoration: none; 
            display: inline-block; 
            text-align: center; 
            transition: transform 0.3s; 
        }
        .btn-secondary:hover { 
            background: #7f8c8d; 
            transform: translateY(-2px); 
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
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-user-plus"></i> Add New Student</h1>
            <p>Enter student details for ID creation</p>
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name" required 
                       placeholder="Enter student's full name">
            </div>
            
            <div class="form-group">
                <label for="student_number">Student Number *</label>
                <input type="text" id="student_number" name="student_number" required 
                       placeholder="Enter student number">
            </div>
            
            <div class="form-group">
                <label for="date_received">Date ID Received *</label>
                <input type="date" id="date_received" name="date_received" 
                       value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="expiration_date">Expiration Date *</label>
                <input type="date" id="expiration_date" name="expiration_date" required>
            </div>
            
            <div class="form-group">
                <label for="comments">Comments</label>
                <textarea id="comments" name="comments" 
                         placeholder="Additional notes..."></textarea>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="lost_id" name="lost_id">
                <label for="lost_id">Lost ID - Needs Payment</label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Save Student
                </button>
                <a href="index.php" class="btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
    
    <script>
        // Set default expiration date to 1 year from today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const oneYearLater = new Date();
            oneYearLater.setFullYear(today.getFullYear() + 1);
            
            const formattedDate = oneYearLater.toISOString().split('T')[0];
            document.getElementById('expiration_date').value = formattedDate;
        });
        
        // Lost ID checkbox functionality
        document.getElementById('lost_id').addEventListener('change', function() {
            const commentsField = document.getElementById('comments');
            if (this.checked) {
                commentsField.value = "Lost ID - Payment required";
            } else if (commentsField.value === "Lost ID - Payment required") {
                commentsField.value = "";
            }
        });
    </script>
</body>
</html>
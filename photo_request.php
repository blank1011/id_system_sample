<?php
require_once 'config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $student_number = trim($_POST['student_number']);
    $request_type = $_POST['request_type'];
    $reason = trim($_POST['reason']);
    $date_submitted = date('Y-m-d');
    
    // Handle file upload
    if (isset($_FILES['student_photo']) && $_FILES['student_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['student_photo'];
        
        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            $error = 'File is too large. Maximum size is 5MB.';
        } else {
            // Get file extension
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Check file type
            if (!in_array($file_ext, ALLOWED_TYPES)) {
                $error = 'Only JPG, JPEG, PNG, and GIF files are allowed.';
            } else {
                // Generate new filename
                $new_filename = sanitizeFilename($file['name'], $student_number);
                
                // Save file
                $upload_path = UPLOAD_DIR . $new_filename;
                
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    // Save to database
                    $conn = getDBConnection();
                    $stmt = $conn->prepare("INSERT INTO photo_requests (student_number, full_name, photo_filename, request_type, reason, date_submitted) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssss", $student_number, $full_name, $new_filename, $request_type, $reason, $date_submitted);
                    
                    if ($stmt->execute()) {
                        $message = 'Photo request submitted successfully!';
                    } else {
                        $error = 'Error saving request to database: ' . $stmt->error;
                        // Delete uploaded file if database save failed
                        unlink($upload_path);
                    }
                    
                    $stmt->close();
                    $conn->close();
                } else {
                    $error = 'Error uploading file. Please try again.';
                }
            }
        }
    } else {
        $error = 'Please select a photo to upload.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Photo Request - ID System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 20px -30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        textarea {
            height: 100px;
            resize: vertical;
        }
        
        .file-input {
            padding: 15px;
            border: 2px dashed #3498db;
            border-radius: 4px;
            text-align: center;
            background: #f8f9fa;
        }
        
        .photo-preview {
            width: 150px;
            height: 150px;
            margin: 10px auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .photo-preview img {
            max-width: 100%;
            max-height: 100%;
            display: none;
        }
        
        .preview-text {
            color: #666;
        }
        
        .submit-btn {
            background: #27ae60;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }
        
        .submit-btn:hover {
            background: #219a52;
        }
        
        .message {
            padding: 15px;
            border-radius: 4px;
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
        
        .requirements {
            background: #fff3cd;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .file-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📸 Student Photo Request</h1>
            <p>Upload photo for student ID card</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message success">
                <?php echo htmlspecialchars($message); ?>
                <br>
                <a href="photo_request.php" style="color: #155724; font-weight: bold;">Submit another request</a>
            </div>
        <?php elseif ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="requirements">
            <strong>📋 Photo Requirements:</strong><br>
            1. Recent passport-style photo<br>
            2. Clear face visible, looking straight at camera<br>
            3. Plain white or light-colored background<br>
            4. No hats, sunglasses, or filters<br>
            5. File types: JPG, PNG, GIF (Max 5MB)
        </div>
        
        <form method="POST" action="" enctype="multipart/form-data" id="photoForm">
            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name" required 
                       placeholder="Enter your full name">
            </div>
            
            <div class="form-group">
                <label for="student_number">Student Number *</label>
                <input type="text" id="student_number" name="student_number" required 
                       placeholder="Enter your student number">
            </div>
            
            <div class="form-group">
                <label for="request_type">Request Type *</label>
                <select id="request_type" name="request_type" required>
                    <option value="">-- Select Request Type --</option>
                    <option value="new">New ID Card</option>
                    <option value="replacement">Replacement (Lost/Damaged)</option>
                    <option value="renewal">Renewal</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="reason">Reason (Optional)</label>
                <textarea id="reason" name="reason" 
                         placeholder="Brief reason for request (e.g., lost ID, damaged, renewal...)"></textarea>
            </div>
            
            <div class="form-group">
                <label for="student_photo">Student Photo *</label>
                <div class="file-input">
                    <input type="file" id="student_photo" name="student_photo" 
                           accept="image/*" required onchange="previewPhoto(event)">
                    <div class="file-info">Click to upload or drag and drop</div>
                    
                    <div class="photo-preview">
                        <img id="previewImage" alt="Photo Preview">
                        <div id="previewText" class="preview-text">No photo selected</div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="submit-btn">Submit Photo Request</button>
        </form>
        
        <a href="index.php" class="back-link">← Back to ID System Dashboard</a>
    </div>
    
    <script>
        function previewPhoto(event) {
            const input = event.target;
            const preview = document.getElementById('previewImage');
            const previewText = document.getElementById('previewText');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    previewText.style.display = 'none';
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
                previewText.style.display = 'block';
            }
        }
        
        // Form validation
        document.getElementById('photoForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('student_photo');
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                
                // Check file size
                if (file.size > maxSize) {
                    e.preventDefault();
                    alert('File is too large. Maximum size is 5MB.');
                    return false;
                }
                
                // Check file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    e.preventDefault();
                    alert('Only JPG, JPEG, PNG, and GIF files are allowed.');
                    return false;
                }
            }
        });
        
        // Drag and drop functionality
        const fileInput = document.getElementById('student_photo');
        const dropZone = document.querySelector('.file-input');
        
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.backgroundColor = '#e8f4fd';
        });
        
        dropZone.addEventListener('dragleave', () => {
            dropZone.style.backgroundColor = '#f8f9fa';
        });
        
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.backgroundColor = '#f8f9fa';
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                const event = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(event);
            }
        });
    </script>
</body>
</html>
<?php
require_once 'config.php';

$conn = getDBConnection();

// Handle actions directly (no login required for simplicity)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    switch ($action) {
        case 'approve':
            $stmt = $conn->prepare("UPDATE photo_requests SET status = 'approved', date_approved = CURDATE() WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $message = "Request approved successfully";
            break;
            
        case 'reject':
            $stmt = $conn->prepare("UPDATE photo_requests SET status = 'rejected' WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $message = "Request rejected";
            break;
            
        case 'delete':
            // Get filename first
            $stmt = $conn->prepare("SELECT photo_filename FROM photo_requests WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $request = $result->fetch_assoc();
            
            // Delete file
            if ($request['photo_filename'] && file_exists(UPLOAD_DIR . $request['photo_filename'])) {
                unlink(UPLOAD_DIR . $request['photo_filename']);
            }
            
            // Delete from database
            $stmt = $conn->prepare("DELETE FROM photo_requests WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $message = "Request deleted";
            break;
            
        case 'complete':
            $stmt = $conn->prepare("UPDATE photo_requests SET status = 'completed', date_completed = CURDATE() WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $message = "Request marked as completed";
            break;
    }
    
    header("Location: manage_requests.php?message=" . urlencode($message));
    exit();
}

// Get all photo requests
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT * FROM photo_requests WHERE 1=1";

if ($search) {
    $query .= " AND (full_name LIKE '%$search%' OR student_number LIKE '%$search%')";
}

if ($status_filter) {
    $query .= " AND status = '$status_filter'";
}

$query .= " ORDER BY created_at DESC";

$requests = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Photo Requests - ID System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .nav { display: flex; gap: 10px; margin-bottom: 20px; }
        .nav a { padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; }
        .filters { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 15px; align-items: center; }
        .filters input, .filters select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .status { padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .status-completed { background-color: #cce5ff; color: #004085; }
        .photo-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; cursor: pointer; }
        .actions { display: flex; gap: 5px; }
        .action-btn { padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .btn-approve { background: #28a745; color: white; }
        .btn-reject { background: #dc3545; color: white; }
        .btn-complete { background: #007bff; color: white; }
        .btn-delete { background: #6c757d; color: white; }
        .btn-add { background: #20c997; color: white; }
        .message { padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .success { background-color: #d4edda; color: #155724; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7); }
        .modal-content { background-color: white; margin: 5% auto; padding: 20px; width: 80%; max-width: 600px; border-radius: 8px; }
        .modal-img { max-width: 100%; max-height: 400px; }
        .close { float: right; font-size: 28px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📸 Manage Photo Requests</h1>
            <p>View and manage student photo submissions</p>
        </div>
        
        <div class="nav">
            <a href="index.php">← Back to Dashboard</a>
            <a href="photo_request.php">📸 New Photo Request</a>
        </div>
        
        <?php if (isset($_GET['message'])): ?>
            <div class="message success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>
        
        <div class="filters">
            <input type="text" id="search" placeholder="Search by name or student number..." 
                   value="<?php echo htmlspecialchars($search); ?>"
                   onkeyup="searchTable()">
            
            <select id="statusFilter" onchange="filterByStatus()">
                <option value="">All Status</option>
                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
            </select>
            
            <button onclick="clearFilters()">Clear Filters</button>
        </div>
        
        <table id="requestsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Photo</th>
                    <th>Student Name</th>
                    <th>Student Number</th>
                    <th>Request Type</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Reason</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($request = $requests->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $request['id']; ?></td>
                    <td>
                        <?php if ($request['photo_filename']): ?>
                            <img src="<?php echo UPLOAD_DIR . $request['photo_filename']; ?>" 
                                 alt="Student Photo" 
                                 class="photo-thumb"
                                 onclick="viewPhoto('<?php echo UPLOAD_DIR . $request['photo_filename']; ?>')">
                        <?php else: ?>
                            No Photo
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($request['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($request['student_number']); ?></td>
                    <td><?php echo ucfirst($request['request_type']); ?></td>
                    <td>
                        <span class="status status-<?php echo $request['status']; ?>">
                            <?php echo ucfirst($request['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($request['date_submitted'])); ?></td>
                    <td><?php echo htmlspecialchars($request['reason'] ?: 'N/A'); ?></td>
                    <td class="actions">
                        <?php if ($request['status'] == 'pending'): ?>
                            <button class="action-btn btn-approve" onclick="approveRequest(<?php echo $request['id']; ?>)">Approve</button>
                            <button class="action-btn btn-reject" onclick="rejectRequest(<?php echo $request['id']; ?>)">Reject</button>
                            <button class="action-btn btn-add" onclick="addToRecords(<?php echo $request['id']; ?>)">Add to Records</button>
                        <?php elseif ($request['status'] == 'approved'): ?>
                            <button class="action-btn btn-complete" onclick="completeRequest(<?php echo $request['id']; ?>)">Complete</button>
                            <button class="action-btn btn-add" onclick="addToRecords(<?php echo $request['id']; ?>)">Add to Records</button>
                        <?php endif; ?>
                        <button class="action-btn btn-delete" onclick="deleteRequest(<?php echo $request['id']; ?>)">Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Photo Modal -->
    <div id="photoModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <img id="modalImage" class="modal-img" src="" alt="Full Size Photo">
        </div>
    </div>
    
    <script>
        function searchTable() {
            const search = document.getElementById('search').value.toLowerCase();
            const rows = document.querySelectorAll('#requestsTable tbody tr');
            
            rows.forEach(row => {
                const name = row.cells[2].textContent.toLowerCase();
                const number = row.cells[3].textContent.toLowerCase();
                
                if (name.includes(search) || number.includes(search)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        function filterByStatus() {
            const status = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#requestsTable tbody tr');
            
            rows.forEach(row => {
                const statusCell = row.cells[5].textContent.toLowerCase();
                
                if (!status || statusCell.includes(status)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        function clearFilters() {
            window.location.href = 'manage_requests.php';
        }
        
        function approveRequest(id) {
            if (confirm('Approve this photo request?')) {
                window.location.href = `manage_requests.php?action=approve&id=${id}`;
            }
        }
        
        function rejectRequest(id) {
            if (confirm('Reject this photo request?')) {
                window.location.href = `manage_requests.php?action=reject&id=${id}`;
            }
        }
        
        function completeRequest(id) {
            if (confirm('Mark this request as completed?')) {
                window.location.href = `manage_requests.php?action=complete&id=${id}`;
            }
        }
        
        function deleteRequest(id) {
            if (confirm('Delete this request? This will also delete the uploaded photo.')) {
                window.location.href = `manage_requests.php?action=delete&id=${id}`;
            }
        }
        
        function addToRecords(id) {
            window.location.href = `add_from_request.php?id=${id}`;
        }
        
        function viewPhoto(src) {
            document.getElementById('modalImage').src = src;
            document.getElementById('photoModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('photoModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('photoModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
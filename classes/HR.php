<?php
// FILE: classes/HR.php - HR & Administration Operations

class HR {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // ==================== EMPLOYEE MANAGEMENT ====================
    
    public function addEmployee($user_id, $employee_id, $designation, $department, $salary) {
        if (empty($user_id) || empty($employee_id) || empty($designation) || empty($department) || !$salary) {
            return ['success' => false, 'message' => 'All fields required'];
        }

        $query = "INSERT INTO employees (user_id, employee_id, designation, department, salary, hire_date, created_at) 
                  VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $this->db->prepare($query);
        $today = date('Y-m-d');
        $stmt->bind_param("isssd", $user_id, $employee_id, $designation, $department, $salary);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Employee added successfully', 'id' => $this->db->insert_id];
        }
        return ['success' => false, 'message' => 'Failed to add employee'];
    }

    public function getAllEmployees() {
        $query = "SELECT e.*, u.full_name, u.email FROM employees e 
                  JOIN users u ON e.user_id = u.id 
                  ORDER BY e.employee_id";
        $result = $this->db->query($query);

        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        return ['success' => true, 'data' => $employees];
    }

    public function getEmployeeDetails($employee_id) {
        $query = "SELECT e.*, u.full_name, u.email FROM employees e 
                  JOIN users u ON e.user_id = u.id 
                  WHERE e.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return ['success' => true, 'data' => $row];
        }
        return ['success' => false, 'message' => 'Employee not found'];
    }

    public function searchEmployees($query) {
        $searchQuery = "SELECT e.*, u.full_name, u.email FROM employees e 
                       JOIN users u ON e.user_id = u.id 
                       WHERE u.full_name LIKE ? OR e.employee_id LIKE ? OR e.designation LIKE ? OR e.department LIKE ?";
        $stmt = $this->db->prepare($searchQuery);
        $like = '%' . $query . '%';
        $stmt->bind_param("ssss", $like, $like, $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();

        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        return ['success' => true, 'data' => $employees];
    }

    public function updateEmployee($employee_id, $designation, $department, $salary) {
        $query = "UPDATE employees SET designation = ?, department = ?, salary = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssdi", $designation, $department, $salary, $employee_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Employee updated successfully'];
        }
        return ['success' => false, 'message' => 'Failed to update employee'];
    }

    // ==================== PAYROLL ====================
    
    public function addPayroll($employee_id, $month, $basic_salary, $allowances, $deductions, $net_salary) {
        if (empty($employee_id) || empty($month)) {
            return ['success' => false, 'message' => 'All fields required'];
        }

        $query = "INSERT INTO payroll (employee_id, month, basic_salary, allowances, deductions, net_salary, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("isdddd", $employee_id, $month, $basic_salary, $allowances, $deductions, $net_salary);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Payroll record created', 'id' => $this->db->insert_id];
        }
        return ['success' => false, 'message' => 'Failed to create payroll record'];
    }

    public function getPayrollRecords($month) {
        $query = "SELECT p.*, e.employee_id, u.full_name FROM payroll p 
                  JOIN employees e ON p.employee_id = e.id 
                  JOIN users u ON e.user_id = u.id 
                  WHERE p.month = ? 
                  ORDER BY e.employee_id";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $month);
        $stmt->execute();
        $result = $stmt->get_result();

        $payroll = [];
        while ($row = $result->fetch_assoc()) {
            $payroll[] = $row;
        }
        return ['success' => true, 'data' => $payroll];
    }

    public function getEmployeePayroll($user_id) {
        $query = "SELECT p.*, e.employee_id FROM payroll p 
                  JOIN employees e ON p.employee_id = e.id 
                  WHERE e.user_id = ? 
                  ORDER BY p.month DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $payroll = [];
        while ($row = $result->fetch_assoc()) {
            $payroll[] = $row;
        }
        return ['success' => true, 'data' => $payroll];
    }

    // ==================== ATTENDANCE ====================
    
    public function markAttendance($employee_id, $attendance_date, $status) {
        $checkQuery = "SELECT id FROM hr_attendance WHERE employee_id = ? AND attendance_date = ?";
        $stmt = $this->db->prepare($checkQuery);
        $stmt->bind_param("is", $employee_id, $attendance_date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $updateQuery = "UPDATE hr_attendance SET status = ? WHERE employee_id = ? AND attendance_date = ?";
            $stmt = $this->db->prepare($updateQuery);
            $stmt->bind_param("sis", $status, $employee_id, $attendance_date);
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Attendance updated'];
            }
        } else {
            $insertQuery = "INSERT INTO hr_attendance (employee_id, attendance_date, status, created_at) 
                           VALUES (?, ?, ?, NOW())";
            $stmt = $this->db->prepare($insertQuery);
            $stmt->bind_param("iss", $employee_id, $attendance_date, $status);
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Attendance recorded'];
            }
        }
        return ['success' => false, 'message' => 'Failed to record attendance'];
    }

    public function getAttendanceRecords($month) {
        $query = "SELECT a.*, e.employee_id, u.full_name FROM hr_attendance a 
                  JOIN employees e ON a.employee_id = e.id 
                  JOIN users u ON e.user_id = u.id 
                  WHERE DATE_FORMAT(a.attendance_date, '%Y-%m') = ? 
                  ORDER BY a.attendance_date DESC, e.employee_id";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $month);
        $stmt->execute();
        $result = $stmt->get_result();

        $attendance = [];
        while ($row = $result->fetch_assoc()) {
            $attendance[] = $row;
        }
        return ['success' => true, 'data' => $attendance];
    }

    public function getEmployeeAttendance($employee_id) {
        $query = "SELECT * FROM hr_attendance WHERE employee_id = ? ORDER BY attendance_date DESC LIMIT 30";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $attendance = [];
        $present = 0;
        $absent = 0;
        $late = 0;

        while ($row = $result->fetch_assoc()) {
            $attendance[] = $row;
            if ($row['status'] === 'present') $present++;
            elseif ($row['status'] === 'absent') $absent++;
            elseif ($row['status'] === 'late') $late++;
        }

        return [
            'success' => true,
            'data' => $attendance,
            'summary' => ['present' => $present, 'absent' => $absent, 'late' => $late]
        ];
    }

    // ==================== LEAVE MANAGEMENT ====================
    
    public function requestLeave($user_id, $start_date, $end_date, $leave_type, $reason) {
        if (empty($user_id) || empty($start_date) || empty($end_date) || empty($leave_type)) {
            return ['success' => false, 'message' => 'All fields required'];
        }

        $query = "INSERT INTO leave_requests (user_id, start_date, end_date, leave_type, reason, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("issss", $user_id, $start_date, $end_date, $leave_type, $reason);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Leave request submitted', 'id' => $this->db->insert_id];
        }
        return ['success' => false, 'message' => 'Failed to submit leave request'];
    }

    public function getEmployeeLeaves($user_id) {
        $query = "SELECT * FROM leave_requests WHERE user_id = ? ORDER BY start_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $leaves = [];
        while ($row = $result->fetch_assoc()) {
            $leaves[] = $row;
        }
        return ['success' => true, 'data' => $leaves];
    }

    public function getAllLeaveRequests() {
        $query = "SELECT lr.*, u.full_name, u.email FROM leave_requests lr 
                  JOIN users u ON lr.user_id = u.id 
                  ORDER BY lr.created_at DESC";
        $result = $this->db->query($query);

        $leaves = [];
        while ($row = $result->fetch_assoc()) {
            $leaves[] = $row;
        }
        return ['success' => true, 'data' => $leaves];
    }

    public function getPendingLeaves() {
        $query = "SELECT lr.*, u.full_name, u.email FROM leave_requests lr 
                  JOIN users u ON lr.user_id = u.id 
                  WHERE lr.status = 'pending' 
                  ORDER BY lr.created_at DESC";
        $result = $this->db->query($query);

        $leaves = [];
        while ($row = $result->fetch_assoc()) {
            $leaves[] = $row;
        }
        return ['success' => true, 'data' => $leaves];
    }

    public function approveLeave($leave_id, $status) {
        $valid_statuses = ['approved', 'rejected', 'pending'];
        if (!in_array($status, $valid_statuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        $query = "UPDATE leave_requests SET status = ?, approved_date = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("si", $status, $leave_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Leave request ' . $status];
        }
        return ['success' => false, 'message' => 'Failed to process leave request'];
    }

    // ==================== PERFORMANCE ====================
    
    public function addPerformanceReview($employee_id, $review_date, $rating, $comments) {
        if (!$employee_id || !$rating) {
            return ['success' => false, 'message' => 'All fields required'];
        }

        $query = "INSERT INTO performance_reviews (employee_id, review_date, rating, comments, created_at) 
                  VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("isis", $employee_id, $review_date, $rating, $comments);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Performance review added', 'id' => $this->db->insert_id];
        }
        return ['success' => false, 'message' => 'Failed to add performance review'];
    }

    public function getPerformanceRecords() {
        $query = "SELECT pr.*, e.employee_id, u.full_name FROM performance_reviews pr 
                  JOIN employees e ON pr.employee_id = e.id 
                  JOIN users u ON e.user_id = u.id 
                  ORDER BY pr.review_date DESC";
        $result = $this->db->query($query);

        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
        return ['success' => true, 'data' => $reviews];
    }

    public function getEmployeePerformance($employee_id) {
        $query = "SELECT * FROM performance_reviews WHERE employee_id = ? ORDER BY review_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
        return ['success' => true, 'data' => $reviews];
    }

    // ==================== ASSET MANAGEMENT ====================
    
    public function addAsset($asset_name, $category, $quantity, $location) {
        if (empty($asset_name) || empty($category) || !$quantity) {
            return ['success' => false, 'message' => 'All fields required'];
        }

        $query = "INSERT INTO assets (asset_name, category, quantity, location, status, created_at) 
                  VALUES (?, ?, ?, ?, 'available', NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssis", $asset_name, $category, $quantity, $location);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Asset added successfully', 'id' => $this->db->insert_id];
        }
        return ['success' => false, 'message' => 'Failed to add asset'];
    }

    public function getAllAssets() {
        $query = "SELECT * FROM assets ORDER BY asset_name";
        $result = $this->db->query($query);

        $assets = [];
        while ($row = $result->fetch_assoc()) {
            $assets[] = $row;
        }
        return ['success' => true, 'data' => $assets];
    }

    public function assignAsset($asset_id, $employee_id, $assignment_date) {
        $query = "INSERT INTO asset_assignments (asset_id, employee_id, assignment_date, status, created_at) 
                  VALUES (?, ?, ?, 'assigned', NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iss", $asset_id, $employee_id, $assignment_date);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Asset assigned successfully'];
        }
        return ['success' => false, 'message' => 'Failed to assign asset'];
    }

    public function getAssetAssignments() {
        $query = "SELECT aa.*, a.asset_name, e.employee_id, u.full_name FROM asset_assignments aa 
                  JOIN assets a ON aa.asset_id = a.id 
                  JOIN employees e ON aa.employee_id = e.id 
                  JOIN users u ON e.user_id = u.id 
                  ORDER BY aa.assignment_date DESC";
        $result = $this->db->query($query);

        $assignments = [];
        while ($row = $result->fetch_assoc()) {
            $assignments[] = $row;
        }
        return ['success' => true, 'data' => $assignments];
    }

    public function getEmployeeAssets($employee_id) {
        $query = "SELECT aa.*, a.asset_name, a.category FROM asset_assignments aa 
                  JOIN assets a ON aa.asset_id = a.id 
                  WHERE aa.employee_id = ? AND aa.status = 'assigned'";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $assets = [];
        while ($row = $result->fetch_assoc()) {
            $assets[] = $row;
        }
        return ['success' => true, 'data' => $assets];
    }

    public function deleteAsset($asset_id) {
        $query = "DELETE FROM assets WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $asset_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Asset deleted'];
        }
        return ['success' => false, 'message' => 'Failed to delete asset'];
    }

    // ==================== DASHBOARD ====================
    
    public function getDashboardStats() {
        $employeeQuery = "SELECT COUNT(*) as count FROM employees";
        $employees = $this->db->query($employeeQuery)->fetch_assoc()['count'];

        $pendingLeavesQuery = "SELECT COUNT(*) as count FROM leave_requests WHERE status = 'pending'";
        $pendingLeaves = $this->db->query($pendingLeavesQuery)->fetch_assoc()['count'];

        $todayAttendanceQuery = "SELECT COUNT(*) as count FROM hr_attendance WHERE attendance_date = CURDATE() AND status = 'present'";
        $todayAttendance = $this->db->query($todayAttendanceQuery)->fetch_assoc()['count'];

        $assetsQuery = "SELECT COUNT(*) as count FROM assets";
        $assets = $this->db->query($assetsQuery)->fetch_assoc()['count'];

        return [
            'success' => true,
            'data' => [
                'total_employees' => $employees,
                'pending_leaves' => $pendingLeaves,
                'today_attendance' => $todayAttendance,
                'total_assets' => $assets
            ]
        ];
    }

    public function getLeaveAnalytics() {
        $pendingQuery = "SELECT COUNT(*) as count FROM leave_requests WHERE status = 'pending'";
        $approvedQuery = "SELECT COUNT(*) as count FROM leave_requests WHERE status = 'approved'";
        $rejectedQuery = "SELECT COUNT(*) as count FROM leave_requests WHERE status = 'rejected'";

        $pending = $this->db->query($pendingQuery)->fetch_assoc()['count'];
        $approved = $this->db->query($approvedQuery)->fetch_assoc()['count'];
        $rejected = $this->db->query($rejectedQuery)->fetch_assoc()['count'];

        $byTypeQuery = "SELECT leave_type, COUNT(*) as count FROM leave_requests GROUP BY leave_type";
        $result = $this->db->query($byTypeQuery);
        $byType = [];
        while ($row = $result->fetch_assoc()) {
            $byType[] = $row;
        }

        return [
            'success' => true,
            'data' => [
                'pending' => $pending,
                'approved' => $approved,
                'rejected' => $rejected,
                'by_type' => $byType
            ]
        ];
    }

    public function getNotifications($user_id, $limit) {
        // Get pending leaves for approval
        $leavesQuery = "SELECT 'leave' as type, id, CONCAT('Leave request from ', (SELECT full_name FROM users WHERE id = user_id)) as message, created_at 
                       FROM leave_requests WHERE status = 'pending' LIMIT ?";
        $stmt = $this->db->prepare($leavesQuery);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $leaves = $stmt->get_result();

        $notifications = [];
        while ($row = $leaves->fetch_assoc()) {
            $notifications[] = $row;
        }

        usort($notifications, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return ['success' => true, 'data' => array_slice($notifications, 0, $limit)];
    }
}
?>
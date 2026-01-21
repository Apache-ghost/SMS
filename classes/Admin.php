<?php
// FILE: classes/Admin.php - Admin Management Operations

class Admin {
    private $db;
    private $usersTable = 'users';
    private $auditLogTable = 'audit_log';

    public function __construct($db) {
        $this->db = $db;
    }

    // ==================== USER MANAGEMENT ====================
    
    public function createUser($full_name, $email, $password, $role = 'student') {
        // Validation
        if (empty($full_name) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        // Check if email already exists
        $checkQuery = "SELECT id FROM " . $this->usersTable . " WHERE email = ?";
        $stmt = $this->db->prepare($checkQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Email already exists'];
        }
        $stmt->close();

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Validate role
        $valid_roles = ['admin', 'staff', 'student', 'faculty', 'lecturer'];
        if (!in_array($role, $valid_roles)) {
            $role = 'student';
        }

        // Insert user
        $query = "INSERT INTO " . $this->usersTable . " (email, password, full_name, role, status, created_at) 
                  VALUES (?, ?, ?, ?, 'active', NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssss", $email, $hashed_password, $full_name, $role);
        
        if ($stmt->execute()) {
            $user_id = $this->db->insert_id;
            $stmt->close();
            
            // If role is staff, faculty, or lecturer, add to employees table
            if (in_array($role, ['staff', 'faculty', 'lecturer'])) {
                $employee_id = 'EMP' . str_pad($user_id, 4, '0', STR_PAD_LEFT);
                $designation = ucfirst($role);
                $department = ($role === 'faculty' || $role === 'lecturer') ? 'Academic' : 'Administration';
                $salary = 0.00; // Default salary, can be updated later

                $emp_query = "INSERT INTO employees (user_id, employee_id, designation, department, salary, hire_date, created_at) 
                              VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
                $emp_stmt = $this->db->prepare($emp_query);
                $emp_stmt->bind_param("issds", $user_id, $employee_id, $designation, $department, $salary);
                $emp_stmt->execute();
                $emp_stmt->close();
            }
            
            // Log the action
            $this->logActivity('CREATE', 'USER', $user_id, null, json_encode([
                'full_name' => $full_name,
                'email' => $email,
                'role' => $role
            ]));
            
            return [
                'success' => true,
                'message' => 'User created successfully',
                'id' => $user_id,
                'user' => [
                    'id' => $user_id,
                    'full_name' => $full_name,
                    'email' => $email,
                    'role' => $role,
                    'status' => 'active'
                ]
            ];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to create user'];
        }
    }

    public function getAllUsers() {
        $query = "SELECT id, full_name, email, role, status, created_at, updated_at 
                  FROM " . $this->usersTable . " 
                  ORDER BY created_at DESC";
        $result = $this->db->query($query);

        if (!$result) {
            return ['success' => false, 'message' => 'Failed to fetch users'];
        }

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        return ['success' => true, 'data' => $users, 'count' => count($users)];
    }

    public function getUserDetails($user_id) {
        if (!$user_id) {
            return ['success' => false, 'message' => 'User ID required'];
        }

        $query = "SELECT id, full_name, email, role, status, created_at, updated_at 
                  FROM " . $this->usersTable . " 
                  WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return ['success' => true, 'data' => $row];
        }

        return ['success' => false, 'message' => 'User not found'];
    }

    public function getUsersByRole($role) {
        $valid_roles = ['admin', 'staff', 'student', 'faculty'];
        if (!in_array($role, $valid_roles)) {
            return ['success' => false, 'message' => 'Invalid role'];
        }

        $query = "SELECT id, full_name, email, role, status, created_at 
                  FROM " . $this->usersTable . " 
                  WHERE role = ? 
                  ORDER BY full_name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        return ['success' => true, 'data' => $users, 'count' => count($users)];
    }

    public function updateUser($user_id, $full_name = null, $email = null, $role = null, $status = null) {
        if (!$user_id) {
            return ['success' => false, 'message' => 'User ID required'];
        }

        // Get current user data for logging
        $currentQuery = "SELECT full_name, email, role, status FROM " . $this->usersTable . " WHERE id = ?";
        $stmt = $this->db->prepare($currentQuery);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $current = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$current) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // Build update query
        $updates = [];
        $params = [];
        $types = '';

        if ($full_name !== null) {
            $updates[] = "full_name = ?";
            $params[] = $full_name;
            $types .= 's';
        }

        if ($email !== null) {
            // Check if email already exists for different user
            $checkQuery = "SELECT id FROM " . $this->usersTable . " WHERE email = ? AND id != ?";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bind_param("si", $email, $user_id);
            $checkStmt->execute();
            if ($checkStmt->get_result()->num_rows > 0) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
            $checkStmt->close();

            $updates[] = "email = ?";
            $params[] = $email;
            $types .= 's';
        }

        if ($role !== null) {
            $valid_roles = ['admin', 'staff', 'student', 'faculty'];
            if (!in_array($role, $valid_roles)) {
                return ['success' => false, 'message' => 'Invalid role'];
            }
            $updates[] = "role = ?";
            $params[] = $role;
            $types .= 's';
        }

        if ($status !== null) {
            $valid_statuses = ['active', 'inactive', 'suspended'];
            if (!in_array($status, $valid_statuses)) {
                return ['success' => false, 'message' => 'Invalid status'];
            }
            $updates[] = "status = ?";
            $params[] = $status;
            $types .= 's';
        }

        if (empty($updates)) {
            return ['success' => false, 'message' => 'No updates provided'];
        }

        $updates[] = "updated_at = NOW()";
        $params[] = $user_id;
        $types .= 'i';

        $query = "UPDATE " . $this->usersTable . " SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $stmt->close();

            // Log the activity
            $this->logActivity('UPDATE', 'USER', $user_id, json_encode($current), json_encode([
                'full_name' => $full_name ?? $current['full_name'],
                'email' => $email ?? $current['email'],
                'role' => $role ?? $current['role'],
                'status' => $status ?? $current['status']
            ]));

            return ['success' => true, 'message' => 'User updated successfully'];
        }

        return ['success' => false, 'message' => 'Failed to update user'];
    }

    public function changeUserStatus($user_id, $status) {
        $valid_statuses = ['active', 'inactive', 'suspended'];
        if (!in_array($status, $valid_statuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        $query = "UPDATE " . $this->usersTable . " SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("si", $status, $user_id);

        if ($stmt->execute()) {
            $this->logActivity('UPDATE_STATUS', 'USER', $user_id, null, $status);
            return ['success' => true, 'message' => 'User status updated'];
        }

        return ['success' => false, 'message' => 'Failed to update status'];
    }

    public function deleteUser($user_id) {
        if (!$user_id) {
            return ['success' => false, 'message' => 'User ID required'];
        }

        // Get user details before deletion
        $userQuery = "SELECT full_name, email FROM " . $this->usersTable . " WHERE id = ?";
        $stmt = $this->db->prepare($userQuery);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // Don't allow deletion of the current user
        $currentUser = $_SESSION['user_id'] ?? null;
        if ($currentUser == $user_id) {
            return ['success' => false, 'message' => 'Cannot delete your own account'];
        }

        // Start transaction
        $this->db->begin_transaction();

        try {
            // Delete related records first
            // Delete enrollments
            $deleteEnrollments = "DELETE FROM enrollments WHERE user_id = ?";
            $stmt = $this->db->prepare($deleteEnrollments);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            // Delete employee record if exists
            $deleteEmployee = "DELETE FROM employees WHERE user_id = ?";
            $stmt = $this->db->prepare($deleteEmployee);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            // Delete grades (if they reference enrollments, but to be safe)
            $deleteGrades = "DELETE g FROM grades g INNER JOIN enrollments e ON g.enrollment_id = e.id WHERE e.user_id = ?";
            $stmt = $this->db->prepare($deleteGrades);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            // Finally delete the user
            $query = "DELETE FROM " . $this->usersTable . " WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $user_id);
            $result = $stmt->execute();
            $stmt->close();

            if ($result) {
                $this->db->commit();
                $this->logActivity('DELETE', 'USER', $user_id, json_encode($user), null);
                return ['success' => true, 'message' => 'User deleted successfully'];
            } else {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to delete user'];
            }
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Failed to delete user: ' . $e->getMessage()];
        }
    }

    // ==================== DASHBOARD STATISTICS ====================
    
    public function getDashboardStats() {
        $stats = ['success' => true, 'data' => []];

        // Total users
        $totalQuery = "SELECT COUNT(*) as count FROM " . $this->usersTable;
        $stats['data']['total_users'] = $this->db->query($totalQuery)->fetch_assoc()['count'];

        // Active users
        $activeQuery = "SELECT COUNT(*) as count FROM " . $this->usersTable . " WHERE status = 'active'";
        $stats['data']['active_users'] = $this->db->query($activeQuery)->fetch_assoc()['count'];

        // By role
        $rolesQuery = "SELECT role, COUNT(*) as count FROM " . $this->usersTable . " GROUP BY role";
        $result = $this->db->query($rolesQuery);
        $roleStats = [];
        while ($row = $result->fetch_assoc()) {
            $roleStats[$row['role']] = $row['count'];
        }

        $stats['data']['student_count'] = $roleStats['student'] ?? 0;
        $stats['data']['faculty_count'] = $roleStats['faculty'] ?? 0;
        $stats['data']['staff_count'] = $roleStats['staff'] ?? 0;
        $stats['data']['admin_count'] = $roleStats['admin'] ?? 0;

        return $stats;
    }

    // ==================== ACTIVITY LOGGING ====================
    
    private function logActivity($action, $entity_type, $entity_id, $old_value, $new_value) {
        $user_id = $_SESSION['user_id'] ?? null;
        
        $query = "INSERT INTO " . $this->auditLogTable . " (user_id, action, entity_type, entity_id, old_value, new_value, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("issiis", $user_id, $action, $entity_type, $entity_id, $old_value, $new_value);
        
        return $stmt->execute();
    }

    public function getActivityLog($limit = 50) {
        $query = "SELECT al.*, u.full_name as admin_name 
                  FROM " . $this->auditLogTable . " al 
                  LEFT JOIN " . $this->usersTable . " u ON al.user_id = u.id 
                  ORDER BY al.created_at DESC 
                  LIMIT ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }

        return ['success' => true, 'data' => $logs];
    }
}
?>
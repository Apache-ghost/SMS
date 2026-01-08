<?php
class User {
    private $db;
    private $table = 'users';

    public function __construct($db) {
        $this->db = $db;
    }

    public function register($email, $password, $full_name, $role = 'student') {
        // Validation
        if (empty($email) || empty($password) || empty($full_name)) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        // Check if user exists
        $query = "SELECT id FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Email already registered'];
        }
        $stmt->close();

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Validate role
        $valid_roles = ['admin', 'staff', 'student', 'faculty'];
        if (!in_array($role, $valid_roles)) {
            $role = 'student';
        }

        // Insert user
        $query = "INSERT INTO " . $this->table . " (email, password, full_name, role, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssss", $email, $hashed_password, $full_name, $role);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'User registered successfully', 'id' => $this->db->insert_id];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required'];
        }

        $query = "SELECT id, email, full_name, password, role, status FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Account is inactive'];
        }

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();

            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role']
                ]
            ];
        } else {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function getCurrentUser() {
        if (self::isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['email'],
                'full_name' => $_SESSION['full_name'],
                'role' => $_SESSION['role']
            ];
        }
        return null;
    }

    public static function logout() {
        $_SESSION = [];
        session_destroy();
        return true;
    }

    public static function hasRole($required_role) {
        if (!self::isLoggedIn()) return false;
        if (is_array($required_role)) {
            return in_array($_SESSION['role'], $required_role);
        }
        return $_SESSION['role'] === $required_role;
    }
}
?>
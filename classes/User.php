<!-- FILE: classes/User.php -->
<?php
class User {
    private $db;
    private $table = 'users';

    public $id;
    public $email;
    public $password;
    public $full_name;
    public $role;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->db = $db;
    }

    // Register new user
    public function register($email, $password, $full_name, $role = 'student') {
        // Check if user exists
        $query = "SELECT id FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $query = "INSERT INTO " . $this->table . " 
                  (email, password, full_name, role, status, created_at) 
                  VALUES (?, ?, ?, ?, 'active', NOW())";
        
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Prepare failed: ' . $this->db->error];
        }

        $stmt->bind_param("ssss", $email, $hashed_password, $full_name, $role);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'User registered successfully', 'id' => $stmt->insert_id];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    // Login user
    public function login($email, $password) {
        $query = "SELECT id, email, full_name, password, role, status FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        $user = $result->fetch_assoc();

        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Account is inactive'];
        }

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();

            return ['success' => true, 'message' => 'Login successful', 'user' => $user];
        } else {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
    }

    // Check if user is logged in
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Get current user
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

    // Check session timeout
    public static function checkSessionTimeout() {
        if (isset($_SESSION['login_time'])) {
            if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
                session_destroy();
                return false;
            }
            $_SESSION['login_time'] = time();
            return true;
        }
        return false;
    }

    // Logout
    public static function logout() {
        session_destroy();
        return true;
    }

    // Check user role
    public static function hasRole($required_role) {
        if (!self::isLoggedIn()) return false;
        
        if (is_array($required_role)) {
            return in_array($_SESSION['role'], $required_role);
        }
        
        return $_SESSION['role'] === $required_role;
    }
}
?>
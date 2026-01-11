<?php
error_reporting(0);
session_start();
$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
     http_response_code(404);
     die();
}else{
    
    
if (isset($_POST['email']) && isset($_POST['password'])) {
    include("assets/config.php");

    if ($conn) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);

        // First check users table (admin, teacher, student)
        $sql = "SELECT id, role, password_hash FROM users WHERE email=?";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            if ($result) {
                $row = mysqli_fetch_assoc($result);

                if ($row) {
                    if (password_verify($password, $row['password_hash'])) {
                        $_SESSION['uid'] = $row['id'];
                        $response['status'] = 'success';
                        $response['role'] = $row['role'];
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = 'Invalid email or password!';
                    }
                } else {
                    // Not found in users table, check parent_users table
                    $parentSql = "SELECT pu.parent_user_id, pu.guardian_id, pu.password, sg.gname 
                                  FROM parent_users pu
                                  JOIN student_guardian sg ON pu.guardian_id = sg.id
                                  WHERE pu.email = ? AND pu.is_active = 1";
                    $stmtParent = mysqli_prepare($conn, $parentSql);
                    
                    if ($stmtParent) {
                        mysqli_stmt_bind_param($stmtParent, "s", $email);
                        mysqli_stmt_execute($stmtParent);
                        $resultParent = mysqli_stmt_get_result($stmtParent);
                        $parentRow = mysqli_fetch_assoc($resultParent);
                        
                        if ($parentRow && password_verify($password, $parentRow['password'])) {
                            $_SESSION['parent_id'] = $parentRow['guardian_id'];
                            $_SESSION['parent_user_id'] = $parentRow['parent_user_id'];
                            $_SESSION['parent_name'] = $parentRow['gname'];
                            $_SESSION['parent_email'] = $email;
                            $_SESSION['role'] = 'parent';
                            
                            // Update last login
                            $updateSql = "UPDATE parent_users SET last_login = NOW() WHERE parent_user_id = ?";
                            $stmtUpdate = mysqli_prepare($conn, $updateSql);
                            mysqli_stmt_bind_param($stmtUpdate, "i", $parentRow['parent_user_id']);
                            mysqli_stmt_execute($stmtUpdate);
                            
                            $response['status'] = 'success';
                            $response['role'] = 'parent';
                        } else {
                            $response['status'] = 'error';
                            $response['message'] = 'Invalid email or password!';
                        }
                        mysqli_stmt_close($stmtParent);
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = 'Invalid email or password!';
                    }
                }

                mysqli_stmt_close($stmt);
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Error fetching result';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error preparing statement';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Database connection error';
    }

    
} else {
    $response['status'] = 'error';
    $response['message'] = 'Both fields are required';
}

// Return the response
echo json_encode($response);
    
}

?>

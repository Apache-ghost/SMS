<!DOCTYPE html>
<html>
<head>
    <title>Exam System Diagnostics</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #667eea; }
        h2 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #667eea; color: white; }
        .code { background: #f4f4f4; padding: 10px; border-left: 3px solid #667eea; margin: 10px 0; }
        .button { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .button:hover { background: #5568d3; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Exam Results System Diagnostics</h1>
    
    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    require './assets/config.php';
    
    $errors = [];
    $warnings = [];
    $successes = [];
    
    // ==================== TEST 1: Database Connection ====================
    echo "<h2>1️⃣ Database Connection</h2>";
    if (isset($conn) && $conn instanceof mysqli) {
        echo "<p class='success'>✅ Database connection successful</p>";
        $successes[] = "Database connected";
    } else {
        echo "<p class='error'>❌ Database connection failed</p>";
        $errors[] = "Database connection issue";
        die("Cannot proceed without database connection");
    }
    
    // ==================== TEST 2: Check Tables ====================
    echo "<h2>2️⃣ Database Tables</h2>";
    
    // Check exams table
    $checkExams = mysqli_query($conn, "SHOW TABLES LIKE 'exams'");
    if ($checkExams && mysqli_num_rows($checkExams) > 0) {
        echo "<p class='success'>✅ Table 'exams' exists</p>";
        $successes[] = "Exams table exists";
    } else {
        echo "<p class='error'>❌ Table 'exams' does NOT exist</p>";
        $errors[] = "Missing exams table";
        
        echo "<div class='code'>";
        echo "<strong>Run this SQL to create the table:</strong><br>";
        echo "<pre>CREATE TABLE `exams` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `exam_id` varchar(50) NOT NULL,
    `exam_title` varchar(255) NOT NULL,
    `subject` varchar(100) NOT NULL,
    `class` varchar(10) NOT NULL,
    `section` varchar(10) NOT NULL,
    `exam_date` date DEFAULT NULL,
    `total_marks` int(11) NOT NULL,
    `passing_marks` int(11) NOT NULL,
    `description` text,
    `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `exam_id` (`exam_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;</pre>";
        echo "</div>";
    }
    
    // Check exam_results table
    $checkResults = mysqli_query($conn, "SHOW TABLES LIKE 'exam_results'");
    if ($checkResults && mysqli_num_rows($checkResults) > 0) {
        echo "<p class='success'>✅ Table 'exam_results' exists</p>";
        $successes[] = "Exam_results table exists";
    } else {
        echo "<p class='error'>❌ Table 'exam_results' does NOT exist</p>";
        $errors[] = "Missing exam_results table";
        
        echo "<div class='code'>";
        echo "<strong>Run this SQL to create the table:</strong><br>";
        echo "<pre>CREATE TABLE `exam_results` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `exam_id` varchar(50) NOT NULL,
    `student_id` varchar(50) NOT NULL,
    `marks_obtained` decimal(10,2) NOT NULL,
    `total_marks` decimal(10,2) NOT NULL,
    `percentage` decimal(5,2) NOT NULL,
    `grade` varchar(5) NOT NULL,
    `status` enum('pass','fail') NOT NULL,
    `remarks` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `exam_id` (`exam_id`),
    KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;</pre>";
        echo "</div>";
    }
    
    // ==================== TEST 3: Check Data ====================
    echo "<h2>3️⃣ Data Check</h2>";
    
    // Count exams
    $examCount = 0;
    if (mysqli_num_rows($checkExams) > 0) {
        $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM exams");
        if ($result) {
            $examCount = mysqli_fetch_assoc($result)['count'];
            echo "<p>Total Exams Created: <strong>$examCount</strong></p>";
            
            if ($examCount == 0) {
                echo "<p class='warning'>⚠️ No exams created yet. Admin needs to create exams first.</p>";
                $warnings[] = "No exams in database";
            } else {
                echo "<p class='success'>✅ Exams found in database</p>";
                
                // Show recent exams
                $exams = mysqli_query($conn, "SELECT exam_id, exam_title, subject, class, section, exam_date, total_marks FROM exams ORDER BY timestamp DESC LIMIT 5");
                echo "<table>";
                echo "<tr><th>Exam ID</th><th>Title</th><th>Subject</th><th>Class</th><th>Section</th><th>Date</th><th>Total Marks</th></tr>";
                while ($exam = mysqli_fetch_assoc($exams)) {
                    echo "<tr>";
                    echo "<td>{$exam['exam_id']}</td>";
                    echo "<td>{$exam['exam_title']}</td>";
                    echo "<td>{$exam['subject']}</td>";
                    echo "<td>{$exam['class']}</td>";
                    echo "<td>{$exam['section']}</td>";
                    echo "<td>{$exam['exam_date']}</td>";
                    echo "<td>{$exam['total_marks']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
    }
    
    // Count results
    $resultCount = 0;
    if (mysqli_num_rows($checkResults) > 0) {
        $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM exam_results");
        if ($result) {
            $resultCount = mysqli_fetch_assoc($result)['count'];
            echo "<p>Total Results Published: <strong>$resultCount</strong></p>";
            
            if ($resultCount == 0) {
                echo "<p class='warning'>⚠️ No results published yet. Admin needs to publish results.</p>";
                $warnings[] = "No results published";
            } else {
                echo "<p class='success'>✅ Results found in database</p>";
                
                // Show recent results
                $results = mysqli_query($conn, "SELECT er.*, e.exam_title, e.subject, s.fname, s.lname, s.class, s.section 
                                                FROM exam_results er 
                                                LEFT JOIN exams e ON er.exam_id = e.exam_id 
                                                LEFT JOIN students s ON er.student_id = s.id 
                                                ORDER BY er.created_at DESC LIMIT 10");
                echo "<table>";
                echo "<tr><th>Student</th><th>Class</th><th>Exam</th><th>Subject</th><th>Marks</th><th>Total</th><th>%</th><th>Grade</th><th>Status</th></tr>";
                while ($result = mysqli_fetch_assoc($results)) {
                    $student_name = ($result['fname'] ?? 'Unknown') . ' ' . ($result['lname'] ?? '');
                    $class_section = ($result['class'] ?? '?') . '-' . ($result['section'] ?? '?');
                    echo "<tr>";
                    echo "<td>{$student_name}</td>";
                    echo "<td>{$class_section}</td>";
                    echo "<td>{$result['exam_title']}</td>";
                    echo "<td>{$result['subject']}</td>";
                    echo "<td>{$result['marks_obtained']}</td>";
                    echo "<td>{$result['total_marks']}</td>";
                    echo "<td>" . number_format($result['percentage'], 1) . "%</td>";
                    echo "<td><strong>{$result['grade']}</strong></td>";
                    echo "<td><strong>{$result['status']}</strong></td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
    }
    
    // ==================== TEST 4: Student Access Test ====================
    echo "<h2>4️⃣ Student Access Test</h2>";
    
    session_start();
    if (isset($_SESSION['uid'])) {
        $studentId = $_SESSION['uid'];
        
        // Check if it's a student
        $userCheck = mysqli_query($conn, "SELECT role FROM users WHERE id = '$studentId'");
        if ($userCheck && mysqli_num_rows($userCheck) > 0) {
            $user = mysqli_fetch_assoc($userCheck);
            
            if ($user['role'] == 'student') {
                echo "<p class='success'>✅ Logged in as Student (ID: $studentId)</p>";
                
                // Get student details
                $studentInfo = mysqli_query($conn, "SELECT id, fname, lname, class, section FROM students WHERE id = '$studentId'");
                if ($studentInfo && mysqli_num_rows($studentInfo) > 0) {
                    $student = mysqli_fetch_assoc($studentInfo);
                    echo "<p><strong>Name:</strong> {$student['fname']} {$student['lname']}</p>";
                    echo "<p><strong>Class:</strong> {$student['class']} - Section: {$student['section']}</p>";
                    
                    // Check student's results
                    if (mysqli_num_rows($checkResults) > 0) {
                        $myResults = mysqli_query($conn, "SELECT er.*, e.exam_title, e.subject, e.exam_date 
                                                          FROM exam_results er 
                                                          JOIN exams e ON er.exam_id = e.exam_id 
                                                          WHERE er.student_id = '$studentId' 
                                                          ORDER BY e.timestamp DESC");
                        
                        if ($myResults && mysqli_num_rows($myResults) > 0) {
                            $myResultCount = mysqli_num_rows($myResults);
                            echo "<p class='success'>✅ You have <strong>$myResultCount</strong> exam result(s)</p>";
                            
                            echo "<table>";
                            echo "<tr><th>Date</th><th>Exam</th><th>Subject</th><th>Marks</th><th>Total</th><th>%</th><th>Grade</th><th>Status</th></tr>";
                            while ($result = mysqli_fetch_assoc($myResults)) {
                                echo "<tr>";
                                echo "<td>{$result['exam_date']}</td>";
                                echo "<td>{$result['exam_title']}</td>";
                                echo "<td>{$result['subject']}</td>";
                                echo "<td>{$result['marks_obtained']}</td>";
                                echo "<td>{$result['total_marks']}</td>";
                                echo "<td>" . number_format($result['percentage'], 1) . "%</td>";
                                echo "<td><strong>{$result['grade']}</strong></td>";
                                echo "<td><strong>{$result['status']}</strong></td>";
                                echo "</tr>";
                            }
                            echo "</table>";
                        } else {
                            echo "<p class='warning'>⚠️ No exam results found for your account</p>";
                            echo "<p><strong>Possible reasons:</strong></p>";
                            echo "<ul>";
                            echo "<li>Admin hasn't published results yet</li>";
                            echo "<li>Results published for different class/section</li>";
                            echo "<li>Your student ID doesn't match published results</li>";
                            echo "</ul>";
                            
                            // Check if there are any exams for this student's class
                            $classExams = mysqli_query($conn, "SELECT COUNT(*) as count FROM exams WHERE class = '{$student['class']}' AND section = '{$student['section']}'");
                            if ($classExams) {
                                $classExamCount = mysqli_fetch_assoc($classExams)['count'];
                                if ($classExamCount > 0) {
                                    echo "<p>There are <strong>$classExamCount</strong> exam(s) for your class, but results not published yet.</p>";
                                } else {
                                    echo "<p>No exams created for your class yet.</p>";
                                }
                            }
                        }
                    }
                } else {
                    echo "<p class='error'>❌ Student record not found in database</p>";
                    $errors[] = "Student record missing";
                }
            } else {
                echo "<p class='warning'>⚠️ You are logged in as: <strong>{$user['role']}</strong></p>";
                echo "<p>This test requires student login. Please logout and login as a student.</p>";
            }
        }
    } else {
        echo "<p class='warning'>⚠️ Not logged in</p>";
        echo "<p>Please <a href='login.php'>login as a student</a> to test student access</p>";
    }
    
    // ==================== SUMMARY ====================
    echo "<h2>📊 Summary</h2>";
    
    if (count($errors) > 0) {
        echo "<h3 style='color: red;'>❌ Errors Found:</h3>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li style='color: red;'>$error</li>";
        }
        echo "</ul>";
    }
    
    if (count($warnings) > 0) {
        echo "<h3 style='color: orange;'>⚠️ Warnings:</h3>";
        echo "<ul>";
        foreach ($warnings as $warning) {
            echo "<li style='color: orange;'>$warning</li>";
        }
        echo "</ul>";
    }
    
    if (count($successes) > 0) {
        echo "<h3 style='color: green;'>✅ Success:</h3>";
        echo "<ul>";
        foreach ($successes as $success) {
            echo "<li style='color: green;'>$success</li>";
        }
        echo "</ul>";
    }
    
    // ==================== QUICK ACTIONS ====================
    echo "<h2>🔧 Quick Actions</h2>";
    echo "<a href='admin_panel/exams.php' class='button'>Go to Admin Panel</a>";
    echo "<a href='student_panel/exams.php' class='button'>Go to Student Panel</a>";
    echo "<a href='parent_panel/exams.php' class='button'>Go to Parent Panel</a>";
    echo "<a href='login.php' class='button'>Login Page</a>";
    echo "<a href='test_exam_system.php' class='button'>Refresh Test</a>";
    
    ?>
    
</div>
</body>
</html>

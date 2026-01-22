<?php
// Test script to check exam results database
session_start();
require './database/config.php';

echo "<h2>Database Test - Exam Results</h2>";

// 1. Check if exam_results table exists
echo "<h3>1. Checking if exam_results table exists...</h3>";
$checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'exam_results'");
if (mysqli_num_rows($checkTable) > 0) {
    echo "✅ Table 'exam_results' EXISTS<br><br>";
} else {
    echo "❌ Table 'exam_results' DOES NOT EXIST<br>";
    echo "<strong>Creating table...</strong><br>";
    
    $createTable = "CREATE TABLE IF NOT EXISTS `exam_results` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (mysqli_query($conn, $createTable)) {
        echo "✅ Table created successfully!<br><br>";
    } else {
        echo "❌ Error creating table: " . mysqli_error($conn) . "<br><br>";
    }
}

// 2. Check if exams table exists
echo "<h3>2. Checking if exams table exists...</h3>";
$checkExams = mysqli_query($conn, "SHOW TABLES LIKE 'exams'");
if (mysqli_num_rows($checkExams) > 0) {
    echo "✅ Table 'exams' EXISTS<br><br>";
} else {
    echo "❌ Table 'exams' DOES NOT EXIST<br>";
    echo "<strong>Creating table...</strong><br>";
    
    $createExams = "CREATE TABLE IF NOT EXISTS `exams` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (mysqli_query($conn, $createExams)) {
        echo "✅ Table created successfully!<br><br>";
    } else {
        echo "❌ Error creating table: " . mysqli_error($conn) . "<br><br>";
    }
}

// 3. Count exams
echo "<h3>3. Checking exams...</h3>";
$countExams = mysqli_query($conn, "SELECT COUNT(*) as count FROM exams");
if ($countExams) {
    $examCount = mysqli_fetch_assoc($countExams)['count'];
    echo "Total Exams: <strong>$examCount</strong><br><br>";
    
    if ($examCount > 0) {
        echo "<h4>Exam List:</h4>";
        $exams = mysqli_query($conn, "SELECT exam_id, exam_title, subject, class, section, exam_date FROM exams ORDER BY timestamp DESC LIMIT 10");
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Exam ID</th><th>Title</th><th>Subject</th><th>Class</th><th>Section</th><th>Date</th></tr>";
        while ($exam = mysqli_fetch_assoc($exams)) {
            echo "<tr>";
            echo "<td>{$exam['exam_id']}</td>";
            echo "<td>{$exam['exam_title']}</td>";
            echo "<td>{$exam['subject']}</td>";
            echo "<td>{$exam['class']}</td>";
            echo "<td>{$exam['section']}</td>";
            echo "<td>{$exam['exam_date']}</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
} else {
    echo "❌ Error: " . mysqli_error($conn) . "<br><br>";
}

// 4. Count exam results
echo "<h3>4. Checking exam results...</h3>";
$countResults = mysqli_query($conn, "SELECT COUNT(*) as count FROM exam_results");
if ($countResults) {
    $resultCount = mysqli_fetch_assoc($countResults)['count'];
    echo "Total Results: <strong>$resultCount</strong><br><br>";
    
    if ($resultCount > 0) {
        echo "<h4>Recent Results (Last 10):</h4>";
        $results = mysqli_query($conn, "SELECT er.*, e.exam_title, e.subject, s.fname, s.lname 
                                        FROM exam_results er 
                                        LEFT JOIN exams e ON er.exam_id = e.exam_id 
                                        LEFT JOIN students s ON er.student_id = s.id 
                                        ORDER BY er.created_at DESC LIMIT 10");
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Student</th><th>Exam Title</th><th>Subject</th><th>Marks</th><th>Total</th><th>%</th><th>Grade</th><th>Status</th></tr>";
        while ($result = mysqli_fetch_assoc($results)) {
            $student_name = $result['fname'] . ' ' . $result['lname'];
            echo "<tr>";
            echo "<td>{$student_name}</td>";
            echo "<td>{$result['exam_title']}</td>";
            echo "<td>{$result['subject']}</td>";
            echo "<td>{$result['marks_obtained']}</td>";
            echo "<td>{$result['total_marks']}</td>";
            echo "<td>" . number_format($result['percentage'], 1) . "%</td>";
            echo "<td>{$result['grade']}</td>";
            echo "<td>{$result['status']}</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
} else {
    echo "❌ Error: " . mysqli_error($conn) . "<br><br>";
}

// 5. Check specific student results
echo "<h3>5. Testing student login...</h3>";
if (isset($_SESSION['uid'])) {
    $studentId = $_SESSION['uid'];
    echo "Logged in as Student ID: <strong>$studentId</strong><br>";
    
    // Get student info
    $studentInfo = mysqli_query($conn, "SELECT fname, lname, class, section FROM students WHERE id = '$studentId'");
    if ($studentInfo && mysqli_num_rows($studentInfo) > 0) {
        $student = mysqli_fetch_assoc($studentInfo);
        echo "Name: {$student['fname']} {$student['lname']}<br>";
        echo "Class: {$student['class']} - Section: {$student['section']}<br><br>";
        
        // Check results for this student
        echo "<h4>Your Results:</h4>";
        $myResults = mysqli_query($conn, "SELECT er.*, e.exam_title, e.subject, e.exam_date 
                                          FROM exam_results er 
                                          JOIN exams e ON er.exam_id = e.exam_id 
                                          WHERE er.student_id = '$studentId' 
                                          ORDER BY e.timestamp DESC");
        
        if ($myResults && mysqli_num_rows($myResults) > 0) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Date</th><th>Exam</th><th>Subject</th><th>Marks</th><th>Total</th><th>%</th><th>Grade</th><th>Status</th></tr>";
            while ($result = mysqli_fetch_assoc($myResults)) {
                echo "<tr>";
                echo "<td>{$result['exam_date']}</td>";
                echo "<td>{$result['exam_title']}</td>";
                echo "<td>{$result['subject']}</td>";
                echo "<td>{$result['marks_obtained']}</td>";
                echo "<td>{$result['total_marks']}</td>";
                echo "<td>" . number_format($result['percentage'], 1) . "%</td>";
                echo "<td>{$result['grade']}</td>";
                echo "<td>{$result['status']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "❌ No results found for your account.<br>";
            echo "This could mean:<br>";
            echo "- Admin hasn't published results yet<br>";
            echo "- Results were published for a different class/section<br>";
            echo "- Your student ID doesn't match the results<br>";
        }
    } else {
        echo "❌ Student record not found in database<br>";
    }
} else {
    echo "⚠️ Not logged in. Please login as a student first, then run this test.<br>";
}

echo "<br><hr>";
echo "<p><a href='admin_panel/exams.php'>Go to Admin Exams</a> | ";
echo "<a href='student_panel/exams.php'>Go to Student Exams</a> | ";
echo "<a href='login.php'>Login</a></p>";
?>

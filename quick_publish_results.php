<!DOCTYPE html>
<html>
<head>
    <title>Quick Publish Test Results</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #667eea; }
        .success { color: green; padding: 10px; background: #d4edda; border: 1px solid green; border-radius: 5px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid red; border-radius: 5px; margin: 10px 0; }
        .info { color: #004085; padding: 10px; background: #cce5ff; border: 1px solid #004085; border-radius: 5px; margin: 10px 0; }
        form { margin: 20px 0; }
        input, select { padding: 8px; margin: 5px 0; width: 100%; box-sizing: border-box; }
        button { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #5568d3; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #667eea; color: white; }
    </style>
</head>
<body>
<div class="container">
    <h1>🚀 Quick Test - Publish Results</h1>
    
    <?php
    session_start();
    require './assets/config.php';
    
    // Check if admin is logged in
    if (!isset($_SESSION['uid'])) {
        echo "<div class='error'>❌ Please login as admin first</div>";
        echo "<p><a href='login.php'>Go to Login</a></p>";
        exit();
    }
    
    $userId = $_SESSION['uid'];
    $userCheck = mysqli_query($conn, "SELECT role FROM users WHERE id = '$userId'");
    if ($userCheck) {
        $user = mysqli_fetch_assoc($userCheck);
        if ($user['role'] != 'admin') {
            echo "<div class='error'>❌ This page is for admin only. You are logged in as: {$user['role']}</div>";
            exit();
        }
    }
    
    echo "<div class='success'>✅ Logged in as Admin</div>";
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['publish_results'])) {
        $examId = mysqli_real_escape_string($conn, $_POST['exam_id']);
        $studentId = mysqli_real_escape_string($conn, $_POST['student_id']);
        $marks = floatval($_POST['marks']);
        
        // Get exam details
        $examQuery = mysqli_query($conn, "SELECT * FROM exams WHERE exam_id = '$examId'");
        $exam = mysqli_fetch_assoc($examQuery);
        
        if ($exam) {
            $totalMarks = $exam['total_marks'];
            $passingMarks = $exam['passing_marks'];
            $percentage = ($marks / $totalMarks) * 100;
            
            // Calculate grade
            if ($marks < $passingMarks) {
                $grade = 'F';
                $status = 'fail';
            } else if ($percentage >= 90) {
                $grade = 'A+';
                $status = 'pass';
            } else if ($percentage >= 80) {
                $grade = 'A';
                $status = 'pass';
            } else if ($percentage >= 70) {
                $grade = 'B';
                $status = 'pass';
            } else if ($percentage >= 60) {
                $grade = 'C';
                $status = 'pass';
            } else if ($percentage >= 50) {
                $grade = 'D';
                $status = 'pass';
            } else {
                $grade = 'F';
                $status = 'fail';
            }
            
            // Insert result
            $insertSql = "INSERT INTO exam_results (exam_id, student_id, marks_obtained, total_marks, percentage, grade, status, remarks) 
                          VALUES ('$examId', '$studentId', $marks, $totalMarks, $percentage, '$grade', '$status', 'Test result')
                          ON DUPLICATE KEY UPDATE 
                          marks_obtained = $marks, 
                          total_marks = $totalMarks, 
                          percentage = $percentage, 
                          grade = '$grade', 
                          status = '$status',
                          updated_at = NOW()";
            
            if (mysqli_query($conn, $insertSql)) {
                echo "<div class='success'>✅ Result published successfully! Grade: $grade, Status: $status</div>";
            } else {
                echo "<div class='error'>❌ Error: " . mysqli_error($conn) . "</div>";
            }
        }
    }
    
    // Get available exams
    $examsQuery = mysqli_query($conn, "SELECT exam_id, exam_title, subject, class, section, total_marks, passing_marks FROM exams ORDER BY timestamp DESC");
    
    // Get students
    $studentsQuery = mysqli_query($conn, "SELECT id, fname, lname, class, section FROM students ORDER BY class, section, fname");
    
    ?>
    
    <div class='info'>
        <strong>📋 Instructions:</strong>
        <ol>
            <li>Select an exam from the dropdown</li>
            <li>Select a student</li>
            <li>Enter marks obtained</li>
            <li>Click "Publish Result"</li>
            <li>Student will see result immediately in their panel</li>
        </ol>
    </div>
    
    <h2>Publish Result</h2>
    <form method="POST">
        <label><strong>Select Exam:</strong></label>
        <select name="exam_id" required onchange="updateExamInfo(this)">
            <option value="">-- Select Exam --</option>
            <?php
            mysqli_data_seek($examsQuery, 0);
            while ($exam = mysqli_fetch_assoc($examsQuery)) {
                echo "<option value='{$exam['exam_id']}' data-total='{$exam['total_marks']}' data-passing='{$exam['passing_marks']}'>";
                echo "{$exam['exam_title']} - {$exam['subject']} (Class {$exam['class']}-{$exam['section']})";
                echo "</option>";
            }
            ?>
        </select>
        
        <label><strong>Select Student:</strong></label>
        <select name="student_id" required>
            <option value="">-- Select Student --</option>
            <?php
            while ($student = mysqli_fetch_assoc($studentsQuery)) {
                echo "<option value='{$student['id']}'>";
                echo "{$student['fname']} {$student['lname']} (Class {$student['class']}-{$student['section']})";
                echo "</option>";
            }
            ?>
        </select>
        
        <label><strong>Marks Obtained:</strong></label>
        <input type="number" name="marks" id="marks" min="0" step="0.1" required placeholder="Enter marks">
        
        <div id="examInfo" style="margin: 10px 0; padding: 10px; background: #f0f0f0; border-radius: 5px; display: none;">
            <p><strong>Total Marks:</strong> <span id="totalMarks">-</span></p>
            <p><strong>Passing Marks:</strong> <span id="passingMarks">-</span></p>
        </div>
        
        <button type="submit" name="publish_results">🚀 Publish Result</button>
    </form>
    
    <hr>
    
    <h2>📊 Current Status</h2>
    <?php
    $totalExams = mysqli_num_rows($examsQuery);
    $totalResults = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM exam_results"));
    
    echo "<p><strong>Total Exams Created:</strong> $totalExams</p>";
    echo "<p><strong>Total Results Published:</strong> $totalResults</p>";
    
    if ($totalResults > 0) {
        echo "<h3>Recently Published Results:</h3>";
        $recentResults = mysqli_query($conn, "SELECT er.*, e.exam_title, e.subject, s.fname, s.lname 
                                              FROM exam_results er 
                                              LEFT JOIN exams e ON er.exam_id = e.exam_id 
                                              LEFT JOIN students s ON er.student_id = s.id 
                                              ORDER BY er.created_at DESC LIMIT 10");
        echo "<table>";
        echo "<tr><th>Student</th><th>Exam</th><th>Subject</th><th>Marks</th><th>Total</th><th>Grade</th><th>Status</th><th>Date</th></tr>";
        while ($result = mysqli_fetch_assoc($recentResults)) {
            echo "<tr>";
            echo "<td>{$result['fname']} {$result['lname']}</td>";
            echo "<td>{$result['exam_title']}</td>";
            echo "<td>{$result['subject']}</td>";
            echo "<td>{$result['marks_obtained']}</td>";
            echo "<td>{$result['total_marks']}</td>";
            echo "<td><strong>{$result['grade']}</strong></td>";
            echo "<td><strong>{$result['status']}</strong></td>";
            echo "<td>{$result['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    ?>
    
    <hr>
    <p>
        <a href="admin_panel/exams.php" style="padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;">📋 Go to Admin Exams Panel</a>
        <a href="test_exam_system.php" style="padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">🔍 Run Diagnostics</a>
    </p>
    
    <script>
    function updateExamInfo(select) {
        const option = select.options[select.selectedIndex];
        if (option.value) {
            const total = option.getAttribute('data-total');
            const passing = option.getAttribute('data-passing');
            
            document.getElementById('totalMarks').textContent = total;
            document.getElementById('passingMarks').textContent = passing;
            document.getElementById('examInfo').style.display = 'block';
            document.getElementById('marks').max = total;
        } else {
            document.getElementById('examInfo').style.display = 'none';
        }
    }
    </script>
</div>
</body>
</html>

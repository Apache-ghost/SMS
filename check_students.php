<?php
session_start();
require './assets/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Check Students Data</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #667eea; color: white; }
        .error { color: red; padding: 10px; background: #fee; border: 1px solid red; }
        .success { color: green; padding: 10px; background: #efe; border: 1px solid green; }
    </style>
</head>
<body>
    <h1>🔍 Check Students for Exam Class/Section</h1>
    
    <?php
    // Get the exam that's failing
    $examId = 'E176904546869717ddc481f1';
    
    echo "<h2>Checking Exam: $examId</h2>";
    
    $examQuery = mysqli_query($conn, "SELECT * FROM exams WHERE exam_id = '$examId'");
    if ($examQuery && mysqli_num_rows($examQuery) > 0) {
        $exam = mysqli_fetch_assoc($examQuery);
        echo "<div class='success'>✅ Exam found</div>";
        echo "<p><strong>Title:</strong> {$exam['exam_title']}</p>";
        echo "<p><strong>Subject:</strong> {$exam['subject']}</p>";
        echo "<p><strong>Class:</strong> {$exam['class']}</p>";
        echo "<p><strong>Section:</strong> {$exam['section']}</p>";
        
        $class = $exam['class'];
        $section = $exam['section'];
        
        echo "<h2>Students in Class $class - Section $section</h2>";
        
        $studentsQuery = mysqli_query($conn, "SELECT id, fname, lname, class, section FROM students WHERE class = '$class' AND section = '$section' ORDER BY fname");
        
        if ($studentsQuery) {
            $studentCount = mysqli_num_rows($studentsQuery);
            
            if ($studentCount > 0) {
                echo "<div class='success'>✅ Found $studentCount student(s)</div>";
                echo "<table>";
                echo "<tr><th>ID</th><th>Name</th><th>Class</th><th>Section</th></tr>";
                while ($student = mysqli_fetch_assoc($studentsQuery)) {
                    echo "<tr>";
                    echo "<td>{$student['id']}</td>";
                    echo "<td>{$student['fname']} {$student['lname']}</td>";
                    echo "<td>{$student['class']}</td>";
                    echo "<td>{$student['section']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='error'>❌ No students found in Class $class - Section $section</div>";
                echo "<p><strong>Problem:</strong> You need to add students to this class/section first!</p>";
                echo "<p><a href='admin_panel/student_management.php'>Go to Student Management</a></p>";
            }
        } else {
            echo "<div class='error'>❌ Error querying students: " . mysqli_error($conn) . "</div>";
        }
        
        // Check all students
        echo "<h2>All Students in Database</h2>";
        $allStudents = mysqli_query($conn, "SELECT id, fname, lname, class, section FROM students ORDER BY class, section, fname LIMIT 20");
        if ($allStudents && mysqli_num_rows($allStudents) > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Name</th><th>Class</th><th>Section</th></tr>";
            while ($student = mysqli_fetch_assoc($allStudents)) {
                echo "<tr>";
                echo "<td>{$student['id']}</td>";
                echo "<td>{$student['fname']} {$student['lname']}</td>";
                echo "<td>{$student['class']}</td>";
                echo "<td>{$student['section']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No students in database or limited to 20 results</p>";
        }
        
    } else {
        echo "<div class='error'>❌ Exam not found</div>";
    }
    ?>
    
    <hr>
    <p>
        <a href="test_exam_system.php">Run Full Diagnostics</a> | 
        <a href="admin_panel/student_management.php">Student Management</a> | 
        <a href="admin_panel/exams.php">Exams Panel</a>
    </p>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam System Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3>Exam System Database Setup</h3>
        </div>
        <div class="card-body">
            <?php
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            
            include('assets/config.php');
            
            echo "<div class='alert alert-info'>Starting database setup...</div>";
            
            $errors = [];
            $success = [];
            
            // Check and add exam_date column to exams table
            $checkCol = @mysqli_query($conn, "SHOW COLUMNS FROM exams LIKE 'exam_date'");
            if (!$checkCol || mysqli_num_rows($checkCol) == 0) {
                echo "<p>Adding exam_date column to exams table...</p>";
                $sql = "ALTER TABLE exams ADD COLUMN exam_date DATE NULL";
                
                if (mysqli_query($conn, $sql)) {
                    $success[] = "✓ Added exam_date column to exams table";
                } else {
                    $errors[] = "✗ Error adding exam_date column: " . mysqli_error($conn);
                }
            } else {
                $success[] = "✓ exam_date column already exists";
            }
            
            // Check and add description column to exams table
            $checkDesc = @mysqli_query($conn, "SHOW COLUMNS FROM exams LIKE 'description'");
            if (!$checkDesc || mysqli_num_rows($checkDesc) == 0) {
                echo "<p>Adding description column to exams table...</p>";
                $sql = "ALTER TABLE exams ADD COLUMN description TEXT NULL";
                
                if (mysqli_query($conn, $sql)) {
                    $success[] = "✓ Added description column to exams table";
                } else {
                    $errors[] = "✗ Error adding description column: " . mysqli_error($conn);
                }
            } else {
                $success[] = "✓ description column already exists";
            }
            
            // Create exam_results table if not exists
            $checkTable = @mysqli_query($conn, "SHOW TABLES LIKE 'exam_results'");
            if (!$checkTable || mysqli_num_rows($checkTable) == 0) {
                echo "<p>Creating exam_results table...</p>";
                $sql = "CREATE TABLE exam_results (
                    result_id INT AUTO_INCREMENT PRIMARY KEY,
                    exam_id VARCHAR(50) NOT NULL,
                    student_id INT NOT NULL,
                    marks_obtained DECIMAL(5,2) NOT NULL,
                    total_marks DECIMAL(5,2) NOT NULL,
                    percentage DECIMAL(5,2) NOT NULL,
                    grade VARCHAR(5) NOT NULL,
                    status ENUM('pass', 'fail') NOT NULL,
                    remarks TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_result (exam_id, student_id),
                    INDEX idx_result_student (student_id),
                    INDEX idx_result_exam (exam_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                
                if (mysqli_query($conn, $sql)) {
                    $success[] = "✓ Created exam_results table";
                    
                    // Add indexes to exams table
                    @mysqli_query($conn, "CREATE INDEX idx_exam_date ON exams(exam_date)");
                    @mysqli_query($conn, "CREATE INDEX idx_exam_class ON exams(class, section)");
                    $success[] = "✓ Added performance indexes";
                } else {
                    $errors[] = "✗ Error creating exam_results table: " . mysqli_error($conn);
                }
            } else {
                $success[] = "✓ exam_results table already exists";
            }
            
            // Check subjects table
            $checkSubjects = @mysqli_query($conn, "SHOW TABLES LIKE 'subjects'");
            if (!$checkSubjects || mysqli_num_rows($checkSubjects) == 0) {
                $errors[] = "⚠ Warning: subjects table does not exist. Create it from your subjects management page.";
            } else {
                $countSubjects = mysqli_query($conn, "SELECT COUNT(*) as count FROM subjects");
                $count = mysqli_fetch_assoc($countSubjects)['count'];
                if ($count == 0) {
                    $errors[] = "⚠ Warning: subjects table is empty. Add subjects before creating exams.";
                } else {
                    $success[] = "✓ Found $count subjects in database";
                }
            }
            
            // Check students table
            $countStudents = mysqli_query($conn, "SELECT COUNT(*) as count FROM students");
            $count = mysqli_fetch_assoc($countStudents)['count'];
            if ($count == 0) {
                $errors[] = "⚠ Warning: No students found. Add students before creating exams.";
            } else {
                $success[] = "✓ Found $count students in database";
            }
            
            mysqli_close($conn);
            
            // Display results
            if (!empty($success)) {
                echo "<div class='alert alert-success mt-3'>";
                echo "<h5>Success:</h5><ul>";
                foreach ($success as $msg) {
                    echo "<li>$msg</li>";
                }
                echo "</ul></div>";
            }
            
            if (!empty($errors)) {
                echo "<div class='alert alert-warning mt-3'>";
                echo "<h5>Warnings/Errors:</h5><ul>";
                foreach ($errors as $msg) {
                    echo "<li>$msg</li>";
                }
                echo "</ul></div>";
            }
            
            if (empty($errors) && !empty($success)) {
                echo "<div class='alert alert-success mt-3'>";
                echo "<h4>Setup Complete!</h4>";
                echo "<p>The exam system is ready to use.</p>";
                echo "<a href='admin_panel/exams.php' class='btn btn-primary'>Go to Exam Management</a>";
                echo "</div>";
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>

<?php
session_start();
require './assets/config.php';

echo "<h1>🔍 Check if Results Were Saved</h1>";
echo "<style>
    body { font-family: Arial; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; }
    th { background: #667eea; color: white; }
    .success { color: green; background: #d4edda; padding: 10px; margin: 10px 0; }
    .error { color: red; background: #f8d7da; padding: 10px; margin: 10px 0; }
</style>";

// Check exam_results table
echo "<h2>1. Checking exam_results table</h2>";
$checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'exam_results'");
if ($checkTable && mysqli_num_rows($checkTable) > 0) {
    echo "<div class='success'>✅ Table exists</div>";
    
    $countResults = mysqli_query($conn, "SELECT COUNT(*) as count FROM exam_results");
    $count = mysqli_fetch_assoc($countResults)['count'];
    
    echo "<p><strong>Total Results in Database: $count</strong></p>";
    
    if ($count > 0) {
        echo "<h3>All Results:</h3>";
        $allResults = mysqli_query($conn, "SELECT er.*, e.exam_title, e.subject, s.fname, s.lname 
                                           FROM exam_results er
                                           LEFT JOIN exams e ON er.exam_id = e.exam_id
                                           LEFT JOIN students s ON er.student_id = s.id
                                           ORDER BY er.created_at DESC");
        echo "<table>";
        echo "<tr><th>ID</th><th>Student ID</th><th>Student Name</th><th>Exam ID</th><th>Exam Title</th><th>Marks</th><th>Total</th><th>%</th><th>Grade</th><th>Status</th><th>Created</th></tr>";
        while ($result = mysqli_fetch_assoc($allResults)) {
            echo "<tr>";
            echo "<td>{$result['id']}</td>";
            echo "<td>{$result['student_id']}</td>";
            echo "<td>{$result['fname']} {$result['lname']}</td>";
            echo "<td>{$result['exam_id']}</td>";
            echo "<td>{$result['exam_title']}</td>";
            echo "<td>{$result['marks_obtained']}</td>";
            echo "<td>{$result['total_marks']}</td>";
            echo "<td>" . number_format($result['percentage'], 1) . "%</td>";
            echo "<td><strong>{$result['grade']}</strong></td>";
            echo "<td><strong>{$result['status']}</strong></td>";
            echo "<td>{$result['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>❌ No results found in database! You need to publish results first.</div>";
    }
} else {
    echo "<div class='error'>❌ Table does not exist</div>";
}

// Check for specific student
echo "<h2>2. Checking for Student: S1718791292 (Student kumar)</h2>";
$studentId = 'S1718791292';

$studentResults = mysqli_query($conn, "SELECT er.*, e.exam_title, e.subject, e.exam_date, e.timestamp
                                       FROM exam_results er
                                       JOIN exams e ON er.exam_id = e.exam_id
                                       WHERE er.student_id = '$studentId'
                                       ORDER BY e.timestamp DESC");

if ($studentResults && mysqli_num_rows($studentResults) > 0) {
    $resultCount = mysqli_num_rows($studentResults);
    echo "<div class='success'>✅ Found $resultCount result(s) for this student</div>";
    
    echo "<table>";
    echo "<tr><th>Date</th><th>Exam</th><th>Subject</th><th>Marks</th><th>Total</th><th>%</th><th>Grade</th><th>Status</th></tr>";
    while ($result = mysqli_fetch_assoc($studentResults)) {
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
    echo "<div class='error'>❌ No results found for student S1718791292</div>";
    echo "<p>This means results were NOT saved to the database.</p>";
}

// Check the exact query used in student panel
echo "<h2>3. Testing Student Panel Query</h2>";
echo "<p>This is the exact query used in student_panel/exams.php:</p>";
echo "<pre style='background: #f4f4f4; padding: 10px;'>";
echo "SELECT er.*, e.exam_title, e.subject, e.exam_date, e.timestamp, e.total_marks as exam_total, e.passing_marks 
FROM exam_results er 
JOIN exams e ON er.exam_id = e.exam_id 
WHERE er.student_id = 'S1718791292'
ORDER BY e.timestamp DESC";
echo "</pre>";

$testQuery = mysqli_query($conn, "SELECT er.*, e.exam_title, e.subject, e.exam_date, e.timestamp, e.total_marks as exam_total, e.passing_marks 
                                  FROM exam_results er 
                                  JOIN exams e ON er.exam_id = e.exam_id 
                                  WHERE er.student_id = '$studentId'
                                  ORDER BY e.timestamp DESC");

if ($testQuery) {
    $rowCount = mysqli_num_rows($testQuery);
    echo "<p><strong>Query executed successfully!</strong></p>";
    echo "<p><strong>Rows returned: $rowCount</strong></p>";
    
    if ($rowCount > 0) {
        echo "<div class='success'>✅ Query returns results - Student panel SHOULD show data</div>";
    } else {
        echo "<div class='error'>❌ Query returns 0 rows - This is why student panel is empty</div>";
    }
} else {
    echo "<div class='error'>❌ Query failed: " . mysqli_error($conn) . "</div>";
}

echo "<hr>";
echo "<h2>📋 Summary & Next Steps</h2>";

if ($count == 0) {
    echo "<div class='error'>";
    echo "<h3>❌ PROBLEM: No results in database</h3>";
    echo "<p><strong>Solution:</strong></p>";
    echo "<ol>";
    echo "<li>Go to: <a href='admin_panel/exams.php'>Admin Exams Panel</a></li>";
    echo "<li>Click the green 'Results' button on any exam</li>";
    echo "<li>Enter marks for students</li>";
    echo "<li>Click 'Save Results'</li>";
    echo "<li>Check browser console (F12) for any errors</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div class='success'>";
    echo "<h3>✅ Results exist in database</h3>";
    echo "<p>But student might not see them if:</p>";
    echo "<ul>";
    echo "<li>Results are for a different exam/class</li>";
    echo "<li>Student ID doesn't match</li>";
    echo "<li>Browser cache issue (try Ctrl+F5 to refresh)</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<p><a href='student_panel/exams.php'>Go to Student Panel</a> | ";
echo "<a href='admin_panel/exams.php'>Go to Admin Panel</a> | ";
echo "<a href='quick_publish_results.php'>Quick Publish Tool</a></p>";
?>

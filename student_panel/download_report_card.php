<?php 
include("../assets/noSessionRedirect.php"); 
include("./verifyRoleRedirect.php");
$id = $_SESSION['uid'];

// Get student details
$student_query = "SELECT * FROM students WHERE id = ?";
$student_stmt = $conn->prepare($student_query);
$student_stmt->bind_param("s", $id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();
$student = $student_result->fetch_assoc();
$student_stmt->close();

// Get exam results
$query_results = "SELECT er.*, e.exam_title, e.subject, e.exam_date, e.timestamp, e.total_marks as exam_total, e.passing_marks 
                  FROM exam_results er 
                  JOIN exams e ON er.exam_id = e.exam_id 
                  WHERE er.student_id = ?
                  ORDER BY e.timestamp DESC";
$stmt_results = $conn->prepare($query_results);
$stmt_results->bind_param("s", $id);
$stmt_results->execute();
$result_data = $stmt_results->get_result();

$results = [];
$totalPercentage = 0;
$totalExams = 0;
$passedExams = 0;

while ($row = $result_data->fetch_assoc()) {
    $results[] = $row;
    $totalPercentage += $row['percentage'];
    $totalExams++;
    if ($row['status'] == 'pass') {
        $passedExams++;
    }
}
$stmt_results->close();

$averagePercentage = $totalExams > 0 ? $totalPercentage / $totalExams : 0;

// Determine overall grade
if ($averagePercentage >= 90) $overallGrade = 'A+';
elseif ($averagePercentage >= 80) $overallGrade = 'A';
elseif ($averagePercentage >= 70) $overallGrade = 'B';
elseif ($averagePercentage >= 60) $overallGrade = 'C';
elseif ($averagePercentage >= 50) $overallGrade = 'D';
else $overallGrade = 'F';

$currentDate = date("F d, Y");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card - <?php echo $student['fname'] . ' ' . $student['lname']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }

        .report-card {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
            opacity: 0.3;
        }

        .school-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            font-weight: bold;
            color: #667eea;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .school-name {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
            position: relative;
            z-index: 1;
        }

        .report-title {
            font-size: 20px;
            font-weight: 300;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .student-info {
            padding: 30px 40px;
            background: #f8f9ff;
            border-bottom: 3px solid #667eea;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 12px;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }

        .info-value {
            font-size: 16px;
            color: #333;
            font-weight: 600;
        }

        .results-section {
            padding: 40px;
        }

        .section-title {
            font-size: 22px;
            font-weight: 700;
            color: #333;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
            display: inline-block;
        }

        .results-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .results-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .results-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .results-table td {
            padding: 15px;
            border-bottom: 1px solid #e8e8e8;
            font-size: 14px;
        }

        .results-table tbody tr:last-child td {
            border-bottom: none;
        }

        .results-table tbody tr:hover {
            background: #f8f9ff;
        }

        .grade-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 14px;
            color: white;
        }

        .grade-A-plus, .grade-A {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .grade-B {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .grade-C {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .grade-D {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .grade-F {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        }

        .status-pass {
            color: #11998e;
            font-weight: 600;
        }

        .status-fail {
            color: #ee5a6f;
            font-weight: 600;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px;
            border-radius: 15px;
            color: white;
            text-align: center;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            transition: transform 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-5px);
        }

        .summary-value {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .summary-label {
            font-size: 14px;
            opacity: 0.9;
            font-weight: 300;
        }

        .footer {
            padding: 30px 40px;
            background: #f8f9ff;
            text-align: center;
            border-top: 3px solid #667eea;
        }

        .signature-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            margin-top: 40px;
        }

        .signature {
            text-align: center;
        }

        .signature-line {
            border-top: 2px solid #333;
            margin: 50px 20px 10px;
        }

        .signature-label {
            font-size: 12px;
            color: #666;
            font-weight: 600;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 25px;
            background: white;
            color: #667eea;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            font-size: 16px;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .print-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(0,0,0,0.4);
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .report-card {
                box-shadow: none;
                border-radius: 0;
            }

            .print-button {
                display: none;
            }
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-results-icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">🖨️ Print Report</button>

    <div class="report-card">
        <!-- Header -->
        <div class="header">
            <div class="school-logo">🎓</div>
            <div class="school-name">School Management System</div>
            <div class="report-title">Academic Report Card</div>
        </div>

        <!-- Student Information -->
        <div class="student-info">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Student Name</span>
                    <span class="info-value"><?php echo $student['fname'] . ' ' . $student['lname']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Student ID</span>
                    <span class="info-value"><?php echo $student['id']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Class</span>
                    <span class="info-value"><?php echo $student['class'] . ' - Section ' . $student['section']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date</span>
                    <span class="info-value"><?php echo $currentDate; ?></span>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="results-section">
            <div class="section-title">📊 Examination Results</div>

            <?php if (count($results) > 0): ?>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Exam Title</th>
                            <th>Subject</th>
                            <th>Marks</th>
                            <th>Total</th>
                            <th>Percentage</th>
                            <th>Grade</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row): 
                            $dateDB = $row['exam_date'] ?? $row['timestamp'];
                            $formattedDate = date("d-m-Y", strtotime($dateDB));
                            $gradeClass = str_replace('+', '-plus', $row['grade']);
                        ?>
                            <tr>
                                <td><?php echo $formattedDate; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['exam_title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                <td><?php echo $row['marks_obtained']; ?></td>
                                <td><?php echo $row['total_marks']; ?></td>
                                <td><strong><?php echo number_format($row['percentage'], 1); ?>%</strong></td>
                                <td>
                                    <span class="grade-badge grade-<?php echo $gradeClass; ?>">
                                        <?php echo $row['grade']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-<?php echo $row['status']; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Summary Cards -->
                <div class="summary-cards">
                    <div class="summary-card">
                        <div class="summary-value"><?php echo $totalExams; ?></div>
                        <div class="summary-label">Total Exams</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-value"><?php echo $passedExams; ?></div>
                        <div class="summary-label">Exams Passed</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-value"><?php echo number_format($averagePercentage, 1); ?>%</div>
                        <div class="summary-label">Average Percentage</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-value"><?php echo $overallGrade; ?></div>
                        <div class="summary-label">Overall Grade</div>
                    </div>
                </div>

            <?php else: ?>
                <div class="no-results">
                    <div class="no-results-icon">📋</div>
                    <h3>No Results Available</h3>
                    <p>Exam results have not been published yet.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer with Signatures -->
        <div class="footer">
            <div class="signature-section">
                <div class="signature">
                    <div class="signature-line"></div>
                    <div class="signature-label">Class Teacher</div>
                </div>
                <div class="signature">
                    <div class="signature-line"></div>
                    <div class="signature-label">Principal</div>
                </div>
                <div class="signature">
                    <div class="signature-line"></div>
                    <div class="signature-label">Parent/Guardian</div>
                </div>
            </div>
            <p style="margin-top: 30px; font-size: 12px; color: #666;">
                This is a computer-generated document. No signature is required.
            </p>
        </div>
    </div>
</body>
</html>

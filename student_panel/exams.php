<?php include("../assets/noSessionRedirect.php"); ?>
<?php include("./verifyRoleRedirect.php");
$id = $_SESSION['uid'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="shortcut icon" href="../images/1.png">
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>

    <style>
        body {
            overflow: hidden;
        }

        header {
            position: relative;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .exam {
            display: flex;
            align-items: center;
            flex-direction: column;
            height: 80vh;
            width: 80%;
            margin: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        thead {
            background: var(--color-primary);
            color: white;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--color-light);
        }

        tbody tr:hover {
            background: var(--color-light);
        }

        .marks-table-search-box {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--color-light);
            border-radius: 5px;
            margin: 1rem 0;
        }

        body {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        body::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body style="overflow-y: scroll;">
    <header>
        <div class="logo">
            <img src="./images/logo.png" alt="">
            <h2>E<span class="danger">R</span>P</h2>
        </div>
        <div class="navbar">
            <a href="index.php">
                <span class="material-icons-sharp">home</span>
                <h3>Home</h3>
            </a>
            <a href="timetable.php">
                <span class="material-icons-sharp">today</span>
                <h3>Time Table</h3>
            </a>
            <a href="exams.php" class="active">
                <span class="material-icons-sharp">grid_view</span>
                <h3>Examination</h3>
            </a>
            <a href="workspace.php">
                <span class="material-icons-sharp">description</span>
                <h3>Workspace</h3>
            </a>
            <a href="password.php">
                <span class="material-icons-sharp">password</span>
                <h3>Change Password</h3>
            </a>
            <a href="logout.php">
                <span class="material-icons-sharp">logout</span>
                <h3>Logout</h3>
            </a>
        </div>
        <div id="profile-btn" style="display: none;">
            <span class="material-icons-sharp">person</span>
        </div>
        <div class="theme-toggler">
            <span class="material-icons-sharp active">light_mode</span>
            <span class="material-icons-sharp">dark_mode</span>
        </div>
    </header>

    <main>
        <div class="exam timetable">
            <h2>Exam Results</h2>
            <h2><?php echo "<a href='exam.php'>View All Results</a> | <a href='progress.php'>Progress Report</a>"; ?></h2>

            <input id="gfg" class="marks-table-search-box" type="text" placeholder="Search for Title, Date, Subjects or Grade">

            <table class="allResultTable" id="allResultList">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Subject</th>
                        <th>Title</th>
                        <th>Obtained Marks</th>
                        <th>Total Marks</th>
                        <th>Percentage</th>
                        <th>Grade</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="geeks">
                    <?php
                    // Get student's class and section
                    $student_query = "SELECT class, section FROM students WHERE id = ?";
                    $student_stmt = $conn->prepare($student_query);
                    $student_stmt->bind_param("s", $id);
                    $student_stmt->execute();
                    $student_result = $student_stmt->get_result();
                    $student_data = $student_result->fetch_assoc();
                    $student_stmt->close();

                    // Get results from new exam_results table
                    $query_new = "SELECT er.*, e.exam_title, e.subject, e.exam_date, e.timestamp, e.total_marks as exam_total, e.passing_marks 
                                  FROM exam_results er 
                                  JOIN exams e ON er.exam_id = e.exam_id 
                                  WHERE er.student_id = ?
                                  ORDER BY e.timestamp DESC";
                    $stmt_new = $conn->prepare($query_new);
                    $stmt_new->bind_param("s", $id);
                    $stmt_new->execute();
                    $result_new = $stmt_new->get_result();

                    $hasResults = false;

                    if ($result_new->num_rows > 0) {
                        $hasResults = true;
                        while ($row = $result_new->fetch_assoc()) {
                            $dateDB = $row['exam_date'] ?? $row['timestamp'];
                            $formattedDate = date("d-m-Y", strtotime($dateDB));

                            $statusColor = $row['status'] == 'pass' ? 'green' : 'red';
                            $statusText = ucfirst($row['status']);

                            echo "<tr>
                                    <td>$formattedDate</td>
                                    <td>{$row['subject']}</td>
                                    <td>{$row['exam_title']}</td>
                                    <td style='text-align:center;'>{$row['marks_obtained']}</td>
                                    <td style='text-align:center;'>{$row['total_marks']}</td>
                                    <td style='text-align:center;'>" . number_format($row['percentage'], 1) . "%</td>
                                    <td style='text-align:center; font-weight:bold; color:$statusColor;'>{$row['grade']}</td>
                                    <td style='color:$statusColor; text-align:center;'>$statusText</td>
                                  </tr>";
                        }
                    }
                    $stmt_new->close();

                    if (!$hasResults) {
                        echo '<tr><td colspan="8" style="text-align:center;padding-top: 3rem;">No exam results available yet</td></tr>';
                    }

                    ?>
                </tbody>
            </table>

        </div>
    </main>

    <script>
        $(document).ready(function() {
                    $("#gfg").on("keyup", function() {
                        var value = $(this).val().toLowerCase();
                        $("#geeks tr").filter(function() {
                            $(this).toggle($(this).text()
                                .toLowerCase().indexOf(value) > -1)
                        });
                    });
                });


</html>

<?php include("../assets/noSessionRedirect.php"); ?>

<?php include("./verifyRoleRedirect.php"); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="shortcut icon" href="./images/logo.png">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/oranbyte-google-translator.css">

    <style type="text/css">
        .container main .subjects .eg #piechart {
            width: 600px;
            height: 350px;
            padding-right: 0%;
            position: relative;
            border-radius: 20px;
        }

        .container main .subjects .eg {
            border-radius: 20px;
        }

        @media screen and (max-width: 700px) {
            .container main .subjects .eg #piechart {
                width: 250px;
                height: 200px;
                padding-left: 0%;
                padding-right: 0%;

            }

            .container main .subjects {
                margin-left: 4%;
            }

            .leaves {
                width: 106%;
                /*margin-left: 5%;*/
                font-size: 10px;
                padding-right: 0;
            }

        }

        #myInput {
            background-image: url('search.svg');
            /* Add a search icon to input */
            background-position: 5px 2px;
            /* Position the search icon */
            background-repeat: no-repeat;
            /* Do not repeat the icon image */
            width: 80%;
            /* Full-width */
            font-size: 16px;
            /* Increase font-size */
            padding: 12px 20px 12px 40px;
            /* Add some padding */
            border: 1px solid #ddd;
            /* Add a grey border */
            margin-bottom: 12px;
            /* Add some space below the input */
            border-radius: 40px;
            position: relative;
        }

        #myTable {
            width: 80%;
            /* Full-width */
            border: 1px solid #ddd;
            /* Add a grey border */
            font-size: 15px;
            /* Increase font-size */
            border-radius: 40px;
            position: relative;
        }

        #myTable th {
            background-color: #A9A9A9;
            color: white;
        }

        #myTable th,
        #myTable td {
            text-align: center;
            /* Left-align text */
            padding: 12px;
            /* Add padding */
            border-radius: 16px;
        }


        #myTable tr {
            /* Add a bottom border to all table rows */
            border-bottom: 1px solid #ddd;
            border-radius: 40px;
            text-align: center;
        }

        #myTable tr.header,
        #myTable tr:hover {
            /* Add a grey background color to the table header and on hover */
            background-color: #f1f1f1;
        }

        @media only screen and (max-width: 768px) {
            #myTable {
                width: 95%;
                margin: 0%;
                font-size: 12.5px;
            }

            #myInput {
                width: 95%;
                margin: 0%;
            }
        }

        .link-btn {
            display: block;
            border: 1px solid rgb(214 183 255);
            background-color: rgb(212 196 255 / 77%);
            color: #000000;
            padding: 8px 10px;
            border-radius: 5px;
            max-width: 100px;
            text-align: center;
        }
    </style>
</head>

<body>
    <header>
        <div class="logo" title="University Management System">
            <img src="./images/logo.png" alt="">
            <h2>E<span class="danger">R</span>P</h2>
        </div>
        <div class="navbar">
            <a href="index.php">
                <span class="material-icons-sharp">home</span>
                <h3>Home</h3>
            </a>
            <a href="timetable.php" onclick="timeTableAll()">
                <span class="material-icons-sharp">today</span>
                <h3>Time Table</h3>
            </a>
            <a href="exam.php">
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
                <span class="material-icons-sharp" onclick="">logout</span>
                <h3>Logout</h3>
            </a>
        </div>
        <div id="profile-btn">
            <span class="material-icons-sharp">person</span>
        </div>
        <div class="theme-toggler">
            <span class="material-icons-sharp active">light_mode</span>
            <span class="material-icons-sharp">dark_mode</span>
        </div>

    </header>
    <div class="container">
        <aside>
            <div class="profile">
                <div class="top">
                    <?php
                    // Ensure session is started and user ID is available
                    if(!isset($_SESSION['uid'])) {
                        header("Location: ../login.php");
                        exit();
                    }
                    
                    $id = $_SESSION['uid'];
                    // Use prepared statement for security
                    $query_sql = "SELECT * FROM students WHERE id=?";
                    $stmt = mysqli_prepare($conn, $query_sql);
                    mysqli_stmt_bind_param($stmt, "s", $id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $row = mysqli_fetch_assoc($result);
                    
                    // Default image if none exists
                    $studentImage = isset($row['image']) && !empty($row['image']) ? $row['image'] : 'default-avatar.png';
                    echo "<div class='profile-photo'>
                        <img src='../studentUploads/" . htmlspecialchars($studentImage) . "' alt='Student Profile'>
                    </div>";
                    mysqli_stmt_close($stmt);
                    ?>

                    <div class="info">
                        <?php
                        // Use the same ID from session with prepared statement
                        $id = $_SESSION['uid'];
                        $query = "SELECT * FROM students WHERE id=?";
                        $stmt = mysqli_prepare($conn, $query);
                        mysqli_stmt_bind_param($stmt, "s", $id);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        if ($row = mysqli_fetch_assoc($result)) {
                            echo "<p>Hey, <b>" . htmlspecialchars($row["fname"]) . "</b> </p>
                        <small class='text-muted'><b>ID&nbsp;:&nbsp;</b>" . htmlspecialchars($row["id"]) . "</small>";
                        }
                        mysqli_stmt_close($stmt);
                        ?>

                    </div>
                </div>
                <br>
                <div id="oranbyte-google-translator" 
                        data-default-lang="en"
                        data-lang-root-style="code-flag"
                        data-lang-list-style="code-flag"
                        ></div>
                <div class="about">
                    <?php
                    $id = $_SESSION['uid'];
                    $query = "SELECT * FROM students WHERE id=?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "s", $id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    if ($row = mysqli_fetch_assoc($result)) {
                        echo "<p><h5>Class : " . htmlspecialchars($row["class"]) . "</h5></p>
                    <p>Section " . htmlspecialchars($row["section"]) . "</p>
                    <h5>DOB</h5>
                    <p>" . $row["dob"] . "</p>
                    <h5>Contact</h5>
                    <p>" . $row["phone"] . "</p>
                    <h5>Email</h5>
                    <p>" . $row["email"] . "</p>
                    <h5>Address</h5>
                    <p>" . $row["address"] . "</p>";
                    }
                    mysqli_stmt_close($stmt);

                    ?><br>

                    <div style="display: inline;">
                  
                    <b><a href="buspanel.php" class="link-btn">Bus Panel</a></b><br>
                    <b><a href="fee-payment.php" class="link-btn">Pay-Fee</a></b>
                    
                    </div>
                </div>
            </div>
        </aside>

        <main>
            <h1>Attendance</h1>
            <div class="subjects">
                <div class="eg">
                    <div id="piechart"></div>

                </div>
            </div>


            <div class="leaves " style="margin-top: 20px;">
                <h2>Syllabus</h2>
                <?php
                $id = $_SESSION['uid'];
                $query_sql = "SELECT class FROM students WHERE id=?";
                $stmt = mysqli_prepare($conn, $query_sql);
                mysqli_stmt_bind_param($stmt, "s", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_assoc($result);
                $class = $row['class'];
                mysqli_stmt_close($stmt);

                $sql2 = "SELECT * FROM syllabus WHERE class=?";
                $stmt2 = mysqli_prepare($conn, $sql2);
                mysqli_stmt_bind_param($stmt2, "s", $class);
                mysqli_stmt_execute($stmt2);
                $result2 = mysqli_stmt_get_result($stmt2);
                if (mysqli_num_rows($result2) > 0) {
                    while ($row2 = mysqli_fetch_assoc($result2)) {
                        echo "<div class='teacher'>
                    <div class='profile-photo'>
                    <a href='../syllabusUploads/" . htmlspecialchars($row2['file']) . "'>
                    <img src='./images/profile-2.png' alt=''></div>
                    <div class='info'>
                        <h3>" . htmlspecialchars($row2['subject']) . "</h3>
                        <small class='text-muted'>Download or View</small>
                        </a>
                    </div>
                </div>";
                    }
                    mysqli_stmt_close($stmt2);
                } else {
                    echo '<p style="padding-left: 20px;margin-top: 10px;">Syllabus not uploaded yet!</p>';
                }
                ?>


            </div>
            
            <!-- Curriculum Section -->
            <div class="leaves" style="margin-top: 20px;">
                <h2>My Curriculum</h2>
                <?php
                $id = $_SESSION['uid'];
                $query_sql = "SELECT class FROM students WHERE id=?";
                $stmt = mysqli_prepare($conn, $query_sql);
                mysqli_stmt_bind_param($stmt, "s", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_assoc($result);
                $class = $row['class'];
                mysqli_stmt_close($stmt);

                // Fetch curriculum for student's class
                $currSql = "SELECT cm.*, 
                            (SELECT COUNT(*) FROM curriculum_subjects cs WHERE cs.curriculum_id = cm.curriculum_id) as subject_count
                            FROM curriculum_master cm 
                            WHERE cm.class=? AND cm.status='active' 
                            ORDER BY cm.created_at DESC LIMIT 1";
                $currStmt = mysqli_prepare($conn, $currSql);
                $classNum = intval($class);
                mysqli_stmt_bind_param($currStmt, "i", $classNum);
                mysqli_stmt_execute($currStmt);
                $currResult = mysqli_stmt_get_result($currStmt);
                
                if ($currRow = mysqli_fetch_assoc($currResult)) {
                    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 15px;'>";
                    echo "<h4 style='color: #667eea;'>" . htmlspecialchars($currRow['curriculum_name']) . "</h4>";
                    echo "<p><strong>Academic Year:</strong> " . htmlspecialchars($currRow['academic_year']) . "</p>";
                    if (!empty($currRow['department_code'])) {
                        echo "<p><strong>Department:</strong> " . htmlspecialchars($currRow['department_code']) . "</p>";
                    }
                    echo "<p><strong>Total Subjects:</strong> " . $currRow['subject_count'] . "</p>";
                    echo "</div>";
                    
                    // Fetch and display subjects with subject names from subjects table
                    $subSql = "SELECT cs.*, s.subject_name 
                              FROM curriculum_subjects cs 
                              LEFT JOIN subjects s ON cs.course_code = s.subject_id 
                              WHERE cs.curriculum_id=? 
                              ORDER BY s.subject_name";
                    $subStmt = mysqli_prepare($conn, $subSql);
                    mysqli_stmt_bind_param($subStmt, "i", $currRow['curriculum_id']);
                    mysqli_stmt_execute($subStmt);
                    $subResult = mysqli_stmt_get_result($subStmt);
                    
                    echo "<div style='display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px;'>";
                    while ($subRow = mysqli_fetch_assoc($subResult)) {
                        $totalHours = intval($subRow['theory_hours']) + intval($subRow['practical_hours']);
                        $subjectName = $subRow['subject_name'] ?? 'Subject ' . $subRow['course_code'];
                        echo "<div style='background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #667eea;'>";
                        echo "<h5 style='margin: 0 0 5px 0; color: #333;'>" . htmlspecialchars($subjectName) . "</h5>";
                        echo "<p style='margin: 0; color: #666; font-size: 13px;'>Code: " . htmlspecialchars($subRow['course_code']) . "</p>";
                        echo "<p style='margin: 5px 0 0 0; color: #667eea; font-size: 12px;'>" . $totalHours . " hours/week</p>";
                        if ($subRow['is_mandatory']) {
                            echo "<span style='background: #10b981; color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px;'>Mandatory</span>";
                        }
                        echo "</div>";
                    }
                    echo "</div>";
                    mysqli_stmt_close($subStmt);
                } else {
                    echo '<p style="padding-left: 20px;margin-top: 10px;">Curriculum not available yet!</p>';
                }
                mysqli_stmt_close($currStmt);
                ?>
            </div>
            
            <div class="timetable" id="timetable">
                <h2>Monthly Attendance</h2>
                <input type="text" id="myInput" onkeyup="myFunction()" placeholder="Search for Date...">

                <table id="myTable">
                    <tr class="header">
                        <th style="width:60%;">Date</th>
                        <th style="width:40%;">Attendence</th>
                    </tr>
                    <tbody id="attendence_table">

                    </tbody>
                </table>
                <br><br>
            </div>
        </main>

        <div class="right">
            <div class="announcements">
                <h2>Notice</h2>
                <div class="updates">
                    <div class="message">
                        <?php
                        $id = $_SESSION['uid'];
                        $query_sql2 = "SELECT class FROM students WHERE id=?";
                        $stmt = mysqli_prepare($conn, $query_sql2);
                        mysqli_stmt_bind_param($stmt, "s", $id);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $row = mysqli_fetch_assoc($result);
                        $class = $row['class'];
                        mysqli_stmt_close($stmt);

                        $sql_query = "SELECT * FROM notice WHERE (role = 'student' AND class=?) OR (role = 'all' OR role='') ORDER BY s_no DESC LIMIT 3";
                        $stmt2 = mysqli_prepare($conn, $sql_query);
                        mysqli_stmt_bind_param($stmt2, "s", $class);
                        mysqli_stmt_execute($stmt2);
                        $result = mysqli_stmt_get_result($stmt2);
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<p> <b>" . htmlspecialchars($row['title']) . "</b> <br>" . htmlspecialchars($row['body']) . "<br></p>";
                                if ($row['file'] != null) {
                                    echo "<a href='../noticeUploads/" . htmlspecialchars($row['file']) . "'><img src='file.svg' height='30px' width='30px'><p style='color:red;'>View Notice</p></a>";
                                }
                                echo "<small class='text-muted'><b>" . htmlspecialchars($row['timestamp']) . "</b></small><hr><br>";
                            }
                            mysqli_stmt_close($stmt2);
                        }
                        ?>



                    </div>

                </div>
            </div>

            <div class="leaves">
                <h2>Feedbacks</h2>
                <?php
                $id = $_SESSION['uid'];

                $sql2 = "SELECT * FROM `feedback` WHERE `receiver_id`='$id' LIMIT 5";
                $result2 = mysqli_query($conn, $sql2);
                if ($result2->num_rows > 0) {
                    while ($row2 = $result2->fetch_assoc()) {
                        $timestamp = $row2['timestamp'];
                        $formattedDate = date('d M, Y', strtotime($timestamp));

                        $senderId = $row2['sender_id'];
                        $tableName = ($senderId >= 1000) ? 'admins' : 'teachers';
                        $sql = "SELECT `fname`, `lname` FROM `$tableName` WHERE id = '$senderId' LIMIT 1";

                        $result = mysqli_query($conn, $sql);
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $sender = ucfirst(strtolower($row['fname'])) . " " . strtolower($row['lname']);
                        } else {
                            $sender = "REMOVED";
                        }

                        echo "<div class='teacher'>
                            <div class='info' style='width: 100%;'>
                                <p class='text-muted para-text'>
                                <i class='bx bxs-chat' ></i>
                                " . $row2['msg'] . "</p>
                                <div class='flexbox' style='margin-top: 8px;'>
                                    <small>" . $formattedDate . "</small>
                                    <small style='margin-left: auto;'>" .  $sender . "</small>
                                </div>
                            </div>
                        </div>";
                    }
                } else {
                    echo "<div class='teacher'>
                    <div class='info' style='width: 100%;'>
                        <p class='text-muted para-text'>
                        <i class='bx bxs-chat' ></i>
                       No Feedbacks</p>
                       
                    </div>
                </div>";
                }
                ?>


            </div>

        </div>
    </div>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        let presentPer = 30;
        let absentPer = 70;


        document.addEventListener("DOMContentLoaded", function() {
            fetch("fetchAttendencePercentage.php", {
                    method: "POST",
                })
                .then(response => response.json())
                .then(data => {


                    if (data['status'] === "success") {
                        presentPer = parseFloat(data['present']);
                        absentPer = parseFloat(data['absent']);



                        google.charts.load("current", {
                            packages: ["corechart"]
                        });
                        google.charts.setOnLoadCallback(drawChart);

                    } else {
                        alert("Something went wrong!");
                    }
                })
                .catch(error => {
                    console.error("error" + error)
                })
        });


        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['Attandence', 'percentage'],
                ['preset', presentPer],
                ['Absent', absentPer],
            ]);

            var options = {
                legend: 'none',
                pieSliceText: 'label',
                title: 'Student Attendence All time',
                pieStartAngle: 100,
            };

            var chart = new google.visualization.PieChart(document.getElementById('piechart'));
            chart.draw(data, options);
        }
    </script>


    <script type="text/javascript" src="app.js"></script>
    <!-- <script type="text/javascript" src="timeTable.js"></script> -->
    <script type="text/javascript" src="index.js"></script>
    <script src="../js/oranbyte-google-translator.js"></script>
</body>

</html>
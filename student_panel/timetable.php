<?php 
session_start();
include("../assets/noSessionRedirect.php");
include("./verifyRoleRedirect.php");
include("../assets/config.php");

// Get student info
$studentId = $_SESSION['uid'];
$query = "SELECT class, section, fname, lname FROM students WHERE id=?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $studentId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Timetable</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="shortcut icon" href="./images/logo.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../css/oranbyte-google-translator.css">
    <style>
        .timetable-wrapper {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        .timetable-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .timetable-grid {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .timetable-grid th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .timetable-grid td {
            padding: 1rem;
            border: 1px solid #e0e0e0;
            text-align: center;
            vertical-align: top;
            min-width: 120px;
        }
        .time-column {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
            white-space: nowrap;
        }
        .period-cell {
            background: #fff;
            transition: all 0.3s;
            cursor: pointer;
        }
        .period-cell:hover {
            background: #f0f7ff;
            transform: translateY(-2px);
        }
        .subject-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
        }
        .period-info {
            font-size: 0.8rem;
            color: #666;
        }
        .break-cell {
            background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
            font-weight: 600;
            color: #856404;
        }
        .no-timetable {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        @media (max-width: 768px) {
            .timetable-wrapper {
                padding: 1rem;
            }
            .timetable-grid {
                font-size: 0.85rem;
            }
            .timetable-grid th, .timetable-grid td {
                padding: 0.5rem;
                min-width: 80px;
            }
        }
    </style>
</head>
<body>
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
            <a href="timetable.php" class="active">
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
                <span class="material-icons-sharp">logout</span>
                <h3>Logout</h3>
            </a>
        </div>
        <div class="theme-toggler">
            <span class="material-icons-sharp active">light_mode</span>
            <span class="material-icons-sharp">dark_mode</span>
        </div>
    </header>

    <main style="margin: 0;">
        <div class="timetable-wrapper">
            <div class="timetable-header">
                <h1 style="margin: 0 0 0.5rem 0;">📚 Weekly Timetable</h1>
                <p style="margin: 0; opacity: 0.9;">
                    <?php echo htmlspecialchars($student['fname'] . ' ' . $student['lname']); ?> | 
                    Level <?php echo htmlspecialchars($student['class']); ?> - Section <?php echo htmlspecialchars($student['section']); ?>
                </p>
            </div>

            <div id="timetableContainer">
                <p style="text-align: center; padding: 2rem;">Loading your timetable...</p>
            </div>
        </div>
    </main>

    <script>
        const studentClass = '<?php echo $student['class']; ?>';
        const studentSection = '<?php echo $student['section']; ?>';

        document.addEventListener('DOMContentLoaded', function() {
            loadTimetable();
        });

        function loadTimetable() {
            const formData = new FormData();
            formData.append('action', 'get_student_timetable');
            formData.append('class', studentClass);
            formData.append('section', studentSection);
            
            fetch('../assets/manageTimetable.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    displayTimetable(data.periods);
                } else {
                    document.getElementById('timetableContainer').innerHTML = `
                        <div class="no-timetable">
                            <span class="material-icons-sharp" style="font-size: 4rem; color: #ddd;">calendar_today</span>
                            <h3>No Timetable Available</h3>
                            <p>${data.message || 'Your class timetable has not been published yet.'}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('timetableContainer').innerHTML = `
                    <div class="no-timetable">
                        <span class="material-icons-sharp" style="font-size: 4rem; color: #f44336;">error</span>
                        <h3>Error Loading Timetable</h3>
                        <p>Please try again later.</p>
                    </div>
                `;
            });
        }

        function displayTimetable(periods) {
            const container = document.getElementById('timetableContainer');
            
            if (!periods || periods.length === 0) {
                container.innerHTML = `
                    <div class="no-timetable">
                        <span class="material-icons-sharp" style="font-size: 4rem; color: #ddd;">schedule</span>
                        <h3>No Periods Scheduled</h3>
                        <p>Your timetable is empty.</p>
                    </div>
                `;
                return;
            }
            
            // Organize periods by day
            const schedule = {
                'Monday': [],
                'Tuesday': [],
                'Wednesday': [],
                'Thursday': [],
                'Friday': []
            };
            
            periods.forEach(period => {
                if (schedule[period.day_of_week]) {
                    schedule[period.day_of_week].push(period);
                }
            });
            
            // Get unique time slots
            const timeSlots = [];
            const timeMap = new Map();
            
            periods.forEach(period => {
                const timeKey = `${period.start_time}-${period.end_time}`;
                if (!timeMap.has(timeKey)) {
                    timeMap.set(timeKey, {
                        start: period.start_time,
                        end: period.end_time
                    });
                    timeSlots.push(timeMap.get(timeKey));
                }
            });
            
            timeSlots.sort((a, b) => a.start.localeCompare(b.start));
            
            // Build table
            let html = '<table class="timetable-grid"><thead><tr><th>Time</th>';
            
            Object.keys(schedule).forEach(day => {
                html += `<th>${day}</th>`;
            });
            
            html += '</tr></thead><tbody>';
            
            timeSlots.forEach(slot => {
                html += '<tr>';
                html += `<td class="time-column">${formatTime(slot.start)}<br>to<br>${formatTime(slot.end)}</td>`;
                
                Object.keys(schedule).forEach(day => {
                    const period = schedule[day].find(p => 
                        p.start_time === slot.start && p.end_time === slot.end
                    );
                    
                    if (period) {
                        if (period.is_break) {
                            html += `<td class="break-cell">☕ BREAK</td>`;
                        } else {
                            html += `<td class="period-cell">
                                <div class="subject-name">${period.subject_name || 'Subject'}</div>
                                <div class="period-info">${period.period_type || 'Class'}</div>
                            </td>`;
                        }
                    } else {
                        html += '<td style="background:#f5f5f5;">-</td>';
                    }
                });
                
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            container.innerHTML = html;
        }

        function formatTime(time) {
            const [hours, minutes] = time.split(':');
            const h = parseInt(hours);
            const ampm = h >= 12 ? 'PM' : 'AM';
            const hour12 = h % 12 || 12;
            return `${hour12}:${minutes} ${ampm}`;
        }
    </script>

    <script src="app.js"></script>
</body>
</html>
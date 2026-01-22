<div class="sidebar">
    <a href="dashboard.php" class="logo">
        <img src="../images/1.png">
        <div class="logo-name"><span class="text-warning">Parent</span><span class="darkTextColor"> Portal</span></div>
    </a>
    
    <ul class="side-menu-opener">
    </ul>
    
    <ul class="side-menu main-side-board">
        <li><a href="dashboard.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
        <li><a href="children.php"><i class='bx bxs-group'></i>My Children</a></li>
        <li><a href="exams.php"><i class='bx bx-file-blank'></i>Exam Results</a></li>
        <li><a href="grades.php"><i class='bx bx-paste'></i>Grades & Reports</a></li>
        <li><a href="assignments.php"><i class='bx bx-file'></i>Assignments</a></li>
        <li><a href="attendance.php"><i class='bx bx-list-check'></i>Attendance</a></li>
        <li><a href="timetable.php"><i class='bx bx-table'></i>Time Table</a></li>
        <li><a href="messages.php"><i class='bx bx-message-dots'></i>Messages</a></li>
        <li><a href="announcements.php"><i class='bx bx-bookmark'></i>Announcements</a></li>
        <li><a href="calendar.php"><i class='bx bx-calendar'></i>School Calendar</a></li>
        <li><a href="fees.php"><i class='bx bx-money'></i>Fee Status</a></li>
        <li><a href="settings.php"><i class='bx bx-cog'></i>Settings</a></li>
    </ul>
    <ul class="side-menu">
        <li>
            <a class="logout" data-bs-toggle="modal" data-bs-target="#logout-modal">
                <i class='bx bx-log-out-circle'></i>
                Logout
            </a>
        </li>
    </ul>
</div>

<div class="modal fade" id="logout-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
            </div>
            <div class="modal-body">
                <strong>Do you really want to logout?</strong>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" onclick="logout()">Logout</button>
            </div>
        </div>
    </div>
</div>

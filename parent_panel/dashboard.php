<?php include('partials/_header.php') ?>

<!-- Sidebar -->
<?php include('partials/_sidebar.php') ?>
<!-- End of Sidebar -->

<!-- Main Content -->
<div class="content">
    <!-- Navbar -->
    <?php include("partials/_navbar.php"); ?>
    <!-- End of Navbar -->

    <main>
        <div class="header">
            <div class="left">
                <h1>Parent Dashboard</h1>
                <ul class="breadcrumb">
                    <li><a>Welcome, <?php echo htmlspecialchars($_SESSION['parent_name'] ?? 'Parent'); ?></a></li>
                </ul>
            </div>
        </div>

        <!-- Quick Stats -->
        <ul class="insights">
            <li>
                <i class='bx bxs-group'></i>
                <span class="info">
                    <h3 class="text-center" id="childrenCount">0</h3>
                    <p>Children</p>
                </span>
            </li>
            <li onclick="window.location='messages.php'">
                <i class='bx bx-message-dots'></i>
                <span class="info">
                    <h3 class="text-center" id="unreadMessages">0</h3>
                    <p>Unread Messages</p>
                </span>
            </li>
            <li onclick="window.location='announcements.php'">
                <i class='bx bx-bookmark'></i>
                <span class="info">
                    <h3 class="text-center" id="newAnnouncements">0</h3>
                    <p>New Announcements</p>
                </span>
            </li>
            <li onclick="window.location='calendar.php'">
                <i class='bx bx-calendar'></i>
                <span class="info">
                    <h3 class="text-center" id="upcomingEvents">0</h3>
                    <p>Upcoming Events</p>
                </span>
            </li>
        </ul>

        <!-- Children Overview -->
        <div class="bottom-data">
            <div class="orders">
                <div class="header">
                    <i class='bx bxs-user-detail'></i>
                    <h3>My Children</h3>
                    <a href="children.php"><i class='bx bx-chevron-right'></i></a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th>Attendance %</th>
                            <th>Latest Grade</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="childrenList">
                        <tr>
                            <td colspan="5" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Recent Notifications -->
            <div class="reminders">
                <div class="header">
                    <i class='bx bx-bell'></i>
                    <h3>Recent Notifications</h3>
                    <i class='bx bx-filter' id="filterNotifications"></i>
                </div>
                <ul class="task-list" id="notificationsList">
                    <li class="not-completed">
                        <div class="task-title">
                            <i class='bx bx-info-circle'></i>
                            <p>Loading notifications...</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Recent Assignments & Messages -->
        <div class="bottom-data mt-3">
            <div class="orders">
                <div class="header">
                    <i class='bx bx-file'></i>
                    <h3>Pending Assignments</h3>
                    <a href="assignments.php"><i class='bx bx-chevron-right'></i></a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Assignment</th>
                            <th>Subject</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="assignmentsList">
                        <tr>
                            <td colspan="5" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="reminders">
                <div class="header">
                    <i class='bx bx-calendar-event'></i>
                    <h3>Upcoming Events</h3>
                    <a href="calendar.php"><i class='bx bx-chevron-right'></i></a>
                </div>
                <ul class="task-list" id="eventsList">
                    <li class="not-completed">
                        <div class="task-title">
                            <i class='bx bx-calendar'></i>
                            <p>Loading events...</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
});

function loadDashboardData() {
    // Load children list
    fetch('../assets/manageParentPortal.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'get_children'})
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('childrenCount').textContent = data.children.length;
            displayChildren(data.children);
        }
    });

    // Load unread messages count
    fetch('../assets/manageParentMessages.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'get_unread_count'})
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('unreadMessages').textContent = data.count;
        }
    });

    // Load notifications
    fetch('../assets/manageParentNotifications.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'get_notifications', limit: 5})
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            displayNotifications(data.notifications);
            document.getElementById('notification-count').textContent = data.unread_count || 0;
        }
    });

    // Load pending assignments
    loadPendingAssignments();

    // Load upcoming events
    loadUpcomingEvents();
}

function displayChildren(children) {
    const tbody = document.getElementById('childrenList');
    if (children.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">No children found</td></tr>';
        return;
    }
    
    tbody.innerHTML = children.map(child => `
        <tr>
            <td>
                <img src="../studentUploads/${child.profile_picture || 'default.jpg'}" width="30" class="rounded-circle">
                ${child.name}
            </td>
            <td>Class ${child.class} ${child.section || ''}</td>
            <td><span class="badge bg-${child.attendance >= 75 ? 'success' : 'danger'}">${child.attendance}%</span></td>
            <td>${child.latest_grade || 'N/A'}</td>
            <td>
                <a href="student_details.php?id=${child.student_id}" class="btn btn-sm btn-primary">View</a>
            </td>
        </tr>
    `).join('');
}

function displayNotifications(notifications) {
    const list = document.getElementById('notificationsList');
    if (notifications.length === 0) {
        list.innerHTML = '<li class="completed"><div class="task-title"><i class="bx bx-check-circle"></i><p>No new notifications</p></div></li>';
        return;
    }
    
    list.innerHTML = notifications.map(notif => `
        <li class="${notif.is_read ? 'completed' : 'not-completed'}">
            <div class="task-title">
                <i class='bx ${getNotificationIcon(notif.notification_type)}'></i>
                <p>${notif.title}</p>
            </div>
            <small>${notif.time_ago}</small>
        </li>
    `).join('');
}

function loadPendingAssignments() {
    // Implementation for loading assignments
    const tbody = document.getElementById('assignmentsList');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center">No pending assignments</td></tr>';
}

function loadUpcomingEvents() {
    // Implementation for loading events
    const list = document.getElementById('eventsList');
    list.innerHTML = '<li class="completed"><div class="task-title"><i class="bx bx-calendar"></i><p>No upcoming events</p></div></li>';
}

function getNotificationIcon(type) {
    const icons = {
        'grade_update': 'bx-trophy',
        'attendance_alert': 'bx-error',
        'assignment_due': 'bx-file',
        'exam_schedule': 'bx-calendar-check',
        'announcement': 'bx-bookmark',
        'event': 'bx-calendar-event'
    };
    return icons[type] || 'bx-info-circle';
}

function logout() {
    window.location.href = '../assets/logout.php';
}
</script>

<?php include('partials/_footer.php') ?>

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
    loadDashboardStats();
    loadChildren();
    loadAnnouncements();
    loadPendingAssignments();
    loadUpcomingEvents();
});

function loadDashboardStats() {
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_dashboard_stats'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('childrenCount').textContent = data.data.children_count || 0;
            document.getElementById('unreadMessages').textContent = data.data.unread_messages || 0;
            document.getElementById('newAnnouncements').textContent = data.data.announcements_count || 0;
            document.getElementById('upcomingEvents').textContent = data.data.upcoming_events || 0;
        }
    })
    .catch(error => console.error('Error loading stats:', error));
}

function loadChildren() {
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_children'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.children.length > 0) {
            displayChildren(data.children);
        } else {
            document.getElementById('childrenList').innerHTML = '<tr><td colspan="5" class="text-center text-muted">No children found</td></tr>';
        }
    })
    .catch(error => {
        console.error('Error loading children:', error);
        document.getElementById('childrenList').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading children</td></tr>';
    });
}

function displayChildren(children) {
    const tbody = document.getElementById('childrenList');
    
    if (!children || children.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No children found</td></tr>';
        return;
    }
    
    let html = '';
    children.forEach((child, index) => {
        if (index < 5) { // Show only first 5 on dashboard
            const attendanceClass = child.attendance_percentage >= 75 ? 'success' : (child.attendance_percentage >= 50 ? 'warning' : 'danger');
            const studentImage = child.image || 'default-avatar.png';
            const studentName = child.name || (child.fname + ' ' + (child.lname || ''));
            
            html += `
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <img src="../studentUploads/${studentImage}" 
                                 width="35" height="35" style="border-radius: 50%; object-fit: cover;" 
                                 onerror="this.src='../images/default-avatar.png'">
                            <span>${studentName}</span>
                        </div>
                    </td>
                    <td>Class ${child.class}${child.section ? ' - ' + child.section : ''}</td>
                    <td><span class="badge" style="background: ${attendanceClass === 'success' ? '#10b981' : attendanceClass === 'warning' ? '#f59e0b' : '#ef4444'}; color: white; padding: 5px 10px; border-radius: 12px;">${child.attendance_percentage}%</span></td>
                    <td><a href="#" onclick="viewStudentGrades('${child.id}'); return false;" style="color: #667eea; text-decoration: none;"><i class='bx bx-bar-chart'></i> View Grades</a></td>
                    <td>
                        <a href="children.php?student_id=${child.id}" style="background: #667eea; color: white; padding: 8px 15px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                            <i class='bx bx-show'></i> View Details
                        </a>
                    </td>
                </tr>
            `;
        }
    });
    
    tbody.innerHTML = html;
}

function loadAnnouncements() {
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_parent_announcements'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.announcements && data.announcements.length > 0) {
            displayNotifications(data.announcements.slice(0, 5));
        } else {
            document.getElementById('notificationsList').innerHTML = `
                <li class="completed">
                    <div class="task-title">
                        <i class='bx bx-check-circle' style="color: #10b981;"></i>
                        <p>No new announcements</p>
                    </div>
                </li>`;
        }
    })
    .catch(error => {
        console.error('Error loading announcements:', error);
        document.getElementById('notificationsList').innerHTML = `
            <li class="not-completed">
                <div class="task-title">
                    <i class='bx bx-error' style="color: #ef4444;"></i>
                    <p>Error loading announcements</p>
                </div>
            </li>`;
    });
}

function displayNotifications(announcements) {
    const list = document.getElementById('notificationsList');
    
    let html = '';
    announcements.forEach(announcement => {
        const timeAgo = getTimeAgo(announcement.created_at || announcement.published_date || announcement.timestamp);
        const icon = getAnnouncementIcon(announcement.priority || 'normal');
        const title = announcement.title || announcement.body || 'Announcement';
        
        html += `
            <li class="not-completed" style="cursor: pointer; transition: background 0.2s;" 
                onmouseover="this.style.background='#f8f9fa'" 
                onmouseout="this.style.background='transparent'">
                <div class="task-title">
                    <i class='bx ${icon}'></i>
                    <p>${title}</p>
                </div>
                <small class="text-muted">${timeAgo}</small>
            </li>
        `;
    });
    
    list.innerHTML = html;
}

function loadPendingAssignments() {
    // Get all children first
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_children'
    })
    .then(response => response.json())
    .then(data => {
        console.log('Children data:', data);
        if (data.status === 'success' && data.children && data.children.length > 0) {
            // Load assignments for all children
            const assignmentPromises = data.children.map(child => {
                return fetch('../assets/parentPortalHandler.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=get_student_assignments&student_id=${child.id}`
                })
                .then(res => res.json())
                .then(assignData => {
                    console.log(`Assignments for ${child.name}:`, assignData);
                    if (assignData.status === 'success' && assignData.assignments) {
                        return assignData.assignments.map(a => ({
                            ...a,
                            student_name: child.name || child.fname + ' ' + (child.lname || ''),
                            student_id: child.id
                        }));
                    }
                    return [];
                })
                .catch(err => {
                    console.error(`Error loading assignments for ${child.name}:`, err);
                    return [];
                });
            });
            
            return Promise.all(assignmentPromises);
        } else {
            console.log('No children found');
            document.getElementById('assignmentsList').innerHTML = '<tr><td colspan="5" class="text-center text-muted">No children found</td></tr>';
            return Promise.resolve([]);
        }
    })
    .then(allAssignments => {
        console.log('All assignments:', allAssignments);
        const flatAssignments = allAssignments.flat();
        console.log('Flat assignments:', flatAssignments);
        
        if (flatAssignments.length === 0) {
            document.getElementById('assignmentsList').innerHTML = '<tr><td colspan="5" class="text-center text-muted">📚 No assignments found</td></tr>';
            return;
        }
        
        const pendingAssignments = flatAssignments
            .filter(a => !a.submission_status || a.submission_status === 'pending')
            .sort((a, b) => new Date(a.due_date) - new Date(b.due_date))
            .slice(0, 5);
            
        console.log('Pending assignments:', pendingAssignments);
        
        if (pendingAssignments.length > 0) {
            displayPendingAssignments(pendingAssignments);
        } else {
            document.getElementById('assignmentsList').innerHTML = '<tr><td colspan="5" class="text-center" style="color: #10b981;">✅ All assignments completed!</td></tr>';
        }
    })
    .catch(error => {
        console.error('Error loading assignments:', error);
        document.getElementById('assignmentsList').innerHTML = '<tr><td colspan="5" class="text-center text-danger">⚠️ Error loading assignments. Check console for details.</td></tr>';
    });
}

function displayPendingAssignments(assignments) {
    const tbody = document.getElementById('assignmentsList');
    
    if (!assignments || assignments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No pending assignments</td></tr>';
        return;
    }
    
    let html = '';
    assignments.forEach(assignment => {
        const statusClass = assignment.is_overdue ? 'danger' : (assignment.is_upcoming ? 'warning' : 'info');
        const statusText = assignment.is_overdue ? 'Overdue' : (assignment.is_upcoming ? 'Due Soon' : 'Pending');
        const statusColor = assignment.is_overdue ? '#ef4444' : (assignment.is_upcoming ? '#f59e0b' : '#3b82f6');
        
        html += `
            <tr>
                <td>${assignment.student_name || 'Student'}</td>
                <td><strong>${assignment.title || assignment.assignment_title || 'Assignment'}</strong></td>
                <td>${assignment.subject || assignment.subject_name || 'N/A'}</td>
                <td>${formatDate(assignment.due_date)}</td>
                <td><span class="badge" style="background: ${statusColor}; color: white; padding: 5px 12px; border-radius: 12px;">${statusText}</span></td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function getAnnouncementIcon(priority) {
    switch(priority) {
        case 'high': return 'bx-error-circle text-danger';
        case 'medium': return 'bx-info-circle text-warning';
        default: return 'bx-info-circle text-info';
    }
}

function getTimeAgo(datetime) {
    const date = new Date(datetime);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'Just now';
    if (seconds < 3600) return Math.floor(seconds / 60) + ' min ago';
    if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
    if (seconds < 604800) return Math.floor(seconds / 86400) + ' days ago';
    return date.toLocaleDateString();
}

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function viewStudentGrades(studentId) {
    window.location.href = `children.php?student_id=${studentId}#grades`;
}

// Load upcoming events from calendar/notices
function loadUpcomingEvents() {
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_upcoming_events'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.events && data.events.length > 0) {
            displayEvents(data.events.slice(0, 5));
        } else {
            document.getElementById('eventsList').innerHTML = `
                <li class="completed">
                    <div class="task-title">
                        <i class='bx bx-calendar' style="color: #10b981;"></i>
                        <p>No upcoming events</p>
                    </div>
                </li>`;
        }
    })
    .catch(error => {
        console.error('Error loading events:', error);
        document.getElementById('eventsList').innerHTML = `
            <li class="not-completed">
                <div class="task-title">
                    <i class='bx bx-calendar' style="color: #999;"></i>
                    <p>No upcoming events</p>
                </div>
            </li>`;
    });
}

function displayEvents(events) {
    const list = document.getElementById('eventsList');
    let html = '';
    
    events.forEach(event => {
        const eventDate = formatDate(event.date || event.event_date);
        html += `
            <li class="not-completed" style="cursor: pointer;">
                <div class="task-title">
                    <i class='bx bx-calendar-event' style="color: #667eea;"></i>
                    <p>${event.title || event.event_name}</p>
                </div>
                <small class="text-muted">${eventDate}</small>
            </li>
        `;
    });
    
    list.innerHTML = html;
}
</script>

<script>
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

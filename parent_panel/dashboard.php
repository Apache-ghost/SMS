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
        <!-- Welcome Banner -->
        <div class="parent-welcome-banner">
            <h2>👨‍👩‍👧 Welcome, <?php echo htmlspecialchars($_SESSION['parent_name'] ?? 'Parent'); ?>!</h2>
            <p>✨ Stay connected with your children's educational journey</p>
        </div>

        <!-- Quick Actions -->
        <div class="parent-quick-actions">
            <a href="children.php" class="parent-action-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="parent-action-icon">👶</div>
                <div class="parent-action-text">
                    <h4>My Children</h4>
                    <p>View student profiles</p>
                </div>
            </a>
            <a href="exams.php" class="parent-action-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="parent-action-icon">📊</div>
                <div class="parent-action-text">
                    <h4>Exam Results</h4>
                    <p>Check performance</p>
                </div>
            </a>
            <a href="messages.php" class="parent-action-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="parent-action-icon">📬</div>
                <div class="parent-action-text">
                    <h4>Messages</h4>
                    <p>School communications</p>
                </div>
            </a>
            <a href="calendar.php" class="parent-action-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="parent-action-icon">🗓️</div>
                <div class="parent-action-text">
                    <h4>Calendar</h4>
                    <p>Events & schedules</p>
                </div>
            </a>
        </div>

        <!-- Achievement Badges -->
        <div class="parent-achievements">
            <h3><i class='bx bx-trophy'></i> Parent Achievements</h3>
            <div class="parent-badges-grid">
                <div class="parent-badge-item earned" title="Active Parent">
                    <div class="parent-badge-icon">🎖️</div>
                    <div class="parent-badge-name">Active</div>
                </div>
                <div class="parent-badge-item" title="Check grades weekly">
                    <div class="parent-badge-icon">📝</div>
                    <div class="parent-badge-name">Engaged</div>
                </div>
                <div class="parent-badge-item" title="Reply to 10 messages">
                    <div class="parent-badge-icon">💬</div>
                    <div class="parent-badge-name">Communicator</div>
                </div>
                <div class="parent-badge-item" title="Attend all meetings">
                    <div class="parent-badge-icon">👥</div>
                    <div class="parent-badge-name">Supportive</div>
                </div>
                <div class="parent-badge-item" title="Log in 30 days">
                    <div class="parent-badge-icon">⭐</div>
                    <div class="parent-badge-name">Dedicated</div>
                </div>
            </div>
        </div>

        <!-- Monitoring Timer & Child Progress -->
        <div class="parent-dashboard-grid">
            <div class="parent-timer-card">
                <div class="parent-card-header">
                    <h3><i class='bx bx-timer'></i> Monitoring Time</h3>
                </div>
                <div class="parent-timer-circle">
                    <div class="parent-timer-text" id="parentTimerDisplay">00:00</div>
                </div>
                <div class="parent-timer-controls">
                    <button class="parent-timer-btn parent-start-btn" id="parentStartBtn" onclick="startParentTimer()">
                        <i class='bx bx-play'></i> Start
                    </button>
                    <button class="parent-timer-btn parent-pause-btn" id="parentPauseBtn" onclick="pauseParentTimer()" style="display:none;">
                        <i class='bx bx-pause'></i> Pause
                    </button>
                    <button class="parent-timer-btn parent-reset-btn" onclick="resetParentTimer()">
                        <i class='bx bx-reset'></i> Reset
                    </button>
                </div>
                <div style="text-align: center; color: #777;">
                    <small>Today: <strong id="parentTodayTime">0h 0m</strong></small>
                </div>
            </div>

            <div class="parent-dashboard-card">
                <div class="parent-card-header">
                    <h3><i class='bx bx-bar-chart-alt'></i> Children Progress</h3>
                </div>
                <div id="childrenProgressSummary">
                    <div class="child-progress-item">
                        <div style="font-weight: 600; color: #333; margin-bottom: 3px;">Loading progress...</div>
                        <div style="color: #777; font-size: 0.85rem;"><i class='bx bx-loader-alt bx-spin'></i> Please wait</div>
                    </div>
                </div>
            </div>
        </div>

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

// Parent Timer Variables
let parentTimerInterval;
let parentTimerSeconds = 0;
let parentIsRunning = false;

function startParentTimer() {
    if (!parentIsRunning) {
        parentIsRunning = true;
        document.getElementById('parentStartBtn').style.display = 'none';
        document.getElementById('parentPauseBtn').style.display = 'inline-block';
        
        parentTimerInterval = setInterval(() => {
            parentTimerSeconds++;
            updateParentTimerDisplay();
            saveParentMonitoringTime();
        }, 1000);
    }
}

function pauseParentTimer() {
    parentIsRunning = false;
    clearInterval(parentTimerInterval);
    document.getElementById('parentStartBtn').style.display = 'inline-block';
    document.getElementById('parentPauseBtn').style.display = 'none';
}

function resetParentTimer() {
    pauseParentTimer();
    parentTimerSeconds = 0;
    updateParentTimerDisplay();
}

function updateParentTimerDisplay() {
    const minutes = Math.floor(parentTimerSeconds / 60);
    const seconds = parentTimerSeconds % 60;
    document.getElementById('parentTimerDisplay').textContent = 
        `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

function saveParentMonitoringTime() {
    const today = new Date().toDateString();
    let monitorData = JSON.parse(localStorage.getItem('parentMonitoringTime') || '{}');
    
    if (!monitorData[today]) {
        monitorData[today] = 0;
    }
    monitorData[today]++;
    
    localStorage.setItem('parentMonitoringTime', JSON.stringify(monitorData));
    updateParentMonitoringStats();
}

function updateParentMonitoringStats() {
    const today = new Date().toDateString();
    let monitorData = JSON.parse(localStorage.getItem('parentMonitoringTime') || '{}');
    
    const todaySeconds = monitorData[today] || 0;
    const todayHours = Math.floor(todaySeconds / 3600);
    const todayMinutes = Math.floor((todaySeconds % 3600) / 60);
    document.getElementById('parentTodayTime').textContent = `${todayHours}h ${todayMinutes}m`;
}

// Create Confetti Effect
function createParentConfetti() {
    const colors = ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#00f2fe', '#43e97b'];
    for (let i = 0; i < 50; i++) {
        setTimeout(() => {
            const confetti = document.createElement('div');
            confetti.className = 'parent-confetti';
            confetti.style.left = Math.random() * window.innerWidth + 'px';
            confetti.style.top = '-10px';
            confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.animationDelay = Math.random() * 2 + 's';
            document.body.appendChild(confetti);
            
            setTimeout(() => confetti.remove(), 3000);
        }, i * 30);
    }
}

// Particle Effect on Click
document.addEventListener('click', (e) => {
    const particle = document.createElement('div');
    particle.style.cssText = `
        position: fixed;
        width: 10px;
        height: 10px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 50%;
        pointer-events: none;
        left: ${e.clientX}px;
        top: ${e.clientY}px;
        animation: particle-burst 0.6s ease-out forwards;
        z-index: 9999;
    `;
    document.body.appendChild(particle);
    setTimeout(() => particle.remove(), 600);
});

// Add particle burst animation
const parentStyle = document.createElement('style');
parentStyle.textContent = `
    @keyframes particle-burst {
        0% {
            transform: scale(1) translate(0, 0);
            opacity: 1;
        }
        100% {
            transform: scale(0) translate(${Math.random() * 100 - 50}px, ${Math.random() * 100 - 50}px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(parentStyle);

// Load Children Progress Summary
function loadChildrenProgress() {
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_children'
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('childrenProgressSummary');
        
        if (data.status === 'success' && data.children && data.children.length > 0) {
            let html = '';
            data.children.forEach(child => {
                const attendancePercent = child.attendance_percentage || 0;
                const gradeColor = child.latest_grade && child.latest_grade.includes('A') ? '#10b981' :
                                  child.latest_grade && child.latest_grade.includes('B') ? '#3b82f6' :
                                  child.latest_grade && child.latest_grade.includes('C') ? '#f59e0b' : '#6b7280';
                
                html += `
                    <div class="child-progress-item">
                        <div style="font-weight: 600; color: #333; margin-bottom: 5px;">${child.student_name || child.fname || 'Student'}</div>
                        <div style="display: flex; gap: 15px; color: #777; font-size: 0.85rem;">
                            <span><i class='bx bx-check-circle'></i> Attendance: <strong>${attendancePercent}%</strong></span>
                            ${child.latest_grade ? `<span style="color: ${gradeColor};"><i class='bx bx-trophy'></i> Grade: <strong>${child.latest_grade}</strong></span>` : ''}
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            container.innerHTML = `
                <div class="child-progress-item">
                    <div style="color: #777;">No children data available</div>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading children progress:', error);
    });
}

// Load Parent Achievements
function loadParentAchievements() {
    // Check children count for Engaged badge
    const childrenCountEl = document.getElementById('childrenCount');
    if (childrenCountEl) {
        const checkCount = setInterval(() => {
            const count = parseInt(childrenCountEl.textContent);
            if (!isNaN(count) && count > 0) {
                if (count >= 2) {
                    unlockParentBadge(1); // Engaged badge
                }
                clearInterval(checkCount);
            }
        }, 1000);
    }

    // Check unread messages for Communicator badge
    const messagesEl = document.getElementById('unreadMessages');
    if (messagesEl) {
        const checkMessages = setInterval(() => {
            const count = parseInt(messagesEl.textContent);
            if (!isNaN(count)) {
                if (count === 0) {
                    unlockParentBadge(2); // Communicator badge (all messages read)
                }
                clearInterval(checkMessages);
            }
        }, 1000);
    }
}

function unlockParentBadge(index) {
    const badges = document.querySelectorAll('.parent-badge-item');
    if (badges[index] && !badges[index].classList.contains('earned')) {
        badges[index].classList.add('earned');
        
        // Celebration effect
        setTimeout(() => {
            createParentConfetti();
        }, 100);
    }
}

// Add hover effects to insight cards
document.querySelectorAll('.insights li').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-10px) scale(1.03)';
        this.style.transition = 'all 0.3s ease';
    });
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
});

// Enhanced initialization
window.addEventListener('load', () => {
    setTimeout(() => {
        createParentConfetti();
    }, 500);
    
    updateParentMonitoringStats();
    loadChildrenProgress();
    loadParentAchievements();
});

console.log('🎉 Parent Dashboard Enhanced! Monitor your children\'s progress with amazing features!');
</script>

<?php include('partials/_footer.php') ?>

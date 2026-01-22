<?php include('partials/_header.php') ?>

<!-- Sidebar -->
<?php include('partials/_sidebar.php') ?>
<input type="hidden" value="1" id="checkFileName">
<!-- End of Sidebar -->



<div class="modal fade" id="reminder-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Add Reminder</h1>
                <button type="button" class="close mr-2" data-bs-dismiss="modal" aria-label="Close"><i
                        class='bx bx-x'></i></button>
            </div>
            <div class="modal-body">

                <div class="container mr-3 ml-3">
                    <div class="alert alert-warning reminder-error" role="alert" style="min-height: 50px;display: none;">
                    Message can't be empty!
                    </div>
                    <div class="mb-3">
                        <!-- <label for="exampleFormControlTextarea1" class="form-label">Example textarea</label> -->
                        <textarea class="form-control" id="reminder-msg" rows="3"></textarea>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary text-center _flex-container" onclick="addReminder()"> <i
                        class='bx bx-plus'></i>&nbsp;<strong>ADD</strong></button>
            </div>
        </div>
    </div>
</div>


<!-- Main Content -->
<div class="content">
    <!-- Navbar -->
    <?php include("partials/_navbar.php"); ?>
    <!-- End of Navbar -->

    <main>
        <!-- Welcome Banner -->
        <div class="admin-welcome-banner">
            <h2>👋 Welcome Back, Admin!</h2>
            <p>✨ Here's what's happening with your school today</p>
        </div>

        <!-- Quick Actions -->
        <div class="admin-quick-actions">
            <a href="student_management.php" class="admin-action-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="admin-action-icon">👥</div>
                <div class="admin-action-text">
                    <h4>Manage Students</h4>
                    <p>Add or edit students</p>
                </div>
            </a>
            <a href="teacher.php" class="admin-action-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="admin-action-icon">👨‍🏫</div>
                <div class="admin-action-text">
                    <h4>Manage Teachers</h4>
                    <p>View teacher list</p>
                </div>
            </a>
            <a href="exams.php" class="admin-action-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="admin-action-icon">📝</div>
                <div class="admin-action-text">
                    <h4>Exams</h4>
                    <p>Manage examinations</p>
                </div>
            </a>
            <a href="announcements.php" class="admin-action-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="admin-action-icon">📢</div>
                <div class="admin-action-text">
                    <h4>Announcements</h4>
                    <p>Post updates</p>
                </div>
            </a>
        </div>

        <!-- Achievement Badges -->
        <div class="admin-achievements">
            <h3><i class='bx bx-trophy'></i> Admin Achievements</h3>
            <div class="admin-badges-grid">
                <div class="admin-badge-item earned" title="System Master">
                    <div class="admin-badge-icon">👑</div>
                    <div class="admin-badge-name">Master</div>
                </div>
                <div class="admin-badge-item" title="100+ Students managed">
                    <div class="admin-badge-icon">🎓</div>
                    <div class="admin-badge-name">Manager</div>
                </div>
                <div class="admin-badge-item" title="50+ Exams created">
                    <div class="admin-badge-icon">📊</div>
                    <div class="admin-badge-name">Organizer</div>
                </div>
                <div class="admin-badge-item" title="100+ Announcements">
                    <div class="admin-badge-icon">📣</div>
                    <div class="admin-badge-name">Communicator</div>
                </div>
                <div class="admin-badge-item" title="Active 30 days">
                    <div class="admin-badge-icon">⚡</div>
                    <div class="admin-badge-name">Active</div>
                </div>
            </div>
        </div>

        <!-- Work Timer & Quick Stats -->
        <div class="admin-dashboard-grid">
            <div class="admin-timer-card">
                <div class="admin-card-header">
                    <h3><i class='bx bx-timer'></i> Work Timer</h3>
                </div>
                <div class="admin-timer-circle">
                    <div class="admin-timer-text" id="adminTimerDisplay">00:00</div>
                </div>
                <div class="admin-timer-controls">
                    <button class="admin-timer-btn admin-start-btn" id="adminStartBtn" onclick="startAdminTimer()">
                        <i class='bx bx-play'></i> Start
                    </button>
                    <button class="admin-timer-btn admin-pause-btn" id="adminPauseBtn" onclick="pauseAdminTimer()" style="display:none;">
                        <i class='bx bx-pause'></i> Pause
                    </button>
                    <button class="admin-timer-btn admin-reset-btn" onclick="resetAdminTimer()">
                        <i class='bx bx-reset'></i> Reset
                    </button>
                </div>
                <div style="text-align: center; color: #777;">
                    <small>Today: <strong id="adminTodayTime">0h 0m</strong></small>
                </div>
            </div>

            <div class="admin-dashboard-card">
                <div class="admin-card-header">
                    <h3><i class='bx bx-trending-up'></i> Recent Activity</h3>
                </div>
                <div id="adminRecentActivity">
                    <div class="admin-activity-item">
                        <div style="font-weight: 600; color: #333; margin-bottom: 3px;">System Active</div>
                        <div style="color: #777; font-size: 0.85rem;"><i class='bx bx-time-five'></i> Just now</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="header">
            <div class="left">
                <h1>Dashboard</h1>
                <ul class="breadcrumb">
                    <li><a>
                            Analytics
                        </a></li>

                </ul>
            </div>
            <!-- <a href="#" class="report">
                <i class='bx bxs-file-pdf'></i>
                <span>Worksheet PDF</span>
            </a> -->
        </div>

        <!-- Insights -->
        <ul class="insights">
            <li onclick="showTeacherList()">
                <!-- <i class='bx bx-calendar-check'></i> -->
                <i class='bx bxs-user'></i>
                <span class="info">
                    <h3 class="text-center" id="teacherCount">_ _ _</h3>
                    <p>Teachers</p>
                </span>
            </li>
            <li onclick="showStudentList()">
                <i class='bx bxs-group'></i>
                <span class="info">
                    <h3  class="text-center" id="studentCount">_ _ _</h3>
                    <p>Students</p>
                </span>
            </li>
            <li onclick="showNotesList()">
                <i class='bx bx-book'></i>
                <span class="info">
                    <h3 class="text-center"  id="classCount">_ _ _</h3>
                    <p>Notes</p>
                </span>
            </li>
            <li onclick="showNoticeList()">
                <i class='bx bxs-bookmark'></i>
                <span class="info">
                    <h3 class="text-center"  id="noticeCount">_ _ _</h3>
                    <p>Notices</p>
                </span>
            </li>
        </ul>
        <!-- End of Insights -->

        <div class="bottom-data">
            <div class="orders">
                <div class="header">
                    <i class='bx bx-receipt'></i>
                    <h3 id="text-heading">Latest Notices</h3>
                    <i class='bx bx-filter'></i>
                    <a href="noticeboard.php" > <i class='bx bx-plus icon-hover-circle' id="plusIconNotification" style="font-size: 30px;"></i></a>
                </div>



                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Sender</th>
                        </tr>
                    </thead>
                    <tbody id="noticeTableBody">
                   
                    </tbody>
                </table>
            </div>

            <!-- Reminders -->
            <div class="reminders">
                <div class="header">
                    <i class='bx bx-note'></i>
                    <h3>Remiders</h3>
                    <!-- <i class='bx bx-filter'></i> -->
                    <a data-bs-toggle="modal" data-bs-target="#reminder-modal"> <i style="font-size: 30px;" class='bx bx-plus icon-hover-circle'></i></a>
                </div>
                <ul class="task-list" id="all-reminders">
                    
                </ul>
            </div>
           

            <!-- End of Reminders-->

            
        </div>
 <br>
    </main>
</div>

<script src="../assets/js/dashboard.js"></script>
<?php include("partials/_footer.php");
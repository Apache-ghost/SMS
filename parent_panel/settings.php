<?php include('partials/_header.php') ?>

<!-- Sidebar -->
<?php include('partials/_sidebar.php') ?>

<div class="content">
    <?php include("partials/_navbar.php"); ?>

    <main>
        <div class="header">
            <div class="left">
                <h1>Settings</h1>
                <ul class="breadcrumb">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active">Settings</a></li>
                </ul>
            </div>
        </div>

        <!-- Profile Settings -->
        <div class="bottom-data">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bx-user'></i>
                    <h3>Profile Information</h3>
                </div>
                
                <div class="p-4">
                    <form id="profileForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Full Name</label>
                                <input type="text" class="form-control" id="parentName" 
                                    value="<?php echo htmlspecialchars($_SESSION['parent_name'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Email</label>
                                <input type="email" class="form-control" id="parentEmail"
                                    value="<?php echo htmlspecialchars($_SESSION['parent_email'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class='bx bx-info-circle'></i> To update profile information, please contact school administration.
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="bottom-data mt-3">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bx-lock'></i>
                    <h3>Change Password</h3>
                </div>
                
                <div class="p-4">
                    <form id="passwordForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Current Password</label>
                                <input type="password" class="form-control" id="currentPassword" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>New Password</label>
                                <input type="password" class="form-control" id="newPassword" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Confirm New Password</label>
                                <input type="password" class="form-control" id="confirmPassword" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-check'></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Notification Preferences -->
        <div class="bottom-data mt-3">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bx-bell'></i>
                    <h3>Notification Preferences</h3>
                </div>
                
                <div class="p-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="notifAttendance" checked>
                        <label class="form-check-label">Attendance Alerts</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="notifGrades" checked>
                        <label class="form-check-label">Grade Updates</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="notifAssignments" checked>
                        <label class="form-check-label">New Assignments</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="notifAnnouncements" checked>
                        <label class="form-check-label">School Announcements</label>
                    </div>
                    <button class="btn btn-primary" onclick="saveNotificationSettings()">
                        <i class='bx bx-save'></i> Save Preferences
                    </button>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (newPassword !== confirmPassword) {
        alert('New passwords do not match!');
        return;
    }
    
    if (newPassword.length < 6) {
        alert('Password must be at least 6 characters long');
        return;
    }
    
    const formData = new FormData();
    formData.append('currentPassword', currentPassword);
    formData.append('newPassword', newPassword);
    
    fetch('../assets/changePassword.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Password changed successfully!');
            document.getElementById('passwordForm').reset();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error changing password');
    });
});

function saveNotificationSettings() {
    const settings = {
        attendance: document.getElementById('notifAttendance').checked,
        grades: document.getElementById('notifGrades').checked,
        assignments: document.getElementById('notifAssignments').checked,
        announcements: document.getElementById('notifAnnouncements').checked
    };
    
    // Save to localStorage for now
    localStorage.setItem('notificationSettings', JSON.stringify(settings));
    alert('Notification preferences saved!');
}

// Load saved settings
window.addEventListener('DOMContentLoaded', function() {
    const saved = localStorage.getItem('notificationSettings');
    if (saved) {
        const settings = JSON.parse(saved);
        document.getElementById('notifAttendance').checked = settings.attendance;
        document.getElementById('notifGrades').checked = settings.grades;
        document.getElementById('notifAssignments').checked = settings.assignments;
        document.getElementById('notifAnnouncements').checked = settings.announcements;
    }
});
</script>

<style>.full-width { width: 100%; }</style>

<?php include('partials/_footer.php') ?>

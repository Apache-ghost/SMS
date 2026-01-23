<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management - ERP System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        body {
            background: #f9fafb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            background: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            overflow-y: auto;
        }

        .sidebar h4 {
            color: var(--primary);
            margin-bottom: 30px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar .nav-link {
            color: #6b7280;
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 8px;
            transition: all 0.3s;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            border: none;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: var(--primary);
            color: white;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 15px 30px;
            margin-bottom: 30px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .table-responsive {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f3f4f6;
            border: none;
            padding: 15px;
            font-weight: 600;
            color: #1f2937;
        }

        .table tbody td {
            padding: 15px;
            border-color: #e5e7eb;
            vertical-align: middle;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            border-left: 5px solid var(--primary);
            margin-bottom: 20px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary);
            margin: 10px 0;
        }

        .stat-label {
            color: #6b7280;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--primary);
            border: none;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .modal-header {
            background: var(--primary);
            color: white;
            border: none;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            padding: 10px 15px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .badge-status {
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
        }

        .badge-active {
            background: #dcfce7;
            color: #166534;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
                padding: 15px;
            }

            .main-content {
                margin-left: 200px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h4><i class="fas fa-shield-alt"></i> Admin Panel</h4>
        
        <button class="nav-link active" onclick="switchSection('users')">
            <i class="fas fa-users"></i> Manage Users
        </button>
        
        <button class="nav-link" onclick="switchSection('reports')">
            <i class="fas fa-chart-bar"></i> Reports
        </button>
        
        <button class="nav-link" onclick="switchSection('logs')">
            <i class="fas fa-history"></i> Audit Logs
        </button>
        
        <button class="nav-link" onclick="switchSection('settings')">
            <i class="fas fa-cog"></i> Settings
        </button>

        <hr style="margin: 20px 0;">

        <button class="nav-link" onclick="goBackToDashboard()">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </button>

        <button class="nav-link" onclick="handleLogout()" style="color: var(--danger);">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Navbar -->
        <div class="navbar">
            <h3 style="margin: 0; color: #1f2937;">Admin Management</h3>
            <span id="userInfo" style="color: #6b7280; font-size: 14px;"></span>
        </div>

        <!-- Alert Container -->
        <div id="alertContainer"></div>

        <!-- Users Management Section -->
        <div id="users" class="section active">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 style="color: #1f2937;">User Management</h2>
                <button class="btn btn-primary" onclick="openAddUserModal()">
                    <i class="fas fa-user-plus"></i> Add New User
                </button>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-label">Total Users</div>
                        <div class="stat-value" id="totalUsers">0</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-label">Active Users</div>
                        <div class="stat-value" id="activeUsers">0</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-label">Students</div>
                        <div class="stat-value" id="studentCount">0</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-label">Staff</div>
                        <div class="stat-value" id="staffCount">0</div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTable">
                        <tr><td colspan="7" class="text-center">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Reports Section -->
        <div id="reports" class="section">
            <h2 style="margin-bottom: 30px; color: #1f2937;">System Reports</h2>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Detailed reports and analytics will be available here
            </div>
        </div>

        <!-- Audit Logs Section -->
        <div id="logs" class="section">
            <h2 style="margin-bottom: 30px; color: #1f2937;">Audit Logs</h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Admin</th>
                            <th>Action</th>
                            <th>Entity</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody id="logsTable">
                        <tr><td colspan="4" class="text-center">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Settings Section -->
        <div id="settings" class="section">
            <h2 style="margin-bottom: 30px; color: #1f2937;">System Settings</h2>
            <div class="card" style="max-width: 600px;">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">System Name</label>
                        <input type="text" class="form-control" value="ERP System">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Support Email</label>
                        <input type="email" class="form-control" value="support@erpsystem.com">
                    </div>
                    <button class="btn btn-primary">Save Settings</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        <input type="hidden" id="userId" value="">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" id="fullName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" id="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" id="password" class="form-control" minlength="8" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select id="role" class="form-select" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="staff">Staff</option>
                                <option value="faculty">Faculty</option>
                                <option value="lecturer">Lecturer</option>
                                <option value="student">Student</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submitBtn" onclick="submitUser()">Add User</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = '/SMS/api/admin.php';
        let currentUser = null;

        window.addEventListener('load', async function() {
            await loadUserData();
            await loadUserStats();
            await loadAllUsers();
        });

        async function loadUserData() {
            try {
                const response = await fetch('/SMS/api/auth.php?action=current-user', {
                    credentials: 'include'
                });
                const data = await response.json();
                
                if (data.success && data.user.role === 'admin') {
                    currentUser = data.user;
                    document.getElementById('userInfo').textContent = `${currentUser.full_name} (Admin)`;
                } else {
                    window.location.href = '/SMS/dashboard.php';
                }
            } catch (error) {
                console.error('Error:', error);
                window.location.href = '/SMS/dashboard.php';
            }
        }

        function switchSection(sectionId) {
            const sections = document.querySelectorAll('.section');
            const navLinks = document.querySelectorAll('.sidebar .nav-link');

            sections.forEach(s => s.classList.remove('active'));
            navLinks.forEach(l => l.classList.remove('active'));

            document.getElementById(sectionId).classList.add('active');
            event.target.classList.add('active');

            if (sectionId === 'users') {
                loadUserStats();
                loadAllUsers();
            } else if (sectionId === 'logs') {
                loadAuditLogs();
            }
        }

        async function loadUserStats() {
            try {
                const response = await fetch(`${API_BASE}?action=dashboard-stats`, {
                    credentials: 'include'
                });
                const data = await response.json();

                if (data.success) {
                    document.getElementById('totalUsers').textContent = data.data.total_users;
                    document.getElementById('activeUsers').textContent = data.data.active_users;
                    document.getElementById('studentCount').textContent = data.data.student_count;
                    document.getElementById('staffCount').textContent = data.data.staff_count;
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function loadAllUsers() {
            try {
                const response = await fetch(`${API_BASE}?action=all-users`, {
                    credentials: 'include'
                });
                const data = await response.json();

                if (data.success) {
                    displayUsers(data.data);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function displayUsers(users) {
            const table = document.getElementById('usersTable');
            
            if (!users || users.length === 0) {
                table.innerHTML = '<tr><td colspan="7" class="text-center">No users found</td></tr>';
                return;
            }

            table.innerHTML = users.map(user => `
                <tr>
                    <td>${user.id}</td>
                    <td>${user.full_name}</td>
                    <td>${user.email}</td>
                    <td><span class="badge bg-info">${user.role}</span></td>
                    <td>
                        <span class="badge-status badge-${user.status === 'active' ? 'active' : 'inactive'}">
                            ${user.status}
                        </span>
                    </td>
                    <td>${new Date(user.created_at).toLocaleDateString()}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editUser(${user.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        async function loadAuditLogs() {
            try {
                const response = await fetch(`${API_BASE}?action=activity-log`, {
                    credentials: 'include'
                });
                const data = await response.json();

                if (data.success) {
                    displayLogs(data.data);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function displayLogs(logs) {
            const table = document.getElementById('logsTable');
            
            if (!logs || logs.length === 0) {
                table.innerHTML = '<tr><td colspan="4" class="text-center">No logs found</td></tr>';
                return;
            }

            table.innerHTML = logs.map(log => `
                <tr>
                    <td>${log.admin_name || 'System'}</td>
                    <td>${log.action}</td>
                    <td>${log.entity_type} #${log.entity_id}</td>
                    <td>${new Date(log.created_at).toLocaleString()}</td>
                </tr>
            `).join('');
        }

        function openAddUserModal() {
            isEditing = false;
            document.getElementById('modalTitle').textContent = 'Add New User';
            document.getElementById('submitBtn').textContent = 'Add User';
            document.getElementById('userId').value = '';
            document.getElementById('fullName').value = '';
            document.getElementById('email').value = '';
            document.getElementById('password').value = '';
            document.getElementById('role').value = '';
            const modal = new bootstrap.Modal(document.getElementById('addUserModal'));
            modal.show();
        }

        function submitUser() {
            const userId = document.getElementById('userId').value;
            const fullName = document.getElementById('fullName').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const role = document.getElementById('role').value;

            if (isEditing) {
                if (!fullName || !email || !role) {
                    showAlert('All fields except password are required');
                    return;
                }
                updateUser(userId, fullName, email, role);
            } else {
                if (!fullName || !email || !password || !role) {
                    showAlert('All fields are required');
                    return;
                }
                createUser(fullName, email, password, role);
            }
        }

        async function createUser(fullName, email, password, role) {
            try {
                const response = await fetch(`${API_BASE}?action=create-user`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        full_name: fullName,
                        email: email,
                        password: password,
                        role: role
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('User created successfully', 'success');
                    document.getElementById('addUserForm').reset();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
                    modal.hide();
                    loadAllUsers();
                    loadUserStats();
                } else {
                    showAlert(data.message || 'Failed to create user');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error creating user');
            }
        }

        async function updateUser(userId, fullName, email, role) {
            try {
                const response = await fetch(`${API_BASE}?action=update-user`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        full_name: fullName,
                        email: email,
                        role: role
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('User updated successfully', 'success');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
                    modal.hide();
                    loadAllUsers();
                    loadUserStats();
                } else {
                    showAlert(data.message || 'Failed to update user');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error updating user');
            }
        }

        function editUser(userId) {
            // Fetch user details
            fetch(`${API_BASE}?action=user-details&user_id=${userId}`, {
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const user = data.data;
                    isEditing = true;
                    document.getElementById('modalTitle').textContent = 'Edit User';
                    document.getElementById('submitBtn').textContent = 'Update User';
                    document.getElementById('userId').value = user.id;
                    document.getElementById('fullName').value = user.full_name;
                    document.getElementById('email').value = user.email;
                    document.getElementById('password').value = ''; // Don't populate password
                    document.getElementById('role').value = user.role;
                    const modal = new bootstrap.Modal(document.getElementById('addUserModal'));
                    modal.show();
                } else {
                    showAlert(data.message || 'Failed to load user details');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error loading user details');
            });
        }

        async function deleteUser(userId) {
            if (!confirm('Are you sure you want to delete this user?')) return;

            try {
                const response = await fetch(`${API_BASE}?action=delete-user&user_id=${userId}`, {
                    method: 'DELETE',
                    credentials: 'include'
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('User deleted successfully', 'success');
                    loadAllUsers();
                    loadUserStats();
                } else {
                    showAlert(data.message || 'Failed to delete user');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error deleting user');
            }
        }

        function showAlert(message, type = 'danger') {
            const alertContainer = document.getElementById('alertContainer');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            alertContainer.innerHTML = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }

        function goBackToDashboard() {
            window.location.href = '/SMS/dashboard.php';
        }

        async function handleLogout() {
            try {
                await fetch('/SMS/api/auth.php?action=logout', {
                    method: 'POST',
                    credentials: 'include'
                });
                window.location.href = '/SMS/index.php';
            } catch (error) {
                console.error('Error:', error);
            }
        }
    </script>
</body>
</html>
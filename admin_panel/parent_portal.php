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
                <h1>Parent Portal Management</h1>
                <ul class="breadcrumb">
                    <li><a>Communication / Parent Portal</a></li>
                </ul>
            </div>
            <button class="btn btn-primary" onclick="syncParents()">
                <i class='bx bx-sync'></i> Sync Parents
            </button>
        </div>

        <!-- Portal Stats -->
        <ul class="insights">
            <li>
                <i class='bx bx-group'></i>
                <span class="info">
                    <h3 class="text-center" id="totalParents">0</h3>
                    <p>Total Parents</p>
                </span>
            </li>
            <li>
                <i class='bx bx-check-circle'></i>
                <span class="info">
                    <h3 class="text-center" id="activeAccounts">0</h3>
                    <p>Active Accounts</p>
                </span>
            </li>
            <li>
                <i class='bx bx-log-in'></i>
                <span class="info">
                    <h3 class="text-center" id="loginsToday">0</h3>
                    <p>Logins Today</p>
                </span>
            </li>
            <li>
                <i class='bx bx-message'></i>
                <span class="info">
                    <h3 class="text-center" id="totalMessages">0</h3>
                    <p>Messages</p>
                </span>
            </li>
        </ul>

        <!-- Parent List -->
        <div class="bottom-data">
            <div class="orders" style="width: 100%;">
                <div class="header">
                    <i class='bx bx-list-ul'></i>
                    <h3>Parent Portal Accounts</h3>
                    <input type="text" id="searchParents" placeholder="Search parents..." class="form-control" style="max-width: 300px;" onkeyup="searchParents()">
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Parent Name</th>
                            <th>Email</th>
                            <th>Children</th>
                            <th>Last Login</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="parentsList">
                        <tr>
                            <td colspan="6" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadParents();
    loadStats();
});

function loadStats() {
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_portal_stats'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('totalParents').textContent = data.stats.total_parents || 0;
            document.getElementById('activeAccounts').textContent = data.stats.active_accounts || 0;
            document.getElementById('loginsToday').textContent = data.stats.logins_today || 0;
            document.getElementById('totalMessages').textContent = data.stats.total_messages || 0;
        }
    })
    .catch(error => console.error('Error loading stats:', error));
}

function loadParents() {
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_all_parents'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text(); // First get as text to see what we receive
    })
    .then(text => {
        console.log('Response:', text); // Debug log
        const data = JSON.parse(text); // Then parse
        const tbody = document.getElementById('parentsList');
        
        if (data.status === 'success' && data.parents && data.parents.length > 0) {
            let html = '';
            
            data.parents.forEach(parent => {
                const statusBadge = parent.is_active == 1 ? 
                    '<span class="badge bg-success">Active</span>' : 
                    '<span class="badge bg-danger">Inactive</span>';
                
                const lastLogin = parent.last_login ? 
                    new Date(parent.last_login).toLocaleDateString() : 
                    'Never';
                
                html += `
                    <tr>
                        <td>${parent.fname} ${parent.lname}</td>
                        <td>${parent.email || 'N/A'}</td>
                        <td>${parent.children_count || 0} child(ren)</td>
                        <td>${lastLogin}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="viewParent('${parent.guardian_id}')">
                                <i class='bx bx-show'></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="resetPassword('${parent.guardian_id}')">
                                <i class='bx bx-key'></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
        } else if (data.message) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-warning">${data.message}<br><button class="btn btn-sm btn-primary mt-2" onclick="syncParents()">Sync Now</button></td></tr>`;
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No parents found</td></tr>';
        }
    })
    .catch(error => {
        console.error('Error loading parents:', error);
        document.getElementById('parentsList').innerHTML = 
            '<tr><td colspan="6" class="text-center text-danger">Error loading parents. Check console for details.</td></tr>';
    });
}

function syncParents() {
    if (!confirm('Sync all parent accounts? This will update parent records from student data.')) {
        return;
    }
    
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Syncing...';
    
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=sync_parents'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('✅ Parents synced successfully!');
            loadParents();
            loadStats();
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Error syncing parents: ' + error);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bx bx-sync"></i> Sync Parents';
    });
}

function viewParent(guardianId) {
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=get_parent_details&guardian_id=${guardianId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            let info = `Parent: ${data.parent.fname} ${data.parent.lname}\n`;
            info += `Email: ${data.parent.email || 'N/A'}\n`;
            info += `Phone: ${data.parent.phone || 'N/A'}\n\n`;
            info += `Children:\n`;
            
            if (data.children && data.children.length > 0) {
                data.children.forEach(child => {
                    info += `- ${child.name} (Class ${child.class})\n`;
                });
            } else {
                info += 'No children linked';
            }
            
            alert(info);
        } else {
            alert('Error loading parent details');
        }
    });
}

function resetPassword(guardianId) {
    if (!confirm('Reset password for this parent? A new password will be generated.')) {
        return;
    }
    
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=reset_parent_password&guardian_id=${guardianId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('✅ Password reset successfully!\nNew Password: ' + data.new_password);
        } else {
            alert('❌ Error: ' + data.message);
        }
    });
}

function searchParents() {
    const searchTerm = document.getElementById('searchParents').value.toLowerCase();
    const rows = document.querySelectorAll('#parentsList tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}
</script>

<?php include('partials/_footer.php') ?>

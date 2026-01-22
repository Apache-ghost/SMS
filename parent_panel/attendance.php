<?php include('partials/_header.php') ?>

<!-- Sidebar -->
<?php include('partials/_sidebar.php') ?>

<div class="content">
    <?php include("partials/_navbar.php"); ?>

    <main>
        <div class="header">
            <div class="left">
                <h1>Attendance</h1>
                <ul class="breadcrumb">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active">Attendance</a></li>
                </ul>
            </div>
        </div>

        <div class="bottom-data">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bx-user'></i>
                    <h3>Select Child</h3>
                </div>
                <div class="p-3">
                    <select class="form-select" id="childSelect" onchange="loadAttendance()">
                        <option value="">Loading children...</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bottom-data mt-3">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bx-list-check'></i>
                    <h3>Attendance Records</h3>
                </div>
                
                <div id="attendanceContent" class="p-3">
                    <p class="text-center text-muted">Select a child to view attendance</p>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
let myChildren = [];

document.addEventListener('DOMContentLoaded', function() {
    loadChildren();
});

function loadChildren() {
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_children'
    })
    .then(response => response.json())
    .then(data => {
        const select = document.getElementById('childSelect');
        if (data.status === 'success' && data.children.length > 0) {
            myChildren = data.children; // Store children list
            let html = '<option value="">-- Select Child --</option>';
            data.children.forEach(child => {
                html += `<option value="${child.id}">${child.name} (Class ${child.class})</option>`;
            });
            select.innerHTML = html;
            
            // Check URL parameter AFTER loading children
            const urlParams = new URLSearchParams(window.location.search);
            const studentId = urlParams.get('student_id');
            if (studentId) {
                // Verify this student belongs to parent
                const isMyChild = myChildren.some(child => child.id === studentId);
                if (isMyChild) {
                    select.value = studentId;
                    loadAttendance();
                } else {
                    alert('⚠️ You do not have access to this student\'s data');
                    window.location.href = 'attendance.php';
                }
            }
        } else {
            select.innerHTML = '<option value="">No children found</option>';
        }
    });
}

function loadAttendance() {
    const studentId = document.getElementById('childSelect').value;
    const content = document.getElementById('attendanceContent');
    
    if (!studentId) {
        content.innerHTML = '<p class="text-center text-muted">Select a child to view attendance</p>';
        return;
    }
    
    content.innerHTML = `<div class="text-center"><div class="spinner-border text-primary"></div><p class="mt-2">Loading attendance...</p></div>`;
    
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=get_student_attendance&student_id=${studentId}`
    })
    .then(response => response.json())
    .then(data => {
        console.log('Attendance data:', data);
        if (data.status === 'success' && data.attendance && data.attendance.length > 0) {
            displayAttendance(data.attendance);
        } else {
            content.innerHTML = `
                <div class="alert alert-info text-center">
                    <i class='bx bx-calendar-check' style='font-size: 60px;'></i>
                    <p class="mt-3">No attendance records found</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading attendance:', error);
        content.innerHTML = `<div class="alert alert-danger"><i class='bx bx-error'></i> Error loading attendance</div>`;
    });
}

function displayAttendance(attendanceData) {
    const content = document.getElementById('attendanceContent');
    
    let present = 0, absent = 0;
    attendanceData.forEach(record => {
        if (record.attendence === 'present' || record.status === 'present') present++;
        else absent++;
    });
    
    const total = attendanceData.length;
    const percentage = total > 0 ? ((present / total) * 100).toFixed(2) : 0;
    const percentageColor = percentage >= 75 ? '#10b981' : percentage >= 50 ? '#f59e0b' : '#ef4444';
    
    let html = `
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center" style="border-left: 4px solid #10b981;">
                    <div class="card-body">
                        <h3 style="color: #10b981;">${present}</h3>
                        <p class="text-muted mb-0">Present</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center" style="border-left: 4px solid #ef4444;">
                    <div class="card-body">
                        <h3 style="color: #ef4444;">${absent}</h3>
                        <p class="text-muted mb-0">Absent</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center" style="border-left: 4px solid #3b82f6;">
                    <div class="card-body">
                        <h3 style="color: #3b82f6;">${total}</h3>
                        <p class="text-muted mb-0">Total Days</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center" style="border-left: 4px solid ${percentageColor};">
                    <div class="card-body">
                        <h3 style="color: ${percentageColor};">${percentage}%</h3>
                        <p class="text-muted mb-0">Attendance</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    html += '<div class="table-responsive"><table class="table table-striped">';
    html += `
        <thead style="background: #667eea; color: white;">
            <tr>
                <th>Date</th>
                <th>Day</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
    `;
    
    attendanceData.forEach(record => {
        const status = record.attendence || record.status;
        const statusColor = status === 'present' ? '#10b981' : '#ef4444';
        const statusIcon = status === 'present' ? 'bx-check-circle' : 'bx-x-circle';
        const date = new Date(record.date);
        const dayName = date.toLocaleDateString('en-US', { weekday: 'long' });
        
        html += `
            <tr>
                <td>${formatDate(record.date)}</td>
                <td>${dayName}</td>
                <td>
                    <span style="background: ${statusColor}; color: white; padding: 5px 12px; border-radius: 12px;">
                        <i class='bx ${statusIcon}'></i> ${status.toUpperCase()}
                    </span>
                </td>
                <td>${record.remarks || '-'}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    content.innerHTML = html;
}

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}
            
            let html = `
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-center bg-success text-white">
                            <div class="card-body">
                                <h3>${data.filter(d => d.status === 'present').length}</h3>
                                <p>Present Days</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center bg-danger text-white">
                            <div class="card-body">
                                <h3>${data.filter(d => d.status === 'absent').length}</h3>
                                <p>Absent Days</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center bg-primary text-white">
                            <div class="card-body">
                                <h3>${((data.filter(d => d.status === 'present').length / total) * 100).toFixed(1)}%</h3>
                                <p>Attendance Rate</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.forEach(record => {
                const statusClass = record.status === 'present' ? 'success' : 'danger';
                html += `
                    <tr>
                        <td>${record.date}</td>
                        <td><span class="badge bg-${statusClass}">${record.status}</span></td>
                        <td>${record.remarks || '-'}</td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
            content.innerHTML = html;
        } else {
            content.innerHTML = `<div class="text-center text-muted"><i class='bx bx-calendar-x' style='font-size: 60px;'></i><p class="mt-2">No attendance records found</p></div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = '<p class="text-center text-danger">Error loading attendance</p>';
    });
}
</script>

<style>.full-width { width: 100%; }</style>

<?php include('partials/_footer.php') ?>

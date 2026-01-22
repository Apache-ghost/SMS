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
        const status = (record.attendence || record.status || '').toLowerCase();
        if (status === 'present' || status === 'p') present++;
        else absent++;
    });
    
    const total = attendanceData.length;
    const percentage = total > 0 ? ((present / total) * 100).toFixed(2) : 0;
    const percentageClass = percentage >= 90 ? 'success' : 
                           percentage >= 75 ? 'primary' : 
                           percentage >= 60 ? 'warning' : 'danger';
    
    let html = `
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center" style="border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
                    <div class="card-body p-4">
                        <i class='bx bx-check-circle' style='font-size: 40px; margin-bottom: 10px;'></i>
                        <h2 style="font-weight: 700; margin: 10px 0;">${present}</h2>
                        <p class="mb-0" style="opacity: 0.9;">Present Days</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center" style="border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white;">
                    <div class="card-body p-4">
                        <i class='bx bx-x-circle' style='font-size: 40px; margin-bottom: 10px;'></i>
                        <h2 style="font-weight: 700; margin: 10px 0;">${absent}</h2>
                        <p class="mb-0" style="opacity: 0.9;">Absent Days</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center" style="border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <div class="card-body p-4">
                        <i class='bx bx-calendar' style='font-size: 40px; margin-bottom: 10px;'></i>
                        <h2 style="font-weight: 700; margin: 10px 0;">${total}</h2>
                        <p class="mb-0" style="opacity: 0.9;">Total Days</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center" style="border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                    <div class="card-body p-4">
                        <i class='bx bx-line-chart' style='font-size: 40px; margin-bottom: 10px;'></i>
                        <h2 style="font-weight: 700; margin: 10px 0;">${percentage}%</h2>
                        <p class="mb-0" style="opacity: 0.9;">Attendance Rate</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filter by Month -->
        <div class="mb-3">
            <select class="form-select" id="monthFilter" onchange="filterByMonth()" style="max-width: 250px; border-radius: 10px;">
                <option value="">All Months</option>
            </select>
        </div>
    `;
    
    html += `
        <div class="table-responsive">
            <table class="table table-hover" id="attendanceTable">
                <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <tr>
                        <th style="border: none;">Date</th>
                        <th style="border: none;">Day</th>
                        <th style="border: none;">Status</th>
                        <th style="border: none;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    // Store all attendance records for filtering
    window.allAttendanceRecords = attendanceData;
    
    // Get unique months for filter
    const months = new Set();
    attendanceData.forEach(record => {
        const date = new Date(record.date);
        const monthYear = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
        months.add(monthYear);
    });
    
    attendanceData.forEach(record => {
        const status = (record.attendence || record.status || '').toLowerCase();
        const statusDisplay = status === 'present' || status === 'p' ? 'Present' : 'Absent';
        const statusColor = statusDisplay === 'Present' ? 'success' : 'danger';
        const statusIcon = statusDisplay === 'Present' ? 'bx-check-circle' : 'bx-x-circle';
        const date = new Date(record.date);
        const dayName = date.toLocaleDateString('en-US', { weekday: 'long' });
        
        html += `
            <tr data-month="${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}">
                <td><strong>${formatDate(record.date)}</strong></td>
                <td>${dayName}</td>
                <td>
                    <span class="badge bg-${statusColor}" style="font-size: 14px; padding: 8px 15px;">
                        <i class='bx ${statusIcon}'></i> ${statusDisplay}
                    </span>
                </td>
                <td>${record.remarks || '-'}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    content.innerHTML = html;
    
    // Populate month filter
    const monthFilter = document.getElementById('monthFilter');
    const sortedMonths = Array.from(months).sort().reverse();
    sortedMonths.forEach(month => {
        const [year, monthNum] = month.split('-');
        const monthName = new Date(year, monthNum - 1).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        monthFilter.innerHTML += `<option value="${month}">${monthName}</option>`;
    });
}

function filterByMonth() {
    const selectedMonth = document.getElementById('monthFilter').value;
    const rows = document.querySelectorAll('#attendanceTable tbody tr');
    
    rows.forEach(row => {
        if (!selectedMonth || row.dataset.month === selectedMonth) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
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

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
document.addEventListener('DOMContentLoaded', function() {
    loadChildren();
    const urlParams = new URLSearchParams(window.location.search);
    const studentId = urlParams.get('student_id');
    if (studentId) {
        setTimeout(() => {
            document.getElementById('childSelect').value = studentId;
            loadAttendance();
        }, 500);
    }
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
            let html = '<option value="">-- Select Child --</option>';
            data.children.forEach(child => {
                html += `<option value="${child.id}">${child.name} (Class ${child.class})</option>`;
            });
            select.innerHTML = html;
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
    
    fetch('../assets/fetchFullAttendence.php?student_id=' + studentId)
    .then(response => response.json())
    .then(data => {
        if (data && data.length > 0) {
            let present = 0, absent = 0, total = data.length;
            
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

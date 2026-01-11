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
                <h1>My Children</h1>
                <ul class="breadcrumb">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active">My Children</a></li>
                </ul>
            </div>
        </div>

        <!-- Children Cards -->
        <div class="bottom-data">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bxs-user-detail'></i>
                    <h3>Children Details</h3>
                </div>
                
                <div id="childrenCards" class="row mt-3">
                    <!-- Loading -->
                    <div class="col-12 text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading children information...</p>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
// Load children data
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
        const container = document.getElementById('childrenCards');
        
        if (data.status === 'success' && data.children.length > 0) {
            let html = '';
            
            data.children.forEach(child => {
                html += `
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-circle me-3">
                                    <i class='bx bxs-user-circle' style='font-size: 60px; color: #667eea;'></i>
                                </div>
                                <div>
                                    <h4 class="mb-1">${child.name}</h4>
                                    <p class="text-muted mb-0">Class: ${child.class} - Section: ${child.section}</p>
                                    <p class="text-muted mb-0">Roll No: ${child.id}</p>
                                </div>
                            </div>
                            
                            <div class="row text-center mt-3">
                                <div class="col-6 border-end">
                                    <h5 class="text-success">${child.attendance || '0'}%</h5>
                                    <small class="text-muted">Attendance</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="text-primary">${child.latest_grade || 'N/A'}</h5>
                                    <small class="text-muted">Latest Grade</small>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex gap-2 mt-3">
                                <button class="btn btn-sm btn-primary flex-fill" onclick="viewGrades('${child.id}')">
                                    <i class='bx bx-file'></i> Grades
                                </button>
                                <button class="btn btn-sm btn-info flex-fill" onclick="viewAttendance('${child.id}')">
                                    <i class='bx bx-list-check'></i> Attendance
                                </button>
                                <button class="btn btn-sm btn-warning flex-fill" onclick="viewTimeTable('${child.class}', '${child.section}')">
                                    <i class='bx bx-table'></i> Timetable
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                `;
            });
            
            container.innerHTML = html;
        } else {
            container.innerHTML = `
                <div class="col-12 text-center">
                    <i class='bx bx-error-circle' style='font-size: 60px; color: #999;'></i>
                    <p class="mt-3">No children found linked to your account.</p>
                    <p class="text-muted">Please contact school administration.</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('childrenCards').innerHTML = `
            <div class="col-12 text-center">
                <i class='bx bx-error' style='font-size: 60px; color: #dc3545;'></i>
                <p class="mt-3 text-danger">Error loading data</p>
            </div>
        `;
    });
}

function viewGrades(studentId) {
    window.location.href = `grades.php?student_id=${studentId}`;
}

function viewAttendance(studentId) {
    window.location.href = `attendance.php?student_id=${studentId}`;
}

function viewTimeTable(className, section) {
    window.location.href = `timetable.php?class=${className}&section=${section}`;
}
</script>

<style>
.full-width {
    width: 100%;
}

.avatar-circle {
    display: inline-block;
}

.card {
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
}

.btn-sm {
    font-size: 12px;
}
</style>

<?php include('partials/_footer.php') ?>

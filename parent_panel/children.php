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

<!-- Grades Modal -->
<div class="modal fade" id="gradesModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class='bx bx-file'></i> <span id="gradesStudentName"></span> - Grades & Reports
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="gradesModalContent">
                <div class="text-center">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Loading grades...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="downloadReportForStudent()">
                    <i class='bx bx-download'></i> Download Report
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class='bx bx-list-check'></i> <span id="attendanceStudentName"></span> - Attendance Records
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="attendanceModalContent">
                <div class="text-center">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Loading attendance...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
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
                                    <h5 class="text-success">${child.attendance_percentage || '0'}%</h5>
                                    <small class="text-muted">Attendance</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="text-primary">View</h5>
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

let currentStudentId = null;
let currentStudentName = null;

function viewGrades(studentId) {
    // Find student name
    const studentCard = event.target.closest('.card-body');
    const studentName = studentCard.querySelector('h4').textContent;
    
    currentStudentId = studentId;
    currentStudentName = studentName;
    
    document.getElementById('gradesStudentName').textContent = studentName;
    document.getElementById('gradesModalContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Loading grades...</p>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('gradesModal'));
    modal.show();
    
    // Load grades
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=get_student_grades_detailed&student_id=${studentId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.grades && data.grades.length > 0) {
            displayGradesInModal(data.grades);
        } else {
            document.getElementById('gradesModalContent').innerHTML = `
                <div class="alert alert-info text-center">
                    <i class='bx bx-info-circle' style='font-size: 50px;'></i>
                    <p class="mt-3">No grades available yet</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('gradesModalContent').innerHTML = `
            <div class="alert alert-danger">
                <i class='bx bx-error'></i> Error loading grades
            </div>
        `;
    });
}

function displayGradesInModal(grades) {
    let html = `
        <div class="table-responsive">
            <table class="table table-hover">
                <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <tr>
                        <th>Subject</th>
                        <th>Exam</th>
                        <th>Marks</th>
                        <th>Total</th>
                        <th>Percentage</th>
                        <th>Grade</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    grades.forEach(grade => {
        const percentage = ((grade.marks_obtained / grade.total_marks) * 100).toFixed(2);
        const gradeClass = percentage >= 90 ? 'success' : 
                          percentage >= 75 ? 'primary' : 
                          percentage >= 60 ? 'warning' : 'danger';
        
        html += `
            <tr>
                <td><strong>${grade.subject_name}</strong></td>
                <td>${grade.exam_name}</td>
                <td><span class="badge bg-${gradeClass}">${grade.marks_obtained}</span></td>
                <td>${grade.total_marks}</td>
                <td>${percentage}%</td>
                <td><span class="badge bg-${gradeClass}">${grade.grade}</span></td>
                <td>${new Date(grade.exam_date).toLocaleDateString()}</td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    document.getElementById('gradesModalContent').innerHTML = html;
}

function viewAttendance(studentId) {
    // Find student name
    const studentCard = event.target.closest('.card-body');
    const studentName = studentCard.querySelector('h4').textContent;
    
    currentStudentId = studentId;
    currentStudentName = studentName;
    
    document.getElementById('attendanceStudentName').textContent = studentName;
    document.getElementById('attendanceModalContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Loading attendance...</p>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('attendanceModal'));
    modal.show();
    
    // Load attendance
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=get_student_attendance&student_id=${studentId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.attendance && data.attendance.length > 0) {
            displayAttendanceInModal(data.attendance);
        } else {
            document.getElementById('attendanceModalContent').innerHTML = `
                <div class="alert alert-info text-center">
                    <i class='bx bx-info-circle' style='font-size: 50px;'></i>
                    <p class="mt-3">No attendance records found</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('attendanceModalContent').innerHTML = `
            <div class="alert alert-danger">
                <i class='bx bx-error'></i> Error loading attendance
            </div>
        `;
    });
}

function displayAttendanceInModal(attendance) {
    // Calculate statistics
    const total = attendance.length;
    const present = attendance.filter(a => a.status === 'Present' || a.status === 'P').length;
    const absent = attendance.filter(a => a.status === 'Absent' || a.status === 'A').length;
    const percentage = ((present / total) * 100).toFixed(2);
    
    let html = `
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="text-center p-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px;">
                    <h3>${total}</h3>
                    <small>Total Days</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center p-3" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border-radius: 10px;">
                    <h3>${present}</h3>
                    <small>Present</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center p-3" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; border-radius: 10px;">
                    <h3>${absent}</h3>
                    <small>Absent</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center p-3" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border-radius: 10px;">
                    <h3>${percentage}%</h3>
                    <small>Attendance</small>
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    attendance.forEach(record => {
        const date = new Date(record.date);
        const status = record.status === 'Present' || record.status === 'P' ? 'Present' : 'Absent';
        const statusClass = status === 'Present' ? 'success' : 'danger';
        const statusIcon = status === 'Present' ? 'bx-check-circle' : 'bx-x-circle';
        
        html += `
            <tr>
                <td>${date.toLocaleDateString()}</td>
                <td>${date.toLocaleDateString('en-US', { weekday: 'long' })}</td>
                <td>
                    <span class="badge bg-${statusClass}">
                        <i class='bx ${statusIcon}'></i> ${status}
                    </span>
                </td>
                <td>${record.remarks || '-'}</td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    document.getElementById('attendanceModalContent').innerHTML = html;
}

function downloadReportForStudent() {
    if (!currentStudentId) return;
    window.open(`../assets/download_report_card.php?student_id=${currentStudentId}`, '_blank');
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

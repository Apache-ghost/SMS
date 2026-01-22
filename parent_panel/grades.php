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
                <h1>Grades & Reports</h1>
                <ul class="breadcrumb">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active">Grades & Reports</a></li>
                </ul>
            </div>
        </div>

        <!-- Select Child -->
        <div class="bottom-data">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bx-user'></i>
                    <h3>Select Child</h3>
                </div>
                <div class="p-3">
                    <select class="form-select" id="childSelect" onchange="loadGrades()">
                        <option value="">Loading children...</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Grades Table -->
        <div class="bottom-data mt-3">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bx-file'></i>
                    <h3>Grade Reports</h3>
                    <button class="btn btn-sm btn-primary" onclick="downloadReport()">
                        <i class='bx bx-download'></i> Download
                    </button>
                </div>
                
                <div id="gradesContent" class="p-3">
                    <p class="text-center text-muted">Select a child to view grades</p>
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
                    loadGrades();
                } else {
                    alert('⚠️ You do not have access to this student\'s grades');
                    window.location.href = 'grades.php';
                }
            }
        } else {
            select.innerHTML = '<option value="">No children found</option>';
        body: 'action=get_children'
    })
    .then(response => response.json())
    .then(data => {
        const select = document.getElementById('childSelect');
        
        if (data.status === 'success' && data.children.length > 0) {
            children = data.children;
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

function loadGrades() {
    const studentId = document.getElementById('childSelect').value;
    const content = document.getElementById('gradesContent');
    
    if (!studentId) {
        content.innerHTML = '<p class="text-center text-muted">Select a child to view grades</p>';
        return;
    }
    
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Loading grades...</p>
        </div>
    `;
    
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=get_student_grades_detailed&student_id=${studentId}`
    })
    .then(response => response.json())
    .then(data => {
        console.log('Grades data:', data);
        if (data.status === 'success' && data.grades && data.grades.length > 0) {
            displayGrades(data.grades);
        } else {
            content.innerHTML = `
                <div class="alert alert-info text-center">
                    <i class='bx bx-book' style='font-size: 60px;'></i>
                    <p class="mt-3">No grades available yet</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading grades:', error);
        content.innerHTML = `
            <div class="alert alert-danger">
                <i class='bx bx-error'></i> Error loading grades. Please try again.
            </div>
        `;
    });
}

function displayGrades(grades) {
    const content = document.getElementById('gradesContent');
    
    // Group by exam/subject
    let html = '<div class="table-responsive"><table class="table table-striped">';
    html += `
        <thead>
            <tr style="background: #667eea; color: white;">
                <th>Exam</th>
                <th>Subject</th>
                <th>Marks Obtained</th>
                <th>Total Marks</th>
                <th>Percentage</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
    `;
    
    grades.forEach(grade => {
        const statusColor = grade.status === 'Pass' ? '#10b981' : '#ef4444';
        const percentageColor = grade.percentage >= 75 ? '#10b981' : grade.percentage >= 50 ? '#f59e0b' : '#ef4444';
        
        html += `
            <tr>
                <td><strong>${grade.exam_name || 'N/A'}</strong></td>
                <td>${grade.subject_display || grade.subject || 'N/A'}</td>
                <td>${grade.marks_obtained || '0'}</td>
                <td>${grade.total_marks || '0'}</td>
                <td><span style="background: ${percentageColor}; color: white; padding: 5px 10px; border-radius: 12px;">${grade.percentage}%</span></td>
                <td><span style="background: ${statusColor}; color: white; padding: 5px 10px; border-radius: 12px;">${grade.status}</span></td>
                <td>${formatDate(grade.exam_date)}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    
    // Add summary
    const totalPercentage = grades.reduce((sum, g) => sum + parseFloat(g.percentage), 0) / grades.length;
    html += `
        <div class="alert alert-info mt-3">
            <h5>Overall Performance</h5>
            <p><strong>Average:</strong> ${totalPercentage.toFixed(2)}%</p>
            <p><strong>Total Exams:</strong> ${grades.length}</p>
        </div>
    `;
    
    content.innerHTML = html;
}

function downloadReport() {
    const studentId = document.getElementById('childSelect').value;
    if (!studentId) {
        alert('Please select a child first');
        return;
    }
    window.location.href = `../assets/downloadReport.php?student_id=${studentId}&type=grades`;
}

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}
    const studentId = document.getElementById('childSelect').value;
    const content = document.getElementById('gradesContent');
    
    if (!studentId) {
        content.innerHTML = '<p class="text-center text-muted">Select a child to view grades</p>';
        return;
    }
    
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Loading grades...</p>
        </div>
    `;
    
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=get_student_grades&student_id=${studentId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.grades && data.grades.length > 0) {
            displayGrades(data.grades);
        } else {
            content.innerHTML = `
                <div class="alert alert-info text-center">
                    <i class='bx bx-file' style='font-size: 60px;'></i>
                    <p class="mt-2">No grades found for this student</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading grades:', error);
        content.innerHTML = '<div class="alert alert-danger">Error loading grades. Please try again.</div>';
    });
}

function displayGrades(grades) {
    const content = document.getElementById('gradesContent');
    
    // Group grades by exam
    const groupedGrades = {};
    grades.forEach(grade => {
        if (!groupedGrades[grade.exam_name]) {
            groupedGrades[grade.exam_name] = [];
        }
        groupedGrades[grade.exam_name].push(grade);
    });
    
    let html = '';
    
    // Display each exam separately
    Object.keys(groupedGrades).forEach(examName => {
        const examGrades = groupedGrades[examName];
        let totalMarks = 0;
        let totalObtained = 0;
        
        html += `
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class='bx bx-book-open'></i> ${examName}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Subject</th>
                                    <th>Marks Obtained</th>
                                    <th>Total Marks</th>
                                    <th>Percentage</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
        `;
        
        examGrades.forEach(grade => {
            const percentage = grade.percentage || 0;
            const gradeValue = getGrade(percentage);
            const statusClass = grade.status === 'Pass' ? 'success' : 'danger';
            
            totalObtained += parseFloat(grade.marks_obtained || 0);
            totalMarks += parseFloat(grade.total_marks || 0);
            
            html += `
                <tr>
                    <td><strong>${grade.subject}</strong></td>
                    <td>${grade.marks_obtained || 'N/A'}</td>
                    <td>${grade.total_marks}</td>
                    <td>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-${getGradeColor(gradeValue)}" 
                                 style="width: ${percentage}%">${percentage}%</div>
                        </div>
                    </td>
                    <td><span class="badge bg-${statusClass}">${grade.status}</span></td>
                    <td>${grade.remarks || '-'}</td>
                </tr>
            `;
        });
        
        const overallPercentage = totalMarks > 0 ? ((totalObtained / totalMarks) * 100).toFixed(2) : 0;
        const overallGrade = getGrade(overallPercentage);
        
        html += `
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <th>Overall</th>
                                    <th>${totalObtained.toFixed(2)}</th>
                                    <th>${totalMarks.toFixed(2)}</th>
                                    <th colspan="3">
                                        <strong>Percentage: ${overallPercentage}%</strong> | 
                                        <span class="badge bg-${getGradeColor(overallGrade)} ms-2">${overallGrade}</span>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        `;
    });
    
    content.innerHTML = html;
}

function getGrade(percentage) {
    if (percentage >= 90) return 'A+';
    if (percentage >= 80) return 'A';
    if (percentage >= 70) return 'B+';
    if (percentage >= 60) return 'B';
    if (percentage >= 50) return 'C';
    if (percentage >= 40) return 'D';
    return 'F';
}

function getGradeColor(grade) {
    const colors = {
        'A+': 'success', 'A': 'success',
        'B+': 'primary', 'B': 'info',
        'C': 'warning', 'D': 'warning',
        'F': 'danger'
    };
    return colors[grade] || 'secondary';
}

function downloadReport() {
    const studentId = document.getElementById('childSelect').value;
    if (!studentId) {
        alert('Please select a child first');
        return;
    }
    window.location.href = `../assets/downloadMarks.php?student_id=${studentId}`;
}
</script>

<style>
.full-width { width: 100%; }
</style>

<?php include('partials/_footer.php') ?>

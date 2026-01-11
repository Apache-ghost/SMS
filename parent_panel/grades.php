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
let children = [];

document.addEventListener('DOMContentLoaded', function() {
    loadChildren();
    
    // Check if student_id in URL
    const urlParams = new URLSearchParams(window.location.search);
    const studentId = urlParams.get('student_id');
    if (studentId) {
        setTimeout(() => {
            document.getElementById('childSelect').value = studentId;
            loadGrades();
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
    
    fetch('../assets/fetchSubjectiveResults.php?student_id=' + studentId)
    .then(response => response.json())
    .then(data => {
        if (data && data.length > 0) {
            let html = `
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Subject</th>
                                <th>Exam</th>
                                <th>Marks Obtained</th>
                                <th>Total Marks</th>
                                <th>Percentage</th>
                                <th>Grade</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.forEach(grade => {
                const percentage = ((grade.marks_obtained / grade.total_marks) * 100).toFixed(2);
                const gradeValue = getGrade(percentage);
                
                html += `
                    <tr>
                        <td>${grade.subject}</td>
                        <td>${grade.exam}</td>
                        <td>${grade.marks_obtained}</td>
                        <td>${grade.total_marks}</td>
                        <td>${percentage}%</td>
                        <td><span class="badge bg-${getGradeColor(gradeValue)}">${gradeValue}</span></td>
                        <td>${grade.remarks || '-'}</td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
            content.innerHTML = html;
        } else {
            content.innerHTML = `
                <div class="text-center text-muted">
                    <i class='bx bx-file' style='font-size: 60px;'></i>
                    <p class="mt-2">No grades found for this student</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = '<p class="text-center text-danger">Error loading grades</p>';
    });
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

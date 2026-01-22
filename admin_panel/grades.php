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
                <h1>Grades & Report Cards</h1>
                <ul class="breadcrumb">
                    <li><a>Academic / Assessment</a></li>
                </ul>
            </div>
            <button class="btn btn-success" onclick="generateReports()">
                <i class='bx bx-file-blank'></i> Generate Reports
            </button>
        </div>

        <!-- Statistics -->
        <ul class="insights">
            <li>
                <i class='bx bx-trophy'></i>
                <span class="info">
                    <h3 class="text-center" id="totalGrades">0</h3>
                    <p>Total Grades</p>
                </span>
            </li>
            <li>
                <i class='bx bx-check-circle'></i>
                <span class="info">
                    <h3 class="text-center" id="publishedGrades">0</h3>
                    <p>Published</p>
                </span>
            </li>
            <li>
                <i class='bx bx-time'></i>
                <span class="info">
                    <h3 class="text-center" id="pendingGrades">0</h3>
                    <p>Pending</p>
                </span>
            </li>
            <li>
                <i class='bx bx-line-chart'></i>
                <span class="info">
                    <h3 class="text-center" id="avgGrade">0</h3>
                    <p>Average Grade</p>
                </span>
            </li>
        </ul>

        <!-- Grade Entry Form -->
        <div class="bottom-data">
            <div class="orders" style="width: 100%;">
                <div class="header">
                    <i class='bx bx-edit'></i>
                    <h3>Grade Entry</h3>
                </div>
                <div class="row p-3">
                    <div class="col-md-3">
                        <label class="form-label">Class</label>
                        <select class="form-control" id="gradeClass">
                            <option value="">Select Class</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Subject</label>
                        <select class="form-control" id="gradeSubject">
                            <option value="">Select Subject</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Assessment Type</label>
                        <select class="form-control" id="assessmentType">
                            <option value="">Select Type</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary form-control" onclick="loadStudentsForGrading()">Load Students</button>
                    </div>
                </div>
                <div id="gradeEntryTable"></div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadClasses();
    loadSubjects();
    loadAssessmentTypes();
    loadGradeStats();
});

function loadGradeStats() {
    fetch('../assets/manageMarks.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_grade_stats'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('totalGrades').textContent = data.stats.total || 0;
            document.getElementById('publishedGrades').textContent = data.stats.published || 0;
            document.getElementById('pendingGrades').textContent = data.stats.pending || 0;
            document.getElementById('avgGrade').textContent = data.stats.average || '0';
        }
    })
    .catch(error => console.error('Error loading stats:', error));
}

function loadClasses() {
    const select = document.getElementById('gradeClass');
    
    fetch('../assets/fetchClassAndSection.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data && Array.isArray(data) && data.length > 0) {
                let html = '<option value="">Select Class</option>';
                
                const uniqueClasses = [...new Set(data.map(item => item.class))];
                uniqueClasses.forEach(cls => {
                    html += `<option value="${cls}">Class ${cls}</option>`;
                });
                
                select.innerHTML = html;
            } else {
                select.innerHTML = '<option value="">No classes found</option>';
            }
        })
        .catch(error => {
            console.error('Error loading classes:', error);
            select.innerHTML = '<option value="">Error loading classes</option>';
        });
}

function loadSubjects() {
    const select = document.getElementById('gradeSubject');
    
    fetch('../assets/fetchSubjects.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data && Array.isArray(data) && data.length > 0) {
                let html = '<option value="">Select Subject</option>';
                
                data.forEach(subject => {
                    html += `<option value="${subject.subject_code}">${subject.subject_name}</option>`;
                });
                
                select.innerHTML = html;
            } else {
                select.innerHTML = '<option value="">No subjects found</option>';
            }
        })
        .catch(error => {
            console.error('Error loading subjects:', error);
            select.innerHTML = '<option value="">Error loading subjects</option>';
        });
}

function loadAssessmentTypes() {
    const select = document.getElementById('assessmentType');
    
    const types = [
        {value: 'quiz', label: 'Quiz'},
        {value: 'test', label: 'Test'},
        {value: 'midterm', label: 'Mid-Term Exam'},
        {value: 'final', label: 'Final Exam'},
        {value: 'assignment', label: 'Assignment'},
        {value: 'project', label: 'Project'},
        {value: 'practical', label: 'Practical'}
    ];
    
    let html = '<option value="">Select Type</option>';
    types.forEach(type => {
        html += `<option value="${type.value}">${type.label}</option>`;
    });
    
    select.innerHTML = html;
}

function loadStudentsForGrading() {
    const classValue = document.getElementById('gradeClass').value;
    const subject = document.getElementById('gradeSubject').value;
    const assessmentType = document.getElementById('assessmentType').value;
    
    if (!classValue || !subject || !assessmentType) {
        alert('⚠️ Please select class, subject, and assessment type');
        return;
    }{
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Students loaded:', data);
        if (data && Array.isArray(data) && data.length > 0) {
            displayGradeEntryForm(data, classValue, subject, assessmentType);
        } else {
            container.innerHTML = '<div class="alert alert-info m-3">No students found for this class</div>';
        }
    })
    .catch(error => {
        console.error('Error loading students:', error);
        container.innerHTML = '<div class="alert alert-danger m-3">Error loading students. Check console for details.
    .then(data => {
        if (data && data.length > 0) {
            displayGradeEntryForm(data, classValue, subject, assessmentType);
        } else {
            container.innerHTML = '<div class="alert alert-info m-3">No students found</div>';
        }
    })
    .catch(error => {
        console.error('Error loading students:', error);
        container.innerHTML = '<div class="alert alert-danger m-3">Error loading students</div>';
    });
}

function displayGradeEntryForm(students, classValue, subject, assessmentType) {
    const container = document.getElementById('gradeEntryTable');
    
    let html = `
        <div class="p-3">
            <h5>Enter Grades for ${students.length} Students</h5>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Roll No</th>
                        <th>Student Name</th>
                        <th>Marks Obtained</th>
                        <th>Total Marks</th>
                        <th>Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    students.forEach((student, index) => {
        html += `
            <tr>
                <td>${student.roll || student.id}</td>
                <td>${student.fname} ${student.lname}</td>
                <td>
                    <input type="number" class="form-control form-control-sm" 
                           id="marks_${student.id}" 
                           min="0" max="100" 
                           onchange="calculateGrade(${student.id})">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" 
                           id="total_${student.id}" 
                           value="100" min="1">
                </td>
                <td>
                    <span id="grade_${student.id}" class="badge bg-secondary">-</span>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" 
                           id="remarks_${student.id}" 
                           placeholder="Optional">
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
            <div class="text-end mt-3">
                <button class="btn btn-success" onclick="saveGrades()">
                    <i class='bx bx-save'></i> Save All Grades
                </button>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
    
    // Store student data for later use
    window.currentStudents = students;
    window.currentClass = classValue;
    window.currentSubject = subject;
    window.currentAssessmentType = assessmentType;
}

function calculateGrade(studentId) {
    const marks = parseFloat(document.getElementById(`marks_${studentId}`).value) || 0;
    const total = parseFloat(document.getElementById(`total_${studentId}`).value) || 100;
    
    const percentage = (marks / total) * 100;
    
    let grade = 'F';
    let gradeClass = 'bg-danger';
    
    if (percentage >= 90) { grade = 'A+'; gradeClass = 'bg-success'; }
    else if (percentage >= 80) { grade = 'A'; gradeClass = 'bg-success'; }
    else if (percentage >= 70) { grade = 'B'; gradeClass = 'bg-info'; }
    else if (percentage >= 60) { grade = 'C'; gradeClass = 'bg-warning'; }
    else if (percentage >= 50) { grade = 'D'; gradeClass = 'bg-warning'; }
    else { grade = 'F'; gradeClass = 'bg-danger'; }
    
    const gradeSpan = document.getElementById(`grade_${studentId}`);
    gradeSpan.textContent = grade;
    gradeSpan.className = `badge ${gradeClass}`;
}

function saveGrades() {
    if (!window.currentStudents) {
        alert('⚠️ No students loaded');
        return;
    }
    
    const grades = [];
    let hasErrors = false;
    
    window.currentStudents.forEach(student => {
        const marks = document.getElementById(`marks_${student.id}`).value;
        const total = document.getElementById(`total_${student.id}`).value;
        const remarks = document.getElementById(`remarks_${student.id}`).value;
        
        if (marks !== '') {
            grades.push({
                student_id: student.id,
                marks: marks,
                total: total,
                remarks: remarks
            });
        }
    });
    
    if (grades.length === 0) {
        alert('⚠️ Please enter at least one grade');
        return;
    }
    
    if (!confirm(`Save grades for ${grades.length} students?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'save_grades');
    formData.append('class', window.currentClass);
    formData.append('subject', window.currentSubject);
    formData.append('assessment_type', window.currentAssessmentType);
    formData.append('grades', JSON.stringify(grades));
    
    fetch('../assets/manageMarks.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('✅ Grades saved successfully!');
            loadGradeStats();
            document.getElementById('gradeEntryTable').innerHTML = '';
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Error saving grades: ' + error);
    });
}

function generateReports() {
    alert('📊 Report generation feature coming soon!');
}
</script>

<?php include('partials/_footer.php') ?>

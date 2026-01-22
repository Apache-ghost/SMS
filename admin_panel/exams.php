<?php include('partials/_header.php') ?>

<!-- Sidebar -->
<?php include('partials/_sidebar.php') ?>

<div class="content">
    <?php include("partials/_navbar.php"); ?>

    <main>
        <div class="header">
            <div class="left">
                <h1>Exam Management</h1>
                <ul class="breadcrumb">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active">Exams</a></li>
                </ul>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExamModal">
                <i class='bx bx-plus'></i> Create Exam
            </button>
        </div>

        <!-- Statistics -->
        <ul class="insights">
            <li>
                <i class='bx bx-file'></i>
                <span class="info">
                    <h3 class="text-center" id="totalExams">0</h3>
                    <p>Total Exams</p>
                </span>
            </li>
            <li>
                <i class='bx bx-calendar-check'></i>
                <span class="info">
                    <h3 class="text-center" id="upcomingExams">0</h3>
                    <p>Upcoming</p>
                </span>
            </li>
            <li>
                <i class='bx bx-check-circle'></i>
                <span class="info">
                    <h3 class="text-center" id="completedExams">0</h3>
                    <p>Completed</p>
                </span>
            </li>
            <li>
                <i class='bx bx-user-check'></i>
                <span class="info">
                    <h3 class="text-center" id="totalResults">0</h3>
                    <p>Results Published</p>
                </span>
            </li>
        </ul>

        <!-- Exams List -->
        <div class="bottom-data">
            <div class="orders" style="width: 100%;">
                <div class="header">
                    <i class='bx bx-list-ul'></i>
                    <h3>All Exams</h3>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="filterClass" onchange="loadExams()">
                            <option value="">All Classes</option>
                        </select>
                        <input type="text" id="searchExam" placeholder="Search..." class="form-control form-control-sm" onkeyup="searchExams()">
                    </div>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Exam Title</th>
                            <th>Subject</th>
                            <th>Class</th>
                            <th>Date</th>
                            <th>Total Marks</th>
                            <th>Passing Marks</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="examsTable">
                        <tr>
                            <td colspan="8" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Add Exam Modal -->
<div class="modal fade" id="addExamModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Exam</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addExamForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Exam Title *</label>
                            <input type="text" class="form-control" name="exam_title" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subject *</label>
                            <select class="form-control" name="subject" id="examSubject" required>
                                <option value="">Select Subject</option>
                                <option value="ALL">All Subjects</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Class *</label>
                            <select class="form-control" name="class" id="examClass" required>
                                <option value="">Select Class</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Section *</label>
                            <select class="form-control" name="section" id="examSection" required>
                                <option value="">Select Section</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Exam Date *</label>
                            <input type="date" class="form-control" name="exam_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Marks *</label>
                            <input type="number" class="form-control" name="total_marks" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Passing Marks *</label>
                            <input type="number" class="form-control" name="passing_marks" min="1" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveExam()">Create Exam</button>
            </div>
        </div>
    </div>
</div>

<!-- Publish Results Modal -->
<div class="modal fade" id="publishResultsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Publish Exam Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="resultsContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="saveResults()">Save Results</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentExamId = null;

document.addEventListener('DOMContentLoaded', function() {
    loadExams();
    loadStats();
    loadClassesForFilter();
    loadClassesForExam();
    loadSubjectsForExam();
});

function loadStats() {
    fetch('../assets/manageExams.php?action=get_stats')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('totalExams').textContent = data.stats.total || 0;
                document.getElementById('upcomingExams').textContent = data.stats.upcoming || 0;
                document.getElementById('completedExams').textContent = data.stats.completed || 0;
                document.getElementById('totalResults').textContent = data.stats.results_published || 0;
            }
        })
        .catch(error => console.error('Error loading stats:', error));
}

function loadClassesForFilter() {
    fetch('../assets/fetchClassAndSection.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('filterClass');
            const uniqueClasses = [...new Set(data.map(item => item.class))];
            
            uniqueClasses.forEach(cls => {
                const option = document.createElement('option');
                option.value = cls;
                option.textContent = `Class ${cls}`;
                select.appendChild(option);
            });
        });
}

function loadClassesForExam() {
    fetch('../assets/fetchClassAndSection.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('examClass');
            const uniqueClasses = [...new Set(data.map(item => item.class))];
            
            uniqueClasses.forEach(cls => {
                const option = document.createElement('option');
                option.value = cls;
                option.textContent = `Class ${cls}`;
                select.appendChild(option);
            });
        });
    
    document.getElementById('examClass').addEventListener('change', function() {
        loadSectionsForClass(this.value);
    });
}

function loadSectionsForClass(classValue) {
    fetch('../assets/fetchClassAndSection.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('examSection');
            select.innerHTML = '<option value="">Select Section</option>';
            
            const sections = data.filter(item => item.class === classValue).map(item => item.section);
            sections.forEach(section => {
                const option = document.createElement('option');
                option.value = section;
                option.textContent = `Section ${section}`;
                select.appendChild(option);
            });
        });
}

function loadSubjectsForExam() {
    fetch('../assets/fetchSubjects.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('examSubject');
            data.forEach(subject => {
                const option = document.createElement('option');
                option.value = subject.subject_name;
                option.textContent = subject.subject_name;
                select.appendChild(option);
            });
        });
}

function loadExams() {
    const filterClass = document.getElementById('filterClass').value;
    const url = `../assets/manageExams.php?action=get_exams${filterClass ? '&class=' + filterClass : ''}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('examsTable');
            
            if (data.status === 'success' && data.exams && data.exams.length > 0) {
                let html = '';
                
                data.exams.forEach(exam => {
                    const examDate = new Date(exam.exam_date || exam.timestamp);
                    const today = new Date();
                    const isPast = examDate < today;
                    const status = isPast ? '<span class="badge bg-success">Completed</span>' : '<span class="badge bg-warning">Upcoming</span>';
                    
                    html += `
                        <tr>
                            <td>${exam.exam_title}</td>
                            <td>${exam.subject}</td>
                            <td>Class ${exam.class} - ${exam.section}</td>
                            <td>${examDate.toLocaleDateString()}</td>
                            <td>${exam.total_marks}</td>
                            <td>${exam.passing_marks}</td>
                            <td>${status}</td>
                            <td>
                                <button class="btn btn-sm btn-success" onclick="publishResults('${exam.exam_id}', '${exam.exam_title}')">
                                    <i class='bx bx-upload'></i> Results
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteExam('${exam.exam_id}')">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                tbody.innerHTML = html;
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">No exams found</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading exams:', error);
            document.getElementById('examsTable').innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error loading exams</td></tr>';
        });
}

function saveExam() {
    const form = document.getElementById('addExamForm');
    const formData = new FormData(form);
    formData.append('action', 'create_exam');
    
    fetch('../assets/manageExams.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('✅ Exam created successfully!');
            bootstrap.Modal.getInstance(document.getElementById('addExamModal')).hide();
            form.reset();
            loadExams();
            loadStats();
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Error creating exam: ' + error);
    });
}

function publishResults(examId, examTitle) {
    currentExamId = examId;
    const modal = new bootstrap.Modal(document.getElementById('publishResultsModal'));
    document.querySelector('#publishResultsModal .modal-title').textContent = `Publish Results: ${examTitle}`;
    
    const content = document.getElementById('resultsContent');
    content.innerHTML = '<div class="text-center"><div class="spinner-border"></div><p>Loading students...</p></div>';
    
    fetch(`../assets/manageExams.php?action=get_students_for_exam&exam_id=${examId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.students) {
                displayResultsForm(data.students, data.exam);
            } else {
                content.innerHTML = '<div class="alert alert-danger">Error loading students</div>';
            }
        });
    
    modal.show();
}

function displayResultsForm(students, exam) {
    const content = document.getElementById('resultsContent');
    
    let html = `
        <h6>Exam: ${exam.exam_title} | Total Marks: ${exam.total_marks} | Passing: ${exam.passing_marks}</h6>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Roll No</th>
                    <th>Student Name</th>
                    <th>Marks Obtained</th>
                    <th>Grade</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    students.forEach(student => {
        const existingMarks = student.marks_obtained || '';
        const existingRemarks = student.remarks || '';
        
        html += `
            <tr>
                <td>${student.roll || student.id}</td>
                <td>${student.fname} ${student.lname}</td>
                <td>
                    <input type="number" class="form-control form-control-sm" 
                           id="marks_${student.id}" 
                           value="${existingMarks}"
                           min="0" max="${exam.total_marks}"
                           onchange="calculateGrade(${student.id}, ${exam.total_marks}, ${exam.passing_marks})">
                </td>
                <td>
                    <span id="grade_${student.id}" class="badge bg-secondary">-</span>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" 
                           id="remarks_${student.id}" 
                           value="${existingRemarks}"
                           placeholder="Optional">
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    content.innerHTML = html;
    
    // Store for later use
    window.currentStudents = students;
    window.currentExam = exam;
}

function calculateGrade(studentId, totalMarks, passingMarks) {
    const marks = parseFloat(document.getElementById(`marks_${studentId}`).value) || 0;
    const percentage = (marks / totalMarks) * 100;
    
    let grade = 'F';
    let gradeClass = 'bg-danger';
    
    if (marks < passingMarks) {
        grade = 'F';
        gradeClass = 'bg-danger';
    } else if (percentage >= 90) {
        grade = 'A+';
        gradeClass = 'bg-success';
    } else if (percentage >= 80) {
        grade = 'A';
        gradeClass = 'bg-success';
    } else if (percentage >= 70) {
        grade = 'B';
        gradeClass = 'bg-info';
    } else if (percentage >= 60) {
        grade = 'C';
        gradeClass = 'bg-warning';
    } else if (percentage >= 50) {
        grade = 'D';
        gradeClass = 'bg-warning';
    }
    
    const gradeSpan = document.getElementById(`grade_${studentId}`);
    gradeSpan.textContent = grade;
    gradeSpan.className = `badge ${gradeClass}`;
}

function saveResults() {
    if (!window.currentStudents || !window.currentExam) {
        alert('⚠️ No data to save');
        return;
    }
    
    const results = [];
    window.currentStudents.forEach(student => {
        const marks = document.getElementById(`marks_${student.id}`).value;
        if (marks !== '') {
            results.push({
                student_id: student.id,
                marks_obtained: marks,
                remarks: document.getElementById(`remarks_${student.id}`).value
            });
        }
    });
    
    if (results.length === 0) {
        alert('⚠️ Please enter at least one result');
        return;
    }
    
    if (!confirm(`Publish results for ${results.length} students?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'save_results');
    formData.append('exam_id', currentExamId);
    formData.append('results', JSON.stringify(results));
    
    fetch('../assets/manageExams.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('✅ Results published successfully!');
            bootstrap.Modal.getInstance(document.getElementById('publishResultsModal')).hide();
            loadStats();
        } else {
            alert('❌ Error: ' + data.message);
        }
    });
}

function deleteExam(examId) {
    if (!confirm('Delete this exam? This will also delete all associated results.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete_exam');
    formData.append('exam_id', examId);
    
    fetch('../assets/manageExams.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('✅ Exam deleted successfully!');
            loadExams();
            loadStats();
        } else {
            alert('❌ Error: ' + data.message);
        }
    });
}

function searchExams() {
    const searchTerm = document.getElementById('searchExam').value.toLowerCase();
    const rows = document.querySelectorAll('#examsTable tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}
</script>

<?php include('partials/_footer.php') ?>

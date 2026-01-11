<?php include('partials/_header.php') ?>

<!-- Sidebar -->
<?php include('partials/_sidebar.php') ?>

<div class="content">
    <?php include("partials/_navbar.php"); ?>

    <main>
        <div class="header">
            <div class="left">
                <h1>Grade Submissions</h1>
                <ul class="breadcrumb">
                    <li><a href="assignments.php">Assignments</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active">Grade Submissions</a></li>
                </ul>
            </div>
            <a href="assignments.php" class="btn btn-secondary">
                <i class='bx bx-arrow-back'></i> Back to Assignments
            </a>
        </div>

        <!-- Assignment Info -->
        <div class="bottom-data">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bx-file'></i>
                    <h3>Assignment Details</h3>
                </div>
                <div id="assignmentInfo" class="p-3">
                    <div class="text-center">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2">Loading...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submissions List -->
        <div class="bottom-data mt-3">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bx-upload'></i>
                    <h3>Student Submissions</h3>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="filterStatus" style="width:auto;" onchange="filterSubmissions()">
                            <option value="">All</option>
                            <option value="submitted">Submitted</option>
                            <option value="graded">Graded</option>
                            <option value="late">Late</option>
                        </select>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Student Name</th>
                                <th>Roll No</th>
                                <th>Submitted At</th>
                                <th>Status</th>
                                <th>Marks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="submissionsList">
                            <tr>
                                <td colspan="6" class="text-center">Loading submissions...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
</div>

<!-- Grade Submission Modal -->
<div class="modal fade" id="gradeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Grade Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="gradeForm">
                    <input type="hidden" id="submissionId" name="submission_id">
                    
                    <div class="mb-3">
                        <h6 id="studentName"></h6>
                    </div>
                    
                    <div class="mb-3">
                        <label>Submission Text</label>
                        <div id="submissionText" class="border p-3 bg-light" style="max-height: 200px; overflow-y: auto;"></div>
                    </div>
                    
                    <div class="mb-3" id="attachmentSection">
                        <!-- Attachment link will be inserted here -->
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Marks Obtained <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="marksObtained" name="marks_obtained" 
                                min="0" required>
                            <small class="text-muted">Out of <span id="maxMarks"></span></small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>Feedback</label>
                        <textarea class="form-control" id="feedback" name="feedback" rows="4" 
                            placeholder="Provide feedback to the student..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="gradeSubmission()">
                    <i class='bx bx-check'></i> Submit Grade
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let assignmentId = null;
let allSubmissions = [];
let assignmentData = null;

document.addEventListener('DOMContentLoaded', function() {
    // Get assignment ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    assignmentId = urlParams.get('assignment_id');
    
    if (!assignmentId) {
        alert('No assignment selected');
        window.location.href = 'assignments.php';
        return;
    }
    
    loadAssignmentDetails();
    loadSubmissions();
});

function loadAssignmentDetails() {
    fetch(`../assets/manageAssignments.php?action=fetch_assignment_details&assignment_id=${assignmentId}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.assignment) {
            assignmentData = data.assignment;
            displayAssignmentInfo(data.assignment);
        } else {
            alert('Error loading assignment details');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function displayAssignmentInfo(assignment) {
    const html = `
        <div class="row">
            <div class="col-md-8">
                <h5>${assignment.title}</h5>
                <p><strong>Code:</strong> ${assignment.assignment_code}</p>
                <p><strong>Class:</strong> ${assignment.class} - Section: ${assignment.section}</p>
                <p><strong>Subject:</strong> ${assignment.course_code}</p>
                <p>${assignment.description}</p>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <p><strong>Due Date:</strong><br>${assignment.due_date}</p>
                        <p><strong>Max Marks:</strong> ${assignment.max_marks}</p>
                        <p><strong>Submissions:</strong> ${assignment.total_submissions || 0}</p>
                        <p><strong>Graded:</strong> ${assignment.graded_submissions || 0}</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('assignmentInfo').innerHTML = html;
}

function loadSubmissions() {
    fetch(`../assets/manageAssignments.php?action=fetch_submissions&assignment_id=${assignmentId}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            allSubmissions = data.submissions;
            displaySubmissions(allSubmissions);
        } else {
            document.getElementById('submissionsList').innerHTML = 
                '<tr><td colspan="6" class="text-center text-danger">Error loading submissions</td></tr>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('submissionsList').innerHTML = 
            '<tr><td colspan="6" class="text-center text-danger">Error loading submissions</td></tr>';
    });
}

function filterSubmissions() {
    const filter = document.getElementById('filterStatus').value;
    
    if (filter === '') {
        displaySubmissions(allSubmissions);
    } else {
        const filtered = allSubmissions.filter(s => s.status === filter);
        displaySubmissions(filtered);
    }
}

function displaySubmissions(submissions) {
    const tbody = document.getElementById('submissionsList');
    
    if (submissions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No submissions found</td></tr>';
        return;
    }
    
    let html = '';
    
    submissions.forEach(sub => {
        const statusClass = sub.status === 'graded' ? 'success' : sub.status === 'late' ? 'danger' : 'warning';
        const marks = sub.marks_obtained !== null ? `${sub.marks_obtained}/${assignmentData.max_marks}` : '-';
        
        html += `
            <tr>
                <td>${sub.student_name}</td>
                <td>${sub.student_id}</td>
                <td>${new Date(sub.submitted_at).toLocaleString()}</td>
                <td><span class="badge bg-${statusClass}">${sub.status}</span></td>
                <td>${marks}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="openGradeModal(${sub.submission_id})">
                        <i class='bx ${sub.status === 'graded' ? 'bx-edit' : 'bx-check'}'></i> 
                        ${sub.status === 'graded' ? 'Edit' : 'Grade'}
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function openGradeModal(submissionId) {
    const submission = allSubmissions.find(s => s.submission_id == submissionId);
    
    if (!submission) return;
    
    document.getElementById('submissionId').value = submissionId;
    document.getElementById('studentName').textContent = submission.student_name + ' (' + submission.student_id + ')';
    document.getElementById('submissionText').textContent = submission.submission_text || 'No text submission';
    document.getElementById('maxMarks').textContent = assignmentData.max_marks;
    document.getElementById('marksObtained').value = submission.marks_obtained || '';
    document.getElementById('marksObtained').max = assignmentData.max_marks;
    document.getElementById('feedback').value = submission.feedback || '';
    
    // Handle attachment
    if (submission.attachment) {
        document.getElementById('attachmentSection').innerHTML = `
            <label>Submitted File</label><br>
            <a href="../studentSubmissions/${submission.attachment}" class="btn btn-sm btn-info" target="_blank">
                <i class='bx bx-download'></i> Download Attachment
            </a>
        `;
    } else {
        document.getElementById('attachmentSection').innerHTML = '<p class="text-muted">No file attached</p>';
    }
    
    new bootstrap.Modal(document.getElementById('gradeModal')).show();
}

function gradeSubmission() {
    const submissionId = document.getElementById('submissionId').value;
    const marksObtained = document.getElementById('marksObtained').value;
    const feedback = document.getElementById('feedback').value;
    
    if (!marksObtained) {
        alert('Please enter marks obtained');
        return;
    }
    
    if (parseInt(marksObtained) > assignmentData.max_marks) {
        alert('Marks cannot exceed maximum marks');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'grade_submission');
    formData.append('submission_id', submissionId);
    formData.append('marks_obtained', marksObtained);
    formData.append('feedback', feedback);
    
    fetch('../assets/manageAssignments.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Submission graded successfully!');
            bootstrap.Modal.getInstance(document.getElementById('gradeModal')).hide();
            loadSubmissions();
            loadAssignmentDetails(); // Refresh stats
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error grading submission');
    });
}
</script>

<style>
.full-width { width: 100%; }
</style>

<?php include('partials/_footer.php') ?>

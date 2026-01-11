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
                <h1>Curriculum Management</h1>
                <ul class="breadcrumb">
                    <li><a>Academic / Curriculum</a></li>
                </ul>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCurriculumModal">
                <i class='bx bx-plus'></i> Add Curriculum
            </button>
        </div>

        <!-- Curriculum Overview -->
        <ul class="insights">
            <li>
                <i class='bx bx-book-content'></i>
                <span class="info">
                    <h3 class="text-center" id="totalCurriculum">0</h3>
                    <p>Total Curriculum</p>
                </span>
            </li>
            <li>
                <i class='bx bx-book-bookmark'></i>
                <span class="info">
                    <h3 class="text-center" id="totalSubjects">0</h3>
                    <p>Subjects</p>
                </span>
            </li>
            <li>
                <i class='bx bx-user'></i>
                <span class="info">
                    <h3 class="text-center" id="assignedTeachers">0</h3>
                    <p>Assigned Teachers</p>
                </span>
            </li>
            <li>
                <i class='bx bx-check-circle'></i>
                <span class="info">
                    <h3 class="text-center" id="activeStatus">0</h3>
                    <p>Active</p>
                </span>
            </li>
        </ul>

        <!-- Curriculum List -->
        <div class="bottom-data">
            <div class="orders" style="width: 100%;">
                <div class="header">
                    <i class='bx bx-book-content'></i>
                    <h3>Curriculum List</h3>
                    <input type="text" id="searchCurriculum" placeholder="Search..." class="form-control" style="max-width: 300px;">
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Academic Year</th>
                                <th>Department</th>
                                <th>Total Subjects</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="curriculumList">
                            <tr>
                                <td colspan="6" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Add Curriculum Modal -->
<div class="modal fade" id="addCurriculumModal" tabindex="-1" aria-labelledby="addCurriculumLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCurriculumLabel"><i class='bx bx-plus-circle'></i> Add New Curriculum</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCurriculumForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="curriculumName" class="form-label">Curriculum Name *</label>
                            <input type="text" class="form-control" id="curriculumName" name="curriculum_name" required placeholder="e.g., Science Stream">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gradeLevel" class="form-label">Class/Grade *</label>
                            <select class="form-select" id="gradeLevel" name="grade_level" required>
                                <option value="">--Select Class--</option>
                                <option value="Nursery">Nursery</option>
                                <option value="LKG">LKG</option>
                                <option value="UKG">UKG</option>
                                <option value="1">Class 1</option>
                                <option value="2">Class 2</option>
                                <option value="3">Class 3</option>
                                <option value="4">Class 4</option>
                                <option value="5">Class 5</option>
                                <option value="6">Class 6</option>
                                <option value="7">Class 7</option>
                                <option value="8">Class 8</option>
                                <option value="9">Class 9</option>
                                <option value="10">Class 10</option>
                                <option value="11">Class 11</option>
                                <option value="12">Class 12</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="academicYear" class="form-label">Academic Year *</label>
                            <input type="text" class="form-control" id="academicYear" name="academic_year" required placeholder="e.g., 2025-2026">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="department" class="form-label">Department/Stream</label>
                            <input type="text" class="form-control" id="department" name="department" placeholder="e.g., Science, Arts, Commerce">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="curriculumDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="curriculumDescription" name="description" rows="3" placeholder="Brief description of the curriculum..."></textarea>
                    </div>
                    
                    <h6 class="mt-4 mb-3">Add Subjects</h6>
                    <div id="subjectsContainer">
                        <div class="subject-row mb-2">
                            <div class="row">
                                <div class="col-md-4">
                                    <select class="form-select" name="subject_code[]" required>
                                        <option value="">--Select Subject--</option>
                                        <?php
                                        include('../assets/config.php');
                                        $subQuery = "SELECT * FROM subjects ORDER BY subject_name";
                                        $subResult = mysqli_query($conn, $subQuery);
                                        while($sub = mysqli_fetch_assoc($subResult)) {
                                            echo "<option value='" . $sub['subject_id'] . "'>" . $sub['subject_name'] . " (" . $sub['subject_id'] . ")</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" name="subject_name[]" placeholder="Theory Hours" min="0" max="10" value="4">
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" name="hours_per_week[]" placeholder="Practical Hours" min="0" max="10" value="0">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger btn-sm remove-subject" style="display:none;"><i class='bx bx-trash'></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mt-2" id="addSubjectBtn"><i class='bx bx-plus'></i> Add Another Subject</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCurriculumBtn"><i class='bx bx-save'></i> Save Curriculum</button>
            </div>
        </div>
    </div>
</div>

<!-- View Curriculum Details Modal -->
<div class="modal fade" id="viewCurriculumModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class='bx bx-book-content'></i> Curriculum Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="curriculumDetailsBody">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<style>
/* Responsive Design for Curriculum Page */
@media (max-width: 768px) {
    .insights {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    
    .insights li {
        padding: 15px;
    }
    
    .insights li h3 {
        font-size: 20px;
    }
    
    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .header button {
        width: 100%;
    }
    
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .table {
        min-width: 600px;
        font-size: 13px;
    }
    
    .table th, .table td {
        padding: 8px 5px;
    }
    
    .modal-dialog {
        margin: 10px;
    }
    
    .modal-lg, .modal-xl {
        max-width: 95%;
    }
    
    #searchCurriculum {
        max-width: 100% !important;
        margin-top: 10px;
    }
}

@media (max-width: 480px) {
    .insights {
        grid-template-columns: 1fr;
    }
    
    .subject-row .row {
        flex-direction: column;
    }
    
    .subject-row .col-md-4,
    .subject-row .col-md-3,
    .subject-row .col-md-2 {
        width: 100%;
        margin-bottom: 10px;
    }
}

.subject-row {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 10px;
}

.badge {
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 12px;
}

.badge-success {
    background: #10b981;
    color: white;
}

.badge-warning {
    background: #f59e0b;
    color: white;
}

.badge-danger {
    background: #ef4444;
    color: white;
}
</style>

<script>
// Add/Remove subject fields
let subjectCount = 1;
document.getElementById('addSubjectBtn').addEventListener('click', function() {
    subjectCount++;
    const container = document.getElementById('subjectsContainer');
    const newRow = document.createElement('div');
    newRow.className = 'subject-row mb-2';
    newRow.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <select class="form-select" name="subject_code[]" required>
                    <option value="">--Select Subject--</option>
                    <?php
                    mysqli_data_seek($subResult, 0);
                    while($sub = mysqli_fetch_assoc($subResult)) {
                        echo "<option value='" . $sub['subject_id'] . "'>" . $sub['subject_name'] . " (" . $sub['subject_id'] . ")</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" class="form-control" name="subject_name[]" placeholder="Theory Hours" min="0" max="10" value="4">
            </div>
            <div class="col-md-3">
                <input type="number" class="form-control" name="hours_per_week[]" placeholder="Practical Hours" min="0" max="10" value="0">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm remove-subject"><i class='bx bx-trash'></i></button>
            </div>
        </div>
    `;
    container.appendChild(newRow);
    
    // Update remove buttons visibility
    updateRemoveButtons();
});

// Remove subject field
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-subject')) {
        e.target.closest('.subject-row').remove();
        subjectCount--;
        updateRemoveButtons();
    }
});

function updateRemoveButtons() {
    const removeButtons = document.querySelectorAll('.remove-subject');
    removeButtons.forEach((btn, index) => {
        btn.style.display = removeButtons.length > 1 ? 'block' : 'none';
    });
}

// Save curriculum
document.getElementById('saveCurriculumBtn').addEventListener('click', function() {
    const form = document.getElementById('addCurriculumForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    formData.append('action', 'add_curriculum');
    
    this.disabled = true;
    this.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Saving...';
    
    fetch('../assets/addCurriculum.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('addCurriculumModal')).hide();
            form.reset();
            subjectCount = 1;
            loadCurriculum();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    })
    .finally(() => {
        this.disabled = false;
        this.innerHTML = '<i class="bx bx-save"></i> Save Curriculum';
    });
});

// Load curriculum list
function loadCurriculum() {
    fetch('../assets/fetchCurriculum.php')
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            displayCurriculum(data.data);
            updateStats(data.data);
        }
    })
    .catch(error => console.error('Error:', error));
}

function displayCurriculum(curriculums) {
    const tbody = document.getElementById('curriculumList');
    if (curriculums.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">No curriculum found</td></tr>';
        return;
    }
    
    tbody.innerHTML = curriculums.map(curr => `
        <tr>
            <td>Class ${curr.class}</td>
            <td>${curr.academic_year}</td>
            <td>${curr.department_code || 'General'}</td>
            <td>${curr.total_subjects}</td>
            <td><span class="badge badge-${curr.status === 'active' ? 'success' : 'warning'}">${curr.status}</span></td>
            <td>
                <button class="btn btn-sm btn-info" onclick="viewCurriculum(${curr.curriculum_id})"><i class='bx bx-show'></i></button>
                <button class="btn btn-sm btn-warning" onclick="editCurriculum(${curr.curriculum_id})"><i class='bx bx-edit'></i></button>
                <button class="btn btn-sm btn-danger" onclick="deleteCurriculum(${curr.curriculum_id})"><i class='bx bx-trash'></i></button>
            </td>
        </tr>
    `).join('');
}

function updateStats(curriculums) {
    document.getElementById('totalCurriculum').textContent = curriculums.length;
    const totalSubjects = curriculums.reduce((sum, curr) => sum + parseInt(curr.total_subjects || 0), 0);
    document.getElementById('totalSubjects').textContent = totalSubjects;
    const activeCount = curriculums.filter(curr => curr.status === 'active').length;
    document.getElementById('activeStatus').textContent = activeCount;
}

// Load on page load
document.addEventListener('DOMContentLoaded', loadCurriculum);

// Search functionality
document.getElementById('searchCurriculum').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#curriculumList tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>

<?php include('partials/_footer.php') ?>

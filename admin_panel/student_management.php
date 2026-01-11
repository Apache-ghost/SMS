<!-- REQ-ACD-001: Student Management Admin Panel -->
<?php include('partials/_header.php') ?>

<style>
    .nav-pills .nav-link {
        border-radius: 5px;
        margin-right: 5px;
    }
    .nav-pills .nav-link.active {
        background-color: #7380ec;
    }
    .badge-status {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.85rem;
    }
    .badge-pending { background-color: #ffc107; color: #000; }
    .badge-active { background-color: #28a745; color: #fff; }
    .badge-approved { background-color: #007bff; color: #fff; }
    .badge-rejected { background-color: #dc3545; color: #fff; }
    .badge-transferred { background-color: #6c757d; color: #fff; }
    .profile-section {
        background: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .profile-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    .profile-img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 20px;
    }
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
        border-left: 2px solid #7380ec;
    }
    .timeline-item:last-child {
        border-left: 0;
    }
    .timeline-marker {
        position: absolute;
        left: -6px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #7380ec;
    }
    .timeline-content {
        padding-left: 20px;
    }
</style>

<!-- Navigation Tabs -->
<div class="container-fluid mt-4">
    <ul class="nav nav-pills mb-4" id="studentMgmtTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="admissions-tab" data-bs-toggle="pill" 
                    data-bs-target="#admissions" type="button">
                <i class='bx bx-user-plus'></i> Admissions
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="enrollments-tab" data-bs-toggle="pill" 
                    data-bs-target="#enrollments" type="button">
                <i class='bx bx-id-card'></i> Enrollments
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="promotions-tab" data-bs-toggle="pill" 
                    data-bs-target="#promotions" type="button">
                <i class='bx bx-trending-up'></i> Promotions
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="transfers-tab" data-bs-toggle="pill" 
                    data-bs-target="#transfers" type="button">
                <i class='bx bx-transfer'></i> Transfers
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="profiles-tab" data-bs-toggle="pill" 
                    data-bs-target="#profiles" type="button">
                <i class='bx bx-user-circle'></i> Complete Profiles
            </button>
        </li>
    </ul>

    <div class="tab-content" id="studentMgmtTabsContent">
        
        <!-- ADMISSIONS TAB -->
        <div class="tab-pane fade show active" id="admissions" role="tabpanel">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5><i class='bx bx-user-plus'></i> Student Admissions</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newAdmissionModal">
                                <i class='bx bx-plus'></i> New Admission
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover" id="admissionsTable">
                                <thead>
                                    <tr>
                                        <th>Application No</th>
                                        <th>Student Name</th>
                                        <th>Father Name</th>
                                        <th>Class</th>
                                        <th>Application Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="admissionsTableBody">
                                    <!-- Populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ENROLLMENTS TAB -->
        <div class="tab-pane fade" id="enrollments" role="tabpanel">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class='bx bx-id-card'></i> Student Enrollments</h5>
                            <div class="row mt-3">
                                <div class="col-md-3">
                                    <select class="form-select" id="enrollmentAcademicYear">
                                        <option value="2024-2025">2024-2025</option>
                                        <option value="2025-2026" selected>2025-2026</option>
                                        <option value="2026-2027">2026-2027</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="enrollmentClass">
                                        <option value="">All Classes</option>
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
                                <div class="col-md-2">
                                    <button class="btn btn-primary" onclick="loadEnrollments()">
                                        <i class='bx bx-search'></i> Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="enrollmentStats" class="mb-3"></div>
                            <table class="table table-hover" id="enrollmentsTable">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Roll No</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th>Academic Year</th>
                                        <th>Scholarship</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="enrollmentsTableBody">
                                    <!-- Populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PROMOTIONS TAB -->
        <div class="tab-pane fade" id="promotions" role="tabpanel">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5><i class='bx bx-trending-up'></i> Student Promotions</h5>
                            <div>
                                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#bulkPromotionModal">
                                    <i class='bx bx-group'></i> Bulk Promotion
                                </button>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newPromotionModal">
                                    <i class='bx bx-plus'></i> New Promotion
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover" id="promotionsTable">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>From Class</th>
                                        <th>To Class</th>
                                        <th>Academic Year</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="promotionsTableBody">
                                    <!-- Populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TRANSFERS TAB -->
        <div class="tab-pane fade" id="transfers" role="tabpanel">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5><i class='bx bx-transfer'></i> Student Transfers</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTransferModal">
                                <i class='bx bx-plus'></i> New Transfer
                            </button>
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-tabs mb-3" id="transferTypeTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" onclick="loadTransfers('')">All Transfers</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" onclick="loadTransfers('incoming')">Incoming</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" onclick="loadTransfers('outgoing')">Outgoing</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" onclick="loadTransfers('internal')">Internal</button>
                                </li>
                            </ul>
                            <table class="table table-hover" id="transfersTable">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Transfer Type</th>
                                        <th>From/To</th>
                                        <th>Transfer Date</th>
                                        <th>TC Status</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="transfersTableBody">
                                    <!-- Populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COMPLETE PROFILES TAB -->
        <div class="tab-pane fade" id="profiles" role="tabpanel">
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h6>Select Student</h6>
                        </div>
                        <div class="card-body">
                            <input type="text" class="form-control mb-2" id="searchStudent" placeholder="Search student...">
                            <div id="studentList" style="max-height: 500px; overflow-y: auto;">
                                <!-- Populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div id="studentProfileView">
                        <div class="alert alert-info">
                            <i class='bx bx-info-circle'></i> Select a student to view complete profile
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modals will be added here -->
<!-- New Admission Modal -->
<div class="modal fade" id="newAdmissionModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Admission Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="admissionForm">
                    <input type="hidden" name="action" value="create_admission">
                    
                    <h6 class="mb-3">Student Information</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>First Name *</label>
                            <input type="text" class="form-control" name="fname" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Last Name *</label>
                            <input type="text" class="form-control" name="lname" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Date of Birth *</label>
                            <input type="date" class="form-control" name="dob" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Gender *</label>
                            <select class="form-control" name="gender" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Blood Group</label>
                            <select class="form-control" name="blood_group">
                                <option value="">Select</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Admission for Class *</label>
                            <select class="form-control" name="admission_class" required>
                                <option value="">Select Class</option>
                                <?php for($i=1; $i<=12; $i++): ?>
                                    <option value="<?php echo $i; ?>">Class <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Category</label>
                            <select class="form-control" name="category">
                                <option value="General">General</option>
                                <option value="OBC">OBC</option>
                                <option value="SC">SC</option>
                                <option value="ST">ST</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Religion</label>
                            <input type="text" class="form-control" name="religion">
                        </div>
                    </div>

                    <h6 class="mb-3 mt-4">Contact Information</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Phone *</label>
                            <input type="text" class="form-control" name="phone" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Emergency Contact</label>
                            <input type="text" class="form-control" name="emergency_contact">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label>Address *</label>
                            <textarea class="form-control" name="address" rows="2" required></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>City *</label>
                            <input type="text" class="form-control" name="city" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>State *</label>
                            <input type="text" class="form-control" name="state" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>ZIP Code *</label>
                            <input type="text" class="form-control" name="zip" required>
                        </div>
                    </div>

                    <h6 class="mb-3 mt-4">Parent/Guardian Information</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Father's Name *</label>
                            <input type="text" class="form-control" name="father" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Father's Occupation</label>
                            <input type="text" class="form-control" name="father_occupation">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Mother's Name</label>
                            <input type="text" class="form-control" name="mother">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Mother's Phone</label>
                            <input type="text" class="form-control" name="mother_phone">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Mother's Occupation</label>
                            <input type="text" class="form-control" name="mother_occupation">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Guardian Name *</label>
                            <input type="text" class="form-control" name="guardian" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Guardian Phone *</label>
                            <input type="text" class="form-control" name="gphone" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Guardian Email</label>
                            <input type="email" class="form-control" name="guardian_email">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Relation *</label>
                            <select class="form-control" name="relation" required>
                                <option value="Father">Father</option>
                                <option value="Mother">Mother</option>
                                <option value="Uncle">Uncle</option>
                                <option value="Aunt">Aunt</option>
                                <option value="Grandfather">Grandfather</option>
                                <option value="Grandmother">Grandmother</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label>Guardian Address *</label>
                            <textarea class="form-control" name="gaddress" rows="2" required></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Guardian City *</label>
                            <input type="text" class="form-control" name="gcity" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Guardian ZIP *</label>
                            <input type="text" class="form-control" name="gzip" required>
                        </div>
                    </div>

                    <h6 class="mb-3 mt-4">Previous School Information</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Previous School</label>
                            <input type="text" class="form-control" name="previous_school">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>TC Number</label>
                            <input type="text" class="form-control" name="tc_number">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitAdmission()">Submit Application</button>
            </div>
        </div>
    </div>
</div>

<script>
// Load pending admissions
function loadAdmissions() {
    $.ajax({
        url: '../assets/manageStudentAdmission.php',
        type: 'POST',
        data: { action: 'fetch_pending_admissions' },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                let tbody = $('#admissionsTableBody');
                tbody.empty();
                
                response.data.forEach(function(admission) {
                    let statusBadge = `<span class="badge-status badge-${admission.admission_status}">${admission.admission_status}</span>`;
                    
                    tbody.append(`
                        <tr>
                            <td>${admission.application_no}</td>
                            <td>${admission.fname} ${admission.lname}</td>
                            <td>${admission.father}</td>
                            <td>${admission.admission_for_class}</td>
                            <td>${admission.application_date}</td>
                            <td>${statusBadge}</td>
                            <td>
                                <button class="btn btn-sm btn-success" onclick="approveAdmission('${admission.student_id}')">
                                    <i class='bx bx-check'></i> Approve
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="rejectAdmission('${admission.student_id}')">
                                    <i class='bx bx-x'></i> Reject
                                </button>
                            </td>
                        </tr>
                    `);
                });
            }
        }
    });
}

// Submit new admission
function submitAdmission() {
    // Validate form before submission
    let form = document.getElementById('admissionForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return false;
    }
    
    let formData = new FormData($('#admissionForm')[0]);
    
    $.ajax({
        url: '../assets/manageStudentAdmission.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            alert(response.message);
            if (response.status === 'success') {
                $('#newAdmissionModal').modal('hide');
                $('#admissionForm')[0].reset();
                loadAdmissions();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            console.error('Response:', xhr.responseText);
            alert('Error submitting form. Please check the console for details.');
        }
    });
}

// Approve admission
function approveAdmission(studentId) {
    if (confirm('Approve this admission?')) {
        $.ajax({
            url: '../assets/manageStudentAdmission.php',
            type: 'POST',
            data: {
                action: 'update_admission_status',
                student_id: studentId,
                admission_status: 'approved',
                approved_by: '<?php echo $_SESSION['id']; ?>'
            },
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                loadAdmissions();
            }
        });
    }
}

// Initialize
$(document).ready(function() {
    loadAdmissions();
    loadEnrollments();
    loadPromotions();
    loadTransfers();
});

// Load enrollments
function loadEnrollments() {
    let academicYear = $('#enrollmentAcademicYear').val();
    let classFilter = $('#enrollmentClass').val();
    
    $.ajax({
        url: '../assets/fetchStudents.php',
        type: 'POST',
        data: { 
            action: 'fetch_all',
            class: classFilter 
        },
        dataType: 'json',
        success: function(response) {
            if (response && Array.isArray(response)) {
                let tbody = $('#enrollmentsTableBody');
                tbody.empty();
                
                response.forEach(function(student) {
                    let statusBadge = `<span class="badge-status badge-active">Active</span>`;
                    
                    tbody.append(`
                        <tr>
                            <td>${student.fname} ${student.lname}</td>
                            <td>-</td>
                            <td>${student.class}</td>
                            <td>${student.section}</td>
                            <td>${academicYear}</td>
                            <td>-</td>
                            <td>${statusBadge}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="viewStudent('${student.id}')">
                                    <i class='bx bx-show'></i> View
                                </button>
                            </td>
                        </tr>
                    `);
                });
            }
        }
    });
}

// Load promotions
function loadPromotions() {
    $.ajax({
        url: '../assets/manageStudentPromotions.php',
        type: 'POST',
        data: { action: 'fetch_promotions' },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                let tbody = $('#promotionsTableBody');
                tbody.empty();
                
                if (response.data.length === 0) {
                    tbody.append(`
                        <tr>
                            <td colspan="7" class="text-center">No promotions found</td>
                        </tr>
                    `);
                    return;
                }
                
                response.data.forEach(function(promotion) {
                    let statusBadge = `<span class="badge-status badge-${promotion.promotion_status}">${promotion.promotion_status}</span>`;
                    
                    tbody.append(`
                        <tr>
                            <td>${promotion.fname} ${promotion.lname}</td>
                            <td>${promotion.from_class}</td>
                            <td>${promotion.to_class}</td>
                            <td>${promotion.academic_year_to}</td>
                            <td>${promotion.promotion_type}</td>
                            <td>${statusBadge}</td>
                            <td>
                                ${promotion.promotion_status === 'pending' ? `
                                    <button class="btn btn-sm btn-success" onclick="approvePromotion(${promotion.promotion_id})">
                                        <i class='bx bx-check'></i> Approve
                                    </button>
                                ` : ''}
                            </td>
                        </tr>
                    `);
                });
            }
        },
        error: function() {
            $('#promotionsTableBody').html(`
                <tr><td colspan="7" class="text-center">Unable to load promotions. Please run database migration script.</td></tr>
            `);
        }
    });
}

// Load transfers
function loadTransfers(type = '') {
    $.ajax({
        url: '../assets/manageStudentTransfers.php',
        type: 'POST',
        data: { 
            action: 'fetch_transfers',
            type: type 
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                let tbody = $('#transfersTableBody');
                tbody.empty();
                
                if (response.data.length === 0) {
                    tbody.append(`
                        <tr>
                            <td colspan="7" class="text-center">No transfers found</td>
                        </tr>
                    `);
                    return;
                }
                
                response.data.forEach(function(transfer) {
                    let statusBadge = `<span class="badge-status badge-${transfer.transfer_status}">${transfer.transfer_status}</span>`;
                    let tcStatus = transfer.tc_issued ? '<i class="bx bx-check text-success"></i> Issued' : '<i class="bx bx-x text-danger"></i> Pending';
                    let fromTo = transfer.transfer_type === 'outgoing' ? transfer.to_school : transfer.from_school;
                    
                    tbody.append(`
                        <tr>
                            <td>${transfer.fname} ${transfer.lname}</td>
                            <td>${transfer.transfer_type}</td>
                            <td>${fromTo || '-'}</td>
                            <td>${transfer.transfer_date}</td>
                            <td>${tcStatus}</td>
                            <td>${statusBadge}</td>
                            <td>
                                ${transfer.transfer_status === 'requested' ? `
                                    <button class="btn btn-sm btn-success" onclick="approveTransfer(${transfer.transfer_id})">
                                        <i class='bx bx-check'></i> Approve
                                    </button>
                                ` : ''}
                            </td>
                        </tr>
                    `);
                });
            }
        },
        error: function() {
            $('#transfersTableBody').html(`
                <tr><td colspan="7" class="text-center">Unable to load transfers. Please run database migration script.</td></tr>
            `);
        }
    });
}

// Approve promotion
function approvePromotion(promotionId) {
    if (confirm('Approve this promotion?')) {
        $.ajax({
            url: '../assets/manageStudentPromotions.php',
            type: 'POST',
            data: {
                action: 'approve_promotion',
                promotion_id: promotionId,
                approved_by: '<?php echo $_SESSION['id']; ?>'
            },
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                if (response.status === 'success') {
                    loadPromotions();
                }
            }
        });
    }
}

// Approve transfer
function approveTransfer(transferId) {
    let tcNumber = prompt('Enter TC Number:');
    if (tcNumber) {
        $.ajax({
            url: '../assets/manageStudentTransfers.php',
            type: 'POST',
            data: {
                action: 'approve_transfer',
                transfer_id: transferId,
                tc_number: tcNumber,
                approved_by: '<?php echo $_SESSION['id']; ?>'
            },
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                if (response.status === 'success') {
                    loadTransfers();
                }
            }
        });
    }
}
</script>

<?php include('partials/_footer.php') ?>

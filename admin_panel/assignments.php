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
                <h1>Assignment Management</h1>
                <ul class="breadcrumb">
                    <li><a>Academic / Assignments</a></li>
                </ul>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAssignmentModal">
                <i class='bx bx-plus'></i> Create Assignment
            </button>
        </div>

        <!-- Assignment Statistics -->
        <ul class="insights">
            <li>
                <i class='bx bx-file'></i>
                <span class="info">
                    <h3 class="text-center" id="totalAssignments">0</h3>
                    <p>Total Assignments</p>
                </span>
            </li>
            <li>
                <i class='bx bx-time'></i>
                <span class="info">
                    <h3 class="text-center" id="activeAssignments">0</h3>
                    <p>Active</p>
                </span>
            </li>
            <li>
                <i class='bx bx-upload'></i>
                <span class="info">
                    <h3 class="text-center" id="totalSubmissions">0</h3>
                    <p>Submissions</p>
                </span>
            </li>
            <li>
                <i class='bx bx-edit'></i>
                <span class="info">
                    <h3 class="text-center" id="pendingGrading">0</h3>
                    <p>Pending Grading</p>
                </span>
            </li>
        </ul>

        <!-- Assignment List -->
        <div class="bottom-data">
            <div class="orders" style="width: 100%;">
                <div class="header">
                    <i class='bx bx-list-ul'></i>
                    <h3>All Assignments</h3>
                    <select class="form-control" style="max-width: 200px;" id="filterStatus">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Assignment Title</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Due Date</th>
                            <th>Submissions</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="assignmentsList">
                        <tr>
                            <td colspan="7" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Create Assignment Modal -->
<div class="modal fade" id="createAssignmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class='bx bx-plus-circle'></i> Create New Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createAssignmentForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Assignment Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Class <span class="text-danger">*</span></label>
                            <select class="form-control" name="class" required>
                                <?php include('partials/select_classes.php') ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Section</label>
                            <select class="form-control" name="section">
                                <option value="ALL">All Sections</option>
                                <?php include('partials/selelct_section.php') ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Assignment Type</label>
                            <select class="form-control" name="assignment_type">
                                <option value="homework">Homework</option>
                                <option value="project">Project</option>
                                <option value="lab">Lab Assignment</option>
                                <option value="essay">Essay</option>
                                <option value="presentation">Presentation</option>
                                <option value="quiz">Quiz</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Max Marks</label>
                            <input type="number" class="form-control" name="max_marks" value="100">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Course Code</label>
                            <input type="text" class="form-control" name="course_code" placeholder="e.g., ICT101">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Assigned Date</label>
                            <input type="date" class="form-control" name="assigned_date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Due Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="due_date" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Brief description of the assignment"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Instructions</label>
                        <textarea class="form-control" name="instructions" rows="4" placeholder="Detailed instructions for students"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class='bx bx-paperclip'></i> Attach Reference Files (Optional)
                        </label>
                        <input type="file" class="form-control" name="attachments[]" id="assignmentAttachments" multiple 
                               accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.zip">
                        <small class="text-muted">You can upload reference materials, templates, or sample files for students. Multiple files allowed.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Academic Year</label>
                            <input type="text" class="form-control" name="academic_year" value="<?php echo date('Y'); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Semester</label>
                            <select class="form-control" name="semester">
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status">
                                <option value="published">Publish Now</option>
                                <option value="draft">Save as Draft</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="late_submission_allowed" value="1" checked>
                        <label class="form-check-label">Allow Late Submission</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveAssignmentBtn">
                    <i class='bx bx-save'></i> Create Assignment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Assignment Details Modal -->
<div class="modal fade" id="viewAssignmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignmentTitle">Assignment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="assignmentDetails">
                <p class="text-center">Loading...</p>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/assignments_admin.js"></script>

<?php include('partials/_footer.php') ?>

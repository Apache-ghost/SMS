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

<?php include('partials/_footer.php') ?>

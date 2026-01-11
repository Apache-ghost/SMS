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
                <h1>My Assignments</h1>
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
                    <h3 class="text-center" id="myAssignments">0</h3>
                    <p>My Assignments</p>
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
                <i class='bx bx-time'></i>
                <span class="info">
                    <h3 class="text-center" id="pendingGrading">0</h3>
                    <p>Pending Grading</p>
                </span>
            </li>
            <li>
                <i class='bx bx-check-circle'></i>
                <span class="info">
                    <h3 class="text-center" id="gradedCount">0</h3>
                    <p>Graded</p>
                </span>
            </li>
        </ul>

        <!-- Assignment List -->
        <div class="bottom-data">
            <div class="orders" style="width: 100%;">
                <div class="header">
                    <i class='bx bx-list-ul'></i>
                    <h3>My Assignments</h3>
                    <select class="form-control" style="max-width: 200px;" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
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

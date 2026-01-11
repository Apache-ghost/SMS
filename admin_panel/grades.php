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
                        <label>Class</label>
                        <select class="form-control" id="gradeClass">
                            <option value="">Select Class</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Subject</label>
                        <select class="form-control" id="gradeSubject">
                            <option value="">Select Subject</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Assessment Type</label>
                        <select class="form-control" id="assessmentType">
                            <option value="">Select Type</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <button class="btn btn-primary form-control" onclick="loadStudentsForGrading()">Load Students</button>
                    </div>
                </div>
                <div id="gradeEntryTable"></div>
            </div>
        </div>
    </main>
</div>

<?php include('partials/_footer.php') ?>

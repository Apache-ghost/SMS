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
                <h1>Grade Entry</h1>
                <ul class="breadcrumb">
                    <li><a>Academic / Grades</a></li>
                </ul>
            </div>
        </div>

        <!-- Statistics -->
        <ul class="insights">
            <li>
                <i class='bx bx-edit'></i>
                <span class="info">
                    <h3 class="text-center" id="pendingGrades">0</h3>
                    <p>Pending Entry</p>
                </span>
            </li>
            <li>
                <i class='bx bx-check'></i>
                <span class="info">
                    <h3 class="text-center" id="completedGrades">0</h3>
                    <p>Completed</p>
                </span>
            </li>
            <li>
                <i class='bx bx-book'></i>
                <span class="info">
                    <h3 class="text-center" id="mySubjects">0</h3>
                    <p>My Subjects</p>
                </span>
            </li>
            <li>
                <i class='bx bx-line-chart'></i>
                <span class="info">
                    <h3 class="text-center" id="classAverage">0</h3>
                    <p>Class Average</p>
                </span>
            </li>
        </ul>

        <!-- Grade Entry Form -->
        <div class="bottom-data">
            <div class="orders" style="width: 100%;">
                <div class="header">
                    <i class='bx bx-edit'></i>
                    <h3>Enter Grades</h3>
                </div>
                <div class="row p-3">
                    <div class="col-md-4">
                        <label>Class</label>
                        <select class="form-control" id="selectClass">
                            <option value="">Select Class</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Subject</label>
                        <select class="form-control" id="selectSubject">
                            <option value="">Select Subject</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Assessment Type</label>
                        <select class="form-control" id="selectAssessment">
                            <option value="">Select Assessment</option>
                        </select>
                    </div>
                    <div class="col-md-12 mt-3">
                        <button class="btn btn-primary" onclick="loadStudentsForGrades()">Load Students</button>
                    </div>
                </div>
                <div id="gradeEntrySection" class="p-3"></div>
            </div>
        </div>
    </main>
</div>

<?php include('partials/_footer.php') ?>

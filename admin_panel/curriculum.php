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
    </main>
</div>

<?php include('partials/_footer.php') ?>

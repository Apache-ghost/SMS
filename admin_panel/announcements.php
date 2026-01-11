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
                <h1>Announcements</h1>
                <ul class="breadcrumb">
                    <li><a>Communication / Announcements</a></li>
                </ul>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAnnouncementModal">
                <i class='bx bx-plus'></i> Create Announcement
            </button>
        </div>

        <!-- Announcement Stats -->
        <ul class="insights">
            <li>
                <i class='bx bx-bullhorn'></i>
                <span class="info">
                    <h3 class="text-center" id="totalAnnouncements">0</h3>
                    <p>Total</p>
                </span>
            </li>
            <li>
                <i class='bx bx-show'></i>
                <span class="info">
                    <h3 class="text-center" id="publishedAnnouncements">0</h3>
                    <p>Published</p>
                </span>
            </li>
            <li>
                <i class='bx bx-user'></i>
                <span class="info">
                    <h3 class="text-center" id="totalReaders">0</h3>
                    <p>Total Readers</p>
                </span>
            </li>
            <li>
                <i class='bx bx-pin'></i>
                <span class="info">
                    <h3 class="text-center" id="pinnedCount">0</h3>
                    <p>Pinned</p>
                </span>
            </li>
        </ul>

        <!-- Announcement List -->
        <div class="bottom-data">
            <div class="orders" style="width: 100%;">
                <div class="header">
                    <i class='bx bx-list-ul'></i>
                    <h3>All Announcements</h3>
                    <select class="form-control" style="max-width: 200px;" id="filterAudienceType">
                        <option value="">All Audience</option>
                        <option value="all">All</option>
                        <option value="students">Students</option>
                        <option value="parents">Parents</option>
                        <option value="teachers">Teachers</option>
                    </select>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Audience</th>
                            <th>Display Period</th>
                            <th>Views</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="announcementsList">
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

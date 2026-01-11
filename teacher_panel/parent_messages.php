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
                <h1>Parent Messages</h1>
                <ul class="breadcrumb">
                    <li><a>Communication / Messages</a></li>
                </ul>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#composeMessageModal">
                <i class='bx bx-message-add'></i> Compose Message
            </button>
        </div>

        <!-- Message Statistics -->
        <ul class="insights">
            <li onclick="filterMessages('all')">
                <i class='bx bx-envelope'></i>
                <span class="info">
                    <h3 class="text-center" id="totalMessages">0</h3>
                    <p>Total Messages</p>
                </span>
            </li>
            <li onclick="filterMessages('unread')">
                <i class='bx bx-envelope-open'></i>
                <span class="info">
                    <h3 class="text-center" id="unreadMessages">0</h3>
                    <p>Unread</p>
                </span>
            </li>
            <li onclick="filterMessages('starred')">
                <i class='bx bx-star'></i>
                <span class="info">
                    <h3 class="text-center" id="starredMessages">0</h3>
                    <p>Starred</p>
                </span>
            </li>
            <li onclick="filterMessages('urgent')">
                <i class='bx bx-error-circle'></i>
                <span class="info">
                    <h3 class="text-center" id="urgentMessages">0</h3>
                    <p>Urgent</p>
                </span>
            </li>
        </ul>

        <!-- Messages List -->
        <div class="bottom-data">
            <div class="orders" style="width: 100%;">
                <div class="header">
                    <i class='bx bx-message-dots'></i>
                    <h3>Messages</h3>
                    <input type="text" placeholder="Search messages..." class="form-control" style="max-width: 300px;" id="searchMessages">
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>From/To</th>
                            <th>Subject</th>
                            <th>Student</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="messagesList">
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

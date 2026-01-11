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
                <h1>Parent Portal Management</h1>
                <ul class="breadcrumb">
                    <li><a>Communication / Parent Portal</a></li>
                </ul>
            </div>
            <button class="btn btn-primary" onclick="syncParents()">
                <i class='bx bx-sync'></i> Sync Parents
            </button>
        </div>

        <!-- Portal Stats -->
        <ul class="insights">
            <li>
                <i class='bx bx-group'></i>
                <span class="info">
                    <h3 class="text-center" id="totalParents">0</h3>
                    <p>Total Parents</p>
                </span>
            </li>
            <li>
                <i class='bx bx-check-circle'></i>
                <span class="info">
                    <h3 class="text-center" id="activeAccounts">0</h3>
                    <p>Active Accounts</p>
                </span>
            </li>
            <li>
                <i class='bx bx-log-in'></i>
                <span class="info">
                    <h3 class="text-center" id="loginsToday">0</h3>
                    <p>Logins Today</p>
                </span>
            </li>
            <li>
                <i class='bx bx-message'></i>
                <span class="info">
                    <h3 class="text-center" id="totalMessages">0</h3>
                    <p>Messages</p>
                </span>
            </li>
        </ul>

        <!-- Parent List -->
        <div class="bottom-data">
            <div class="orders" style="width: 100%;">
                <div class="header">
                    <i class='bx bx-list-ul'></i>
                    <h3>Parent Portal Accounts</h3>
                    <input type="text" placeholder="Search parents..." class="form-control" style="max-width: 300px;">
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Parent Name</th>
                            <th>Email</th>
                            <th>Children</th>
                            <th>Last Login</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="parentsList">
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

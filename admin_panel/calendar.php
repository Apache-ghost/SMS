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
                <h1>School Calendar</h1>
                <ul class="breadcrumb">
                    <li><a>Administration / Calendar</a></li>
                </ul>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                <i class='bx bx-plus'></i> Add Event
            </button>
        </div>

        <!-- Calendar View -->
        <div class="bottom-data">
            <div class="orders" style="width: 100%;">
                <div class="header">
                    <i class='bx bx-calendar'></i>
                    <h3>Events & Holidays</h3>
                    <div>
                        <button class="btn btn-sm btn-outline-primary me-2" onclick="changeView('month')">Month</button>
                        <button class="btn btn-sm btn-outline-primary me-2" onclick="changeView('week')">Week</button>
                        <button class="btn btn-sm btn-outline-primary" onclick="changeView('list')">List</button>
                    </div>
                </div>
                <div id="calendarView" class="p-3">
                    <p class="text-center">Calendar loading...</p>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include('partials/_footer.php') ?>

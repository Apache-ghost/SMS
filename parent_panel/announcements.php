<?php include('partials/_header.php') ?>

<!-- Sidebar -->
<?php include('partials/_sidebar.php') ?>

<div class="content">
    <?php include("partials/_navbar.php"); ?>

    <main>
        <div class="header">
            <div class="left">
                <h1>School Announcements</h1>
                <ul class="breadcrumb">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active">Announcements</a></li>
                </ul>
            </div>
        </div>

        <div class="bottom-data">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bx-bookmark'></i>
                    <h3>Latest Announcements</h3>
                </div>
                
                <div id="announcementsContent" class="p-3">
                    <div class="text-center">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2">Loading announcements...</p>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadAnnouncements();
});

function loadAnnouncements() {
    fetch('../assets/fetchNotices.php')
    .then(response => response.json())
    .then(data => {
        const content = document.getElementById('announcementsContent');
        
        if (data && data.length > 0) {
            let html = '';
            
            data.forEach(notice => {
                html += `
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title">${notice.title}</h5>
                                <span class="badge bg-primary">${notice.date}</span>
                            </div>
                            <p class="card-text mt-2">${notice.content}</p>
                            ${notice.attachment ? `<a href="../noticeUploads/${notice.attachment}" class="btn btn-sm btn-outline-primary" target="_blank"><i class='bx bx-download'></i> Download Attachment</a>` : ''}
                        </div>
                    </div>
                `;
            });
            
            content.innerHTML = html;
        } else {
            content.innerHTML = `
                <div class="text-center text-muted">
                    <i class='bx bx-bookmark-minus' style='font-size: 60px;'></i>
                    <p class="mt-2">No announcements available</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('announcementsContent').innerHTML = '<p class="text-center text-danger">Error loading announcements</p>';
    });
}
</script>

<style>
.full-width { width: 100%; }
.card { border-radius: 10px; transition: transform 0.2s; }
.card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important; }
</style>

<?php include('partials/_footer.php') ?>

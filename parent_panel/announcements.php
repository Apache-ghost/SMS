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
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_parent_announcements'
    })
    .then(response => response.json())
    .then(data => {
        const content = document.getElementById('announcementsContent');
        
        if (data.status === 'success' && data.announcements && data.announcements.length > 0) {
            displayAnnouncements(data.announcements);
        } else {
            content.innerHTML = `
                <div class="alert alert-info text-center">
                    <i class='bx bx-bookmark-minus' style='font-size: 60px;'></i>
                    <p class="mt-3">No announcements available at this time</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading announcements:', error);
        document.getElementById('announcementsContent').innerHTML = 
            '<div class="alert alert-danger">Error loading announcements. Please try again.</div>';
    });
}

function displayAnnouncements(announcements) {
    const content = document.getElementById('announcementsContent');
    
    let html = '';
    
    announcements.forEach(announcement => {
        const priorityClass = getPriorityClass(announcement.priority);
        const priorityIcon = getPriorityIcon(announcement.priority);
        const timeAgo = getTimeAgo(announcement.created_at);
        
        html += `
            <div class="card mb-3 shadow-sm announcement-card" data-id="${announcement.announcement_id}">
                <div class="card-header bg-${priorityClass} text-white d-flex justify-content-between align-items-center">
                    <div>
                        <i class='bx ${priorityIcon} me-2'></i>
                        <strong>${announcement.title}</strong>
                    </div>
                    <div>
                        <span class="badge bg-light text-dark">${announcement.priority || 'Normal'}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="announcement-content">
                        ${announcement.content}
                    </div>
                    
                    ${announcement.attachment_path ? `
                        <div class="mt-3">
                            <a href="../announcementUploads/${announcement.attachment_path}" 
                               class="btn btn-sm btn-outline-primary" 
                               target="_blank" 
                               download>
                                <i class='bx bx-download'></i> Download Attachment
                            </a>
                        </div>
                    ` : ''}
                    
                    <div class="mt-3 d-flex justify-content-between align-items-center text-muted">
                        <small>
                            <i class='bx bx-calendar'></i> Published: ${formatDate(announcement.created_at)}
                        </small>
                        <small>
                            <i class='bx bx-time'></i> ${timeAgo}
                        </small>
                    </div>
                    
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class='bx bx-target-lock'></i> Audience: ${getAudienceText(announcement.target_audience)}
                        </small>
                    </div>
                </div>
            </div>
        `;
    });
    
    content.innerHTML = html;
}

function getPriorityClass(priority) {
    switch(priority) {
        case 'high': return 'danger';
        case 'medium': return 'warning';
        case 'low': return 'info';
        default: return 'primary';
    }
}

function getPriorityIcon(priority) {
    switch(priority) {
        case 'high': return 'bx-error-circle';
        case 'medium': return 'bx-info-circle';
        case 'low': return 'bx-check-circle';
        default: return 'bx-bookmark';
    }
}

function getTimeAgo(datetime) {
    const date = new Date(datetime);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'Just now';
    if (seconds < 3600) return Math.floor(seconds / 60) + ' minutes ago';
    if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
    if (seconds < 604800) return Math.floor(seconds / 86400) + ' days ago';
    return date.toLocaleDateString();
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getAudienceText(audience) {
    if (audience === 'all') return 'All Parents & Students';
    if (audience === 'parents') return 'Parents Only';
    return audience.replace(/_/g, ' ').toUpperCase();
}
</script>

<style>
.full-width { width: 100%; }
.announcement-card { 
    border-radius: 10px; 
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
}
.announcement-card:hover { 
    transform: translateY(-3px); 
    box-shadow: 0 5px 20px rgba(0,0,0,0.15) !important; 
}
.announcement-content {
    line-height: 1.6;
    color: #333;
}
.card-header {
    font-size: 1.1rem;
}
</style>

<?php include('partials/_footer.php') ?>

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

<!-- Create Announcement Modal -->
<div class="modal fade" id="createAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createAnnouncementForm">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content *</label>
                        <textarea class="form-control" name="content" rows="5" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type *</label>
                            <select class="form-control" name="announcement_type" required>
                                <option value="general">General</option>
                                <option value="academic">Academic</option>
                                <option value="event">Event</option>
                                <option value="holiday">Holiday</option>
                                <option value="exam">Exam</option>
                                <option value="fee">Fee</option>
                                <option value="emergency">Emergency</option>
                                <option value="achievement">Achievement</option>
                                <option value="sports">Sports</option>
                                <option value="cultural">Cultural</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority *</label>
                            <select class="form-control" name="priority" required>
                                <option value="low">Low</option>
                                <option value="normal" selected>Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Target Audience *</label>
                            <select class="form-control" name="target_audience" id="targetAudience" required>
                                <option value="all">All</option>
                                <option value="students">Students</option>
                                <option value="parents">Parents</option>
                                <option value="teachers">Teachers</option>
                                <option value="class_specific">Specific Class</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3" id="classField" style="display:none;">
                            <label class="form-label">Class</label>
                            <input type="number" class="form-control" name="class" min="1" max="12">
                        </div>
                        <div class="col-md-3 mb-3" id="sectionField" style="display:none;">
                            <label class="form-label">Section</label>
                            <input type="text" class="form-control" name="section" maxlength="10">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Display From *</label>
                            <input type="datetime-local" class="form-control" name="display_from" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Display Until *</label>
                            <input type="datetime-local" class="form-control" name="display_until" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">External Link (optional)</label>
                        <input type="url" class="form-control" name="external_link" placeholder="https://...">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_pinned" value="1">
                        <label class="form-check-label">Pin this announcement</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="allow_comments" value="1">
                        <label class="form-check-label">Allow comments</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select class="form-control" name="status" required>
                            <option value="draft">Save as Draft</option>
                            <option value="published" selected>Publish Now</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createAnnouncement()">Create Announcement</button>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide class and section fields based on target audience
document.getElementById('targetAudience').addEventListener('change', function() {
    const classField = document.getElementById('classField');
    const sectionField = document.getElementById('sectionField');
    
    if (this.value === 'class_specific') {
        classField.style.display = 'block';
        sectionField.style.display = 'block';
    } else {
        classField.style.display = 'none';
        sectionField.style.display = 'none';
    }
});

// Load announcements on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAnnouncements();
    loadAnnouncementStats();
});

// Filter announcements by audience type
document.getElementById('filterAudienceType').addEventListener('change', function() {
    loadAnnouncements();
});

function loadAnnouncements() {
    const filterAudience = document.getElementById('filterAudienceType').value;
    
    const formData = new FormData();
    formData.append('action', 'get_announcements');
    if (filterAudience) {
        formData.append('target_audience', filterAudience);
    }
    
    fetch('../assets/manageAnnouncements.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        console.log('Load announcements response:', text);
        try {
            const data = JSON.parse(text);
            if (data.status === 'success') {
                displayAnnouncements(data.data);
            } else {
                document.getElementById('announcementsList').innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error: ' + (data.message || 'Unknown error') + '</td></tr>';
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            console.error('Response text:', text);
            document.getElementById('announcementsList').innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error: Invalid response from server</td></tr>';
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        document.getElementById('announcementsList').innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading announcements: ' + error.message + '</td></tr>';
    });
}

function displayAnnouncements(announcements) {
    const tbody = document.getElementById('announcementsList');
    
    if (announcements.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No announcements found</td></tr>';
        return;
    }
    
    let html = '';
    announcements.forEach(announcement => {
        const isPinned = announcement.is_pinned == 1;
        const isExpired = announcement.is_expired;
        
        let statusBadge = '';
        if (announcement.status === 'published') {
            statusBadge = '<span class="badge bg-success">Published</span>';
        } else if (announcement.status === 'draft') {
            statusBadge = '<span class="badge bg-warning">Draft</span>';
        } else {
            statusBadge = '<span class="badge bg-secondary">Archived</span>';
        }
        
        if (isExpired) {
            statusBadge += ' <span class="badge bg-danger">Expired</span>';
        }
        
        html += `
            <tr>
                <td>
                    ${isPinned ? '<i class="bx bx-pin" style="color: red;"></i> ' : ''}
                    <strong>${announcement.title}</strong>
                    <br><small class="text-muted">${announcement.announcement_type}</small>
                </td>
                <td>
                    <span class="badge ${getPriorityClass(announcement.priority)}">${announcement.priority}</span>
                </td>
                <td>${announcement.target_audience}</td>
                <td>
                    <small>${formatDate(announcement.display_from)}</small>
                    <br>to<br>
                    <small>${formatDate(announcement.display_until)}</small>
                </td>
                <td>${announcement.view_count || 0}</td>
                <td>${statusBadge}</td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="viewAnnouncement(${announcement.announcement_id})">
                        <i class="bx bx-show"></i>
                    </button>
                    ${announcement.status === 'draft' ? `
                        <button class="btn btn-sm btn-success" onclick="publishAnnouncement(${announcement.announcement_id})">
                            <i class="bx bx-send"></i>
                        </button>
                    ` : ''}
                    <button class="btn btn-sm btn-danger" onclick="deleteAnnouncement(${announcement.announcement_id})">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function getPriorityClass(priority) {
    switch(priority) {
        case 'urgent': return 'bg-danger';
        case 'high': return 'bg-warning';
        case 'normal': return 'bg-info';
        case 'low': return 'bg-secondary';
        default: return 'bg-secondary';
    }
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', { 
        month: 'short', 
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function loadAnnouncementStats() {
    const formData = new FormData();
    formData.append('action', 'get_announcements');
    
    fetch('../assets/manageAnnouncements.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const announcements = data.data;
            const published = announcements.filter(a => a.status === 'published').length;
            const pinned = announcements.filter(a => a.is_pinned == 1).length;
            const totalReaders = announcements.reduce((sum, a) => sum + (parseInt(a.view_count) || 0), 0);
            
            document.getElementById('totalAnnouncements').textContent = announcements.length;
            document.getElementById('publishedAnnouncements').textContent = published;
            document.getElementById('totalReaders').textContent = totalReaders;
            document.getElementById('pinnedCount').textContent = pinned;
        }
    })
    .catch(error => console.error('Error:', error));
}

function createAnnouncement() {
    const form = document.getElementById('createAnnouncementForm');
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    formData.append('action', 'create_announcement');
    formData.append('published_by', '<?php echo $_SESSION["uid"]; ?>');
    
    // Show loading state
    const submitBtn = event.target;
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Creating...';
    
    fetch('../assets/manageAnnouncements.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        console.log('Raw response:', text);
        try {
            const data = JSON.parse(text);
            if (data.status === 'success') {
                alert('Announcement created successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            console.error('Response text:', text);
            alert('Error: Invalid response from server. Check console for details.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Error creating announcement: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function viewAnnouncement(id) {
    alert('View announcement ' + id + ' - Full view functionality can be added');
}

function publishAnnouncement(id) {
    if (!confirm('Are you sure you want to publish this announcement?')) return;
    
    const formData = new FormData();
    formData.append('action', 'publish_announcement');
    formData.append('announcement_id', id);
    
    fetch('../assets/manageAnnouncements.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Announcement published successfully!');
            loadAnnouncements();
            loadAnnouncementStats();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error publishing announcement');
    });
}

function deleteAnnouncement(id) {
    if (!confirm('Are you sure you want to delete this announcement?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete_announcement');
    formData.append('announcement_id', id);
    
    fetch('../assets/manageAnnouncements.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Announcement deleted successfully!');
            loadAnnouncements();
            loadAnnouncementStats();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting announcement');
    });
}
</script>

<?php include('partials/_footer.php') ?>

// Assignment Management for Admin
document.addEventListener('DOMContentLoaded', function() {
    loadStatistics();
    loadAssignments();

    // Create assignment button
    const saveBtn = document.getElementById('saveAssignmentBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', createAssignment);
    }

    // Filter status
    const filterStatus = document.getElementById('filterStatus');
    if (filterStatus) {
        filterStatus.addEventListener('change', loadAssignments);
    }
});

function loadStatistics() {
    fetch('../assets/manageAssignments.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_statistics'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('totalAssignments').textContent = data.total_assignments || 0;
            document.getElementById('activeAssignments').textContent = data.active_assignments || 0;
            document.getElementById('totalSubmissions').textContent = data.total_submissions || 0;
            document.getElementById('pendingGrading').textContent = data.pending_grading || 0;
        }
    })
    .catch(error => console.error('Error:', error));
}

function loadAssignments() {
    const filterStatus = document.getElementById('filterStatus').value;
    const tbody = document.getElementById('assignmentsList');
    
    tbody.innerHTML = '<tr><td colspan="7" class="text-center">Loading...</td></tr>';

    const formData = new FormData();
    formData.append('action', 'get_all_assignments');
    if (filterStatus) {
        formData.append('status_filter', filterStatus);
    }

    fetch('../assets/manageAssignments.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.assignments) {
            displayAssignments(data.assignments);
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No assignments found</td></tr>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading assignments</td></tr>';
    });
}

function displayAssignments(assignments) {
    const tbody = document.getElementById('assignmentsList');
    tbody.innerHTML = '';

    if (assignments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No assignments found</td></tr>';
        return;
    }

    assignments.forEach(assignment => {
        const dueDate = new Date(assignment.due_date);
        const now = new Date();
        const isOverdue = dueDate < now;
        
        const statusBadge = getStatusBadge(assignment.status);
        const dueDateClass = isOverdue ? 'text-danger' : '';
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <strong>${assignment.title}</strong><br>
                <small class="text-muted">${assignment.assignment_code || ''}</small>
            </td>
            <td>Level ${assignment.class} ${assignment.section || ''}</td>
            <td>${assignment.course_code || '-'}</td>
            <td class="${dueDateClass}">
                ${formatDateTime(assignment.due_date)}
                ${isOverdue ? '<br><small class="text-danger">⚠ Overdue</small>' : ''}
            </td>
            <td class="text-center">
                ${assignment.total_submissions || 0} / ${assignment.expected_count || '?'}
            </td>
            <td>${statusBadge}</td>
            <td>
                <button class="btn btn-sm btn-info" onclick="viewAssignment(${assignment.assignment_id})">
                    <i class='bx bx-show'></i>
                </button>
                <button class="btn btn-sm btn-success" onclick="viewSubmissions(${assignment.assignment_id})">
                    <i class='bx bx-file'></i>
                </button>
                ${assignment.status === 'draft' ? `
                <button class="btn btn-sm btn-primary" onclick="publishAssignment(${assignment.assignment_id})">
                    <i class='bx bx-send'></i>
                </button>
                ` : ''}
                <button class="btn btn-sm btn-danger" onclick="deleteAssignment(${assignment.assignment_id})">
                    <i class='bx bx-trash'></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function createAssignment() {
    const form = document.getElementById('createAssignmentForm');
    const formData = new FormData(form);
    
    // Add action
    formData.append('action', 'create_assignment');
    formData.append('assignment_code', 'ASG' + Date.now());
    formData.append('created_by', 'admin');
    formData.append('teacher_id', 'admin');

    const btn = document.getElementById('saveAssignmentBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Creating...';

    fetch('../assets/manageAssignments.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.status === 'success') {
                showNotification('Assignment created successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('createAssignmentModal')).hide();
                form.reset();
                loadAssignments();
                loadStatistics();
            } else {
                showNotification(data.message || 'Error creating assignment', 'error');
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            console.error('Response text:', text);
            showNotification('Server error: Invalid response', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to create assignment', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bx bx-save"></i> Create Assignment';
    });
}

function viewAssignment(assignmentId) {
    const modal = new bootstrap.Modal(document.getElementById('viewAssignmentModal'));
    const detailsDiv = document.getElementById('assignmentDetails');
    
    detailsDiv.innerHTML = '<p class="text-center">Loading...</p>';
    modal.show();

    const formData = new FormData();
    formData.append('action', 'get_assignment_details');
    formData.append('assignment_id', assignmentId);

    fetch('../assets/manageAssignments.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.assignment) {
            const assignment = data.assignment;
            document.getElementById('assignmentTitle').textContent = assignment.title;
            
            detailsDiv.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Assignment Code:</strong> ${assignment.assignment_code || 'N/A'}</p>
                        <p><strong>Class:</strong> Level ${assignment.class} ${assignment.section || ''}</p>
                        <p><strong>Course:</strong> ${assignment.course_code || 'N/A'}</p>
                        <p><strong>Type:</strong> ${assignment.assignment_type || 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Max Marks:</strong> ${assignment.max_marks || 100}</p>
                        <p><strong>Assigned:</strong> ${formatDate(assignment.assigned_date)}</p>
                        <p><strong>Due:</strong> ${formatDateTime(assignment.due_date)}</p>
                        <p><strong>Status:</strong> ${getStatusBadge(assignment.status)}</p>
                    </div>
                </div>
                <hr>
                <h6>Description:</h6>
                <p>${assignment.description || 'No description'}</p>
                <h6>Instructions:</h6>
                <p>${assignment.instructions || 'No instructions'}</p>
                ${assignment.attachments && assignment.attachments.length > 0 ? `
                <hr>
                <h6>Reference Files:</h6>
                <ul class="list-group">
                    ${assignment.attachments.map(att => `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class='bx bx-file'></i> ${att.file_name} (${formatFileSize(att.file_size)})</span>
                            <a href="../assignmentUploads/${att.file_path}" class="btn btn-sm btn-primary" download>
                                <i class='bx bx-download'></i> Download
                            </a>
                        </li>
                    `).join('')}
                </ul>
                ` : ''}
                <hr>
                <p><strong>Submissions:</strong> ${assignment.total_submissions || 0}</p>
                <p><strong>Graded:</strong> ${assignment.graded_submissions || 0}</p>
            `;
        } else {
            detailsDiv.innerHTML = '<p class="text-center text-danger">Error loading assignment details</p>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        detailsDiv.innerHTML = '<p class="text-center text-danger">Failed to load details</p>';
    });
}

function publishAssignment(assignmentId) {
    if (!confirm('Publish this assignment? Students will be able to see it.')) return;

    const formData = new FormData();
    formData.append('action', 'update_assignment_status');
    formData.append('assignment_id', assignmentId);
    formData.append('status', 'published');

    fetch('../assets/manageAssignments.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showNotification('Assignment published successfully!', 'success');
            loadAssignments();
            loadStatistics();
        } else {
            showNotification(data.message || 'Error publishing assignment', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to publish assignment', 'error');
    });
}

function deleteAssignment(assignmentId) {
    if (!confirm('Delete this assignment? This cannot be undone.')) return;

    const formData = new FormData();
    formData.append('action', 'delete_assignment');
    formData.append('assignment_id', assignmentId);

    fetch('../assets/manageAssignments.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showNotification('Assignment deleted successfully!', 'success');
            loadAssignments();
            loadStatistics();
        } else {
            showNotification(data.message || 'Error deleting assignment', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to delete assignment', 'error');
    });
}

function viewSubmissions(assignmentId) {
    // Redirect to submissions page
    window.location.href = `grade_submissions.php?assignment_id=${assignmentId}`;
}

function getStatusBadge(status) {
    const badges = {
        'draft': '<span class="badge bg-secondary">Draft</span>',
        'published': '<span class="badge bg-success">Published</span>',
        'closed': '<span class="badge bg-danger">Closed</span>',
        'cancelled': '<span class="badge bg-warning">Cancelled</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    notification.style.position = 'fixed';
    notification.style.top = '80px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 4000);
}

function formatFileSize(bytes) {
    if (!bytes) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

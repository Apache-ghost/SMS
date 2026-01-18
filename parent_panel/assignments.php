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
                <h1>Assignments</h1>
                <ul class="breadcrumb">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active">Assignments</a></li>
                </ul>
            </div>
        </div>

        <!-- Select Child -->
        <div class="bottom-data">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bx-user'></i>
                    <h3>Select Child</h3>
                </div>
                <div class="p-3">
                    <select class="form-select" id="childSelect" onchange="loadAssignments()">
                        <option value="">Loading children...</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Assignments List -->
        <div class="bottom-data mt-3">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bx-file'></i>
                    <h3>Assignments</h3>
                </div>
                
                <div id="assignmentsContent" class="p-3">
                    <p class="text-center text-muted">Select a child to view assignments</p>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadChildren();
});

function loadChildren() {
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_children'
    })
    .then(response => response.json())
    .then(data => {
        const select = document.getElementById('childSelect');
        
        if (data.status === 'success' && data.children.length > 0) {
            let html = '<option value="" data-class="" data-section="">-- Select Child --</option>';
            
            data.children.forEach(child => {
                html += `<option value="${child.id}" data-class="${child.class}" data-section="${child.section}">${child.name} (Class ${child.class})</option>`;
            });
            
            select.innerHTML = html;
        } else {
            select.innerHTML = '<option value="">No children found</option>';
        }
    });
}

function loadAssignments() {
    const select = document.getElementById('childSelect');
    const selectedOption = select.options[select.selectedIndex];
    const studentClass = selectedOption.getAttribute('data-class');
    const section = selectedOption.getAttribute('data-section');
    const content = document.getElementById('assignmentsContent');
    
    if (!studentClass) {
        content.innerHTML = '<p class="text-center text-muted">Select a child to view assignments</p>';
        return;
    }
    
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Loading assignments...</p>
        </div>
    `;
    
    // Fetch assignments using parentPortalHandler
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=get_student_assignments&class=${studentClass}&section=${section}&student_id=${select.value}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.assignments && data.assignments.length > 0) {
            displayAssignments(data.assignments, studentClass, section);
        } else {
            content.innerHTML = `
                <div class="alert alert-info text-center">
                    <i class='bx bx-file' style='font-size: 60px;'></i>
                    <p class="mt-3">No assignments found for Class ${studentClass} - Section ${section}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading assignments:', error);
        content.innerHTML = '<div class="alert alert-danger">Error loading assignments. Please try again.</div>';
    });
}

function displayAssignments(assignments, className, section) {
    const content = document.getElementById('assignmentsContent');
    
    // Separate assignments by status
    const pending = assignments.filter(a => !a.submission_status && a.days_remaining >= 0);
    const overdue = assignments.filter(a => a.is_overdue && !a.submission_status);
    const submitted = assignments.filter(a => a.submission_status);
    
    let html = `
        <div class="alert alert-info mb-3">
            <i class='bx bx-info-circle'></i>
            Showing assignments for Class ${className} - Section ${section}
        </div>
    `;
    
    // Overdue Assignments
    if (overdue.length > 0) {
        html += `
            <div class="mb-4">
                <h5 class="text-danger"><i class='bx bx-error-circle'></i> Overdue Assignments (${overdue.length})</h5>
                <div class="row">
        `;
        
        overdue.forEach(assignment => {
            html += createAssignmentCard(assignment, 'danger');
        });
        
        html += '</div></div>';
    }
    
    // Pending Assignments
    if (pending.length > 0) {
        html += `
            <div class="mb-4">
                <h5 class="text-warning"><i class='bx bx-time'></i> Pending Assignments (${pending.length})</h5>
                <div class="row">
        `;
        
        pending.forEach(assignment => {
            const badgeClass = assignment.is_upcoming ? 'warning' : 'info';
            html += createAssignmentCard(assignment, badgeClass);
        });
        
        html += '</div></div>';
    }
    
    // Submitted Assignments
    if (submitted.length > 0) {
        html += `
            <div class="mb-4">
                <h5 class="text-success"><i class='bx bx-check-circle'></i> Submitted Assignments (${submitted.length})</h5>
                <div class="row">
        `;
        
        submitted.forEach(assignment => {
            html += createAssignmentCard(assignment, 'success');
        });
        
        html += '</div></div>';
    }
    
    if (pending.length === 0 && overdue.length === 0 && submitted.length === 0) {
        html += `
            <div class="alert alert-info text-center">
                <i class='bx bx-file' style='font-size: 60px;'></i>
                <p class="mt-3">No assignments found</p>
            </div>
        `;
    }
    
    content.innerHTML = html;
}

function createAssignmentCard(assignment, badgeClass) {
    const daysText = assignment.days_remaining > 0 
        ? `${assignment.days_remaining} days remaining` 
        : (assignment.days_remaining === 0 ? 'Due today' : `${Math.abs(assignment.days_remaining)} days overdue`);
    
    const statusText = assignment.submission_status 
        ? `Submitted on ${formatDate(assignment.submission_date)}` 
        : (assignment.is_overdue ? 'Overdue' : 'Pending');
    
    return `
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-header bg-${badgeClass} text-white">
                    <h6 class="mb-0">${assignment.subject}</h6>
                </div>
                <div class="card-body">
                    <h5 class="card-title">${assignment.title}</h5>
                    <p class="card-text text-muted">${assignment.description || 'No description provided'}</p>
                    
                    <div class="mb-2">
                        <small class="text-muted">
                            <i class='bx bx-calendar'></i> Due: ${formatDate(assignment.due_date)}
                        </small>
                        <br>
                        <small class="text-muted">
                            <i class='bx bx-time'></i> ${daysText}
                        </small>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="badge bg-${badgeClass}">${statusText}</span>
                        ${assignment.marks_obtained ? `<span class="badge bg-primary">Score: ${assignment.marks_obtained}/${assignment.max_marks || 100}</span>` : ''}
                    </div>
                    
                    ${assignment.feedback ? `
                        <div class="alert alert-info mt-2 mb-0">
                            <small><strong>Feedback:</strong> ${assignment.feedback}</small>
                        </div>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
}

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}
</script>

<style>
.full-width { width: 100%; }
.card { border: 1px solid #e0e0e0; border-radius: 8px; }
.card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
</style>

<?php include('partials/_footer.php') ?>

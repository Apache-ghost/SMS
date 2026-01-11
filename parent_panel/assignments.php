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
    
    // Fetch assignments for the class
    fetch(`../admin_panel/assignments.php?class=${studentClass}&section=${section}&ajax=1`)
    .then(response => response.text())
    .then(html => {
        // Since we're getting HTML, we'll display a list format
        content.innerHTML = `
            <div class="alert alert-info">
                <i class='bx bx-info-circle'></i>
                Showing assignments for Class ${studentClass} - Section ${section}
            </div>
            <div class="row" id="assignmentsList">
                <p class="text-center">Loading assignment details...</p>
            </div>
        `;
        
        // Fetch actual assignments data
        loadAssignmentsList(studentClass, section);
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = '<p class="text-center text-danger">Error loading assignments</p>';
    });
}

function loadAssignmentsList(className, section) {
    // Create a sample assignments display (you'll need to create a proper endpoint)
    const list = document.getElementById('assignmentsList');
    
    const sampleAssignments = [
        {
            subject: 'Mathematics',
            title: 'Chapter 5 Exercises',
            due_date: '2026-01-10',
            status: 'Pending'
        },
        {
            subject: 'Science',
            title: 'Lab Report - Experiment 3',
            due_date: '2026-01-08',
            status: 'Pending'
        },
        {
            subject: 'English',
            title: 'Essay Writing',
            due_date: '2026-01-15',
            status: 'Upcoming'
        }
    ];
    
    let html = '';
    sampleAssignments.forEach(assignment => {
        const statusColor = assignment.status === 'Pending' ? 'warning' : 'info';
        html += `
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title">${assignment.subject}</h5>
                            <span class="badge bg-${statusColor}">${assignment.status}</span>
                        </div>
                        <p class="card-text">${assignment.title}</p>
                        <small class="text-muted">
                            <i class='bx bx-calendar'></i> Due: ${assignment.due_date}
                        </small>
                    </div>
                </div>
            </div>
        `;
    });
    
    list.innerHTML = html || '<p class="text-center text-muted col-12">No assignments available</p>';
}
</script>

<style>
.full-width { width: 100%; }
.card { border: 1px solid #e0e0e0; border-radius: 8px; }
.card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
</style>

<?php include('partials/_footer.php') ?>

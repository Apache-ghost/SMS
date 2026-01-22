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
                <h1>Exam Results</h1>
                <ul class="breadcrumb">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active">Exam Results</a></li>
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
                    <select class="form-select" id="childSelect" onchange="loadExamResults()">
                        <option value="">Loading children...</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <ul class="insights mt-3" id="statsSection" style="display: none;">
            <li>
                <i class='bx bx-file'></i>
                <span class="info">
                    <h3 class="text-center" id="totalExams">0</h3>
                    <p>Total Exams</p>
                </span>
            </li>
            <li>
                <i class='bx bx-check-circle'></i>
                <span class="info">
                    <h3 class="text-center" id="resultsPublished">0</h3>
                    <p>Results Published</p>
                </span>
            </li>
            <li>
                <i class='bx bx-trophy'></i>
                <span class="info">
                    <h3 class="text-center" id="passedExams">0</h3>
                    <p>Passed</p>
                </span>
            </li>
            <li>
                <i class='bx bx-bar-chart'></i>
                <span class="info">
                    <h3 class="text-center" id="avgPercentage">0%</h3>
                    <p>Average</p>
                </span>
            </li>
        </ul>

        <!-- Exam Results Table -->
        <div class="bottom-data mt-3">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bx-file'></i>
                    <h3>Exam Results</h3>
                    <input type="text" id="searchExam" placeholder="Search..." class="form-control form-control-sm" style="max-width: 200px;" onkeyup="searchResults()">
                </div>
                
                <div id="examResultsContent" class="p-3">
                    <p class="text-center text-muted">Select a child to view exam results</p>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
let myChildren = [];
let currentResults = [];

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
            myChildren = data.children;
            let html = '<option value="">-- Select Child --</option>';
            data.children.forEach(child => {
                html += `<option value="${child.id}">${child.name} (Class ${child.class})</option>`;
            });
            select.innerHTML = html;
            
            // Check URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const studentId = urlParams.get('student_id');
            if (studentId) {
                const isMyChild = myChildren.some(child => child.id === studentId);
                if (isMyChild) {
                    select.value = studentId;
                    loadExamResults();
                } else {
                    alert('⚠️ You do not have access to this student\'s exam results');
                    window.location.href = 'exams.php';
                }
            }
        } else {
            select.innerHTML = '<option value="">No children found</option>';
        }
    })
    .catch(error => {
        console.error('Error loading children:', error);
    });
}

function loadExamResults() {
    const studentId = document.getElementById('childSelect').value;
    const content = document.getElementById('examResultsContent');
    const statsSection = document.getElementById('statsSection');
    
    if (!studentId) {
        content.innerHTML = '<p class="text-center text-muted">Select a child to view exam results</p>';
        statsSection.style.display = 'none';
        return;
    }
    
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Loading exam results...</p>
        </div>
    `;
    
    // Load exam results and statistics
    Promise.all([
        fetch('../assets/parentExamHandler.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=get_exam_results&student_id=${studentId}`
        }).then(r => r.json()),
        
        fetch('../assets/parentExamHandler.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=get_exam_stats&student_id=${studentId}`
        }).then(r => r.json())
    ])
    .then(([resultsData, statsData]) => {
        console.log('Results:', resultsData);
        console.log('Stats:', statsData);
        
        // Display statistics
        if (statsData.status === 'success' && statsData.stats) {
            document.getElementById('totalExams').textContent = statsData.stats.total_exams || 0;
            document.getElementById('resultsPublished').textContent = statsData.stats.results_published || 0;
            document.getElementById('passedExams').textContent = statsData.stats.passed || 0;
            document.getElementById('avgPercentage').textContent = statsData.stats.average || '0%';
            statsSection.style.display = 'grid';
        }
        
        // Display results
        if (resultsData.status === 'success' && resultsData.results && resultsData.results.length > 0) {
            currentResults = resultsData.results;
            displayResults(resultsData.results);
        } else {
            content.innerHTML = `
                <div class="alert alert-info text-center">
                    <i class='bx bx-file' style='font-size: 60px;'></i>
                    <p class="mt-3">No exam results available yet</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading exam results:', error);
        content.innerHTML = `
            <div class="alert alert-danger">
                <i class='bx bx-error'></i> Error loading exam results. Please try again.
            </div>
        `;
    });
}

function displayResults(results) {
    const content = document.getElementById('examResultsContent');
    
    let html = '<div class="table-responsive"><table class="table table-striped" id="resultsTable">';
    html += `
        <thead>
            <tr style="background: #667eea; color: white;">
                <th>Date</th>
                <th>Exam Title</th>
                <th>Subject</th>
                <th>Marks Obtained</th>
                <th>Total Marks</th>
                <th>Percentage</th>
                <th>Grade</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
    `;
    
    results.forEach(result => {
        const examDate = new Date(result.exam_date || result.timestamp);
        const formattedDate = examDate.toLocaleDateString('en-GB');
        
        const statusColor = result.status === 'pass' ? '#10b981' : '#ef4444';
        const gradeColor = ['A+', 'A'].includes(result.grade) ? '#10b981' : 
                           ['B', 'C'].includes(result.grade) ? '#f59e0b' : '#ef4444';
        
        html += `
            <tr>
                <td>${formattedDate}</td>
                <td><strong>${result.exam_title}</strong></td>
                <td>${result.subject}</td>
                <td style="text-align: center;">${result.marks_obtained}</td>
                <td style="text-align: center;">${result.total_marks}</td>
                <td style="text-align: center;"><strong>${result.percentage}%</strong></td>
                <td style="text-align: center;">
                    <span style="background: ${gradeColor}; color: white; padding: 5px 10px; border-radius: 12px; font-weight: bold;">
                        ${result.grade}
                    </span>
                </td>
                <td style="text-align: center;">
                    <span style="background: ${statusColor}; color: white; padding: 5px 10px; border-radius: 12px;">
                        ${result.status.toUpperCase()}
                    </span>
                </td>
                <td>${result.remarks || '-'}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    content.innerHTML = html;
}

function searchResults() {
    const searchTerm = document.getElementById('searchExam').value.toLowerCase();
    const rows = document.querySelectorAll('#resultsTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}
</script>

<style>
.insights {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.insights li {
    background: var(--color-white);
    padding: 1.5rem;
    border-radius: var(--border-radius-1);
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: var(--box-shadow);
    transition: all 0.3s ease;
}

.insights li:hover {
    box-shadow: 0 0 1.5rem rgba(102, 126, 234, 0.3);
    transform: translateY(-3px);
}

.insights li i {
    font-size: 2.5rem;
    color: var(--color-primary);
}

.insights li .info h3 {
    margin: 0;
    font-size: 2rem;
    color: var(--color-dark);
}

.insights li .info p {
    margin: 0.3rem 0 0 0;
    color: var(--color-info-dark);
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.table thead tr {
    border-radius: 8px;
}

.table tbody tr:hover {
    background: #f9fafb;
}

.form-control {
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
}

.form-select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: white;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
    border: 0.3em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
    to { transform: rotate(360deg); }
}
</style>

<?php include('partials/_footer.php') ?>

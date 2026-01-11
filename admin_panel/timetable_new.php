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
                <h1>Timetable Management</h1>
                <ul class="breadcrumb">
                    <li><a>Administration / Timetable</a></li>
                </ul>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTimetableModal">
                <i class='bx bx-plus'></i> Create Timetable
            </button>
        </div>

        <!-- Filter Section -->
        <div class="bottom-data">
            <div class="orders" style="width: 100%;">
                <div class="header">
                    <i class='bx bx-filter'></i>
                    <h3>Filter Timetables</h3>
                </div>
                <div class="p-3">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Level</label>
                            <select class="form-select" id="filterLevel" onchange="loadTimetables()">
                                <option value="">All Levels</option>
                                <option value="1">Level 1</option>
                                <option value="2">Level 2</option>
                                <option value="3">Level 3</option>
                                <option value="4">Level 4</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Section</label>
                            <select class="form-select" id="filterSection" onchange="loadTimetables()">
                                <option value="">All Sections</option>
                                <option value="A">Section A</option>
                                <option value="B">Section B</option>
                                <option value="C">Section C</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Academic Year</label>
                            <input type="text" class="form-control" id="filterYear" value="2026" onchange="loadTimetables()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Semester</label>
                            <select class="form-select" id="filterSemester" onchange="loadTimetables()">
                                <option value="">All Semesters</option>
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timetables List -->
        <div class="bottom-data">
            <div class="orders" style="width: 100%;">
                <div class="header">
                    <i class='bx bx-calendar-week'></i>
                    <h3>Timetables</h3>
                </div>
                <div id="timetablesContainer" class="p-3"></div>
            </div>
        </div>
    </main>
</div>

<!-- Create Timetable Modal -->
<div class="modal fade" id="createTimetableModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Timetable</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createTimetableForm">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Level *</label>
                            <select class="form-select" name="class" id="timetableLevel" required>
                                <option value="">Select Level</option>
                                <option value="1">Level 1</option>
                                <option value="2">Level 2</option>
                                <option value="3">Level 3</option>
                                <option value="4">Level 4</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Section *</label>
                            <select class="form-select" name="section" required>
                                <option value="">Select Section</option>
                                <option value="A">Section A</option>
                                <option value="B">Section B</option>
                                <option value="C">Section C</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Academic Year *</label>
                            <input type="text" class="form-control" name="academic_year" value="2026" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Semester *</label>
                            <select class="form-select" name="semester" required>
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Effective From *</label>
                            <input type="date" class="form-control" name="effective_from" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Effective To</label>
                            <input type="date" class="form-control" name="effective_to">
                        </div>
                    </div>

                    <hr>
                    <h5>Weekly Schedule</h5>
                    <p class="text-muted">Add periods for each day. Breaks will be automatically added.</p>
                    
                    <div id="scheduleContainer"></div>

                    <input type="hidden" name="created_by" value="<?php echo $_SESSION['userID']; ?>">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveTimetableBtn">Save Timetable</button>
            </div>
        </div>
    </div>
</div>

<style>
.timetable-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin: 15px 0;
    background: #f8f9fa;
}
.timetable-card h4 {
    color: #2c3e50;
    margin-bottom: 10px;
}
.timetable-card .meta {
    font-size: 14px;
    color: #666;
    margin: 5px 0;
}
.timetable-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    background: white;
}
.timetable-table th {
    background: #3788d8;
    color: white;
    padding: 12px;
    text-align: left;
    font-weight: 600;
}
.timetable-table td {
    padding: 10px 12px;
    border: 1px solid #e0e0e0;
}
.timetable-table tr:nth-child(even) {
    background: #f8f9fa;
}
.period-break {
    background: #fff3cd !important;
    font-weight: 600;
    text-align: center;
}
.btn-view {
    background: #3788d8;
    color: white;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    display: inline-block;
    margin-right: 10px;
}
.btn-view:hover {
    background: #2c6bb8;
}
.day-schedule {
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 15px;
    margin: 10px 0;
    background: white;
}
.period-row {
    display: grid;
    grid-template-columns: 100px 100px 1fr 80px;
    gap: 10px;
    margin: 10px 0;
    align-items: center;
}
.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    display: inline-block;
}
.badge-success {
    background: #28a745;
    color: white;
}
.badge-warning {
    background: #ffc107;
    color: #000;
}
.badge-secondary {
    background: #6c757d;
    color: white;
}
</style>

<script>
let allSubjects = [];
let currentLevel = '';

document.addEventListener('DOMContentLoaded', function() {
    loadTimetables();
    
    document.getElementById('timetableLevel').addEventListener('change', function() {
        currentLevel = this.value;
        if (currentLevel) {
            loadSubjectsForLevel(currentLevel);
        }
    });
    
    document.getElementById('saveTimetableBtn').addEventListener('click', saveTimetable);
});

function loadSubjectsForLevel(level) {
    const formData = new FormData();
    formData.append('action', 'get_subjects_by_class');
    formData.append('class', level);
    
    fetch('../assets/manageTimetable.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            allSubjects = data.data;
            buildScheduleForm();
        }
    })
    .catch(error => console.error('Error:', error));
}

function buildScheduleForm() {
    const container = document.getElementById('scheduleContainer');
    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    
    let html = '';
    
    days.forEach(day => {
        html += `<div class="day-schedule">
            <h6>${day}</h6>
            <button type="button" class="btn btn-sm btn-success mb-2" onclick="addPeriod('${day}')">
                <i class='bx bx-plus'></i> Add Period
            </button>
            <div id="periods-${day}"></div>
        </div>`;
    });
    
    container.innerHTML = html;
}

let periodCounter = 0;

function addPeriod(day) {
    const container = document.getElementById(`periods-${day}`);
    const periodId = `period-${day}-${periodCounter++}`;
    
    let subjectOptions = '<option value="">Select Subject</option>';
    allSubjects.forEach(subject => {
        subjectOptions += `<option value="${subject.subject_id}">${subject.subject_name}</option>`;
    });
    
    const periodHtml = `
        <div class="period-row" id="${periodId}">
            <input type="time" class="form-control" name="periods[${day}][${periodId}][start_time]" required>
            <input type="time" class="form-control" name="periods[${day}][${periodId}][end_time]" required>
            <select class="form-select" name="periods[${day}][${periodId}][subject]" required>
                ${subjectOptions}
            </select>
            <button type="button" class="btn btn-sm btn-danger" onclick="removePeriod('${periodId}')">
                <i class='bx bx-trash'></i>
            </button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', periodHtml);
}

function removePeriod(periodId) {
    document.getElementById(periodId).remove();
}

function loadTimetables() {
    const level = document.getElementById('filterLevel').value;
    const section = document.getElementById('filterSection').value;
    const year = document.getElementById('filterYear').value;
    const semester = document.getElementById('filterSemester').value;
    
    const formData = new FormData();
    formData.append('action', 'get_timetables');
    if (level) formData.append('class', level);
    if (section) formData.append('section', section);
    if (year) formData.append('academic_year', year);
    if (semester) formData.append('semester', semester);
    
    fetch('../assets/manageTimetable.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            displayTimetables(data.data);
        } else {
            document.getElementById('timetablesContainer').innerHTML = '<p class="text-muted">No timetables found</p>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('timetablesContainer').innerHTML = '<p class="text-danger">Error loading timetables</p>';
    });
}

function displayTimetables(timetables) {
    const container = document.getElementById('timetablesContainer');
    
    if (timetables.length === 0) {
        container.innerHTML = '<p class="text-muted">No timetables found. Create one to get started.</p>';
        return;
    }
    
    let html = '';
    timetables.forEach(timetable => {
        const statusClass = timetable.status === 'active' ? 'badge-success' : 
                           timetable.status === 'approved' ? 'badge-warning' : 'badge-secondary';
        
        html += `
            <div class="timetable-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h4>Level ${timetable.class} - Section ${timetable.section}</h4>
                        <div class="meta">
                            <i class='bx bx-calendar'></i> ${timetable.academic_year} | Semester ${timetable.semester}
                        </div>
                        <div class="meta">
                            <i class='bx bx-time'></i> Effective: ${timetable.effective_from} ${timetable.effective_to ? ' to ' + timetable.effective_to : ''}
                        </div>
                    </div>
                    <span class="badge ${statusClass}">${timetable.status.toUpperCase()}</span>
                </div>
                <div class="mt-3">
                    <a href="view_timetable.php?id=${timetable.timetable_id}" class="btn-view">
                        <i class='bx bx-show'></i> View Schedule
                    </a>
                    <button class="btn-view" onclick="activateTimetable(${timetable.timetable_id})">
                        <i class='bx bx-check'></i> Activate
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function saveTimetable() {
    const form = document.getElementById('createTimetableForm');
    const formData = new FormData(form);
    formData.append('action', 'create_full_timetable');
    
    fetch('../assets/manageTimetable.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.status === 'success') {
                alert('Timetable created successfully!');
                bootstrap.Modal.getInstance(document.getElementById('createTimetableModal')).hide();
                form.reset();
                loadTimetables();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (e) {
            console.error('Parse error:', text);
            alert('Error creating timetable');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error creating timetable');
    });
}

function activateTimetable(id) {
    if (!confirm('Activate this timetable? This will deactivate other timetables for the same class/section.')) return;
    
    const formData = new FormData();
    formData.append('action', 'activate_timetable');
    formData.append('timetable_id', id);
    
    fetch('../assets/manageTimetable.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Timetable activated!');
            loadTimetables();
        } else {
            alert('Error: ' + data.message);
        }
    });
}
</script>

<?php include('partials/_footer.php') ?>

<?php include('partials/_header.php') ?>

<!-- Sidebar -->
<?php include('partials/_sidebar.php') ?>

<div class="content">
    <?php include("partials/_navbar.php"); ?>

    <main>
        <div class="header">
            <div class="left">
                <h1>Time Table</h1>
                <ul class="breadcrumb">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active">Time Table</a></li>
                </ul>
            </div>
        </div>

        <div class="bottom-data">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bx-user'></i>
                    <h3>Select Child</h3>
                </div>
                <div class="p-3">
                    <select class="form-select" id="childSelect" onchange="loadTimetable()">
                        <option value="">Loading children...</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bottom-data mt-3">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bx-table'></i>
                    <h3>Weekly Schedule</h3>
                </div>
                
                <div id="timetableContent" class="p-3">
                    <p class="text-center text-muted">Select a child to view timetable</p>
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

function loadTimetable() {
    const select = document.getElementById('childSelect');
    const selectedOption = select.options[select.selectedIndex];
    const studentClass = selectedOption.getAttribute('data-class');
    const section = selectedOption.getAttribute('data-section');
    const content = document.getElementById('timetableContent');
    
    if (!studentClass) {
        content.innerHTML = '<p class="text-center text-muted">Select a child to view timetable</p>';
        return;
    }
    
    content.innerHTML = `<div class="text-center"><div class="spinner-border text-primary"></div><p class="mt-2">Loading timetable...</p></div>`;
    
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=get_student_timetable&class=${studentClass}&section=${section}`
    })
    .then(response => response.json())
    .then(data => {
        console.log('Timetable data:', data);
        if (data.status === 'success' && data.timetable && data.timetable.length > 0) {
            displayTimetable(data.timetable, studentClass, section);
        } else {
            content.innerHTML = `
                <div class="alert alert-info text-center">
                    <i class='bx bx-table' style='font-size: 60px;'></i>
                    <p class="mt-3">No timetable available for Class ${studentClass} - Section ${section}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading timetable:', error);
        content.innerHTML = `<div class="alert alert-danger"><i class='bx bx-error'></i> Error loading timetable</div>`;
    });
}

function displayTimetable(timetable, studentClass, section) {
    const content = document.getElementById('timetableContent');
    
    let html = `
        <div class="alert alert-info">
            <i class='bx bx-info-circle'></i> <strong>Class ${studentClass} - Section ${section}</strong>
        </div>
    `;
    
    // Group by day
    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    const groupedByDay = {};
    
    timetable.forEach(entry => {
        const day = entry.day || 'Monday';
        if (!groupedByDay[day]) {
            groupedByDay[day] = [];
        }
        groupedByDay[day].push(entry);
    });
    
    html += '<div class="accordion" id="timetableAccordion">';
    
    days.forEach((day, index) => {
        if (groupedByDay[day] && groupedByDay[day].length > 0) {
            const isToday = new Date().toLocaleDateString('en-US', { weekday: 'long' }) === day;
            
            html += `
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading${index}">
                        <button class="accordion-button ${isToday ? '' : 'collapsed'}" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#collapse${index}" 
                                style="${isToday ? 'background: #667eea; color: white;' : ''}">
                            ${isToday ? '📅 ' : ''}${day} ${isToday ? '(Today)' : ''}
                        </button>
                    </h2>
                    <div id="collapse${index}" class="accordion-collapse collapse ${isToday ? 'show' : ''}" 
                         data-bs-parent="#timetableAccordion">
                        <div class="accordion-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead style="background: #f8f9fa;">
                                        <tr>
                                            <th>Period</th>
                                            <th>Time</th>
                                            <th>Subject</th>
                                            <th>Teacher</th>
                                            <th>Room</th>
                                        </tr>
                                    </thead>
                                    <tbody>
            `;
            
            groupedByDay[day].sort((a, b) => (a.period || 0) - (b.period || 0));
            
            groupedByDay[day].forEach(entry => {
                const startTime = entry.start_time || entry.time || '-';
                const endTime = entry.end_time || '-';
                const timeDisplay = endTime !== '-' ? `${startTime} - ${endTime}` : startTime;
                
                html += `
                    <tr>
                        <td><strong>${entry.period || '-'}</strong></td>
                        <td>${timeDisplay}</td>
                        <td><span class="badge bg-primary">${entry.subject_name || entry.subject || '-'}</span></td>
                        <td>${entry.teacher_name || entry.teacher || '-'}</td>
                        <td>${entry.room || entry.room_number || '-'}</td>
                    </tr>
                `;
            });
            
            html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
    });
    
    html += '</div>';
    
    if (Object.keys(groupedByDay).length === 0) {
        html = `
            <div class="alert alert-warning text-center">
                <i class='bx bx-info-circle'></i> No timetable entries found
            </div>
        `;
    }
    
    content.innerHTML = html;
}
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Period</th>
                                ${days.map(day => `<th>${day}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            // Group by period
            const periods = [...new Set(data.map(d => d.period))].sort();
            
            periods.forEach(period => {
                html += '<tr>';
                html += `<td><strong>Period ${period}</strong></td>`;
                
                days.forEach(day => {
                    const entry = data.find(d => d.day === day && d.period == period);
                    if (entry) {
                        html += `<td>${entry.subject}<br><small class="text-muted">${entry.time_start} - ${entry.time_end}</small></td>`;
                    } else {
                        html += '<td class="table-secondary">-</td>';
                    }
                });
                
                html += '</tr>';
            });
            
            html += '</tbody></table></div>';
            content.innerHTML = html;
        } else {
            content.innerHTML = `<div class="text-center text-muted"><i class='bx bx-calendar-x' style='font-size: 60px;'></i><p class="mt-2">No timetable found for this class</p></div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = '<p class="text-center text-danger">Error loading timetable</p>';
    });
}
</script>

<style>.full-width { width: 100%; }</style>

<?php include('partials/_footer.php') ?>

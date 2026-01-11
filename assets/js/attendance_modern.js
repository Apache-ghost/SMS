// Modern Attendance Management System
let studentsData = [];
let attendanceRecords = {};

document.addEventListener('DOMContentLoaded', function() {
    const loadStudentsBtn = document.getElementById('loadStudentsBtn');
    const markAllPresentBtn = document.getElementById('markAllPresentBtn');
    const markAllAbsentBtn = document.getElementById('markAllAbsentBtn');
    const submitAttendanceBtn = document.getElementById('submitAttendanceBtn');

    // Load students
    if (loadStudentsBtn) {
        loadStudentsBtn.addEventListener('click', loadStudents);
    }

    // Mark all present
    if (markAllPresentBtn) {
        markAllPresentBtn.addEventListener('click', () => {
            markAllStudents('present');
        });
    }

    // Mark all absent
    if (markAllAbsentBtn) {
        markAllAbsentBtn.addEventListener('click', () => {
            markAllStudents('absent');
        });
    }

    // Submit attendance
    if (submitAttendanceBtn) {
        submitAttendanceBtn.addEventListener('click', submitAttendance);
    }
});

function loadStudents() {
    const classValue = document.getElementById('classTakeAttendence').value;
    const section = document.getElementById('sectionTakeAttendence').value;
    const date = document.getElementById('dateTakeAttendence').value;

    if (!classValue || !section) {
        showNotification('Please select class and section', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'get_students_for_attendance');
    formData.append('class', classValue);
    formData.append('section', section);
    formData.append('date', date);

    fetch('../assets/manageAttendance.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            studentsData = data.students;
            displayStudents(data.students);
            document.getElementById('studentCountBadge').textContent = data.students.length + ' students';
            document.getElementById('submitAttendanceBtn').style.display = 'inline-block';
            showNotification('Students loaded successfully', 'success');
        } else {
            showNotification(data.message || 'Error loading students', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to load students', 'error');
    });
}

function displayStudents(students) {
    const tbody = document.getElementById('takeAttendenceTable');
    tbody.innerHTML = '';

    if (students.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">No students found</td></tr>';
        return;
    }

    students.forEach((student, index) => {
        const status = student.attendance_status || 'unmarked';
        attendanceRecords[student.id] = status === 'unmarked' ? 'present' : status;

        const imagePath = student.image ? `../studentUploads/${student.image}` : '../images/user.png';

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${student.id}</td>
            <td>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <img src="${imagePath}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;" 
                         onerror="this.src='../images/user.png'">
                    <span>${student.name}</span>
                </div>
            </td>
            <td>
                <span class="status-badge status-${attendanceRecords[student.id]}" id="status-${student.id}">
                    ${attendanceRecords[student.id].toUpperCase()}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-success mark-present-btn" data-student-id="${student.id}">
                    <i class='bx bx-check'></i> Present
                </button>
                <button class="btn btn-sm btn-danger mark-absent-btn" data-student-id="${student.id}">
                    <i class='bx bx-x'></i> Absent
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });

    // Add event listeners to buttons
    document.querySelectorAll('.mark-present-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const studentId = btn.getAttribute('data-student-id');
            markStudent(studentId, 'present');
        });
    });

    document.querySelectorAll('.mark-absent-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const studentId = btn.getAttribute('data-student-id');
            markStudent(studentId, 'absent');
        });
    });
}

function markStudent(studentId, status) {
    attendanceRecords[studentId] = status;
    
    const statusBadge = document.getElementById(`status-${studentId}`);
    if (statusBadge) {
        statusBadge.className = `status-badge status-${status}`;
        statusBadge.textContent = status.toUpperCase();
    }
}

function markAllStudents(status) {
    studentsData.forEach(student => {
        attendanceRecords[student.id] = status;
        const statusBadge = document.getElementById(`status-${student.id}`);
        if (statusBadge) {
            statusBadge.className = `status-badge status-${status}`;
            statusBadge.textContent = status.toUpperCase();
        }
    });
    
    showNotification(`All students marked as ${status}`, 'success');
}

function submitAttendance() {
    const classValue = document.getElementById('classTakeAttendence').value;
    const section = document.getElementById('sectionTakeAttendence').value;
    const date = document.getElementById('dateTakeAttendence').value;

    if (Object.keys(attendanceRecords).length === 0) {
        showNotification('No attendance data to submit', 'warning');
        return;
    }

    // Prepare attendance array
    const attendanceArray = Object.keys(attendanceRecords).map(studentId => ({
        student_id: studentId,
        status: attendanceRecords[studentId]
    }));

    const formData = new FormData();
    formData.append('action', 'submit_attendance');
    formData.append('class', classValue);
    formData.append('section', section);
    formData.append('date', date);
    formData.append('attendance', JSON.stringify(attendanceArray));

    // Show loading
    document.getElementById('submitAttendanceBtn').disabled = true;
    document.getElementById('submitAttendanceBtn').innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Saving...';

    fetch('../assets/manageAttendance.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showNotification(data.message, 'success');
            // Reload students to show updated attendance
            setTimeout(() => loadStudents(), 1000);
        } else {
            showNotification(data.message || 'Error submitting attendance', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to submit attendance', 'error');
    })
    .finally(() => {
        document.getElementById('submitAttendanceBtn').disabled = false;
        document.getElementById('submitAttendanceBtn').innerHTML = '<i class="bx bx-save"></i> Save Attendance';
    });
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'warning'} alert-dismissible fade show`;
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
    
    // Auto-remove after 4 seconds
    setTimeout(() => {
        notification.remove();
    }, 4000);
}

// Add CSS for status badges
const style = document.createElement('style');
style.textContent = `
    .status-badge {
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        display: inline-block;
    }
    
    .status-present {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .status-absent {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .status-unmarked {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    
    #attendanceTable {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    
    #attendanceTable th {
        background-color: #f8f9fa;
        padding: 12px;
        text-align: left;
        border-bottom: 2px solid #dee2e6;
    }
    
    #attendanceTable td {
        padding: 12px;
        border-bottom: 1px solid #dee2e6;
    }
    
    #attendanceTable tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .mark-present-btn,
    .mark-absent-btn {
        margin-right: 5px;
    }
`;
document.head.appendChild(style);

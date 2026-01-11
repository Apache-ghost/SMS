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
                <h1>School Calendar</h1>
                <ul class="breadcrumb">
                    <li><a>Administration / Calendar</a></li>
                </ul>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                <i class='bx bx-plus'></i> Add Event
            </button>
        </div>

        <!-- Calendar View -->
        <div class="bottom-data">
            <div class="orders" style="width: 100%;">
                <div class="header">
                    <i class='bx bx-calendar'></i>
                    <h3>Events & Holidays</h3>
                </div>
                <div id="calendarContainer" class="p-3"></div>
            </div>
        </div>
    </main>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Calendar Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addEventForm">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Event Title *</label>
                            <input type="text" class="form-control" name="event_title" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Event Type *</label>
                            <select class="form-select" name="event_type" required>
                                <option value="holiday">Holiday</option>
                                <option value="exam">Exam</option>
                                <option value="event">Event</option>
                                <option value="meeting">Meeting</option>
                                <option value="sports">Sports</option>
                                <option value="cultural">Cultural</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="event_description" rows="3"></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Date *</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date *</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Start Time</label>
                            <input type="time" class="form-control" name="start_time" id="startTime">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Time</label>
                            <input type="time" class="form-control" name="end_time" id="endTime">
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="is_all_day" id="isAllDay" value="1">
                                <label class="form-check-label" for="isAllDay">All Day Event</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" placeholder="e.g. School Auditorium">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Organizer</label>
                            <input type="text" class="form-control" name="organizer" placeholder="e.g. Sports Department">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Target Audience *</label>
                            <select class="form-select" name="target_audience" id="targetAudience" required>
                                <option value="all">All</option>
                                <option value="students">Students</option>
                                <option value="teachers">Teachers</option>
                                <option value="parents">Parents</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>
                        <div class="col-md-4" id="classField" style="display:none;">
                            <label class="form-label">Class</label>
                            <input type="text" class="form-control" name="class" placeholder="e.g. 10, 12c">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Color</label>
                            <input type="color" class="form-control form-control-color" name="color_code" value="#3788d8">
                        </div>
                    </div>
                    
                    <input type="hidden" name="created_by" value="<?php echo $_SESSION['userID']; ?>">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEventBtn">Save Event</button>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: #e0e0e0;
    border: 1px solid #e0e0e0;
}
.calendar-header {
    background: #3788d8;
    color: white;
    padding: 10px;
    text-align: center;
    font-weight: 600;
}
.calendar-day {
    background: white;
    min-height: 100px;
    padding: 8px;
    position: relative;
}
.calendar-day.other-month {
    background: #f5f5f5;
    color: #999;
}
.calendar-day.today {
    background: #e3f2fd;
}
.calendar-day-number {
    font-weight: 600;
    margin-bottom: 5px;
}
.calendar-event {
    font-size: 11px;
    padding: 2px 6px;
    margin: 2px 0;
    border-radius: 3px;
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: white;
}
.calendar-event:hover {
    opacity: 0.8;
}
.calendar-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}
.event-list-item {
    padding: 15px;
    margin: 10px 0;
    border-left: 4px solid;
    background: #f8f9fa;
    border-radius: 4px;
}
.event-list-item h5 {
    margin: 0 0 5px 0;
}
.event-list-item .event-meta {
    font-size: 13px;
    color: #666;
}
.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    margin-right: 5px;
}

@media (max-width: 768px) {
    .calendar-day {
        min-height: 80px;
        padding: 4px;
        font-size: 12px;
    }
    .calendar-event {
        font-size: 9px;
        padding: 1px 4px;
    }
}
</style>

<script>
let currentDate = new Date();
let currentView = 'month';
let allEvents = [];

document.addEventListener('DOMContentLoaded', function() {
    loadCalendarEvents();
    
    // All day checkbox handler
    const isAllDayCheckbox = document.getElementById('isAllDay');
    const startTimeInput = document.getElementById('startTime');
    const endTimeInput = document.getElementById('endTime');
    
    if (isAllDayCheckbox) {
        isAllDayCheckbox.addEventListener('change', function() {
            startTimeInput.disabled = this.checked;
            endTimeInput.disabled = this.checked;
            if (this.checked) {
                startTimeInput.value = '';
                endTimeInput.value = '';
            }
        });
    }
    
    // Target audience handler
    const targetAudienceSelect = document.getElementById('targetAudience');
    const classField = document.getElementById('classField');
    
    if (targetAudienceSelect) {
        targetAudienceSelect.addEventListener('change', function() {
            if (this.value === 'students') {
                classField.style.display = 'block';
            } else {
                classField.style.display = 'none';
            }
        });
    }
    
    // Save event button
    const saveEventBtn = document.getElementById('saveEventBtn');
    if (saveEventBtn) {
        saveEventBtn.addEventListener('click', saveEvent);
    }
});

function loadCalendarEvents() {
    const formData = new FormData();
    formData.append('action', 'get_calendar_events');
    formData.append('start_date', getMonthStart(currentDate));
    formData.append('end_date', getMonthEnd(currentDate));
    
    fetch('../assets/manageAnnouncements.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            allEvents = data.data;
            renderCalendar();
        } else {
            console.error('Error loading events:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('calendarContainer').innerHTML = '<p class="text-center text-danger">Error loading calendar</p>';
    });
}

function renderCalendar() {
    const container = document.getElementById('calendarContainer');
    
    // Calendar controls
    const monthYear = currentDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    
    let html = `
        <div class="calendar-controls">
            <button class="btn btn-sm btn-outline-primary" onclick="previousMonth()">
                <i class='bx bx-chevron-left'></i> Previous
            </button>
            <h4 class="mb-0">${monthYear}</h4>
            <button class="btn btn-sm btn-outline-primary" onclick="nextMonth()">
                Next <i class='bx bx-chevron-right'></i>
            </button>
        </div>
    `;
    
    // Calendar grid
    html += '<div class="calendar-grid">';
    
    // Headers
    const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    days.forEach(day => {
        html += `<div class="calendar-header">${day}</div>`;
    });
    
    // Get first day of month
    const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
    const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
    const startingDayOfWeek = firstDay.getDay();
    const today = new Date();
    
    // Previous month days
    const prevMonthDays = new Date(currentDate.getFullYear(), currentDate.getMonth(), 0).getDate();
    for (let i = startingDayOfWeek - 1; i >= 0; i--) {
        html += `<div class="calendar-day other-month"><div class="calendar-day-number">${prevMonthDays - i}</div></div>`;
    }
    
    // Current month days
    for (let day = 1; day <= lastDay.getDate(); day++) {
        const currentDayDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
        const isToday = currentDayDate.toDateString() === today.toDateString();
        const dateStr = currentDayDate.toISOString().split('T')[0];
        
        // Get events for this day
        const dayEvents = allEvents.filter(event => {
            return dateStr >= event.start_date && dateStr <= event.end_date;
        });
        
        html += `<div class="calendar-day ${isToday ? 'today' : ''}">
            <div class="calendar-day-number">${day}</div>`;
        
        dayEvents.forEach(event => {
            html += `<div class="calendar-event" style="background-color: ${event.color_code}" 
                     onclick="showEventDetails(${event.event_id})" title="${event.event_title}">
                ${event.event_title}
            </div>`;
        });
        
        html += '</div>';
    }
    
    // Next month days
    const remainingDays = 42 - (startingDayOfWeek + lastDay.getDate());
    for (let i = 1; i <= remainingDays; i++) {
        html += `<div class="calendar-day other-month"><div class="calendar-day-number">${i}</div></div>`;
    }
    
    html += '</div>';
    
    // Event list below calendar
    html += '<div class="mt-4"><h5>Upcoming Events</h5>';
    const upcomingEvents = allEvents.filter(event => new Date(event.start_date) >= today).slice(0, 5);
    
    if (upcomingEvents.length === 0) {
        html += '<p class="text-muted">No upcoming events</p>';
    } else {
        upcomingEvents.forEach(event => {
            const startDate = new Date(event.start_date).toLocaleDateString();
            html += `<div class="event-list-item" style="border-left-color: ${event.color_code}">
                <h5>${event.event_title}</h5>
                <div class="event-meta">
                    <span class="badge" style="background-color: ${event.color_code}">${event.event_type}</span>
                    <i class='bx bx-calendar'></i> ${startDate}
                    ${event.location ? '<i class="bx bx-map"></i> ' + event.location : ''}
                </div>
                ${event.event_description ? '<p class="mt-2 mb-0">' + event.event_description + '</p>' : ''}
            </div>`;
        });
    }
    html += '</div>';
    
    container.innerHTML = html;
}

function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    loadCalendarEvents();
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    loadCalendarEvents();
}

function getMonthStart(date) {
    return new Date(date.getFullYear(), date.getMonth(), 1).toISOString().split('T')[0];
}

function getMonthEnd(date) {
    return new Date(date.getFullYear(), date.getMonth() + 1, 0).toISOString().split('T')[0];
}

function showEventDetails(eventId) {
    const event = allEvents.find(e => e.event_id == eventId);
    if (event) {
        alert(`${event.event_title}\n\n${event.event_description || 'No description'}\n\nDate: ${event.start_date} to ${event.end_date}`);
    }
}

function saveEvent() {
    const form = document.getElementById('addEventForm');
    const formData = new FormData(form);
    formData.append('action', 'create_calendar_event');
    
    fetch('../assets/manageAnnouncements.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.status === 'success') {
                alert('Event added successfully!');
                bootstrap.Modal.getInstance(document.getElementById('addEventModal')).hide();
                form.reset();
                loadCalendarEvents();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (e) {
            console.error('Parse error:', text);
            alert('Error creating event');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error creating event');
    });
}
</script>

<?php include('partials/_footer.php') ?>

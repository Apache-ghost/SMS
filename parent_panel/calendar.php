<?php include('partials/_header.php') ?>

<!-- Sidebar -->
<?php include('partials/_sidebar.php') ?>

<div class="content">
    <?php include("partials/_navbar.php"); ?>

    <main>
        <div class="header">
            <div class="left">
                <h1>School Calendar</h1>
                <ul class="breadcrumb">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active">Calendar</a></li>
                </ul>
            </div>
        </div>

        <div class="bottom-data">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bx-calendar'></i>
                    <h3>School Events & Holidays</h3>
                </div>
                
                <div class="p-3">
                    <div class="row">
                        <div class="col-md-8">
                            <div id="calendar" style="min-height: 500px;"></div>
                        </div>
                        <div class="col-md-4">
                            <h5><i class='bx bx-list-ul'></i> Upcoming Events</h5>
                            <div id="eventsList" class="mt-3">
                                <div class="text-center">
                                    <div class="spinner-border text-primary"></div>
                                    <p class="mt-2">Loading events...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<!-- FullCalendar CSS & JS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>

<script>
let calendar;

document.addEventListener('DOMContentLoaded', function() {
    initCalendar();
    loadEvents();
});

function initCalendar() {
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listWeek'
        },
        events: function(info, successCallback, failureCallback) {
            loadCalendarEvents(successCallback, failureCallback);
        },
        eventClick: function(info) {
            showEventDetails(info.event);
        },
        height: 'auto'
    });
    calendar.render();
}

function loadCalendarEvents(successCallback, failureCallback) {
    fetch('../assets/manageCalendar.php?action=get_events')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.events) {
                const events = data.events.map(event => ({
                    id: event.event_id,
                    title: event.event_title,
                    start: event.start_date,
                    end: event.end_date,
                    allDay: event.is_all_day == 1,
                    color: getEventColor(event.event_type),
                    extendedProps: {
                        description: event.event_description,
                        type: event.event_type,
                        startTime: event.start_time,
                        endTime: event.end_time
                    }
                }));
                successCallback(events);
            } else {
                successCallback([]);
            }
        })
        .catch(error => {
            console.error('Error loading calendar events:', error);
            failureCallback(error);
        });
}

function loadEvents() {
    fetch('../assets/manageCalendar.php?action=get_upcoming_events')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.events) {
                displayEventsList(data.events);
            } else {
                document.getElementById('eventsList').innerHTML = '<p class="text-muted">No upcoming events</p>';
            }
        })
        .catch(error => {
            console.error('Error loading events:', error);
            document.getElementById('eventsList').innerHTML = '<p class="text-danger">Error loading events</p>';
        });
}

function displayEventsList(events) {
    const eventsList = document.getElementById('eventsList');
    
    if (events && events.length > 0) {
        let html = '';
        
        events.forEach(event => {
            const typeColors = {
                'meeting': '#0d6efd',
                'event': '#198754',
                'holiday': '#dc3545',
                'exam': '#ffc107',
                'sports': '#0dcaf0',
                'cultural': '#6f42c1',
                'other': '#6c757d'
            };
            
            const color = typeColors[event.event_type] || '#6c757d';
            const startDate = new Date(event.start_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            
            html += `
                <div class="card mb-3 border-0 shadow-sm" style="border-left: 4px solid ${color} !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0">${event.event_title}</h6>
                            <span class="badge" style="background: ${color};">${event.event_type}</span>
                        </div>
                        <p class="mb-1"><small class="text-muted"><i class='bx bx-calendar'></i> ${startDate}</small></p>
                        ${event.event_description ? `<p class="mb-0"><small>${event.event_description}</small></p>` : ''}
                        ${event.start_time ? `<p class="mb-0"><small class="text-muted"><i class='bx bx-time'></i> ${event.start_time}</small></p>` : ''}
                    </div>
                </div>
            `;
        });
        
        eventsList.innerHTML = html;
    } else {
        eventsList.innerHTML = '<p class="text-muted text-center"><i class="bx bx-calendar-x" style="font-size: 40px;"></i><br>No upcoming events</p>';
    }
}

function getEventColor(type) {
    const colors = {
        'holiday': '#dc3545',
        'exam': '#ffc107',
        'event': '#198754',
        'meeting': '#0d6efd',
        'sports': '#0dcaf0',
        'cultural': '#6f42c1',
        'other': '#6c757d'
    };
    return colors[type] || '#6c757d';
}

function showEventDetails(event) {
    const typeMap = {
        'holiday': 'Holiday',
        'exam': 'Exam',
        'event': 'Event',
        'meeting': 'Meeting',
        'sports': 'Sports',
        'cultural': 'Cultural',
        'other': 'Other'
    };
    
    const details = `
Event: ${event.title}
Type: ${typeMap[event.extendedProps.type] || event.extendedProps.type}
Date: ${event.start.toDateString()}${event.end && event.end !== event.start ? ' to ' + event.end.toDateString() : ''}
${event.extendedProps.startTime ? 'Time: ' + event.extendedProps.startTime + (event.extendedProps.endTime ? ' - ' + event.extendedProps.endTime : '') : ''}
${event.extendedProps.description ? '\nDescription: ' + event.extendedProps.description : ''}
    `.trim();
    
    alert(details);
}
</script>

<style>
.fc { font-size: 0.9em; }
.fc-event { cursor: pointer; }
#eventsList { max-height: 600px; overflow-y: auto; }
</style>

<?php include('partials/_footer.php') ?>
    document.getElementById('calendar').innerHTML = `
        <div class="alert alert-info">
            <i class='bx bx-info-circle'></i> Calendar view - Events are listed on the right
        </div>
        <div class="text-center p-5" style="background: #f8f9fa; border-radius: 10px;">
            <i class='bx bx-calendar' style='font-size: 80px; color: #667eea;'></i>
            <p class="mt-3">Interactive calendar coming soon</p>
        </div>
    `;
}
</script>

<style>
.full-width { width: 100%; }
.event-card { border-radius: 8px; transition: transform 0.2s; }
.event-card:hover { transform: translateX(5px); }
</style>

<?php include('partials/_footer.php') ?>

<?php 
session_start();
include("../assets/noSessionRedirect.php");
include("./verifyRoleRedirect.php");
include("../assets/config.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Calendar - Student</title>
    <link rel="shortcut icon" href="./images/logo.png">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 20px;
        }
        .calendar-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .header-section h1 {
            color: #2c3e50;
            margin: 0;
        }
        .back-btn {
            background: #3788d8;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .back-btn:hover {
            background: #2c6bb8;
        }
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .filter-section select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            min-width: 200px;
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #e0e0e0;
            border: 1px solid #e0e0e0;
            margin-bottom: 30px;
        }
        .calendar-header {
            background: #3788d8;
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: 600;
        }
        .calendar-day {
            background: white;
            min-height: 100px;
            padding: 10px;
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
            font-size: 14px;
        }
        .calendar-event {
            font-size: 11px;
            padding: 3px 6px;
            margin: 3px 0;
            border-radius: 4px;
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
        .calendar-controls button {
            background: #3788d8;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .calendar-controls button:hover {
            background: #2c6bb8;
        }
        .event-list-item {
            padding: 20px;
            margin: 15px 0;
            border-left: 4px solid;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .event-list-item h5 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        .event-list-item .event-meta {
            font-size: 14px;
            color: #666;
            margin: 8px 0;
        }
        .event-list-item .event-description {
            margin-top: 12px;
            font-size: 14px;
            color: #444;
            line-height: 1.5;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            margin-right: 8px;
            display: inline-block;
            color: white;
            font-weight: 600;
        }
        @media (max-width: 768px) {
            .calendar-day {
                min-height: 70px;
                padding: 5px;
            }
            .calendar-event {
                font-size: 9px;
                padding: 2px 4px;
            }
            .calendar-controls {
                flex-direction: column;
                gap: 10px;
            }
            .header-section {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>

<div class="calendar-container">
    <div class="header-section">
        <h1>📅 School Calendar</h1>
        <a href="index.php" class="back-btn">
            <i class='bx bx-arrow-back'></i> Back to Dashboard
        </a>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <label style="font-weight: 600; margin-right: 10px;">Filter by Event Type:</label>
        <select id="filterEventType" onchange="loadCalendarEvents()">
            <option value="">All Types</option>
            <option value="holiday">Holidays</option>
            <option value="exam">Exams</option>
            <option value="event">Events</option>
            <option value="meeting">Meetings</option>
            <option value="sports">Sports</option>
            <option value="cultural">Cultural</option>
            <option value="other">Other</option>
        </select>
    </div>

    <!-- Calendar View -->
    <div id="calendarContainer">
        <p style="text-align: center; color: #666;">Loading calendar...</p>
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
.event-list-item .event-description {
    margin-top: 8px;
    font-size: 14px;
    color: #444;
}
.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    margin-right: 5px;
}
.event-details-modal {
    background: white;
    padding: 20px;
    border-radius: 8px;
    max-width: 500px;
    margin: 20px auto;
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
    .calendar-controls {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<script>
let currentDate = new Date();
let allEvents = [];
const studentClass = '<?php echo $_SESSION['class'] ?? ''; ?>';

document.addEventListener('DOMContentLoaded', function() {
    loadCalendarEvents();
});

function loadCalendarEvents() {
    const eventType = document.getElementById('filterEventType')?.value || '';
    
    const formData = new FormData();
    formData.append('action', 'get_calendar_events');
    formData.append('start_date', getMonthStart(currentDate));
    formData.append('end_date', getMonthEnd(currentDate));
    formData.append('target_audience', 'students');
    if (eventType) {
        formData.append('event_type', eventType);
    }
    
    fetch('../assets/manageAnnouncements.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Filter events for student's class or all students
            allEvents = data.data.filter(event => {
                return event.target_audience === 'all' || 
                       event.target_audience === 'students' && (!event.class || event.class === studentClass);
            });
            renderCalendar();
        } else {
            console.error('Error loading events:', data.message);
            document.getElementById('calendarContainer').innerHTML = '<p class="text-center text-danger">Error loading calendar</p>';
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
    const upcomingEvents = allEvents.filter(event => new Date(event.start_date) >= today).slice(0, 10);
    
    if (upcomingEvents.length === 0) {
        html += '<p class="text-muted">No upcoming events</p>';
    } else {
        upcomingEvents.forEach(event => {
            const startDate = new Date(event.start_date).toLocaleDateString();
            const endDate = new Date(event.end_date).toLocaleDateString();
            const dateRange = startDate === endDate ? startDate : `${startDate} - ${endDate}`;
            
            html += `<div class="event-list-item" style="border-left-color: ${event.color_code}">
                <h5>${event.event_title}</h5>
                <div class="event-meta">
                    <span class="badge" style="background-color: ${event.color_code}">${event.event_type}</span>
                    <i class='bx bx-calendar'></i> ${dateRange}
                    ${event.location ? '<br><i class="bx bx-map"></i> ' + event.location : ''}
                    ${event.organizer ? '<br><i class="bx bx-user"></i> Organized by: ' + event.organizer : ''}
                </div>
                ${event.event_description ? '<div class="event-description">' + event.event_description + '</div>' : ''}
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
    if (!event) return;
    
    const startDate = new Date(event.start_date).toLocaleDateString('en-US', { 
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
    });
    const endDate = new Date(event.end_date).toLocaleDateString('en-US', { 
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
    });
    
    let timeInfo = '';
    if (event.is_all_day == 1) {
        timeInfo = 'All Day';
    } else if (event.start_time) {
        timeInfo = `${event.start_time}${event.end_time ? ' - ' + event.end_time : ''}`;
    }
    
    let details = `📅 ${event.event_title}\n\n`;
    details += `Type: ${event.event_type.toUpperCase()}\n`;
    details += `Date: ${startDate}${startDate !== endDate ? ' to ' + endDate : ''}\n`;
    if (timeInfo) details += `Time: ${timeInfo}\n`;
    if (event.location) details += `Location: ${event.location}\n`;
    if (event.organizer) details += `Organizer: ${event.organizer}\n`;
    if (event.event_description) details += `\nDescription:\n${event.event_description}`;
    
    alert(details);
}
</script>

</body>
</html>

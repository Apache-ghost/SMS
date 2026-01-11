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
                    <h3>Upcoming Events & Holidays</h3>
                </div>
                
                <div id="calendarContent" class="p-3">
                    <div class="row">
                        <div class="col-md-8">
                            <div id="calendar"></div>
                        </div>
                        <div class="col-md-4">
                            <h5>Upcoming Events</h5>
                            <div id="eventsList" class="mt-3">
                                <div class="text-center">
                                    <div class="spinner-border text-primary spinner-border-sm"></div>
                                    <p class="mt-2">Loading...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadEvents();
});

function loadEvents() {
    // Sample events - replace with actual API call
    const events = [
        { title: 'Parent-Teacher Meeting', date: '2026-01-15', type: 'meeting' },
        { title: 'Annual Sports Day', date: '2026-01-20', type: 'event' },
        { title: 'Winter Break Starts', date: '2026-01-25', type: 'holiday' },
        { title: 'Mid-Term Exams Begin', date: '2026-02-01', type: 'exam' },
        { title: 'Science Fair', date: '2026-02-10', type: 'event' }
    ];
    
    displayEvents(events);
}

function displayEvents(events) {
    const eventsList = document.getElementById('eventsList');
    
    if (events && events.length > 0) {
        let html = '';
        
        events.forEach(event => {
            const typeColors = {
                'meeting': 'primary',
                'event': 'success',
                'holiday': 'danger',
                'exam': 'warning'
            };
            
            const color = typeColors[event.type] || 'info';
            
            html += `
                <div class="event-card mb-3 p-3" style="border-left: 4px solid var(--bs-${color}); background: #f8f9fa;">
                    <div class="d-flex justify-content-between">
                        <strong>${event.title}</strong>
                        <span class="badge bg-${color}">${event.type}</span>
                    </div>
                    <small class="text-muted"><i class='bx bx-calendar'></i> ${event.date}</small>
                </div>
            `;
        });
        
        eventsList.innerHTML = html;
    } else {
        eventsList.innerHTML = '<p class="text-muted">No upcoming events</p>';
    }
    
    // Simple calendar display
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

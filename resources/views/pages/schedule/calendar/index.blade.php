@php
// Get events from the DB
$events_list = App\Models\Event::all();
$calendar_events = json_encode(['events' => $events_list]);
@endphp

<div class="card-body p-0">
    <div class="content main">
        <div class="row" style="width: 100%; height: 100%;">
            <div class="col-md-9">
                <div class="calendar-container" style="width: 100%; height: 100%;">
                    <div class="calendar" style="background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 10px;">

                        @if(Qs::userIsTeamSA())
                        <div class="mb-3">
                            <button class="btn btn-info btn-sm float-end " id="add-button">
                                <i class="icon-plus2 mr-2"></i> Add Event
                            </button>
                        </div>
                        @endif
                        
                        <div class="year-header d-flex justify-content-between align-items-center mb-4">
                            <span class="left-button btn btn-light" id="prev" style="cursor: pointer;">&#8592;</span>
                            <span class="year h4 mb-0" id="label"></span>
                            <span class="right-button btn btn-light" id="next" style="cursor: pointer;">&#8594;</span>
                        </div>

                        <table class="months-table table table-borderless mb-3">
                            <tbody>
                                <tr class="months-row text-center">
                                    <td class="month" data-month="0">Jan</td>
                                    <td class="month" data-month="1">Feb</td>
                                    <td class="month" data-month="2">Mar</td>
                                    <td class="month" data-month="3">Apr</td>
                                    <td class="month" data-month="4">May</td>
                                    <td class="month" data-month="5">Jun</td>
                                    <td class="month" data-month="6">Jul</td>
                                    <td class="month" data-month="7">Aug</td>
                                    <td class="month" data-month="8">Sep</td>
                                    <td class="month" data-month="9">Oct</td>
                                    <td class="month" data-month="10">Nov</td>
                                    <td class="month" data-month="11">Dec</td>
                                </tr>
                            </tbody>
                        </table>

                        <table class="days-table table table-borderless mb-2">
                            <tr class="text-center">
                                <td class="day fw-bold">Sun</td>
                                <td class="day fw-bold">Mon</td>
                                <td class="day fw-bold">Tue</td>
                                <td class="day fw-bold">Wed</td>
                                <td class="day fw-bold">Thu</td>
                                <td class="day fw-bold">Fri</td>
                                <td class="day fw-bold">Sat</td>
                            </tr>
                        </table>

                        <div class="frame">
                            <table class="dates-table table table-borderless">
                                <tbody class="tbody text-center">
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="events-container p-3" style="background: #f8f9fa; border-radius: 8px; min-height: 400px;">
                    <h5 class="mb-3">Today's Events</h5>
                    <div id="today-events-list">
                        <p class="text-muted text-center">Select a date to view events</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!--Create Event Modal -->
        <div class="dialog modal fade" id="eventDialog" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form class="form" id="eventForm">
                            <div class="form-container">
                                <div class="mb-3">
                                    <label class="form-label" for="event_name">Event name</label>
                                    <input class="form-control" type="text" id="event_name" maxlength="50" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="event_description">Event Description</label>
                                    <textarea maxlength="150" class="form-control" id="event_description" rows="3" required></textarea>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-4">
                                        <label class="form-label">Year</label>
                                        <input type="number" class="form-control" id="event_year" readonly>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label">Month</label>
                                        <input type="number" class="form-control" id="event_month" readonly>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label">Day</label>
                                        <input type="number" class="form-control" id="event_day" readonly>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="button" class="btn btn-warning me-2" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .calendar .month.active {
            background-color: #007bff;
            color: white;
            border-radius: 20px;
            padding: 5px 5px;
        }
        
        .calendar .dates-table td {
            padding: 10px;
            cursor: pointer;
            transition: all 0.1s;
            border-radius: 4px;
        }
        
        .calendar .dates-table td:hover {
            background-color: #e9ecef;
        }
        
        .calendar .dates-table td.has-event {
            background-color: #cfe2ff;
            font-weight: bold;
            position: relative;
        }
        
        .calendar .dates-table td.has-event::after {
            content: '';
            position: absolute;
            bottom: 2px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background-color: #007bff;
        }
        
        .calendar .dates-table td.today {
            background-color: #28a745;
            color: white;
            font-weight: bold;
        }
        
        .calendar .dates-table td.selected {
            background-color: #007bff;
            color: white;
        }
        
        .event-item {
            padding: 10px;
            margin-bottom: 10px;
            background: white;
            border-radius: 4px;
            border-left: 3px solid #007bff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .event-item .event-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .event-item .event-description {
            font-size: 0.9em;
            color: #6c757d;
        }
        
        .event-item .event-date {
            font-size: 0.8em;
            color: #007bff;
        }
    </style>

    <script>
        // Assign events data to js variable
        var events_data = {!! $calendar_events !!};
        
        document.addEventListener('DOMContentLoaded', function() {
            initCalendar();
        });

        function initCalendar() {
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            let currentDate = new Date();
            let currentMonth = currentDate.getMonth();
            let currentYear = currentDate.getFullYear();
            
            const monthLabels = document.querySelectorAll('.month');
            const yearLabel = document.getElementById('label');
            const tbody = document.querySelector('.tbody');
            const prevBtn = document.getElementById('prev');
            const nextBtn = document.getElementById('next');
            const addButton = document.getElementById('add-button');
            
            // Parse events from events_data
            const events = events_data.events || [];
            
            // Function to check if a date has events
            function hasEvent(year, month, day) {
                return events.some(event => 
                    event.year == year && 
                    event.month == (month + 1) && 
                    event.day == day
                );
            }
            
            // Function to get events for a specific date
            function getEventsForDate(year, month, day) {
                return events.filter(event => 
                    event.year == year && 
                    event.month == (month + 1) && 
                    event.day == day
                );
            }
            
            // Function to display events in the right panel
            function displayEventsForDate(year, month, day) {
                const eventsList = getEventsForDate(year, month, day);
                const container = document.getElementById('today-events-list');
                
                if (eventsList.length === 0) {
                    container.innerHTML = '<p class="text-muted text-center">No events for this date</p>';
                    return;
                }
                
                let html = '';
                eventsList.forEach(event => {
                    html += `
                        <div class="event-item">
                            <div class="event-title">${event.name}</div>
                            <div class="event-description">${event.description}</div>
                            <div class="event-date">Status: ${event.status}</div>
                        </div>
                    `;
                });
                
                container.innerHTML = html;
            }
            
            function loadCalendar(month, year) {
                tbody.innerHTML = '';
                
                // Update month highlighting
                monthLabels.forEach((label, index) => {
                    if (index === month) {
                        label.classList.add('active');
                    } else {
                        label.classList.remove('active');
                    }
                });
                
                yearLabel.textContent = `${monthNames[month]} ${year}`;
                
                let firstDay = new Date(year, month, 1).getDay();
                let daysInMonth = new Date(year, month + 1, 0).getDate();
                
                let date = 1;
                let row = document.createElement('tr');
                
                // Create calendar cells
                for (let i = 0; i < 6; i++) {
                    row = document.createElement('tr');
                    
                    for (let j = 0; j < 7; j++) {
                        let cell = document.createElement('td');
                        
                        if (i === 0 && j < firstDay) {
                            cell.textContent = '';
                            row.appendChild(cell);
                        } else if (date > daysInMonth) {
                            cell.textContent = '';
                            row.appendChild(cell);
                        } else {
                            cell.textContent = date;
                            
                            // Check if this date has events
                            if (hasEvent(year, month, date)) {
                                cell.classList.add('has-event');
                            }
                            
                            // Check if this is today
                            if (date === currentDate.getDate() && 
                                month === currentDate.getMonth() && 
                                year === currentDate.getFullYear()) {
                                cell.classList.add('today');
                            }
                            
                            // Add click handler
                            cell.addEventListener('click', function() {
                                // Remove selected class from all cells
                                document.querySelectorAll('.dates-table td').forEach(td => {
                                    td.classList.remove('selected');
                                });
                                
                                // Add selected class to clicked cell
                                this.classList.add('selected');
                                
                                // Display events for this date
                                displayEventsForDate(year, month, parseInt(this.textContent));
                            });
                            
                            row.appendChild(cell);
                            date++;
                        }
                    }
                    
                    tbody.appendChild(row);
                }
            }
            
            // Add month click handlers
            monthLabels.forEach((label, index) => {
                label.addEventListener('click', function() {
                    currentMonth = index;
                    loadCalendar(currentMonth, currentYear);
                });
            });
            
            // Previous month button
            prevBtn.addEventListener('click', function() {
                if (currentMonth === 0) {
                    currentMonth = 11;
                    currentYear--;
                } else {
                    currentMonth--;
                }
                loadCalendar(currentMonth, currentYear);
            });
            
            // Next month button
            nextBtn.addEventListener('click', function() {
                if (currentMonth === 11) {
                    currentMonth = 0;
                    currentYear++;
                } else {
                    currentMonth++;
                }
                loadCalendar(currentMonth, currentYear);
            });
            
            // Add event button
            if (addButton) {
                addButton.addEventListener('click', function() {
                    // Set current date in dialog
                    document.getElementById('event_year').value = currentYear;
                    document.getElementById('event_month').value = currentMonth + 1;
                    document.getElementById('event_day').value = new Date().getDate();
                    
                    // Show dialog
                    new bootstrap.Modal(document.getElementById('eventDialog')).show();
                });
            }
            
            // Handle form submission
            const eventForm = document.getElementById('eventForm');
            if (eventForm) {
                eventForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData();
                    formData.append('name', document.getElementById('event_name').value);
                    formData.append('description', document.getElementById('event_description').value);
                    formData.append('year', document.getElementById('event_year').value);
                    formData.append('month', document.getElementById('event_month').value);
                    formData.append('day', document.getElementById('event_day').value);
                    formData.append('status', 'new');
                    
                    fetch('{{ route("schedule.create-event") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error creating event');
                        }
                    });
                });
            }
            
            // Cancel button
            document.getElementById('cancel-button')?.addEventListener('click', function() {
                bootstrap.Modal.getInstance(document.getElementById('eventDialog')).hide();
            });
            
            // Load initial calendar
            loadCalendar(currentMonth, currentYear);
        }
    </script>
</div>
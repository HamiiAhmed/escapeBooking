<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EscapeBooking - Monthly Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: transparent;
            /* FIXED: trasparent → transparent */
            min-height: 100vh;
            padding: 20px;
        }

        .calendar-wrapper {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            /* overflow: hidden; REMOVED for smooth scroll */
        }

        .header {
            background: #6b0501;
            color: white;
            padding: 15px;
            text-align: center;
        }

        .header h1 {
            font-size: 22px;
            margin: 0;
        }

        #calendar {
            padding: 30px;
        }

        /* CALENDAR COLORS */
        .fc-daygrid-day.fc-day-available {
            background: #6b0501 !important;
            border: 2px solid #cf9b5d !important;
            cursor: pointer;
        }

        .fc-daygrid-day.fc-day-past {
            background: #f8f9fa !important;
            color: #6c757d !important;
            cursor: not-allowed !important;
        }

        .fc-event {
            border-radius: 4px !important;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* PACKAGES SECTION */
        .packages-section {
            padding: 0 25px;
            background: #f8f9fa;
            border-top: 3px solid #6b0501;
            max-height: 0;
            overflow: hidden;
            transition: all 0.5s ease;
        }

        .packages-section.active {
            max-height: 1200px;
            padding: 25px;
        }

        .packages-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        #selectedDateTitle {
            color: #6b0501;
            font-size: 20px;
            margin: 0;
        }

        .back-btn {
            background: #6b0501;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background: #cf9b5d;
        }

        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        /* PACKAGE CARDS */
        .compact-package-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            height: 175px;
            display: flex;
            flex-direction: column;
        }

        .compact-package-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            border-color: #6b0501;
        }

        .compact-package-card.selected {
            border-color: #27ae60 !important;
            box-shadow: 0 10px 30px rgba(39, 174, 96, 0.3);
        }

        .compact-image {
            height: 100px;
            width: 100%;
            object-fit: cover;
            background: linear-gradient(45deg, #6b0501, #cf9b5d);
        }

        .fc .fc-daygrid-day-number {
            color: white !important;
        }

        .compact-content {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .compact-name {
            font-weight: 700;
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 8px;
            overflow: hidden;
        }

        .compact-price {
            font-size: 18px;
            font-weight: 800;
            color: #e74c3c;
            margin-bottom: 8px;
        }

        .compact-duration {
            font-size: 14px;
            color: #27ae60;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .compact-bookings {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: auto;
        }

        .compact-book-btn {
            background: #6b0501;
            color: white;
            border: none;
            padding: 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            margin-top: auto;
        }

        /* SLOTS SECTION */
        .slots-section {
            margin-top: 25px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .slots-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-weight: 700;
            color: #2c3e50;
        }

        .time-slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 12px;
            margin-bottom: 25px;
        }

        .time-slot {
            padding: 12px 8px;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .time-slot.available {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
        }

        .time-slot.booked {
            background: #f8d7da;
            color: #dc3545;
            cursor: not-allowed;
        }

        .time-slot.available:hover {
            transform: scale(1.05);
        }

        .time-slot.selected-slot {
            box-shadow: 0 0 0 3px #f39c12;
        }

        /* BOOKING FORM */
        .booking-form {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #6b0501;
            box-shadow: 0 0 0 3px rgba(141, 27, 19, 0.1);
        }

        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            background-color: #fff;
            color: #2c3e50;
            transition: border 0.3s, box-shadow 0.3s;

            /* Remove default styling */
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;

            /* Custom dropdown arrow */
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%232c3e50'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z' clip-rule='evenodd'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 18px;
            padding-right: 40px;
        }

        .form-group select:focus {
            outline: none;
            border-color: #6b0501;
            box-shadow: 0 0 0 3px rgba(141, 27, 19, 0.1);
        }

        .book-now-btn {
            width: 100%;
            padding: 15px;
            background: #6b0501;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
        }

        .book-now-btn:hover {
            background: #6b0501;
        }

        .book-now-btn:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .packages-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }

            .compact-package-card {
                height: 200px;
            }

            .compact-image {
                height: 80px;
            }
        }
    </style>
</head>

<body>
    <div class="calendar-wrapper">
        <div class="header">
            <h1><i class="fas fa-calendar-alt"></i> Escape Booking</h1>
        </div>
        <div id="calendar"></div>

        <!-- PACKAGES SECTION -->
        <div class="packages-section" id="packagesSection">
            <div class="packages-header">
                <h3 id="selectedDateTitle">📅 Select Date First</h3>
                <button onclick="hidePackages()" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Calendar
                </button>
            </div>
            <div class="packages-grid" id="packagesGrid"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- head section mein -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        // PHP Packages Data to JS
        const packages = @json($packages);
        let selectedPackageId = null;
        let selectedDate = null;
        let selectedSlot = null;

        $(document).ready(function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'today'
                },
                validRange: {
                    start: new Date()
                },
                dayMaxEvents: true,
                height: 'auto',
                dateClick: function(info) {
                    if (info.dayEl.classList.contains('fc-day-past')) return false;
                    showPackages(info.dateStr);
                    hideSlots();
                },
                dayCellClassNames: function(arg) {
                    var today = new Date();
                    today.setHours(0, 0, 0, 0);
                    var cellDate = new Date(arg.date);
                    cellDate.setHours(0, 0, 0, 0);
                    return cellDate < today ? ['fc-day-past'] : ['fc-day-available'];
                }
            });
            calendar.render();
        });

        function showPackages(dateStr) {
            selectedDate = dateStr;
            $('#selectedDateTitle').html('📅 ' + new Date(dateStr).toLocaleDateString('ur-PK'));
            $('#packagesGrid').empty();

            packages.forEach(function(pkg) {


                var html = `
                    <div class="compact-package-card" onclick="selectPackage(${pkg.id}, '${dateStr}')">
                        
                        <div class="compact-content">
                            <div class="compact-name">${pkg.name}</div>
                            <div class="compact-price">Rs ${pkg.price}</div>
                            <div class="compact-duration">⏰ ${pkg.duration_minutes}min</div>
                            <div class="compact-bookings">👥 ${pkg.min_bookings}-${pkg.max_bookings}</div>
                            <button class="compact-book-btn">Book Now</button>
                        </div>
                    </div>
                `;
                $('#packagesGrid').append(html);
            });

            $('#packagesSection').addClass('active');
            setTimeout(() => {
                document.getElementById('packagesSection').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 150);
        }

        function hidePackages() {
            $('#packagesSection').removeClass('active');
            setTimeout(() => {
                document.getElementById('calendar').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 150);
        }

        function selectPackage(packageId, dateStr) {
            selectedPackageId = packageId;
            $('.compact-package-card').removeClass('selected');
            $(event.target).closest('.compact-package-card').addClass('selected');

            // AJAX call for slots
            $.ajax({
                url: '/calendar/getBookings',
                method: 'GET',
                data: {
                    package_id: packageId,
                    date: dateStr
                },
                success: function(response) {
                    showSlots(packageId, dateStr, response.slots, response.package);
                },
                error: function() {
                    alert('Sometihing went wrong. Please refresh!');
                }
            });
        }

        function showSlots(packageId, dateStr, slots, package) {
            $('#slotsSection').remove(); // Previous slots clear

            $('#packagesGrid').after(`
        <div class="slots-section" id="slotsSection">
            <div class="slots-header">
                <span>🕒 Available Time Slots - ${new Date(dateStr).toLocaleDateString('ur-PK')}</span>
                <button onclick="hideSlots()" style="background:none;border:none;font-size:18px;cursor:pointer;color:#dc3545;">✕</button>
            </div>
            <div class="time-slots-grid" id="slotsGrid"></div>
            <form class="booking-form" id="bookingForm">
                <div class="form-group">
                    <label>Customer Name *</label>
                    <input type="text" name="customer_name" required placeholder="Full Name">
                </div>
                <div class="form-group">
                    <label>Number of People * (${package.min_bookings}-${package.max_bookings})</label>
                    <select name="num_people" class="form-select" id="numPeople" required>
                        <option value="">Select Number</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Phone *</label>
                    <input type="tel" name="customer_phone" required placeholder="XXXXXXXXXXX">
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="customer_email" required placeholder="email@example.com">
                </div>
                <!-- Hidden fields -->
                <input type="hidden" name="package_id" value="${packageId}">
                <input type="hidden" name="date" value="${dateStr}">
                <input type="hidden" name="package_min" value="${package.min_bookings}">
                <input type="hidden" name="package_max" value="${package.max_bookings}">
                <input type="hidden" name="package_price" value="${package.price}">
                <input type="hidden" name="selected_slot" id="selectedSlot">
                <div id="totalPrice" style="font-size:18px;font-weight:bold;color:#27ae60;margin:15px 0;text-align:center;">Total: Rs 0</div>
                <button type="submit" class="book-now-btn" id="bookBtn" disabled>📅 Proceed to Payment</button>
            </form>
        </div>
    `);

            // Slots populate
            const slotsGrid = $('#slotsGrid');
            slotsGrid.empty();

            slots.forEach(function(slot) {
                slotsGrid.append(`
            <div class="time-slot ${slot.is_available ? 'available' : 'booked'}" 
                 ${slot.is_available ? `onclick="selectSlot('${slot.start_full}', this)"` : ''}>
                ${slot.start}<br><small>${slot.end}</small>
                ${!slot.is_available ? '<br><small>(Booked)</small>' : ''}
            </div>
        `);
            });

            // ✅ Number of People dropdown populate
            const numPeopleSelect = $('#numPeople');
            numPeopleSelect.empty().append('<option value="">Select Number</option>');

            for (let i = package.min_bookings; i <= package.max_bookings; i++) {
                numPeopleSelect.append(`<option value="${i}">${i} People</option>`);
            }

            // Total price calculation
            $('#numPeople, #selectedSlot').on('change', function() {
                calculateTotal();
            });

            $('html, body').animate({
                scrollTop: $("#slotsSection").offset().top - 50
            }, 500);
        }

        function calculateTotal() {
            const numPeople = parseInt($('#numPeople').val()) || 0;
            const packagePrice = parseInt($('input[name="package_price"]').val()) || 0;
            const total = numPeople * packagePrice;

            $('#totalPrice').text(`Total: SAR ${total.toLocaleString()}`);

            if (numPeople > 0 && selectedSlot) {
                $('#bookBtn').prop('disabled', false);
            } else {
                $('#bookBtn').prop('disabled', true);
            }
        }

        function selectSlot(slotTime, element) {
            selectedSlot = slotTime;
            $('.time-slot').removeClass('selected-slot');
            $(element).addClass('selected-slot');
            $('#selectedSlot').val(slotTime);
            calculateTotal(); // Recalculate total
        }

        function hideSlots() {
            $('#slotsSection').remove();
            $('.compact-package-card').removeClass('selected');
        }

        $(document).on('submit', '#bookingForm', function(e) {
            e.preventDefault();
            $('#bookBtn').prop('disabled', true);
            
            if (!selectedSlot) {
                alert('First select the time slot!');
                $('#bookBtn').prop('disabled', false);
                return;
            }

            const formData = new FormData(this);
            formData.append('selected_slot', selectedSlot);
            formData.append('_token', $('meta[name="csrf-token"]').attr('content')); // ✅ EXTRA

            $.ajax({
                url: '{{ route('booking.initiate') }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.redirect_url) {
                        console.log('🔄 Redirecting to:', response.redirect_url);
                        window.location.href = response.redirect_url;
                    }
                },

                error: function(xhr, errorType, error) {
                    console.error('❌ AJAX ERROR:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        response: xhr.responseText,
                        errorType: errorType
                    });

                    let errorMsg = 'Booking failed! Please try again.';

                    if (xhr.status === 422) {
                        try {
                            const errors = JSON.parse(xhr.responseText);
                            errorMsg = Object.values(errors)[0][0];
                        } catch (e) {
                            errorMsg = 'Validation failed!';
                        }
                    } else if (xhr.status >= 500) {
                        errorMsg = 'Server error! Please try again later.';
                    } else if (xhr.status === 401 || xhr.status === 403) {
                        errorMsg = 'Session expired! Please login again.';
                        window.location.href = '/login';
                        return;
                    }

                    alert(`❌ ${errorMsg}\n\nStatus: ${xhr.status}\nCheck console for details.`);
                    $('#bookBtn').prop('disabled', false);
                }
            });
        });
    </script>
</body>

</html>

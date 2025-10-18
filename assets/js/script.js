// script.js
document.addEventListener('DOMContentLoaded', function () {
    const calendar = document.getElementById('calendar');

    const daysOfWeek = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
    const hoursWeekdays = Array.from({ length: 14 }, (_, i) => 9 + i);
    const hoursSaturday = Array.from({ length: 6 }, (_, i) => 9 + i);
    const bookedAppointments = [
        { day: 'Lundi', hour: 10, name: 'John Doe' },
        { day: 'Mardi', hour: 14, name: 'Jane Doe' },
        { day: 'Vendredi', hour: 17, name: 'Alice Smith' },
        { day: 'Samedi', hour: 10, name: 'Bob Johnson' }
    ];

    daysOfWeek.forEach(day => {
        const dayColumn = document.createElement('div');
        dayColumn.className = 'day';

        const dayHeader = document.createElement('div');
        dayHeader.className = 'day-header';
        dayHeader.textContent = day;
        dayColumn.appendChild(dayHeader);

        const hours = day === 'Samedi' ? hoursSaturday : hoursWeekdays;
        if (day !== 'Dimanche') {
            hours.forEach(hour => {
                const hourBlock = document.createElement('div');
                hourBlock.className = 'hour';
                hourBlock.dataset.day = day;
                hourBlock.dataset.hour = hour;

                const appointment = bookedAppointments.find(app => app.day === day && app.hour === hour);
                if (appointment) {
                    hourBlock.className += ' booked';
                    hourBlock.textContent = `${hour}:00 - ${appointment.name}`;
                } else {
                    hourBlock.textContent = `${hour}:00`;
                }

                dayColumn.appendChild(hourBlock);
            });
        }

        calendar.appendChild(dayColumn);
    });
});

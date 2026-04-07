<!DOCTYPE html>
<html>
<head>
    <title>Booking Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #28a745; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .booking-details { background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #28a745; }
        .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Booking Confirmed!</h1>
        </div>
        <div class="content">
            <h2>Hello {{ $name }}!</h2>
            <p>Your booking has been successfully confirmed. Here are the details:</p>

            <div class="booking-details">
                <h3>Booking Details</h3>
                <p><strong>Programme:</strong> {{ $booking['programme'] ?? 'N/A' }}</p>
                <p><strong>Date:</strong> {{ $booking['date'] ?? 'N/A' }}</p>
                <p><strong>Time:</strong> {{ $booking['time'] ?? 'N/A' }}</p>
                <p><strong>Room:</strong> {{ $booking['room'] ?? 'N/A' }}</p>
                <p><strong>Trainer:</strong> {{ $booking['trainer'] ?? 'N/A' }}</p>
                <p><strong>Booking ID:</strong> {{ $booking['id'] ?? 'N/A' }}</p>
            </div>

            <p>Please arrive 10 minutes before your scheduled time. If you need to cancel or reschedule, please do so at least 24 hours in advance.</p>

            <p>We look forward to seeing you at the gym!</p>
        </div>
        <div class="footer">
            <p>This email was sent from Exergame - MINDEF Gym Portal</p>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Exergame</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Exergame!</h1>
        </div>
        <div class="content">
            <h2>Hello {{ $name }}!</h2>
            <p>Welcome to the official MINDEF Gym Portal. We're excited to have you on board!</p>
            <p>You can now:</p>
            <ul>
                <li>Book gym sessions</li>
                <li>View available programmes</li>
                <li>Manage your bookings</li>
                <li>Track your fitness progress</li>
            </ul>
            <p>Get started by logging into your account and exploring all the features we have to offer.</p>
        </div>
        <div class="footer">
            <p>This email was sent from Exergame - MINDEF Gym Portal</p>
        </div>
    </div>
</body>
</html>

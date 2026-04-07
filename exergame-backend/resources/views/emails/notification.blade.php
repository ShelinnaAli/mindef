<!DOCTYPE html>
<html>
<head>
    <title>Notification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #17a2b8; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .message { background-color: white; padding: 15px; margin: 15px 0; border-radius: 4px; }
        .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Notification</h1>
        </div>
        <div class="content">
            <h2>Hello {{ $name }}!</h2>
            <p>You have received a new notification from Exergame:</p>

            <div class="message">
                {{ $notificationMessage }}
            </div>

            <p>Thank you for using Exergame!</p>
        </div>
        <div class="footer">
            <p>This email was sent from Exergame - MINDEF Gym Portal</p>
        </div>
    </div>
</body>
</html>

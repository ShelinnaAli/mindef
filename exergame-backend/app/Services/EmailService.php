<?php

namespace App\Services;

use App\Services\MailConfigService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailable;

class EmailService
{
    /**
     * Send a welcome email to a user
     *
     * @param string $userEmail
     * @param string $userName
     * @return bool
     */
    public static function sendWelcomeEmail(string $userEmail, string $userName): bool
    {
        try {
            // Configure mail from database
            MailConfigService::configureFromDatabase();

            // Send the email
            Mail::send('emails.welcome', ['name' => $userName], function ($message) use ($userEmail, $userName) {
                $message->to($userEmail, $userName)
                        ->subject('Welcome to Exergame!');
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send welcome email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a booking confirmation email
     *
     * @param string $userEmail
     * @param string $userName
     * @param array $bookingDetails
     * @return bool
     */
    public static function sendBookingConfirmation(string $userEmail, string $userName, array $bookingDetails): bool
    {
        try {
            // Configure mail from database
            MailConfigService::configureFromDatabase();

            // Send the email
            Mail::send('emails.booking-confirmation', [
                'name' => $userName,
                'booking' => $bookingDetails
            ], function ($message) use ($userEmail, $userName) {
                $message->to($userEmail, $userName)
                        ->subject('Booking Confirmation - Exergame');
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send booking confirmation email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a password reset email
     *
     * @param string $userEmail
     * @param string $userName
     * @param string $resetToken
     * @return bool
     */
    public static function sendPasswordReset(string $userEmail, string $userName, string $resetToken): bool
    {
        try {
            // Configure mail from database
            MailConfigService::configureFromDatabase();

            $resetUrl = env('APP_URL') . '/reset-password?token=' . $resetToken;

            // Send the email
            Mail::send('emails.password-reset', [
                'name' => $userName,
                'resetUrl' => $resetUrl
            ], function ($message) use ($userEmail, $userName) {
                $message->to($userEmail, $userName)
                        ->subject('Reset Your Password - Exergame');
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a simple notification email
     *
     * @param string $userEmail
     * @param string $userName
     * @param string $subject
     * @param string $message
     * @return bool
     */
    public static function sendNotification(string $userEmail, string $userName, string $subject, string $message): bool
    {
        try {
            // Configure mail from database
            MailConfigService::configureFromDatabase();

            // Send the email
            Mail::send('emails.notification', [
                'name' => $userName,
                'notificationMessage' => $message
            ], function ($mail) use ($userEmail, $userName, $subject) {
                $mail->to($userEmail, $userName)
                     ->subject($subject);
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send notification email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a test email to verify SMTP configuration
     *
     * @param string $testEmail
     * @return array
     */
    public static function sendTestEmail(string $testEmail): array
    {
        try {
            // Get current mail configuration
            $config = MailConfigService::getCurrentConfig();

            // Configure mail from database
            MailConfigService::configureFromDatabase();

            // Send test email
            Mail::raw('This is a test email to verify your SMTP configuration is working correctly.', function ($message) use ($testEmail) {
                $message->to($testEmail)
                        ->subject('SMTP Configuration Test - Exergame');
            });

            return [
                'success' => true,
                'message' => 'Test email sent successfully',
                'config' => $config
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
                'config' => MailConfigService::getCurrentConfig()
            ];
        }
    }

    /**
     * Send email using a Mailable class with database configuration
     *
     * @param Mailable $mailable
     * @return bool
     */
    public static function sendMailable(Mailable $mailable): bool
    {
        try {
            // Configure mail from database
            MailConfigService::configureFromDatabase();

            // Send the mailable
            Mail::send($mailable);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send mailable email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get current mail configuration for debugging
     *
     * @return array
     */
    public static function getMailConfiguration(): array
    {
        return MailConfigService::getCurrentConfig();
    }
}

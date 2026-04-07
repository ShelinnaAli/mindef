<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class MailConfigService
{
    /**
     * Configure mail settings from database
     */
    public static function configureFromDatabase(): void
    {
        try {
            $smtpSettings = AppSettingService::getSmtpSettings();

            if (!empty($smtpSettings)) {
                // Update mail configuration at runtime
                Config::set([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.host' => $smtpSettings['SMTP_HOST'] ?? Config::get('mail.mailers.smtp.host'),
                    'mail.mailers.smtp.port' => $smtpSettings['SMTP_PORT'] ?? Config::get('mail.mailers.smtp.port'),
                    'mail.mailers.smtp.username' => $smtpSettings['SMTP_USERNAME'] ?? Config::get('mail.mailers.smtp.username'),
                    'mail.mailers.smtp.password' => $smtpSettings['SMTP_PASSWORD'] ?? Config::get('mail.mailers.smtp.password'),
                    'mail.from.address' => $smtpSettings['SMTP_EMAIL_SENDER'] ?? Config::get('mail.from.address'),
                ]);

                // Purge the mail manager so it picks up the new configuration
                Mail::purge();
            }
        } catch (\Exception $e) {
            // Log the error but don't break the application
            \Log::warning('Failed to configure mail from database: ' . $e->getMessage());
        }
    }

    /**
     * Send email with database configuration
     */
    public static function send($mailable)
    {
        self::configureFromDatabase();
        return Mail::send($mailable);
    }

    /**
     * Get current mail configuration with database values
     */
    public static function getCurrentConfig(): array
    {
        try {
            $smtpSettings = AppSettingService::getSmtpSettings();

            return [
                'host' => $smtpSettings['SMTP_HOST'] ?? env('MAIL_HOST'),
                'port' => $smtpSettings['SMTP_PORT'] ?? env('MAIL_PORT'),
                'username' => $smtpSettings['SMTP_USERNAME'] ?? env('MAIL_USERNAME'),
                'password' => $smtpSettings['SMTP_PASSWORD'] ? '***HIDDEN***' : null,
                'from_address' => $smtpSettings['SMTP_EMAIL_SENDER'] ?? env('MAIL_FROM_ADDRESS'),
                'source' => !empty($smtpSettings) ? 'database' : 'environment'
            ];
        } catch (\Exception $e) {
            return [
                'host' => env('MAIL_HOST'),
                'port' => env('MAIL_PORT'),
                'username' => env('MAIL_USERNAME'),
                'password' => env('MAIL_PASSWORD') ? '***HIDDEN***' : null,
                'from_address' => env('MAIL_FROM_ADDRESS'),
                'source' => 'environment (database unavailable)'
            ];
        }
    }
}

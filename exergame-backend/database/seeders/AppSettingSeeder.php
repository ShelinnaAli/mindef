<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AppSetting;

class AppSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'APP_NAME' => 'FITCOMMAND MINDEF',
            'ADVERTISEMENT_MSG' => 'Welcome to the official MINDEF Gym Portal. Book your sessions now!',
            'LOGIN_BACKGROUND_MEDIA_URL' => 'https://assets.mixkit.co/active_storage/video_items/100530/1725383823/100530-video-720.mp4',
        ];

        foreach ($data as $key => $value) {
            AppSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'flag' => 'WEB']
            );
        }


        $data = [
            'SMTP_HOST' => null,
            'SMTP_PORT' => null,
            'SMTP_USERNAME' => null,
            'SMTP_PASSWORD' => null,
            'SMTP_EMAIL_SENDER' => null,
        ];

        foreach ($data as $key => $value) {
            AppSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'flag' => 'SMTP']
            );
        }
    }
}

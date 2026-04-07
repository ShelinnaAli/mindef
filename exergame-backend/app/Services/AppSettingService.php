<?php

namespace App\Services;

use App\Models\AppSetting;

class AppSettingService
{
    /**
     * Create a new AppSetting record with the provided data.
     *
     * @param array $data The data to be used for creating the AppSetting.
     * @return AppSetting The newly created AppSetting instance.
     */
    public static function create(array $data): AppSetting
    {
        return AppSetting::create($data);
    }

    /**
     * Update the specified AppSetting with the provided data.
     *
     * @param string $flag The flag of the AppSetting to update.
     * @param array $data The data to update the AppSetting with.
     * @return array The updated AppSetting items.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the AppSetting with the given ID does not exist.
     */
    public static function updateAppSetting(string $flag, array $data): array
    {
        $appSettings = [];
        foreach($data as $key => $value) {
            $key = \Str::upper(\Str::snake($key));

            $appSetting = AppSetting::where('flag', $flag)
                ->where('key', $key)
                ->first();

            if ($appSetting) {
                $appSetting->update(['value' => $value]);
            } else {
                // If the setting does not exist, create it
                $appSetting = AppSetting::create([
                    'flag' => $flag,
                    'key' => $key,
                    'value' => $value
                ]);
            }
            $appSettings[] = $appSetting;
        }
        return $appSettings;
    }

    /**
     * Retrieves application settings from the database, optionally filtered by a flag.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming request containing optional filter parameters.
     * @return \Illuminate\Database\Eloquent\Collection  A collection of AppSetting models ordered by key.
     */
    public static function getAppSettings($request)
    {
        return AppSetting::query()
            ->when($request->flag, function ($query, $flag) {
                $query->where('flag', $flag);
            })
            ->orderBy('key')
            ->get();
    }

    /**
     * Get SMTP settings from database as an associative array.
     *
     * @return array SMTP settings with keys mapped to values
     */
    public static function getSmtpSettings(): array
    {
        $smtpSettings = AppSetting::where('flag', 'SMTP')->get();

        $settings = [];
        foreach ($smtpSettings as $setting) {
            $settings[$setting->key] = $setting->value;
        }

        return $settings;
    }

    // get available flags
    public static function getAvailableFlags(): array
    {
        return AppSetting::distinct()->pluck('flag')->toArray();
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppSettingRequest;
use App\Services\AppSettingService;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    private const PUBLIC_FLAG = ['WEB'];

    /**
     * Retrieve all application settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request.
     * @return \Illuminate\Http\JsonResponse
     */
    public function findAll(Request $request, $flag = null)
    {
        try{
            if ($flag) {
                if (!in_array($flag, AppSettingService::getAvailableFlags())) {
                    throw new \Exception('App setting not found');
                }
                $request->merge(['flag' => $flag]);
            }
            $appSettings = AppSettingService::getAppSettings($request);

            return $this->response(
                'App settings retrieved successfully',
                $appSettings->toArray(),
                true
            );
        } catch (\Exception $e) {
            \Log::error('APP SETTINGS FETCH ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to fetch app settings: '.$e->getMessage(), 500);
        }
    }

    /**
     * Update the specified app setting.
     *
     * @param  AppSettingRequest  $request  The validated request instance containing update data.
     * @param  int  $flag  The flag of the app setting to update.
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(AppSettingRequest $request, $flag)
    {
        try {
            if (!in_array($flag, AppSettingService::getAvailableFlags())) {
                throw new \Exception('App setting not found');
            }
            $appSettings = AppSettingService::updateAppSetting($flag, $request->settings);

            return $this->response('App setting updated successfully', $appSettings);
        } catch (\Exception $e) {
            \Log::error('APP SETTING UPDATE ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to update app setting: '.$e->getMessage(), 500);
        }
    }
}

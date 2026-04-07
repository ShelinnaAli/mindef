<?php

namespace App\Http\Controllers;

use App\Services\ProgrammeService;
use App\Services\UserService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Get user age distributions report
     */
    public function userAgeDistributions(Request $request)
    {
        try {
            $summary = UserService::getUserAgeDistributions($request);
            return $this->response(
                'Success to retrieve user age distributions',
                $summary->toArray(),
                true
            );
        } catch (\Exception $e) {
            \Log::error('USER AGE DISTRIBUTIONS FETCH ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to fetch user age distributions: '.$e->getMessage(), 500);
        }
    }
    /**
     * Get user participation history report
     */
    public function userParticipationHistory(Request $request)
    {
        try{
            $summary = UserService::getUserParticipationHistory($request);
            return $this->response(
                'Success to retrieve user participation history',
                $summary->toArray(),
                true
            );
        } catch (\Exception $e) {
            \Log::error('USER PARTICIPATION HISTORY FETCH ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to fetch user participation history: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get user participation report with programme and booking details.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userParticipationReport(Request $request)
    {
        try {
            $report = UserService::getUserParticipationReport($request);
            return $this->response(
                'User participation report retrieved successfully',
                $report->toArray(),
                true
            );
        } catch (\Exception $e) {
            \Log::error('GET USER PARTICIPATION REPORT ERROR: ', [$e->getMessage(), $request->all()]);
            return $this->errorResponse('Failed to retrieve user participation report: '.$e->getMessage());
        }
    }

    /**
     * Get user list report
     */
    public function userListReport(Request $request)
    {
        try{
            $summary = UserService::getUserListReport($request);
            return $this->response(
                'Success to retrieve user list report',
                $summary->toArray(),
                true
            );
        } catch (\Exception $e) {
            \Log::error('USER LIST REPORT FETCH ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to fetch user list report: '.$e->getMessage(), 500);
        }
    }
    /**
     * Get programme cancellation frequencies report
     */
    public function programmeCancellationFrequencies(Request $request)
    {
        try{
            $summary = ProgrammeService::getProgrammeCancellationFrequencies($request);
            return $this->response(
                'Success to retrieve programme cancellation frequencies',
                $summary->toArray(),
                true
            );
        } catch (\Exception $e) {
            \Log::error('PROGRAMMES CANCELLATION FREQUENCIES FETCH ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to fetch programme cancellation frequencies: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get programme take up rates report
     */
    public function programmeTakeUpRates(Request $request)
    {
        try{
            $summary = ProgrammeService::getProgrammeTakeUpRates($request);
            return $this->response(
                'Success to retrieve programme take up rates',
                $summary->toArray(),
                true
            );
        } catch (\Exception $e) {
            \Log::error('PROGRAMMES TAKE UP RATES FETCH ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to fetch programme take up rates: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get programme run frequencies report
     */
    public function programmeRunFrequencies(Request $request)
    {
        try{
            $summary = ProgrammeService::getProgrammeRunFrequencies($request);
            return $this->response(
                'Success to retrieve programme run frequencies',
                $summary->toArray(),
                true
            );
        } catch (\Exception $e) {
            \Log::error('PROGRAMMES RUN FREQUENCIES FETCH ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to fetch programme run frequencies: '.$e->getMessage(), 500);
        }
    }
}

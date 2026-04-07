<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProgrammeSchedule\GetMonthlySchedulesRequest;
use App\Http\Requests\ProgrammeSchedule\StoreProgrammeScheduleRequest;
use App\Http\Requests\ProgrammeSchedule\UpdateProgrammeScheduleRequest;
use App\Models\ProgrammeSchedule;
use App\Services\ProgrammeScheduleService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProgrammeScheduleController extends Controller
{

    /**
     * Get average completed bookings per session for a trainer
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function trainerAvgAttendance(Request $request)
    {
        try {
            $avg = ProgrammeScheduleService::getTrainerAvgAttendance();

            return $this->response('Trainer average attendance retrieved successfully', ['average' => $avg]);
        } catch (\Exception $e) {
            \Log::error('GET TRAINER AVG ATTENDANCE ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to retrieve trainer average attendance: '.$e->getMessage());
        }
    }

    /**
     * Get total bookings for a trainer's programme schedules
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function trainerTotalBookings(Request $request)
    {
        try {
            $total = ProgrammeScheduleService::getTrainerTotalBookings();

            return $this->response('Trainer total bookings retrieved successfully', ['total' => $total]);
        } catch (\Exception $e) {
            \Log::error('GET TRAINER TOTAL BOOKINGS ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to retrieve trainer total bookings: '.$e->getMessage());
        }
    }

    /**
     * Get programme schedule counters (active and cancelled)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function scheduleCounters(Request $request)
    {
        try {
            $counters = ProgrammeScheduleService::getScheduleCounters($request);

            return $this->response('Programme schedule counters retrieved successfully', $counters);
        } catch (\Exception $e) {
            \Log::error('GET SCHEDULE COUNTERS ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to retrieve schedule counters: '.$e->getMessage());
        }
    }

    /**
     * Get all programme schedules
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function findAll(Request $request)
    {
        try {
            $schedules = ProgrammeScheduleService::getSchedules($request);

            return $this->response(
                'All programme schedules retrieved successfully',
                $schedules->toArray(),
                true
            );
        } catch (\Exception $e) {
            \Log::error('PROGRAMME SCHEDULES FETCH ALL ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to fetch all schedules: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get users who have bookings for a specific programme schedule
     *
     * @param  int  $scheduleId  The ID of the programme schedule
     * @return \Illuminate\Http\JsonResponse
     */
    public function findUserBookingsByScheduleId(Request $request, $scheduleId)
    {
        try {
            $users = ProgrammeScheduleService::getUserBookingsByScheduleId((int) $scheduleId);

            return $this->response(
                'Users retrieved successfully',
                $users->toArray(),
                true
            );
        } catch (\Exception $e) {
            \Log::error('GET USERS FOR SCHEDULE ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to retrieve users for schedule: '.$e->getMessage());
        }
    }

    /**
     * Get monthly programme schedules
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMonthlySchedules(GetMonthlySchedulesRequest $request)
    {
        try {
            $schedules = ProgrammeScheduleService::getMonthlySchedules(
                $request->validated('year'),
                $request->validated('month')
            );

            return $this->response(
                'Monthly schedules retrieved successfully',
                $schedules->toArray(),
                true
            );
        } catch (\Exception $e) {
            \Log::error('PROGRAMME SCHEDULES FETCH ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to fetch schedules: '.$e->getMessage(), 500);
        }
    }

    /**
     * Create a new programme schedule
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreProgrammeScheduleRequest $request)
    {
        try {
            // Map camelCase input to snake_case for database
            $mappedData = $this->mapInputData($request->all(), ProgrammeSchedule::class);

            $schedule = ProgrammeScheduleService::createSchedule($mappedData);

            return $this->response(
                'Programme schedule created successfully',
                $schedule->toArray(),
                true,
                201
            );
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('PROGRAMME SCHEDULE CREATE ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to create programme schedule: '.$e->getMessage(), 500);
        }
    }

    /**
     * Update a specific programme schedule
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateProgrammeScheduleRequest $request, $id)
    {
        try {
            // Map camelCase input to snake_case for database
            $data = $this->mapInputData($request->all(), ProgrammeSchedule::class);

            $schedule = ProgrammeScheduleService::updateSchedule($id, $data);

            return $this->response(
                'Programme schedule updated successfully',
                $schedule->toArray(),
                true
            );
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('PROGRAMME SCHEDULE UPDATE ERROR: ', [$e->getMessage(), $request->all(), $e->getTraceAsString()]);

            return $this->errorResponse('Failed to update programme schedule: '.$e->getMessage(), 500);
        }
    }
}

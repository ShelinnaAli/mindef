<?php

namespace App\Http\Controllers;

use App\Http\Requests\Programme\StoreProgrammeRequest;
use App\Http\Requests\Programme\UpdateProgrammeRequest;
use App\Http\Requests\Programme\UploadProgrammeCoverRequest;
use App\Http\Requests\ProgrammeSchedule\StoreProgrammeScheduleRequest;
use App\Models\Programme;
use App\Models\ProgrammeSchedule;
use App\Services\ProgrammeScheduleService;
use App\Services\ProgrammeService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProgrammeController extends Controller
{
    /**
     * Get all active programmes with their game types
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function findAll(Request $request)
    {
        try {
            $programmes = ProgrammeService::getProgrammes($request);

            return $this->response('Programmes retrieved successfully', $programmes->toArray());
        } catch (\Exception $e) {
            \Log::error('PROGRAMMES FETCH ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to fetch programmes: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get a specific programme by ID
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function find($id)
    {
        try {
            $programme = ProgrammeService::getProgrammeById((int) $id);

            return $this->response('Programme retrieved successfully', $programme->toArray());
        } catch (\Exception $e) {
            \Log::error('PROGRAMME FETCH ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to fetch programme: '.$e->getMessage(), 500);
        }
    }

    /**
     * Retrieves a list of popular programmes.
     *
     * Handles the incoming request to fetch popular programmes based on certain criteria.
     *
     * @param  \Illuminate\Http\Request  $request  The HTTP request instance containing query parameters.
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function popular(Request $request)
    {
        try {
            $programmes = ProgrammeService::getPopularProgrammes($request);

            return $this->response(
                'Popular programmes retrieved successfully',
                $programmes->toArray(),
                true
            );
        } catch (\Exception $e) {
            \Log::error('POPULAR PROGRAMME FETCH ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to fetch popular programme: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get total unique programmes for a trainer
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function trainerTotalProgrammes(Request $request)
    {
        try {
            $trainerId = $request->trainer_id ?? auth()->id();
            $request->merge(['user_id' => $trainerId, 'isActive' => true]);
            $total = ProgrammeService::getProgrammes($request)->count();

            return $this->response('Trainer total programmes retrieved successfully', ['total' => $total]);
        } catch (\Exception $e) {
            \Log::error('GET TRAINER TOTAL PROGRAMMES ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to retrieve trainer total programmes: '.$e->getMessage());
        }
    }

    /**
     * Get users who have bookings for a specific programme schedule
     *
     * @param  int  $programmeId  The ID of the programme schedule
     * @return \Illuminate\Http\JsonResponse
     */
    public function findUserBookingsByProgrammeId(Request $request, $programmeId)
    {
        try {
            $users = ProgrammeService::getUserBookingsByProgrammeId((int) $programmeId, $request);

            return $this->response(
                'Users retrieved successfully',
                $users->toArray(),
                true
            );
        } catch (\Exception $e) {
            \Log::error('GET USERS BOOKING BY PROGRAMME ID ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to retrieve users for programme: '.$e->getMessage());
        }
    }

    /**
     * Create a new programme
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreProgrammeRequest $request)
    {
        try {
            \DB::beginTransaction();
            // programme creation
            $programme = $this->storeProgramme($request);

            // programme schedule creation (optional)
            $request->merge(['programmeId' => $programme->id]);
            $programme->schedule = $this->storeProgrammeSchedule($request);
            \DB::commit();

            return $this->response('Programme created successfully', $programme->toArray(), false, 201);
        } catch (ValidationException $e) {
            \DB::rollBack();
            return $this->validationErrorResponse($e->errors(), 422);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('PROGRAMME CREATE ERROR: ', [$e->getMessage(), $e->getTraceAsString()]);

            return $this->errorResponse('Failed to create programme: '.$e->getMessage(), 500);
        }
    }

    private function storeProgramme($request) {
        $programmeData = $request->only([
            'typeId',
            'name',
            'synopsis',
            'coverImage',
            'intensityLevel',
            'sessionType',
            'maxParticipants',
            'durationMinutes',
            'isActive'
        ]);
        $programmeData = $this->mapInputData($programmeData, Programme::class);
        return ProgrammeService::createProgramme($programmeData, auth()->id());
    }

    private function storeProgrammeSchedule($request) {
        $user = auth()->user();
        if ($user->role === 'trainer') {
            $request->merge(['trainerId' => $user->id]);
        }

        $scheduleData = $request->only([
            'programmeId',
            'trainerId',
            'roomId',
            'day',
            'startTime',
            'endTime'
        ]);

        $hasCompleteScheduleInput = filled($scheduleData['roomId'] ?? null)
            && filled($scheduleData['day'] ?? null)
            && filled($scheduleData['startTime'] ?? null)
            && filled($scheduleData['trainerId'] ?? null);

        if (! $hasCompleteScheduleInput) {
            return null;
        }

        // Manually validate
        $validator = \Validator::make(
            $scheduleData,
            (new StoreProgrammeScheduleRequest())->rules(),
            (new StoreProgrammeScheduleRequest())->messages()
        );
        (new StoreProgrammeScheduleRequest())->withValidator($validator);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $scheduleData = $this->mapInputData($scheduleData, ProgrammeSchedule::class);
        return ProgrammeScheduleService::createSchedule($scheduleData);
    }
    /**
     * Update a specific programme
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateProgrammeRequest $request, $id)
    {
        try {
            // Map camelCase input to snake_case for database, exclude created_by from updates
            $data = $this->mapInputData($request->all(), Programme::class, ['created_by']);

            $programme = ProgrammeService::updateProgramme($id, $data);
            if ($request->expand) {
                $programme = $this->loadRelationModel($programme, $request->expand);
            }

            return $this->response('Programme updated successfully', $programme->toArray());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 422);

        } catch (\Exception $e) {
            \Log::error('PROGRAMME UPDATE ERROR: ', [$e->getMessage(), $request->all(), $e->getTraceAsString()]);

            return $this->errorResponse('Failed to update programme: '.$e->getMessage(), 500);
        }
    }

    /**
     * Delete a programme (soft delete by setting is_active to false)
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            ProgrammeService::deleteProgramme($id);

            return $this->response('Programme deactivated successfully');
        } catch (\Exception $e) {
            \Log::error('PROGRAMME DELETE ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to deactivate programme: '.$e->getMessage(), 500);
        }
    }

    /**
     * Upload a programme cover image and return the public URL
     *
     * @param  \App\Http\Requests\Programme\UploadProgrammeCoverRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadCover(UploadProgrammeCoverRequest $request)
    {
        try {
            $url = ProgrammeService::uploadCover($request->file('file'));
            return $this->response('Image uploaded successfully', ['url' => $url]);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('PROGRAMME COVER UPLOAD ERROR: ', [$e->getMessage(), $e->getTraceAsString()]);
            return $this->errorResponse('Failed to upload image: '.$e->getMessage(), 500);
        }
    }


    /**
     * Get programme counters (active and cancelled)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function programmeCounters()
    {
        try {
            $counters = ProgrammeService::getProgrammeCounters();

            return $this->response('Programme counters retrieved successfully', $counters);
        } catch (\Exception $e) {
            \Log::error('GET PROGRAMME COUNTERS ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to retrieve programme counters: '.$e->getMessage());
        }
    }
}

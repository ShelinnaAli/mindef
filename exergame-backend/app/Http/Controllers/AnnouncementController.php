<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AnnouncementController extends Controller
{
    /**
     * Get all announcements
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function findAll(Request $request)
    {
        try {
            $announcements = AnnouncementService::getAnnouncements($request);

            return $this->response('Announcements retrieved successfully', $announcements->toArray());
        } catch (\Exception $e) {
            \Log::error('ANNOUNCEMENTS FETCH ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to fetch announcements: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get all announcement types
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function findTypes()
    {
        try {
            $types = AnnouncementService::getAnnouncementTypes();

            return $this->response('Announcement types retrieved successfully', $types->toArray());
        } catch (\Exception $e) {
            \Log::error('ANNOUNCEMENT TYPES FETCH ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to fetch announcement types: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get a specific announcement by ID
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function find($id)
    {
        try {
            $announcement = AnnouncementService::getAnnouncementById($id);

            return $this->response('Announcement retrieved successfully', $announcement->toArray());
        } catch (\Exception $e) {
            \Log::error('ANNOUNCEMENT FETCH ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to fetch announcement: '.$e->getMessage(), 500);
        }
    }

    /**
     * Create a new announcement
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(AnnouncementRequest $request)
    {
        try {
            $request->merge([
                'isActive' => true
            ]);
            $mappedData = $this->mapInputData($request->all(), Announcement::class);
            $announcement = AnnouncementService::createAnnouncement($mappedData);

            return $this->response('Announcement created successfully', $announcement->toArray(), false, 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('ANNOUNCEMENT CREATE ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to create announcement: '.$e->getMessage(), 500);
        }
    }

    /**
     * Update a specific announcement
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(AnnouncementRequest $request, $id)
    {
        try {
            $request->merge([
                'isActive' => true
            ]);
            $data = $this->mapInputData($request->all(), Announcement::class);
            $announcement = AnnouncementService::updateAnnouncement($id, $data);
            if ($request->get('expand')) {
                $announcement = $this->loadRelationModel($announcement, $request->get('expand'));
            }
            if (! $announcement) {
                throw new \Exception('Announcement not found', 404);
            }

            return $this->response('Announcement updated successfully', $announcement->toArray());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('ANNOUNCEMENT UPDATE ERROR: ', [$e->getMessage(), $request->all(), $e->getTraceAsString()]);

            return $this->errorResponse('Failed to update announcement: '.$e->getMessage(), 500);
        }
    }

    /**
     * Delete an announcement (soft delete by setting is_active to false)
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            AnnouncementService::deleteAnnouncement($id);

            return $this->response('Announcement deactivated successfully');
        } catch (\Exception $e) {
            \Log::error('ANNOUNCEMENT DELETE ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to deactivate announcement: '.$e->getMessage(), 500);
        }
    }
}

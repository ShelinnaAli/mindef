<?php

namespace App\Http\Controllers;

use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Requests\Booking\UpdateBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\ProgrammeScheduleService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Get booking counters for the current user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function counter(Request $request)
    {
        try {
            $user = $request->user();
            $counters = BookingService::getBookingCounters($user);

            return $this->response('Booking counters retrieved successfully', $counters);
        } catch (\Exception $e) {
            \Log::error('BOOKING COUNTER ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to fetch booking counters: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get all bookings
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function findAll(Request $request)
    {
        try {
            $bookings = BookingService::getBookings($request);

            return $this->response(
                'Bookings retrieved successfully',
                $bookings->toArray(),
                true
            );
        } catch (\Exception $e) {
            \Log::error('BOOKINGS FETCH ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to fetch bookings: '.$e->getMessage(), 500);
        }
    }

    /**
     * Create a new booking
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreBookingRequest $request)
    {
        try {
            // Map camelCase to snake_case for the service
            $data = [
                'schedule_id' => $request->scheduleId,
                'is_liability_waiver_accepted' => $request->isLiabilityWaiverAccepted,
            ];

            $booking = BookingService::createBooking($data);

            // Load relationships for response
            $booking->load(['schedule.programme', 'schedule.trainer', 'schedule.room']);

            return $this->response(
                'Booking created successfully',
                $booking->toArray(),
                true,
                201
            );
        } catch (\Exception $e) {
            \Log::error('BOOKING CREATION ERROR: ', [$e->getMessage(), $e->getTraceAsString()]);

            return $this->errorResponse('Failed to create booking: '.$e->getMessage(), 400);
        }
    }

    /**
     * Update a booking
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateBookingRequest $request, $id)
    {
        try {
            $booking = BookingService::getBookingById($id);

            if ($request?->status === 'pending'
                && ProgrammeScheduleService::isAvailableToBooked($booking->schedule_id) === false
            ) {
                throw new \Exception('This session is fully booked');
            }

            // Map camelCase to snake_case for the service
            $data = $this->mapInputData($request->all(), Booking::class);

            $booking = BookingService::updateBooking($booking, $data);

            // Load relationships for response
            $booking->load(['schedule.programme', 'schedule.trainer', 'schedule.room']);

            return $this->response(
                'Booking updated successfully',
                $booking->toArray(),
                true
            );
        } catch (\Exception $e) {
            \Log::error('BOOKING UPDATE ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to update booking: '.$e->getMessage(), 400);
        }
    }

    /**
     * Delete a booking
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            BookingService::deleteBooking($id);

            return $this->response('Booking deleted successfully');
        } catch (\Exception $e) {
            \Log::error('BOOKING DELETION ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to delete booking: '.$e->getMessage(), 500);
        }
    }
}

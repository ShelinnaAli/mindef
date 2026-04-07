<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Booking;
use App\Models\ProgrammeSchedule;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scheduleId' => 'required|exists:programme_schedules,id',
            'isLiabilityWaiverAccepted' => 'required|boolean|accepted',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $data = $validator->getData();
        $validator->after(function ($validator) use ($data) {
            $this->validateBookingSlot($validator, $data);
        });
    }

    /**
     * Validate booking slot availability
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @param  array  $data
     * @return void
     */
    private function validateBookingSlot($validator, $data)
    {
        // Only validate if scheduleId is present and no existing errors
        if (!isset($data['scheduleId']) || $validator->errors()->has('scheduleId')) {
            return;
        }

        try {
            // Get the schedule with programme
            $schedule = ProgrammeSchedule::with('programme')
                ->where('id', $data['scheduleId'])
                ->where('is_cancelled', false)
                ->first();

            if (!$schedule) {
                $validator->errors()->add('scheduleId', 'The selected session is not available or has been cancelled.');
                return;
            }

            // Check if schedule is in the past
            $scheduleDateTime = \Carbon\Carbon::parse($schedule->day . ' ' . $schedule->start_time);
            if ($scheduleDateTime->isPast()) {
                $validator->errors()->add('scheduleId', 'Cannot book a past session.');
                return;
            }

            // Retrieve all bookings for this schedule
            $bookings = Booking::where('schedule_id', $data['scheduleId'])->get();

            // Check if user already has a booking for this schedule
            $existingBooking = $bookings->where('user_id', auth()->id())->first();

            if ($existingBooking) {
                $validator->errors()->add('scheduleId', 'You have already booked this session.');
                return;
            }

            // Get current confirmed bookings count for this schedule
            $confirmedBookingsCount = $bookings->where('status', 'confirmed')->count();

            // Check if adding this booking would exceed max participants
            $maxParticipants = $schedule->programme->max_participants;

            if ($confirmedBookingsCount >= $maxParticipants) {
                $validator->errors()->add('scheduleId', 'This session is fully booked.');
                return;
            }

        } catch (\Exception $e) {
            \Log::error('BOOKING SLOT VALIDATION ERROR: ' . $e->getMessage());
            $validator->errors()->add('scheduleId', 'Unable to validate booking slot availability. Please try again.');
        }
    }


    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'scheduleId.required' => 'Programme schedule selection is required.',
            'scheduleId.exists' => 'The selected programme schedule does not exist.',
            'isLiabilityWaiverAccepted.required' => 'Liability waiver acceptance is required.',
            'isLiabilityWaiverAccepted.accepted' => 'You must accept the liability waiver to proceed with the booking.',
        ];
    }
}

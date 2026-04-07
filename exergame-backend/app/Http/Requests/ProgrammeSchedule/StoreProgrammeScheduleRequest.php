<?php

namespace App\Http\Requests\ProgrammeSchedule;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgrammeScheduleRequest extends FormRequest
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
            'programmeId' => 'required|exists:programmes,id',
            'trainerId' => 'required|exists:users,id',
            'roomId' => 'required|exists:rooms,id',
            'day' => 'required|date|after_or_equal:today',
            'startTime' => 'required|date_format:H:i',
            'endTime' => 'nullable|date_format:H:i',
            'isCancelled' => 'boolean',
            'cancellationReason' => 'nullable|string|max:1000',
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
            $this->validateSessionDuration($validator, $data);
            $this->validateUniqueSchedule($validator, $data);
        });
    }

    /**
     * Validate that the session duration doesn't exceed the programme's max duration.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    private function validateSessionDuration($validator, $data)
    {
        $startTime = $data['startTime'] ?? null;
        $endTime = $data['endTime'] ?? null;
        $programmeId = $data['programmeId'] ?? null;

        // Only validate if we have all required fields and no existing errors
        if ($startTime && $endTime && $programmeId && ! $validator->errors()->has(['startTime', 'endTime', 'programmeId'])) {
            try {
                $programme = \App\Models\Programme::find($programmeId);

                if ($programme) {
                    // Calculate session duration in minutes
                    $startDateTime = \Carbon\Carbon::createFromFormat('H:i', $startTime);
                    $endDateTime = \Carbon\Carbon::createFromFormat('H:i', $endTime);

                    // Check if this is a same-day session where end is the same as start (invalid)
                    if ($startTime === $endTime) {
                        $validator->errors()->add('endTime', 'End time must be different from start time.');

                        return;
                    }

                    // Handle overnight sessions (end time next day)
                    if ($endDateTime->lessThan($startDateTime)) {
                        $endDateTime->addDay();
                    }

                    $sessionDurationMinutes = $startDateTime->diffInMinutes($endDateTime);

                    // Check if session duration exceeds programme's max duration
                    if ($sessionDurationMinutes > $programme->duration_minutes) {
                        $validator->errors()->add(
                            'endTime',
                            "Session duration ({$sessionDurationMinutes} minutes) exceeds the programme's maximum duration ({$programme->duration_minutes} minutes)."
                        );
                    }
                }
            } catch (\Exception $e) {
                // If there's an error calculating duration, add a generic error
                $validator->errors()->add('endTime', 'Invalid time format for duration calculation.');
            }
        }
    }

    /**
     * Validate that the combination of trainerId, roomId, day, and startTime is unique.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    private function validateUniqueSchedule($validator, $data)
    {
        $trainerId = $data['trainerId'] ?? null;
        $roomId = $data['roomId'] ?? null;
        $day = $data['day'] ?? null;
        $startTime = $data['startTime'] ?? null;
        $endTime = $this->input('endTime') ?? null;

        // Only validate if all required fields are present and no existing errors
        if ($trainerId && $roomId && $day && $startTime && ! $validator->errors()->has(['trainerId', 'roomId', 'day', 'startTime'])) {
            // Check for exact match
            $exists = \App\Models\ProgrammeSchedule::query()
                ->active()
                ->where(function($query) use($trainerId, $roomId) {
                    $query->where('trainer_id', $trainerId)
                    ->orWhere('room_id', $roomId);
                })
                ->where('day', $day)
                ->where('start_time', $startTime)
                ->exists();

            if ($exists) {
                $validator->errors()->add('startTime', 'A schedule with this trainer or room or schedule already exists.');

                return;
            }

            // Check for time overlap
            $overlap = \App\Models\ProgrammeSchedule::query()
                ->active()
                ->where(function($query) use($trainerId, $roomId) {
                    $query->where('trainer_id', $trainerId)
                    ->orWhere('room_id', $roomId);
                })
                ->where('day', $day)
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->whereRaw("'$startTime' < end_time");
                })
                ->exists();

            if ($overlap) {
                $validator->errors()->add('startTime', 'The selected time overlaps with an existing session for this room or trainer.');
            }
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'programmeId.required' => 'Programme is required.',
            'programmeId.exists' => 'Selected programme does not exist.',
            'trainerId.required' => 'Trainer is required.',
            'trainerId.exists' => 'Selected trainer does not exist.',
            'roomId.required' => 'Room is required.',
            'roomId.exists' => 'Selected room does not exist.',
            'day.required' => 'Schedule date is required.',
            'day.date' => 'Please provide a valid date.',
            'day.after_or_equal' => 'Schedule date cannot be in the past.',
            'startTime.required' => 'Start time is required.',
            'startTime.date_format' => 'Start time must be in HH:MM format.',
            'endTime.date_format' => 'End time must be in HH:MM format.',
            'endTime.after' => 'End time must be after start time.',
            'cancellationReason.max' => 'Cancellation reason cannot exceed 1000 characters.',
            'startTime.unique_schedule' => 'A schedule with this trainer, room, date, and start time already exists.',
        ];
    }
}

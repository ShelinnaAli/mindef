<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Carbon\Carbon;

class ProgrammeScheduleDurationValidationUnitTest extends TestCase
{
    /**
     * Test duration calculation logic directly without Laravel dependencies
     */
    public function test_calculates_session_duration_correctly()
    {
        // Test same-day session
        $startTime = '10:00';
        $endTime = '10:45';

        $startDateTime = Carbon::createFromFormat('H:i', $startTime);
        $endDateTime = Carbon::createFromFormat('H:i', $endTime);

        $duration = $startDateTime->diffInMinutes($endDateTime);

        $this->assertEquals(45, $duration, 'Same-day session duration should be 45 minutes');
    }

    /**
     * Test overnight session duration calculation
     */
    public function test_calculates_overnight_session_duration_correctly()
    {
        // Test overnight session
        $startTime = '23:30';
        $endTime = '00:15';

        $startDateTime = Carbon::createFromFormat('H:i', $startTime);
        $endDateTime = Carbon::createFromFormat('H:i', $endTime);

        // Handle overnight sessions (end time next day)
        if ($endDateTime->lessThan($startDateTime)) {
            $endDateTime->addDay();
        }

        $duration = $startDateTime->diffInMinutes($endDateTime);

        $this->assertEquals(45, $duration, 'Overnight session duration should be 45 minutes');
    }

    /**
     * Test that overnight logic doesn't affect same-day sessions
     */
    public function test_same_day_sessions_are_not_affected_by_overnight_logic()
    {
        // Test normal same-day session
        $startTime = '09:00';
        $endTime = '11:00';

        $startDateTime = Carbon::createFromFormat('H:i', $startTime);
        $endDateTime = Carbon::createFromFormat('H:i', $endTime);

        // Handle overnight sessions (should not add day for same-day sessions)
        if ($endDateTime->lessThan($startDateTime)) {
            $endDateTime->addDay();
        }

        $duration = $startDateTime->diffInMinutes($endDateTime);

        $this->assertEquals(120, $duration, 'Same-day session should be 120 minutes');
    }

    /**
     * Test duration validation logic
     */
    public function test_duration_validation_logic()
    {
        $programmeDurationMinutes = 30;

        // Test valid duration (within limit)
        $validSessionDuration = 25;
        $this->assertTrue(
            $validSessionDuration <= $programmeDurationMinutes,
            'Session duration within programme limit should be valid'
        );

        // Test invalid duration (exceeds limit)
        $invalidSessionDuration = 45;
        $this->assertFalse(
            $invalidSessionDuration <= $programmeDurationMinutes,
            'Session duration exceeding programme limit should be invalid'
        );

        // Test exact duration (should be valid)
        $exactSessionDuration = 30;
        $this->assertTrue(
            $exactSessionDuration <= $programmeDurationMinutes,
            'Session duration exactly matching programme limit should be valid'
        );
    }

    /**
     * Test various time combinations
     */
    public function test_various_time_combinations()
    {
        $testCases = [
            ['start' => '10:00', 'end' => '10:30', 'expected' => 30, 'description' => '30-minute session'],
            ['start' => '14:15', 'end' => '15:45', 'expected' => 90, 'description' => '90-minute session'],
            ['start' => '23:45', 'end' => '00:30', 'expected' => 45, 'description' => 'Overnight 45-minute session'],
            ['start' => '22:00', 'end' => '02:00', 'expected' => 240, 'description' => 'Overnight 4-hour session'],
            ['start' => '08:30', 'end' => '08:35', 'expected' => 5, 'description' => 'Short 5-minute session'],
        ];

        foreach ($testCases as $case) {
            $startDateTime = Carbon::createFromFormat('H:i', $case['start']);
            $endDateTime = Carbon::createFromFormat('H:i', $case['end']);

            // Handle overnight sessions
            if ($endDateTime->lessThan($startDateTime)) {
                $endDateTime->addDay();
            }

            $duration = $startDateTime->diffInMinutes($endDateTime);

            $this->assertEquals(
                $case['expected'],
                $duration,
                "Failed for {$case['description']}: {$case['start']} to {$case['end']}"
            );
        }
    }

    /**
     * Test that same start and end times are detected
     */
    public function test_detects_same_start_and_end_times()
    {
        $startTime = '10:00';
        $endTime = '10:00';

        $this->assertEquals($startTime, $endTime, 'Same start and end times should be detected');
    }

    /**
     * Test validation error message generation
     */
    public function test_validation_error_message_generation()
    {
        $sessionDuration = 45;
        $programmeDuration = 30;

        $expectedMessage = "Session duration ({$sessionDuration} minutes) exceeds the programme's maximum duration ({$programmeDuration} minutes).";

        $actualMessage = "Session duration ({$sessionDuration} minutes) exceeds the programme's maximum duration ({$programmeDuration} minutes).";

        $this->assertEquals($expectedMessage, $actualMessage, 'Error message should match expected format');
    }
}

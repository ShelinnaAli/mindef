<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Programme;
use App\Models\User;
use App\Models\Room;
use App\Models\GameType;
use App\Http\Requests\ProgrammeSchedule\StoreProgrammeScheduleRequest;
use App\Http\Requests\ProgrammeSchedule\UpdateProgrammeScheduleRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

class ProgrammeScheduleDurationValidationTest extends TestCase
{
    // Removed RefreshDatabase trait to avoid Mockery dependency

    protected $programme;
    protected $trainer;
    protected $room;

    protected function setUp(): void
    {
        parent::setUp();

        // Manually clear and create test data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        \DB::table('programme_schedules')->truncate();
        \DB::table('programmes')->truncate();
        \DB::table('users')->truncate();
        \DB::table('rooms')->truncate();
        \DB::table('game_types')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Create test data using direct model creation instead of factories
        $gameType = GameType::create([
            'name' => 'Test Game Type',
            'is_active' => true
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->programme = Programme::create([
            'type_id' => $gameType->id,
            'created_by' => $user->id,
            'name' => 'Test Programme',
            'synopsis' => 'Test programme for validation',
            'cover_image' => 'test-image.jpg',
            'intensity_level' => 'medium',
            'session_type' => 'single',
            'max_participants' => 10,
            'duration_minutes' => 30,
            'is_active' => true
        ]);

        $this->trainer = User::create([
            'name' => 'Test Trainer',
            'email' => 'trainer@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->room = Room::create([
            'name' => 'Test Room',
            'description' => 'Test room for validation',
            'capacity' => 20,
            'is_active' => true
        ]);
    }

    /** @test */
    public function it_passes_validation_when_session_duration_is_within_programme_limit()
    {
        $validData = [
            'programmeId' => $this->programme->id,
            'trainerId' => $this->trainer->id,
            'roomId' => $this->room->id,
            'day' => '2025-08-10',
            'startTime' => '10:00',
            'endTime' => '10:25' // 25 minutes - within 30-minute limit
        ];

        $request = new StoreProgrammeScheduleRequest();
        $request->merge($validData);
        $validator = Validator::make($validData, $request->rules());
        $request->withValidator($validator);

        $this->assertFalse($validator->fails(), 'Validation should pass for valid duration');
        $this->assertEmpty($validator->errors()->all(), 'There should be no validation errors');
    }

    /** @test */
    public function it_fails_validation_when_session_duration_exceeds_programme_limit()
    {
        $invalidData = [
            'programmeId' => $this->programme->id,
            'trainerId' => $this->trainer->id,
            'roomId' => $this->room->id,
            'day' => '2025-08-10',
            'startTime' => '10:00',
            'endTime' => '11:30' // 90 minutes - exceeds 30-minute limit
        ];

        $request = new StoreProgrammeScheduleRequest();
        $request->merge($invalidData);
        $validator = Validator::make($invalidData, $request->rules());
        $request->withValidator($validator);

        $this->assertTrue($validator->fails(), 'Validation should fail for duration exceeding limit');
        $this->assertStringContainsString(
            'Session duration (90 minutes) exceeds the programme\'s maximum duration (30 minutes)',
            $validator->errors()->first('endTime')
        );
    }

    /** @test */
    public function it_handles_overnight_sessions_correctly()
    {
        $overnightData = [
            'programmeId' => $this->programme->id,
            'trainerId' => $this->trainer->id,
            'roomId' => $this->room->id,
            'day' => '2025-08-10',
            'startTime' => '23:45',
            'endTime' => '00:30' // 45 minutes spanning midnight - exceeds 30-minute limit
        ];

        $request = new StoreProgrammeScheduleRequest();
        $request->merge($overnightData);
        $validator = Validator::make($overnightData, $request->rules());
        $request->withValidator($validator);

        $this->assertTrue($validator->fails(), 'Validation should fail for overnight session exceeding limit');
        $this->assertStringContainsString(
            'Session duration (45 minutes) exceeds the programme\'s maximum duration (30 minutes)',
            $validator->errors()->first('endTime')
        );
    }

    /** @test */
    public function it_handles_valid_overnight_sessions()
    {
        // Create a programme with longer duration for valid overnight test
        $gameType = GameType::create([
            'name' => 'Long Test Game Type',
            'is_active' => true
        ]);

        $user = User::create([
            'name' => 'Long Test User',
            'email' => 'longtest@example.com',
            'password' => bcrypt('password'),
        ]);

        $longProgramme = Programme::create([
            'type_id' => $gameType->id,
            'created_by' => $user->id,
            'name' => 'Long Test Programme',
            'synopsis' => 'Long test programme for overnight validation',
            'cover_image' => 'long-test-image.jpg',
            'intensity_level' => 'medium',
            'session_type' => 'single',
            'max_participants' => 10,
            'duration_minutes' => 60,
            'is_active' => true
        ]);

        $validOvernightData = [
            'programmeId' => $longProgramme->id,
            'trainerId' => $this->trainer->id,
            'roomId' => $this->room->id,
            'day' => '2025-08-10',
            'startTime' => '23:30',
            'endTime' => '00:15' // 45 minutes spanning midnight - within 60-minute limit
        ];

        $request = new StoreProgrammeScheduleRequest();
        $request->merge($validOvernightData);
        $validator = Validator::make($validOvernightData, $request->rules());
        $request->withValidator($validator);

        $this->assertFalse($validator->fails(), 'Validation should pass for valid overnight session');
        $this->assertEmpty($validator->errors()->all(), 'There should be no validation errors');
    }

    /** @test */
    public function it_fails_when_start_and_end_times_are_the_same()
    {
        $sameTimeData = [
            'programmeId' => $this->programme->id,
            'trainerId' => $this->trainer->id,
            'roomId' => $this->room->id,
            'day' => '2025-08-10',
            'startTime' => '10:00',
            'endTime' => '10:00' // Same as start time
        ];

        $request = new StoreProgrammeScheduleRequest();
        $request->merge($sameTimeData);
        $validator = Validator::make($sameTimeData, $request->rules());
        $request->withValidator($validator);

        $this->assertTrue($validator->fails(), 'Validation should fail when start and end times are the same');
        $this->assertStringContainsString(
            'End time must be different from start time',
            $validator->errors()->first('endTime')
        );
    }

    /** @test */
    public function it_validates_exact_programme_duration_as_valid()
    {
        $exactDurationData = [
            'programmeId' => $this->programme->id,
            'trainerId' => $this->trainer->id,
            'roomId' => $this->room->id,
            'day' => '2025-08-10',
            'startTime' => '10:00',
            'endTime' => '10:30' // Exactly 30 minutes - matches programme duration
        ];

        $request = new StoreProgrammeScheduleRequest();
        $request->merge($exactDurationData);
        $validator = Validator::make($exactDurationData, $request->rules());
        $request->withValidator($validator);

        $this->assertFalse($validator->fails(), 'Validation should pass for exact programme duration');
        $this->assertEmpty($validator->errors()->all(), 'There should be no validation errors');
    }

    /** @test */
    public function update_request_validates_duration_correctly()
    {
        $invalidUpdateData = [
            'programmeId' => $this->programme->id,
            'startTime' => '10:00',
            'endTime' => '12:00' // 120 minutes - exceeds 30-minute limit
        ];

        $request = new UpdateProgrammeScheduleRequest();
        $request->merge($invalidUpdateData);
        $validator = Validator::make($invalidUpdateData, $request->rules());
        $request->withValidator($validator);

        $this->assertTrue($validator->fails(), 'Update validation should fail for duration exceeding limit');
        $this->assertStringContainsString(
            'Session duration (120 minutes) exceeds the programme\'s maximum duration (30 minutes)',
            $validator->errors()->first('endTime')
        );
    }

    /** @test */
    public function it_handles_invalid_time_format_gracefully()
    {
        $invalidTimeData = [
            'programmeId' => $this->programme->id,
            'trainerId' => $this->trainer->id,
            'roomId' => $this->room->id,
            'day' => '2025-08-10',
            'startTime' => '25:00', // Invalid hour
            'endTime' => '10:30'
        ];

        $request = new StoreProgrammeScheduleRequest();
        $request->merge($invalidTimeData);
        $validator = Validator::make($invalidTimeData, $request->rules());
        $request->withValidator($validator);

        $this->assertTrue($validator->fails(), 'Validation should fail for invalid time format');

        // Should fail on time format validation before reaching duration validation
        $errors = $validator->errors()->all();
        $this->assertNotEmpty($errors, 'There should be validation errors');
    }

    /** @test */
    public function it_skips_duration_validation_when_programme_not_found()
    {
        $nonExistentProgrammeData = [
            'programmeId' => 99999, // Non-existent programme ID
            'trainerId' => $this->trainer->id,
            'roomId' => $this->room->id,
            'day' => '2025-08-10',
            'startTime' => '10:00',
            'endTime' => '12:00'
        ];

        $request = new StoreProgrammeScheduleRequest();
        $request->merge($nonExistentProgrammeData);
        $validator = Validator::make($nonExistentProgrammeData, $request->rules());
        $request->withValidator($validator);

        $this->assertTrue($validator->fails(), 'Validation should fail for non-existent programme');

        // Should fail on programme existence check, not duration validation
        $this->assertStringContainsString(
            'Selected programme does not exist',
            $validator->errors()->first('programmeId')
        );
    }

    /** @test */
    public function it_validates_different_programme_durations()
    {
        // Test with a programme having different duration
        $gameType = GameType::create([
            'name' => 'Short Test Game Type',
            'is_active' => true
        ]);

        $user = User::create([
            'name' => 'Short Test User',
            'email' => 'shorttest@example.com',
            'password' => bcrypt('password'),
        ]);

        $shortProgramme = Programme::create([
            'type_id' => $gameType->id,
            'created_by' => $user->id,
            'name' => 'Short Programme',
            'synopsis' => 'Short test programme for duration validation',
            'cover_image' => 'short-test-image.jpg',
            'intensity_level' => 'low',
            'session_type' => 'single',
            'max_participants' => 8,
            'duration_minutes' => 15,
            'is_active' => true
        ]);

        // This should pass for short programme
        $validShortData = [
            'programmeId' => $shortProgramme->id,
            'trainerId' => $this->trainer->id,
            'roomId' => $this->room->id,
            'day' => '2025-08-10',
            'startTime' => '10:00',
            'endTime' => '10:10' // 10 minutes - within 15-minute limit
        ];

        $request1 = new StoreProgrammeScheduleRequest();
        $request1->merge($validShortData);
        $validator1 = Validator::make($validShortData, $request1->rules());
        $request1->withValidator($validator1);

        $this->assertFalse($validator1->fails(), 'Validation should pass for short programme with valid duration');

        // This should fail for short programme
        $invalidShortData = [
            'programmeId' => $shortProgramme->id,
            'trainerId' => $this->trainer->id,
            'roomId' => $this->room->id,
            'day' => '2025-08-10',
            'startTime' => '10:00',
            'endTime' => '10:20' // 20 minutes - exceeds 15-minute limit
        ];

        $request2 = new StoreProgrammeScheduleRequest();
        $request2->merge($invalidShortData);
        $validator2 = Validator::make($invalidShortData, $request2->rules());
        $request2->withValidator($validator2);

        $this->assertTrue($validator2->fails(), 'Validation should fail for short programme with exceeding duration');
        $this->assertStringContainsString(
            'Session duration (20 minutes) exceeds the programme\'s maximum duration (15 minutes)',
            $validator2->errors()->first('endTime')
        );
    }
}

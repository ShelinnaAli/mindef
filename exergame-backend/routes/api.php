<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\GameTypeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\ProgrammeScheduleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserSchemeController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/validate-username', [AuthController::class, 'validateUsername'])->name('validate.username');
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::prefix('auth')->group(function () {
        Route::post('/change-password', [AuthController::class, 'changePassword'])->name('user.password.change');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'findAll'])->name('users.findAll');
        Route::get('/total', [UserController::class, 'totalUsers'])->name('users.total');
    });
    Route::prefix('user')->group(function () {
        Route::get('/{id?}', [UserController::class, 'find'])->name('user.get');
        Route::put('/{id?}', [UserController::class, 'update'])->name('user.update');
        Route::post('/', [UserController::class, 'store'])->name('user.store');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('user.delete');
    });

    // Programme routes
    Route::group(['prefix' => 'programmes'], function () {
        Route::get('/', [ProgrammeController::class, 'findAll'])->name('programmes.findAll');
        Route::get('/popular', [ProgrammeController::class, 'popular'])->name('programmes.popular');
        Route::get('/trainer-programmes', [ProgrammeController::class, 'trainerTotalProgrammes'])->name('programme.trainerProgrammes');
        Route::get('/counters', [ProgrammeController::class, 'programmeCounters'])->name('programme.counters');
        Route::post('/upload-cover', [ProgrammeController::class, 'uploadCover'])->name('programmes.uploadCover');
    });
    Route::group(['prefix' => 'programme'], function () {
        Route::get('/{id}', [ProgrammeController::class, 'find'])->name('programme.get');
        Route::post('/', [ProgrammeController::class, 'store'])->name('programme.store');
        Route::put('/{id}', [ProgrammeController::class, 'update'])->name('programme.update');
        Route::delete('/{id}', [ProgrammeController::class, 'destroy'])->name('programme.delete');
    });

    // Announcement routes
    Route::group(['prefix' => 'announcements'], function () {
        Route::get('/', [AnnouncementController::class, 'findAll'])->name('announcements.findAll');
    });
    Route::group(['prefix' => 'announcement'], function () {
        Route::get('/types', [AnnouncementController::class, 'findTypes'])->name('announcement.findTypes');
        Route::get('/{id}', [AnnouncementController::class, 'find'])->name('announcement.find');
        Route::post('/', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::put('/{id}', [AnnouncementController::class, 'update'])->name('announcement.update');
        Route::delete('/{id}', [AnnouncementController::class, 'destroy'])->name('announcement.destroy');
    });

    // Game Type routes
    Route::group(['prefix' => 'game-types'], function () {
        Route::get('/', [GameTypeController::class, 'findAll'])->name('gameTypes.findAll');
    });

    // Announcement routes
    Route::group(['prefix' => 'programme-schedules'], function () {
        Route::get('/', [ProgrammeScheduleController::class, 'findAll'])->name('programmeSchedule.findAll');
        Route::get('/monthly', [ProgrammeScheduleController::class, 'getMonthlySchedules'])->name('programmeSchedules.monthly');
    });
    // Programme Schedule routes (authenticated - for CRUD operations)
    Route::group(['prefix' => 'programme-schedule'], function () {
        Route::get('/trainer-bookings', [ProgrammeScheduleController::class, 'trainerTotalBookings'])->name('programmeSchedule.trainerBookings');
        Route::get('/trainer-avg-attendance', [ProgrammeScheduleController::class, 'trainerAvgAttendance'])->name('programmeSchedule.trainerAvgAttendance');
        Route::get('/counters', [ProgrammeScheduleController::class, 'scheduleCounters'])->name('programmeSchedule.counters');
        Route::post('/', [ProgrammeScheduleController::class, 'store'])->name('programmeSchedule.store');
        Route::put('/{id}', [ProgrammeScheduleController::class, 'update'])->name('programmeSchedule.update');
    });

    // Room routes
    Route::group(['prefix' => 'rooms'], function () {
        Route::get('/', [RoomController::class, 'findAll'])->name('rooms.findAll');
    });
    Route::group(['prefix' => 'room'], function () {
        Route::get('/{id}', [RoomController::class, 'find'])->name('room.find');
    });

    // Booking routes
    Route::group(['prefix' => 'bookings'], function () {
        Route::get('/', [BookingController::class, 'findAll'])->name('bookings.findAll');
        Route::get('/my-bookings', [BookingController::class, 'findAll'])->name('bookings.getUserBookings');
        Route::get('/counter', [BookingController::class, 'counter'])->name('bookings.counter');
        Route::get('/users/programme/{programmeId}', [ProgrammeController::class, 'findUserBookingsByProgrammeId'])->name('bookings.findUserBookingsByProgrammeId');
        Route::get('/users/schedule/{scheduleId}', [ProgrammeScheduleController::class, 'findUserBookingsByScheduleId'])->name('bookings.findUserBookingsByScheduleId');
    });
    Route::group(['prefix' => 'booking'], function () {
        Route::post('/', [BookingController::class, 'store'])->name('booking.store');
        Route::put('/{id}', [BookingController::class, 'update'])->name('booking.update');
        Route::delete('/{id}', [BookingController::class, 'destroy'])->name('booking.delete');
    });

    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', [NotificationController::class, 'findByUser'])->name('notification.findByUser');
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead'])->name('notification.markAsRead');
    });

    Route::group(['prefix' => 'report'], function () {
        Route::get('/user-age-distributions', [ReportController::class, 'userAgeDistributions'])->name('report.userAgeDistributions');
        Route::get('/user-participation-history', [ReportController::class, 'userParticipationHistory'])->name('report.userParticipationHistory');
        Route::get('/user-participation-report', [ReportController::class, 'userParticipationReport'])->name('report.userParticipationReport');
        Route::get('/user-list-report', [ReportController::class, 'userListReport'])->name('report.userListReport');
        Route::get('/programme-cancellation-frequencies', [ReportController::class, 'programmeCancellationFrequencies'])->name('report.programmeCancellationFrequencies');
        Route::get('/programme-take-up-rates', [ReportController::class, 'programmeTakeUpRates'])->name('report.programmeTakeUpRates');
        Route::get('/programme-run-frequencies', [ReportController::class, 'programmeRunFrequencies'])->name('report.programmeRunFrequencies');
    });

    Route::group(['prefix' => 'app-settings'], function () {
        Route::get('/', [AppSettingController::class, 'findAll']);
        Route::put('/{flag}', [AppSettingController::class, 'update'])->name('appSettings.update');
    });
});

Route::get('/app-settings/{flag}', [AppSettingController::class, 'findAll']);
Route::get('/user-schemes', [UserSchemeController::class, 'findAll']);

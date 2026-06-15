<?php

use App\Http\Controllers\API\AdmissionPaymentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\ClassScheduleController;
use App\Http\Controllers\API\GradeController;
use App\Http\Controllers\Api\InstituteHallController;
use App\Http\Controllers\API\MobileDashboardController;
use App\Http\Controllers\API\QuickPhotoController;
use App\Http\Controllers\API\StudentAttendanceController;
use App\Http\Controllers\API\StudentAttendanceReadController;
use App\Http\Controllers\API\StudentClassController;
use App\Http\Controllers\API\StudentClassEnrollmentController;
use App\Http\Controllers\API\StudentController;
use App\Http\Controllers\API\StudentImageController;
use App\Http\Controllers\API\StudentPaymentController;
use App\Http\Controllers\API\StudentPaymentReadController;
use App\Http\Controllers\API\StudentRegisterController;
use App\Http\Controllers\API\StudentTuteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Mobile App + Web API
|
*/

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::post(
    '/login',
    [LoginController::class, 'login']
)->name('api.login');


/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:sanctum',
    'user.active',
    'permission'
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/logout',
        [LoginController::class, 'logout']
    )->name('api.logout');


    /*
    |--------------------------------------------------------------------------
    | Student
    |--------------------------------------------------------------------------
    */


    Route::post(
        '/student',
        [StudentRegisterController::class, 'QuickStudentStore']
    )->name('api.QuickStudentStore');

    Route::get(
        '/students',
        [StudentController::class, 'fetchAllStudent']
    )->name('api.students.all');

    Route::get(
        '/students/filter',
        [StudentController::class, 'studentDetailsSearch']
    )->name('api.students.filter');

    Route::get(
        '/students/search',
        [StudentController::class, 'searchStudent']
    )->name('api.students.search');

    Route::post(
        '/students/update-image',
        [StudentController::class, 'updateStudentImage']
    )->name('api.students.update-image');


    Route::post(
        '/quick-photo/upload',
        [QuickPhotoController::class, 'uploadQuickPhoto']
    )->name('api.quick-photo.upload');

    Route::get(
        '/students-image/fetch-image',
        [StudentImageController::class, 'fetchStudentImage']
    )->name('api.students.fetch-image');

    Route::post(
        '/students-image/update-image',
        [StudentImageController::class, 'updateStudentImage']
    )->name('api.students.update-image');


    Route::get(
        '/grades',
        [GradeController::class, 'fetchGrade']
    )->name('api.grades.fetch');


    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */

    // Read payment info from QR
    Route::post(
        '/payments/read',
        [StudentPaymentReadController::class, 'read']
    )->name('api.payments.read');



    /*
    |--------------------------------------------------------------------------
    | Student Payment Store
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/payments/today-payments',
        [StudentPaymentController::class, 'todayReceipt']
    )->name('api.student-payments.today-receipt');

    Route::post(
        '/payments/today',
        [StudentPaymentController::class, 'todayPayments']
    );

    Route::get(
        '/payments/students/{studentId}/enrollments/{enrolledId}',
        [StudentPaymentController::class, 'fetchStudentAllPayment']
    );



    Route::post(
        '/student-payments',
        [StudentPaymentController::class, 'store']
    )->name('api.student-payments.store');


    /*
    |--------------------------------------------------------------------------
    | Student Admission Payment Delete
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/admission',
        [AdmissionPaymentController::class, 'fetchAdmission']
    )->name('api.admission-payment.fetch-admission');

    Route::get(
        '/admission-payments',
        [AdmissionPaymentController::class, 'fetchAdmissionPayment']
    )->name('api.admission-payment.fetch-admission-payments');

    Route::post(
        '/admission-payments/store',
        [AdmissionPaymentController::class, 'storeAdmissionPayment']
    )->name('api.admission-payment.store-admission-payment');


    /*
    |--------------------------------------------------------------------------
    | Student Payment Delete
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/payments/{paymentId}',
        [StudentPaymentController::class, 'destroy']
    )->name('api.student-payments.destroy');



    /*
    |--------------------------------------------------------------------------
    | Attendance
    |--------------------------------------------------------------------------
    */

    // Read attendance info from QR
    Route::post(
        '/attendance/read',
        [StudentAttendanceReadController::class, 'read']
    )->name('api.attendance.read');



    Route::post(
        '/attendance/store',
        [StudentAttendanceController::class, 'store']
    )->name('api.attendance.store');

    Route::get(
        '/attendance/students/{studentId}/enrollments/{enrolledId}',
        [StudentAttendanceController::class, 'studentAttendanceHistory']
    );


    Route::post(
        '/student-tutes/read',
        [StudentTuteController::class, 'readStudentTute']
    )->name('api.attendance.read');

    Route::post(
        '/student-tutes/store',
        [StudentTuteController::class, 'store']
    )->name('api.student-tutes.store');

    Route::get(
        '/student-tutes/students/{studentId}/enrollments/{enrolledId}',
        [StudentTuteController::class, 'studentTuteHistory']
    );


    Route::get(
        '/student-classes/{gradeId}',
        [StudentClassController::class, 'fetchStudentClass']
    )->name('api.student-classes.fetch_student_classes');





    /*
    |--------------------------------------------------------------------------
    | Class Enrollments
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/student-class-enrollments/{studentId}',
        [StudentClassEnrollmentController::class, 'fetchStudentClasses']
    )->name('api.student-class-enrollments.fetch_student_classes');


    Route::post(
        '/student-class-enrollments',
        [StudentClassEnrollmentController::class, 'store']
    )->name('api.student-class-enrollments.store');

    Route::patch(
        '/student-class-enrollments/{enrollment}',
        [StudentClassEnrollmentController::class, 'update']
    )->name('api.student-class-enrollments.patch');

    Route::post(
        '/student-class-enrollments/read-student-class',
        [StudentClassEnrollmentController::class, 'readStudentClass']
    )->name('api.student-class-enrollments.read_student_class');


    Route::patch(
        '/student-class-enrollments/toggle-status/{enrollmentId}',
        [StudentClassEnrollmentController::class, 'toggleClassStatusChange']
    );



    Route::get(
        '/class-schedule/today-class',
        [ClassScheduleController::class, 'todayClasses']
    );

    Route::get(
        '/class-schedule/ongoing-class',
        [ClassScheduleController::class, 'fetchOngoingClass']
    );

    Route::get(
        '/class-schedule/category',
        [ClassScheduleController::class, 'fetchClassCategory']
    );

    Route::get(
        '/class-schedule/fetch',
        [ClassScheduleController::class, 'fetchClassSchedule']
    );

    Route::post(
        '/class-schedule/store',
        [ClassScheduleController::class, 'storeAddNewDay']
    );

    Route::put(
        '/class-schedule/update',
        [ClassScheduleController::class, 'updateClassSchedule']
    );

    Route::patch(
        '/class-schedule/cancel',
        [ClassScheduleController::class, 'classCancel']
    );

    Route::get(
        '/institute-halls',
        [InstituteHallController::class, 'fetchInstituteHall']
    );


    Route::get(
        '/mobile-dashboard',
        [MobileDashboardController::class, 'mobileDashboardDetails']
    );
});

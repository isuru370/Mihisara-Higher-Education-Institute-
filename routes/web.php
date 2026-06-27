<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdmissionController;
use App\Http\Controllers\Admin\AdmissionPaymentController;
use App\Http\Controllers\Admin\OrganizerPaymentController;
use App\Http\Controllers\Admin\SystemUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentClassController;
use App\Http\Controllers\Admin\StudentClassEnrollmentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\OrganizerController;
use App\Http\Controllers\Admin\ClassCategoryController;
use App\Http\Controllers\Admin\ClassCategoryFeeController;
use App\Http\Controllers\Admin\ClassHallController;
use App\Http\Controllers\Admin\ClassScheduleController;
use App\Http\Controllers\Admin\ClassTimeTableController;
use App\Http\Controllers\Admin\DailyReportController;
use App\Http\Controllers\Admin\DatabaseBackupController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ExtraIncomeController;
use App\Http\Controllers\Admin\ForgotPasswordController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\ImageUploadController;
use App\Http\Controllers\Admin\InstituteExpenseController;
use App\Http\Controllers\Admin\InstituteIncomeController;
use App\Http\Controllers\Admin\InstitutePaymentReportController;
use App\Http\Controllers\Admin\InstituteReportController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\MonthlyReportController;
use App\Http\Controllers\Admin\ReceiptController;
use App\Http\Controllers\Admin\StudentIDCardController;
use App\Http\Controllers\Admin\StudentImageController;
use App\Http\Controllers\Admin\StudentPaymentController;
use App\Http\Controllers\Admin\TeacherReportController;
use App\Http\Controllers\Admin\TeacherSalaryController;
use App\Http\Controllers\Admin\TemporaryIDCardController;
use App\Http\Controllers\Admin\TodayAttendanceController;
use App\Http\Controllers\Admin\UserPermissionController;
use App\Http\Controllers\Admin\UserProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    if (auth()->check()) {
        return redirect('/dashboard');
    }

    return view('welcome');
})->name('welcome');


Route::get('/contact_administrator', function () {
    return view('contact_administrator');
})->name('contact_administrator');

Route::post('/contact_administrator', function (Request $request) {
    $validated = $request->validate([
        'full_name'  => ['required', 'string', 'max:100'],
        'email'      => ['required', 'email', 'max:150'],
        'phone'      => ['required', 'string', 'max:20'],
        'subject'    => ['required', 'string', 'max:150'],
        'message'    => ['required', 'string', 'max:5000'],
        'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
    ]);

    $toEmail = 'info@nexorait.lk';

    Mail::send('emails.contact_administrator', [
        'data' => $validated,
    ], function ($mail) use ($validated, $toEmail, $request) {
        $mail->to($toEmail)
            ->subject('Contact Form: ' . $validated['subject'])
            ->replyTo($validated['email'], $validated['full_name']);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');

            $mail->attach($file->getRealPath(), [
                'as' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
            ]);
        }
    });

    return back()->with('success', 'Your message has been sent successfully.');
})->name('contact_administrator.send');


/*
|--------------------------------------------------------------------------
| Forgot Password
|--------------------------------------------------------------------------
*/

Route::prefix('forgot-password')
    ->controller(ForgotPasswordController::class)
    ->group(function () {

        Route::get('/', 'index')->name('forgot_password.form');
        Route::post('/send-otp', 'sendOtp')->name('forgot_password.send_otp');
        Route::post('/verify-otp', 'verifyOtp')->name('forgot_password.verify_otp');
        Route::post('/resend-otp', 'resendOtp')->name('forgot_password.resend_otp');
        Route::post('/reset-password', 'updatePassword')->name('forgot_password.reset');
    });


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

// Login
Route::get(
    '/login',
    [LoginController::class, 'showLoginForm']
)->name('login');

Route::post(
    '/login',
    [LoginController::class, 'login']
)->name('login.post');

// Logout
Route::post(
    '/logout',
    [LoginController::class, 'logout']
)->name('logout');


/*
|--------------------------------------------------------------------------
| Protected Admin Routes
|--------------------------------------------------------------------------
|
| Only authenticated active users
|
*/

Route::middleware([
    'auth',
    'user.active',
    'permission'
])

    ->prefix('admin')

    ->name('admin.')

    ->group(function () {


        Route::get('/profile', [UserProfileController::class, 'index'])
            ->name('profile.index');

        Route::post('/profile', [UserProfileController::class, 'update'])
            ->name('profile.update');

        Route::post('/profile/password', [UserProfileController::class, 'changePassword'])
            ->name('profile.password');
        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/dashboard',
            [DashboardController::class, 'index']
        )->name('dashboard');

        Route::resource(
            'system-users',
            SystemUserController::class
        );

        Route::get('system-users/export/excel', [SystemUserController::class, 'exportExcel'])
            ->name('system-users.export.excel');

        Route::get('system-users/export/pdf', [SystemUserController::class, 'exportPdf'])
            ->name('system-users.export.pdf');

        Route::get(
            'user-permissions/{systemUser}',
            [UserPermissionController::class, 'index']
        )->name('user-permissions.index');

        Route::post(
            'user-permissions/{systemUser}',
            [UserPermissionController::class, 'store']
        )->name('user-permissions.store');


        Route::get(
            'institute-yearly-report',
            [InstitutePaymentReportController::class, 'yearlyPaymentReport']
        )->name('institute-yearly-report');


        /*
        |--------------------------------------------------------------------------
        | Student Management
        |--------------------------------------------------------------------------
        */

        // Search
        Route::get(
            'students/search',
            [StudentController::class, 'search']
        )->name('students.search');

        // Resource
        Route::resource(
            'students',
            StudentController::class
        );

        // Toggle Active
        Route::patch(
            'students/{student}/toggle-active',
            [StudentController::class, 'toggleActive']
        )->name('students.toggleActive');

        // Export
        Route::get(
            'students-export/excel',
            [StudentController::class, 'exportExcel']
        )->name('students.exportExcel');

        Route::get(
            'students-export/pdf',
            [StudentController::class, 'exportPdf']
        )->name('students.exportPdf');

        /*
        |--------------------------------------------------------------------------
        | Student QR Reports
        |--------------------------------------------------------------------------
        */

        Route::get(
            'students-export/all-students-pdf',
            [StudentController::class, 'allStudentDetailsPdf']
        )->name('students.allStudentDetailsPdf');

        Route::get(
            'students-export/temporary-card-expired-soon-pdf',
            [StudentController::class, 'studentTemporaryCardExpiredSoon']
        )->name('students.studentTemporaryCardExpiredSoon');



        /*
        |--------------------------------------------------------------------------
        | Student Payment Counter
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/students-payments',
            [StudentPaymentController::class, 'index']
        )->name('students-payments.index');

        Route::get(
            '/students-payments/{id}',
            [StudentPaymentController::class, 'show']
        )->name('students-payments.show');


        /*
        |--------------------------------------------------------------------------
        | Student Image Upload
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/upload/student-image',
            [ImageUploadController::class, 'uploadStudentImage']
        )->name('upload.student.image');

        Route::post(
            '/upload/quick-photo',
            [ImageUploadController::class, 'uploadQuickPhoto']
        )->name('upload.quick.photo');

        Route::delete(
            '/upload/delete',
            [ImageUploadController::class, 'delete']
        )->name('upload.delete');


        /*
        |--------------------------------------------------------------------------
        | Teacher Management
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'teachers',
            TeacherController::class
        );

        Route::patch(
            'teachers/{teacher}/toggle-active',
            [TeacherController::class, 'toggleActive']
        )->name('teachers.toggleActive');

        Route::get(
            'teachers-export/excel',
            [TeacherController::class, 'exportExcel']
        )->name('teachers.exportExcel');

        Route::get(
            'teachers-export/pdf',
            [TeacherController::class, 'exportPdf']
        )->name('teachers.exportPdf');


        /*
        |--------------------------------------------------------------------------
        | Organizer Management
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'organizers',
            OrganizerController::class
        );

        Route::patch(
            'organizers/{organizer}/toggle-active',
            [OrganizerController::class, 'toggleActive']
        )->name('organizers.toggleActive');


        /*
        |--------------------------------------------------------------------------
        | Student Classes
        |--------------------------------------------------------------------------
        */

        Route::get(
            'student-classes/search',
            [StudentClassController::class, 'search']
        )->name('student-classes.search');

        Route::resource(
            'student-classes',
            StudentClassController::class
        );

        Route::patch(
            'student-classes/{studentClass}/toggle-active',
            [StudentClassController::class, 'toggleActive']
        )->name('student-classes.toggleActive');

        Route::patch(
            'student-classes/{studentClass}/toggle-ongoing',
            [StudentClassController::class, 'toggleOngoing']
        )->name('student-classes.toggleOngoing');

        Route::get(
            'student-classes/export/excel',
            [StudentClassController::class, 'exportExcel']
        )->name('student-classes.exportExcel');

        Route::get(
            'student-classes/export/pdf',
            [StudentClassController::class, 'exportPdf']
        )->name('student-classes.exportPdf');


        /*
|--------------------------------------------------------------------------
| Grade Management
|--------------------------------------------------------------------------
*/

        Route::resource(
            'grades',
            GradeController::class
        );

        Route::patch(
            'grades/{grade}/toggle-active',
            [GradeController::class, 'toggleActive']
        )->name('grades.toggleActive');


        /*
        |--------------------------------------------------------------------------
        | Class Categories
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'class-categories',
            ClassCategoryController::class
        );

        Route::patch(
            'class-categories/{classCategory}/toggle-active',
            [ClassCategoryController::class, 'toggleActive']
        )->name('class-categories.toggleActive');


        /*
        |--------------------------------------------------------------------------
        | Class Category Fees
        |--------------------------------------------------------------------------
        */

        Route::get(
            'class-category-fees/by-class/{studentClass}',
            [ClassCategoryFeeController::class, 'byClass']
        )->name('class-category-fees.byClass');

        Route::resource(
            'class-category-fees',
            ClassCategoryFeeController::class
        );

        Route::patch(
            'class-category-fees/{classCategoryFee}/toggle-active',
            [ClassCategoryFeeController::class, 'toggleActive']
        )->name('class-category-fees.toggleActive');


        /*
        |--------------------------------------------------------------------------
        | Class Halls
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'class-halls',
            ClassHallController::class
        );

        Route::patch(
            'class-halls/{classHall}/toggle-active',
            [ClassHallController::class, 'toggleActive']
        )->name('class-halls.toggleActive');


        /*
        |--------------------------------------------------------------------------
        | Class Schedules
        |--------------------------------------------------------------------------
        */
        Route::get('class-schedules/category-view', [ClassScheduleController::class, 'categorySchedules'])
            ->name('class-schedules.categorySchedules');

        Route::resource(
            'class-schedules',
            ClassScheduleController::class
        );

        Route::patch(
            'class-schedules/{classSchedule}/toggle-active',
            [ClassScheduleController::class, 'toggleActive']
        )->name('class-schedules.toggleActive');

        Route::patch(
            'class-schedules/{classSchedule}/cancel',
            [ClassScheduleController::class, 'cancel']
        )->name('class-schedules.cancel');

        Route::patch(
            'class-schedules/{classSchedule}/status-update',
            [ClassScheduleController::class, 'statusUpdate']
        )->name('class-schedules.statusUpdate');

        Route::get(
            'today-classes',
            [ClassScheduleController::class, 'todayClasses']
        )->name('class-schedules.todayClasses');




        /*
        |--------------------------------------------------------------------------
        | Student Class Enrollments
        |--------------------------------------------------------------------------
        */

        // Category Students
        Route::get(
            'student-class-enrollments/class/{studentClass}/category/{classCategory}/students',
            [StudentClassEnrollmentController::class, 'categoryStudents']
        )->name('student-class-enrollments.categoryStudents');

        // Export PDF
        Route::get(
            'student-class-enrollments/class/{studentClass}/category/{classCategory}/students/pdf',
            [StudentClassEnrollmentController::class, 'categoryStudentsPdf']
        )->name('student-class-enrollments.categoryStudentsPdf');

        // Export Excel
        Route::get(
            'student-class-enrollments/class/{studentClass}/category/{classCategory}/students/excel',
            [StudentClassEnrollmentController::class, 'categoryStudentsExcel']
        )->name('student-class-enrollments.categoryStudentsExcel');

        Route::get(
            'student-class-enrollments/class/{class}/category-fee/{classCategoryFee}/students/{year}/{month}',
            [StudentClassEnrollmentController::class, 'classCategoryWisePaymentStudent']
        )->name('student-class-enrollments.category-wise-payment');

        // Resource
        Route::resource(
            'student-class-enrollments',
            StudentClassEnrollmentController::class
        );

        // Toggle Active
        Route::patch(
            'student-class-enrollments/{studentClassEnrollment}/toggle-active',
            [StudentClassEnrollmentController::class, 'toggleActive']
        )->name('student-class-enrollments.toggleActive');

        // Leave Enrollment
        Route::patch(
            'student-class-enrollments/{studentClassEnrollment}/leave',
            [StudentClassEnrollmentController::class, 'leave']
        )->name('student-class-enrollments.leave');

        // Restore Enrollment
        Route::patch(
            'student-class-enrollments/{id}/restore',
            [StudentClassEnrollmentController::class, 'restore']
        )->name('student-class-enrollments.restore');


        /*
        |--------------------------------------------------------------------------
        | Payments
        |--------------------------------------------------------------------------
        */

        Route::get('/payments', function () {
            return view('admin.payment.index');
        })->name('payments.index');

        Route::get('/payments/today-receipt', function () {
            return view('admin.payment.today-receipt');
        })->name('payments.today-receipt');



        Route::resource(
            'admissions',
            AdmissionController::class
        );

        Route::resource(
            'admission-payments',
            AdmissionPaymentController::class
        );

        /*
        |--------------------------------------------------------------------------
        | Attendance
        |--------------------------------------------------------------------------
        */

        Route::get('/attendance', function () {
            return view('admin.attendance.index');
        })->name('attendance.index');

        /*
|--------------------------------------------------------------------------
| STUDENT EXAM
|--------------------------------------------------------------------------
*/

        // Upcoming exams view
        Route::get(
            'exams/upcoming',
            [ExamController::class, 'upcoming']
        )->name('exams.upcoming');

        // Exam counts for dashboard
        Route::get(
            'exams/counts',
            [ExamController::class, 'counts']
        )->name('exams.counts');

        // Export Excel
        Route::get(
            'exams/export/excel',
            [ExamController::class, 'exportExcel']
        )->name('exams.export.excel');

        // Export PDF
        Route::get(
            'exams/export/pdf',
            [ExamController::class, 'exportPdf']
        )->name('exams.export.pdf');

        // Hall availability check (AJAX)
        Route::get(
            'exams/check-hall-availability',
            [
                ExamController::class,
                'checkHallAvailability'
            ]
        )->name('exams.check-hall-availability');

        // Get categories by class (AJAX)
        Route::get(
            'exams/get-categories',
            [ExamController::class, 'getCategoriesByClass']
        )->name('exams.get-categories');

        // Resource routes
        Route::resource(
            'exams',
            ExamController::class
        )->except(['destroy']);

        // Delete (soft delete)
        Route::delete(
            'exams/{exam}',
            [ExamController::class, 'destroy']
        )->name('exams.destroy');

        // Cancel exam
        Route::post(
            'exams/{exam}/cancel',
            [ExamController::class, 'cancel']
        )->name('exams.cancel');

        // Quick status update
        Route::put(
            'exams/{exam}/status',
            [ExamController::class, 'updateStatus']
        )->name('exams.update-status');

        Route::get(
            '/exams/search-classes',
            [ExamController::class, 'searchClasses']
        )->name('exams.search-classes');

        /*
|--------------------------------------------------------------------------
| EXAM RESULTS / MARK ENTRY
|--------------------------------------------------------------------------
*/

        // Mark entry page
        Route::get(
            'exams/{exam}/mark-entry',
            [ExamController::class, 'markEntry']
        )->name('exams.mark-entry');

        // Save student marks
        Route::post(
            'exams/{exam}/save-marks',
            [ExamController::class, 'saveMarks']
        )->name('exams.save-marks');

        // View exam results
        Route::get(
            'exams/{exam}/results',
            [ExamController::class, 'results']
        )->name('exams.results');

        // Recalculate ranks
        Route::post(
            'exams/{exam}/recalculate-ranks',
            [ExamController::class, 'recalculateRanks']
        )->name('exams.recalculate-ranks');

        Route::get(
            'exams/{exam}/results/excel',
            [ExamController::class, 'exportResultsExcel']
        )->name('exams.results.excel');

        Route::get(
            'exams/{exam}/results/pdf',
            [ExamController::class, 'exportResultsPdf']
        )->name('exams.results.pdf');


        /*
        |--------------------------------------------------------------------------
        | Organizer Payments
        |--------------------------------------------------------------------------
        */

        Route::get('/organizer-payments', [
            OrganizerPaymentController::class,
            'index'
        ])->name('organizer-payments.index');

        Route::get('/organizer-payments/{organizer}/pay', [
            OrganizerPaymentController::class,
            'pay'
        ])->name('organizer-payments.pay');

        Route::post('/organizer-payments/{organizer}/store', [
            OrganizerPaymentController::class,
            'store'
        ])->name('organizer-payments.store');

        Route::delete('/organizer-payments/{organizerPayment}', [
            OrganizerPaymentController::class,
            'destroy'
        ])->name('organizer-payments.destroy');

        Route::get('/organizer-payments/{organizer}/salary-slip', [
            OrganizerPaymentController::class,
            'salarySlip'
        ])->name('organizer-payments.salary-slip');

        Route::post('/organizer-payments/{organizer}/adjustment', [
            OrganizerPaymentController::class,
            'storeAdjustment'
        ])->name('organizer-payments.adjustment-store');

        Route::get('organizers/export/excel', [OrganizerController::class, 'exportExcel'])
            ->name('organizers.export.excel');

        Route::get('organizers/export/pdf', [OrganizerController::class, 'exportPdf'])
            ->name('organizers.export.pdf');


        /*
        |--------------------------------------------------------------------------
        | Teacher Salary Management
        |--------------------------------------------------------------------------
        */

        Route::get('/teacher-salaries', [
            TeacherSalaryController::class,
            'index'
        ])->name('teacher-salaries.index');

        Route::get('/teacher-salaries/{teacher}/{year}/{month}', [
            TeacherSalaryController::class,
            'show'
        ])->name('teacher-salaries.show');

        Route::post('/teacher-salaries/{teacher}/pay', [
            TeacherSalaryController::class,
            'teacherSalaryPaid'
        ])->name('teacher-salaries.pay');

        Route::get('/teacher-salaries/{teacher}/{year}/{month}/slip', [
            TeacherSalaryController::class,
            'printSalarySlip'
        ])->name('teacher-salaries.slip');


        Route::post(
            '/teacher-salaries/{teacher}/payment',
            [TeacherSalaryController::class, 'teacherPaymentStore']
        )->name('teacher-salaries.payment.store');

        Route::get('/teacher-salaries/{teacher}/{year}/{month}/details', [
            TeacherSalaryController::class,
            'paymentDetailsView'
        ])->name('teacher-salaries.payment-details');

        Route::delete('/teacher-salaries/{paymentId}', [
            TeacherSalaryController::class,
            'paymentDelete'
        ])->name('teacher-salaries.payment-delete');

        Route::get('/teacher-salaries/{teacher}/{year}/{month}/summary', [
            TeacherSalaryController::class,
            'teacherPaymentAndClassSummery'
        ])->name('teacher-salaries.payment-summary');

        /* 
        |--------------------------------------------------------------------------
        | Institute Income Report
        |-------------------------------------------------------------------------- 
        */
        Route::get('institute-income/monthly-report', [
            InstituteIncomeController::class,
            'monthlyIncomeReport'
        ])->name('institute-income.monthly-report');

        /*
        |--------------------------------------------------------------------------
        | Extra Incomes
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'extra-incomes',
            ExtraIncomeController::class
        );

        Route::resource(
            'temporary-id-cards',
            TemporaryIDCardController::class
        )->only([
            'index',
            'create',
            'store'
        ]);;

        Route::get(
            'temporary-id-cards/preview',
            [TemporaryIDCardController::class, 'preview']
        )->name('temporary-id-cards.preview');

        Route::post(
            'temporary-id-cards/preview-generate',
            [TemporaryIDCardController::class, 'generatePreview']
        )->name('temporary-id-cards.preview-generate');

        Route::get(
            'temporary-id-cards/status',
            [TemporaryIDCardController::class, 'updateStatusPage']
        )->name('temporary-id-cards.update-status');

        Route::post(
            'temporary-id-cards/change-status',
            [TemporaryIDCardController::class, 'changeStatus']
        )->name('temporary-id-cards.change-status');

        Route::post(
            'temporary-id-cards/download-pdf',
            [TemporaryIDCardController::class, 'downloadPdf']
        )->name('temporary-id-cards.download-pdf');
        /*
|--------------------------------------------------------------------------
| Daily Reports
|--------------------------------------------------------------------------
*/

        // Daily Report Routes
        Route::get('daily-report', [DailyReportController::class, 'index'])->name('daily-report.index');
        Route::get('daily-report/{type}/pdf', [DailyReportController::class, 'downloadPdf'])->name('daily-report.pdf');
        Route::get('daily-report/{type}/excel', [DailyReportController::class, 'downloadExcel'])->name('daily-report.excel');

        // Summary Report Download Routes (for generateDailyReport)
        Route::get('daily-report/summary/pdf', [DailyReportController::class, 'downloadSummaryPdf'])->name('daily-report.summary.pdf');
        Route::get('daily-report/summary/excel', [DailyReportController::class, 'downloadSummaryExcel'])->name('daily-report.summary.excel');

        // Teacher Report Routes
        Route::get('/teacher-report', [TeacherReportController::class, 'index'])->name('teacher-report.index');
        Route::get('/teacher-report/pdf', [TeacherReportController::class, 'downloadTeacherWithStudentPaymentsPdf'])->name('teacher-report.pdf');
        Route::get('/teacher-report/excel', [TeacherReportController::class, 'downloadTeacherWithStudentPaymentsExcel'])->name('teacher-report.excel');
        // Teacher Expense Report
        Route::get('/teacher-report/expense', [TeacherReportController::class, 'teacherExpenseReport'])->name('teacher-expense-report');
        Route::get('/teacher-report/expense/excel', [TeacherReportController::class, 'teacherExpenseReportExcel'])->name('teacher-expense-report.excel');
        Route::get('/teacher-report/expense/pdf', [TeacherReportController::class, 'teacherExpenseReportPdf'])->name('teacher-expense-report.pdf');

        Route::get('/monthly-report', [MonthlyReportController::class, 'index'])
            ->name('monthly-report.index');

        // Excel
        Route::get(
            '/excel/teacher/teacher-salary-report/excel',
            [MonthlyReportController::class, 'TeacherSalaryReportExcel']
        )->name('teacher.salary.report.excel');

        // PDF
        Route::get(
            '/pdf/teacher/teacher-salary-report/pdf',
            [MonthlyReportController::class, 'TeacherSalaryReportPdf']
        )->name('teacher.salary.report.pdf');
        /*
|--------------------------------------------------------------------------
| Institute Expenses
|--------------------------------------------------------------------------
*/

        Route::get('/student-images', [StudentImageController::class, 'index'])
            ->name('student-images.index');

        Route::post('/student-images/{quickPhoto}/assign', [StudentImageController::class, 'assign'])
            ->name('student-images.assign');

        Route::resource(
            'institute-expenses',
            InstituteExpenseController::class
        );

        Route::patch(
            'institute-expenses/{instituteExpense}/toggle-status',
            [InstituteExpenseController::class, 'toggleStatus']
        )->name('institute-expenses.toggle-status');


        Route::prefix('student-id-cards')
            ->name('student-id-cards.')
            ->group(function () {

                // View routes
                Route::get('/', [StudentIDCardController::class, 'index'])
                    ->name('index');

                Route::get('{studentIdCard}/print', [StudentIDCardController::class, 'print'])
                    ->name('print');

                // Download routes (NO Browsershot - Client side)
                Route::get('{studentIdCard}/download', [StudentIDCardController::class, 'downloadSingle'])
                    ->name('download-single');

                Route::post('download-bulk', [StudentIDCardController::class, 'downloadBulk'])
                    ->name('download-bulk');

                // Status update routes (using Fetch API)
                Route::patch('{studentIdCard}/status', [StudentIDCardController::class, 'updateStatus'])
                    ->name('update-status');

                Route::patch('bulk-status', [StudentIDCardController::class, 'bulkUpdateStatus'])
                    ->name('bulk-update-status');
            });

        Route::get('/today-attendance', [TodayAttendanceController::class, 'index'])
            ->name('today-attendance.index');

        Route::get(
            '/reports/teacher-student-payment',
            [MonthlyReportController::class, 'TeacherWithStudentPaymentReport']
        )->name('teacher.student.payment.report');
        Route::get(
            '/reports/teacher-student-payment-excel',
            [MonthlyReportController::class, 'TeacherWithStudentPaymentReportExcel']
        )->name('teacher.student.payment.report.excel');
        Route::get(
            '/reports/teacher-student-payment-pdf',
            [MonthlyReportController::class, 'TeacherWithStudentPaymentReportPdf']
        )->name('teacher.student.payment.report.pdf');



        Route::get(
            '/reports/teacher-student-payment-date',
            [MonthlyReportController::class, 'TeacherWithStudentPaymentDateReport']
        )->name('teacher.student.payment.report');
        Route::get(
            '/reports/teacher-student-payment-date-excel',
            [MonthlyReportController::class, 'TeacherWithStudentPaymentDateReportExcel']
        )->name('teacher.student.payment.report.excel');
        Route::get(
            '/reports/teacher-student-payment-date-pdf',
            [MonthlyReportController::class, 'TeacherWithStudentPaymentDateReportPdf']
        )->name('teacher.student.payment.report.pdf');

        // Report Page
        Route::get(
            '/institute-reports',
            [InstituteReportController::class, 'index']
        )->name('institute-reports.index');

        // PDF Download
        Route::get(
            '/institute-reports/pdf',
            [InstituteReportController::class, 'institutePaymentReportPdf']
        )->name('institute-reports.pdf');

        // Excel Download
        Route::get(
            '/institute-reports/excel',
            [InstituteReportController::class, 'institutePaymentReportExcel']
        )->name('institute-reports.excel');


        /*
|--------------------------------------------------------------------------
| Institute Expenses
|--------------------------------------------------------------------------
*/

        Route::get('weekly-timetable', [ClassTimeTableController::class, 'weeklyTimeTable'])
            ->name('weekly-timetable');

        Route::get('weekly-timetable/pdf', [ClassTimeTableController::class, 'downloadPdf'])
            ->name('weekly.pdf');

        Route::get('weekly-timetable/excel', [ClassTimeTableController::class, 'downloadExcel'])
            ->name('weekly.excel');

        /*
|--------------------------------------------------------------------------
| System Setting
|--------------------------------------------------------------------------
*/

        Route::get('setting', [DatabaseBackupController::class, 'index'])
            ->name('setting.index');

        Route::get('setting/backup/export', [DatabaseBackupController::class, 'export'])
            ->name('setting.backup.export');

        Route::post('setting/backup/import', [DatabaseBackupController::class, 'import'])
            ->name('setting.backup.import');

        /*
|--------------------------------------------------------------------------
|  Activity Logs
|--------------------------------------------------------------------------
*/

        Route::get('/activity-logs', [App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/activity-logs/export', [App\Http\Controllers\Admin\ActivityLogController::class, 'export'])->name('activity-logs.export');
        Route::post('/activity-logs/clear', [App\Http\Controllers\Admin\ActivityLogController::class, 'clearOld'])->name('activity-logs.clear');


        /*
|--------------------------------------------------------------------------
|  Laravel Logs
|--------------------------------------------------------------------------
*/

        Route::get(
            '/logs/laravel',
            [LogController::class, 'index']
        )->name('logs.laravel.index');

        Route::post(
            '/logs/laravel/clear',
            [LogController::class, 'clear']
        )->name('logs.laravel.clear');

        Route::get(
            '/logs/laravel/download',
            [LogController::class, 'download']
        )->name('logs.laravel.download');

        Route::get(
            '/logs/laravel/stats',
            [LogController::class, 'stats']
        )
            ->name('logs.laravel.stats');

        Route::get(
            '/receipts',
            [ReceiptController::class, 'index']
        )->name('receipts.index');

        Route::get(
            '/receipts/export/excel',
            [ReceiptController::class, 'exportExcel']
        )->name('receipts.export.excel');

        Route::get(
            '/receipts/export/pdf',
            [ReceiptController::class, 'exportPdf']
        )->name('receipts.export.pdf');
    });

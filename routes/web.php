<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GpaController;
use App\Http\Controllers\SmsController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\FeesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarkController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\PaperController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\CronjobController;
use App\Http\Controllers\ICTCoreController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DormitoryController;
use App\Http\Controllers\InstituteController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\GradesheetController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\TabulationController;
use App\Http\Controllers\AcadamicYearController;





// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/session', [UsersController::class, 'session']);


Route::group(['middleware' => ['web', 'activity']], function () {
    Route::get('/', [HomeController::class, 'index'])->name("login");
    Route::get('/dashboard/', [DashboardController::class, 'index']);
    Route::post('/users/login', [UsersController::class, 'postSignin']);
    Route::get('/login/{user_id}/{d_id}', [UsersController::class, 'dologin']);
    Route::get('/verification_code', [UsersController::class, 'codeverify']);
    Route::post('/users/code_check', [UsersController::class, 'code_check']);
    Route::get('/branches', [InstituteController::class, 'branches']);
    Route::post('/branch', [InstituteController::class, 'createbranch']);
    Route::get('/attendance/today_delete', [AttendanceController::class, 'today_delete']);
    Route::get('/verify_code', [UsersController::class, 'verify_code']);
    Route::post('/verified', [UsersController::class, 'verified']);
    Route::get('/users/logout', [UsersController::class, 'getLogout']);
    Route::get('/users', [UsersController::class, 'show']);
    Route::post('/usercreate', [UsersController::class, 'create']);
    Route::get('/useredit/{id}', [UsersController::class, 'edit']);
    Route::post('/userupdate', [UsersController::class, 'update']);
    Route::get('/userdelete/{id}', [UsersController::class, 'delete']);
});

Route::group(['middleware' => ['auth', 'activity']], function () {
    /**
     * Class Routes
     **/
    Route::get('/class/create', [ClassController::class, 'index'])->middleware('checkPermission:class_add');
    Route::post('/class/create', [ClassController::class, 'create'])->middleware('checkPermission:class_add');
    Route::post('/ajaxcreate/create', [ClassController::class, 'ajaxcreate'])->middleware('checkPermission:class_add');
    Route::get('/class/list', [ClassController::class, 'show'])->middleware('checkPermission:class_view');
    Route::get('/class/edit/{id}', [ClassController::class, 'edit'])->middleware('checkPermission:class_update');
    Route::post('/class/update', [ClassController::class, 'update'])->middleware('checkPermission:class_update');
    Route::get('/class/delete/{id}', [ClassController::class, 'delete']);
    // Uncomment the line below to enable the middleware for delete permission
    // ->middleware('checkPermission:class_delete');
});


Route::group(['middleware' => ['web', 'activity']], function () {
    Route::get('/class/getsubjects/{class}', [ClassController::class, 'getSubjects']);
    Route::get('/class/diary/{class_id}', [ClassController::class, 'diary']);
    Route::get('/class/section/{class_id}', [ClassController::class, 'getForsectionjoin']);
    Route::post('/class/diary/save', [ClassController::class, 'diary_create']);
});


Route::group(['middleware' => ['auth', 'activity']], function () {
    Route::get('/section/create', [SectionController::class, 'index'])->middleware('checkPermission:section_add');
    Route::post('/section/create', [SectionController::class, 'create'])->middleware('checkPermission:section_add');
    Route::get('/section/list', [SectionController::class, 'show'])->middleware('checkPermission:section_view');
    Route::get('/get/section/{class_code}', [SectionController::class, 'get_section'])->middleware('checkPermission:section_view');
    Route::get('/section/edit/{id}', [SectionController::class, 'edit'])->middleware('checkPermission:section_update');
    Route::post('/section/update', [SectionController::class, 'update'])->middleware('checkPermission:section_update');
    Route::get('/section/delete/{id}', [SectionController::class, 'delete'])->middleware('checkPermission:section_delete');
    Route::get('/section/getList/{class}', [SectionController::class, 'getsections'])->middleware('checkPermission:class_add');
    Route::get('/section/view-timetable/{id}', [SectionController::class, 'view_timetable'])->middleware('checkPermission:section_time_table');
    Route::get('/section/getList/{class}/{session}', [SectionController::class, 'getsections']);
    Route::get('/section/getList/{class}', [SectionController::class, 'getsectionsc']);
    Route::get('/section/view-timetable/{id}', [SectionController::class, 'view_timetable']);

    //level routes
    Route::get('/level/create', [LevelController::class, 'index']);
    Route::post('/level/create', [LevelController::class, 'create']);
    Route::get('/level/list', [LevelController::class, 'show']);
    Route::get('/level/edit/{id}', [LevelController::class, 'edit']);
    Route::post('/level/update', [LevelController::class, 'update']);
    Route::get('/level/delete/{id}', [LevelController::class, 'delete']);


    Route::get('/subject/create', [SubjectController::class, 'index'])->middleware('checkPermission:subject_add');
    Route::post('/subject/create', [SubjectController::class, 'create'])->middleware('checkPermission:subject_add');
    Route::get('/subject/list', [SubjectController::class, 'show'])->middleware('checkPermission:subject_view');
    Route::get('/subject/edit/{id}', [SubjectController::class, 'edit'])->middleware('checkPermission:subject_update');
    Route::post('/subject/update', [SubjectController::class, 'update'])->middleware('checkPermission:subject_update');
    Route::get('/subject/delete/{id}', [SubjectController::class, 'delete'])->middleware('checkPermission:subject_delete');

    //Question routes
    Route::get('/question/create', [QuestionController::class, 'create'])->middleware('checkPermission:paper_add');
    Route::post('/question/create', [QuestionController::class, 'store'])->middleware('checkPermission:paper_add');
    Route::get('/paper/generate', [QuestionController::class, 'generate'])->middleware('checkPermission:paper_add');
    Route::post('/paper/generate', [QuestionController::class, 'post_generate'])->middleware('checkPermission:paper_add');
    Route::get('/question/list', [QuestionController::class, 'list'])->middleware('checkPermission:paper_view');
    Route::post('/question/list', [QuestionController::class, 'getlist'])->middleware('checkPermission:paper_view');
    Route::get('/question/edit/{id}', [QuestionController::class, 'edit'])->middleware('checkPermission:paper_update');
    Route::post('/question/update', [QuestionController::class, 'update'])->middleware('checkPermission:paper_update');
    Route::get('/question/delete/{id}', [QuestionController::class, 'delete'])->middleware('checkPermission:paper_delete');
    Route::get('/chapter/getList/{class}', [QuestionController::class, 'chapters'])->middleware('checkPermission:paper_view');
});


Route::group(['middleware' => ['web', 'activity']], function () {
    Route::get('/subject/getmarks/{subject}/{cls}', [SubjectController::class, 'getmarks']);
    Route::get('/subject/getList/{cls}', [SubjectController::class, 'getsubjects']);
});

// Students Routes
Route::middleware(['auth', 'activity'])->group(function() {
    Route::get('/student/getRegi/{class}/{session}/{section}', [StudentController::class, 'getRegi']);

    Route::middleware('checkPermission:student_add')->group(function() {
        Route::get('/student/create', [StudentController::class, 'index']);
        Route::post('/student/create', [StudentController::class, 'create']);
    });

    Route::middleware('checkPermission:student_view')->group(function() {
        Route::get('/student/list', [StudentController::class, 'show']);
        Route::post('/student/list', [StudentController::class, 'getList']);
    });

    Route::middleware('checkPermission:student_info')->group(function() {
        Route::get('/student/view/{id}', [StudentController::class, 'view']);
    });

    Route::middleware('checkPermission:student_student_portal_access')->group(function() {
        Route::get('/student/access/{id}', [StudentController::class, 'access']);
    });

    Route::middleware('checkPermission:student_update')->group(function() {
        Route::get('/student/edit/{id}', [StudentController::class, 'edit']);
        Route::post('/student/update', [StudentController::class, 'update']);
    });

    Route::middleware('checkPermission:student_delete')->group(function() {
        Route::get('/student/delete/{id}', [StudentController::class, 'delete']);
    });

    Route::middleware('checkPermission:student_student_bulk_add')->group(function() {
        Route::get('/student/create-file', [StudentController::class, 'index_file']);
        Route::post('/student/create-file', [StudentController::class, 'create_file']);
        Route::get('/student/csvexample', [StudentController::class, 'csvexample']);
    });

    Route::get('/family/list', [StudentController::class, 'family_list']);
    Route::get('/family/edit/{f_id}', [StudentController::class, 'family_edit']);
    Route::post('/family/update', [StudentController::class, 'family_update']);
    Route::get('/family/students/{f_id}', [StudentController::class, 'family_student_list']);
    Route::post('/family_discount/{f_id}', [StudentController::class, 'add_family_discount']);
    Route::post('/student/add/{f_id}', [StudentController::class, 'add_family_student']);
    Route::post('/students/shift/{f_id}', [StudentController::class, 'shift_student_family']);
});


Route::group(['middleware' => ['web', 'activity']], function () {
    Route::get('/student/getList/{class}/{section}/{shift}/{session}', [StudentController::class, 'getForMarks']);
    Route::get('/get/refral/{refral}', [StudentController::class, 'getrefral']);
    Route::get('/get/family_id/list/{refral}', [StudentController::class, 'f_id_list']);
    Route::get('/student/getsList/{class}/{section}/{shift}/{session}', [StudentController::class, 'getForMarksjoin']);
    Route::post('/student/search', [StudentController::class, 'search']);
    Route::post('/family/search', [StudentController::class, 'family']);
    Route::post('/family/student/search', [StudentController::class, 'familystudent']);
    Route::post('/get/family_id', [StudentController::class, 'get_family_id']);
    Route::post('/get/family/data', [StudentController::class, 'get_family_data']);
    Route::post('/sms/send', [StudentController::class, 'send_sms']);
    Route::get('/fee/getdiscountjson/{student_registration}', [StudentController::class, 'getdiscount']);

    // Teacher routes
    Route::get('/teacher/getRegi/{class}/{session}/{section}', [TeacherController::class, 'getRegi']);
});


Route::group(['middleware' => ['auth', 'activity']], function () {
    Route::get('/teacher/create', [TeacherController::class, 'index'])->middleware('checkPermission:teacher_add');
    Route::post('/teacher/create', [TeacherController::class, 'create'])->middleware('checkPermission:teacher_add');
    Route::post('/teacher/ajaxcreate', [TeacherController::class, 'ajaxcreate'])->middleware('checkPermission:teacher_add');
});


Route::group(['middleware' => ['web', 'activity']], function () {
    Route::get('/teacher/list', [TeacherController::class, 'show'])->middleware('checkPermission:teacher_view');
    Route::post('/teacher/list', [TeacherController::class, 'getList'])->middleware('checkPermission:teacher_view');
    Route::get('/get/teacher/{teacher_id}', [TeacherController::class, 'getteacherinfo'])->middleware('checkPermission:teacher_add');
    Route::get('/teacher/view/{id}', [TeacherController::class, 'view'])->middleware('checkPermission:teacher_view');
});


Route::group(['middleware' => 'auth'], function () {
    Route::get('/teacher/edit/{id}', [TeacherController::class, 'edit'])->middleware('checkPermission:teacher_update');
    Route::post('/teacher/update', [TeacherController::class, 'update'])->middleware('checkPermission:teacher_update');
    Route::get('/teacher/delete/{id}', [TeacherController::class, 'delete'])->middleware('checkPermission:teacher_delete');
});


Route::group(['middleware' => ['web', 'activity']], function () {
    Route::get('/teacher/getList/{class}/{section}/{shift}/{session}', [TeacherController::class, 'getForMarks']);
});


Route::group(['middleware' => ['auth', 'activity']], function () {
    Route::get('/teacher/create-file', [TeacherController::class, 'index_file'])->middleware('checkPermission:teacher_bulk_add');
    Route::post('/teacher/create-file', [TeacherController::class, 'create_file'])->middleware('checkPermission:teacher_bulk_add');
    Route::get('/teacher/access/{id}', [TeacherController::class, 'access'])->middleware('checkPermission:teacher_portal_access');
    Route::get('/teacher/create-timetable', [TeacherController::class, 'index_timetable'])->middleware('checkPermission:teacher_timetable_add');
    Route::post('/teacher/create_timetable', [TeacherController::class, 'create_timetable'])->middleware('checkPermission:teacher_timetable_add');
    Route::get('/timetable/edit/{timetable_id}', [TeacherController::class, 'edit_timetable']);
    Route::post('/timetable/update', [TeacherController::class, 'update_timetable']);
    Route::get('/timetable/delete/{timetable_id}', [TeacherController::class, 'delete_timetable']);
    Route::get('/teacher/diary/{teacher_id}', [TeacherController::class, 'diary_add']);
    Route::post('/teacher/diary', [TeacherController::class, 'diary_create']);
    Route::get('/teacher/getsubjects/{class_id}/{teacher_id}', [TeacherController::class, 'teachersubject']);
    Route::get('/teacher/getsections/{class_id}/{teacher_id}', [TeacherController::class, 'teachersection']);
    Route::get('/teacher/diary/show/{teacher_id}', [TeacherController::class, 'diaryshow']);
    Route::get('/diary/delete/{diary_id}', [TeacherController::class, 'delete_diary']);
});


Route::group(['middleware' => ['web', 'activity']], function () {
    Route::get('/teacher/view-timetable/{id}', [TeacherController::class, 'view_timetable']);
    Route::get('/section/getList/{class}/{session}', [SectionController::class, 'getsections']);
    Route::get('/section/getList/{class}', [SectionController::class, 'getsectionsc']);

    // Student attendance
    Route::get('/attendance/create', [AttendanceController::class, 'index'])->middleware('checkPermission:add_student_attendance');
    Route::post('/attendance/create', [AttendanceController::class, 'create'])->middleware('checkPermission:add_student_attendance');
    Route::get('/attendance/create-file', [AttendanceController::class, 'index_file']);
    Route::post('/attendance/create-file', [AttendanceController::class, 'create_file']);
    Route::get('/attendance/list', [AttendanceController::class, 'show'])->middleware('checkPermission:teacher_timetable_view');
    Route::post('/attendance/list', [AttendanceController::class, 'getlist'])->middleware('checkPermission:view_student_attendance');
    Route::get('/attendance/edit/{id}', [AttendanceController::class, 'edit'])->middleware('checkPermission:teacher_timetable_view');
    Route::post('/attendance/update', [AttendanceController::class, 'update'])->middleware('checkPermission:teacher_timetable_view');
    Route::get('/attendance/printlist/{class}/{section}/{shift}/{session}/{date}', [AttendanceController::class, 'printlist']);
});


Route::group(['middleware' => ['auth', 'activity']], function () {
    Route::get('/attendance/report', [AttendanceController::class, 'report']);
    Route::post('/attendance/report', [AttendanceController::class, 'getReport']);
    Route::get('/attendance/student_report', [AttendanceController::class, 'stdatdreportindex']);
    Route::get('/attendance/print_student_report/{b_form}', [AttendanceController::class, 'stdatdreport']);
    Route::get('/attendance_detail', [AttendanceController::class, 'attendance_detail']);
    Route::get('/attendance/monthly-report', [AttendanceController::class, 'monthlyReport'])->middleware('checkPermission:view_student_monthly_reports');
    /**
     * Papers route
     **/
    Route::get('/paper/create', [PaperController::class, 'index']);
    Route::post('/paper/create', [PaperController::class, 'create']);
    Route::get('/paper/list', [PaperController::class, 'show']);
    Route::get('/paper/edit/{id}', [PaperController::class, 'edit']);
    Route::post('/paper/update', [PaperController::class, 'update']);
    Route::get('/paper/delete/{id}', [PaperController::class, 'delete']);
    Route::get('/paper/getList/{class}', [PaperController::class, 'getexams']);

    // Exam
    Route::get('/exam/create', [ExamController::class, 'index'])->middleware('checkPermission:exam_add');
    Route::post('/exam/create', [ExamController::class, 'create'])->middleware('checkPermission:exam_add');
    Route::get('/exam/list', [ExamController::class, 'show'])->middleware('checkPermission:exam_view');
    Route::get('/exam/edit/{id}', [ExamController::class, 'edit'])->middleware('checkPermission:exam_update');
    Route::post('/exam/update', [ExamController::class, 'update'])->middleware('checkPermission:exam_update');
    Route::get('/exam/delete/{id}', [ExamController::class, 'delete'])->middleware('checkPermission:exam_delete');
    Route::get('/exam/getList/{class}', [ExamController::class, 'getexams']);
});


// Acadamic Year
Route::get('/academicYear', [AcadamicYearController::class, 'index']);
Route::get('/academicYear/create', [AcadamicYearController::class, 'add'])->name('year.add');
Route::post('/academicYear/create', [AcadamicYearController::class, 'create'])->name('year.create');
Route::get('/academicYear/list', [AcadamicYearController::class, 'show']);
Route::get('/academicYear/edit/{id}', [AcadamicYearController::class, 'edit']);
Route::post('/academicYear/update', [AcadamicYearController::class, 'update'])->name('year.update');
Route::get('/academicYear/delete/{id}', [AcadamicYearController::class, 'delete']);
Route::get('/academicYear/status/{id}', [AcadamicYearController::class, 'status']);

// GPA Routes
Route::get('/gpa', [GpaController::class, 'index'])->middleware('checkPermission:gpa_rule_add');
Route::post('/gpa/create', [GpaController::class, 'create'])->middleware('checkPermission:gpa_rule_add');
Route::get('/gpa/list', [GpaController::class, 'show'])->middleware('checkPermission:gpa_rule_view');
Route::get('/gpa/edit/{id}', [GpaController::class, 'edit'])->middleware('checkPermission:gpa_rule_update');
Route::post('/gpa/update', [GpaController::class, 'update'])->middleware('checkPermission:gpa_rule_update');
Route::get('/gpa/delete/{id}', [GpaController::class, 'delete'])->middleware('checkPermission:gpa_rule_delete');


Route::group(['middleware' => 'auth'], function () {

    Route::get('/cron/run', function () {
        Log::info('Executed at ' . date('Y-m-d H:i:s'));
        Artisan::call("Invoice:genrate");
        return Redirect::to('/dashboard')->with('success', "Invoice Created Successfully");
    })->name('cron.run');

    Route::get('/cron/invoices/months', function (Request $request) {
        $months = $request->input('month');
        $f_id = $request->input('family_id');
        Log::info('Executed at ' . date('Y-m-d H:i:s'));
        Artisan::call("Invoice:months", ['arg_name' => ['month' => $months, 'family_id' => $f_id]]);
        return Redirect::to('/dashboard')->with('success', "Invoice Created Successfully");
    })->name('cron.run1');

    Route::get('/smslog', [SmsController::class, 'getsmsLog']);
    Route::post('/smslog', [SmsController::class, 'postsmsLog']);
    Route::get('/smslog/delete/{id}', [SmsController::class, 'deleteLog']);
});


// Mark routes
Route::get('/mark/create', [MarkController::class, 'index'])->middleware('checkPermission:add_marks');
Route::post('/mark/create', [MarkController::class, 'create'])->middleware('checkPermission:add_marks');
Route::post('/new/mark/create', [MarkController::class, 'newcreate']);
Route::get('/marks/section/{class}', [MarkController::class, 'getForMarksjoin']);
Route::get('/create/marks', [MarkController::class, 'createmarks']);
Route::get('/mark/m_create', [MarkController::class, 'm_index'])->middleware('checkPermission:add_marks');
Route::post('/mark/m_create', [MarkController::class, 'm_create'])->middleware('checkPermission:add_marks');
Route::get('/mark/list', [MarkController::class, 'show'])->middleware('checkPermission:view_marks');
Route::post('/mark/list', [MarkController::class, 'getlist'])->middleware('checkPermission:view_marks');
Route::get('/mark/m_list', [MarkController::class, 'm_show'])->middleware('checkPermission:view_marks');
Route::post('/mark/m_list', [MarkController::class, 'm_getlist'])->middleware('checkPermission:view_marks');
Route::get('/mark/edit/{id}', [MarkController::class, 'edit'])->middleware('checkPermission:update_marks');
Route::get('/mark/m_edit/{id}', [MarkController::class, 'm_edit'])->middleware('checkPermission:update_marks');
Route::post('/mark/update', [MarkController::class, 'update'])->middleware('checkPermission:update_marks');
Route::post('/mark/m_update', [MarkController::class, 'm_update'])->middleware('checkPermission:update_marks');
Route::get('/mark/delete/{id}', [MarkController::class, 'delete'])->middleware('checkPermission:delete_marks');
Route::get('/template/creates', [MarkController::class, 'template']);
Route::get('/template/message/edit/{message_id}', [MarkController::class, 'edittemplate']);


    //Markssheet
Route::group(['middleware' => 'auth'], function () {
    Route::get('/result/generate', [GradesheetController::class, 'getgenerate']);
    Route::post('/result/generate', [GradesheetController::class, 'postgenerate']);
    Route::post('/result/m_generate', [GradesheetController::class, 'mpostgenerate']);
    Route::get('/result/search', [GradesheetController::class, 'search']);
    Route::post('/result/search', [GradesheetController::class, 'postsearch']);
    Route::get('/results', [GradesheetController::class, 'searchpub']);
    Route::post('/results', [GradesheetController::class, 'postsearchpub']);
    Route::get('/gradesheet', [GradesheetController::class, 'index']);
    Route::post('/gradesheet', [GradesheetController::class, 'stdlist']);
    Route::get('/gradesheet/print/{regiNo}/{exam}/{class}', [GradesheetController::class, 'printsheet']);
    Route::get('/gradesheet/m_print/{regiNo}/{exam}/{class}', [GradesheetController::class, 'm_printsheet']);
});

    //tabulation sheet
Route::group(['middleware' => 'auth'], function () {
    Route::get('/tabulation', [TabulationController::class, 'index']);
    Route::post('/tabulation', [TabulationController::class, 'getsheet']);

    // Settings
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::post('/settings', [SettingsController::class, 'save']);
    Route::get('/institute', [InstituteController::class, 'index']);
    Route::post('/institute', [InstituteController::class, 'save']);
    Route::get('/ictcore', [ICTCoreController::class, 'index']);
    Route::post('/ictcore', [ICTCoreController::class, 'create']);
    Route::post('/notification_type', [ICTCoreController::class, 'noti_create']);
    Route::get('/notification_type', [ICTCoreController::class, 'noti_index']);
    Route::get('/ictcore/attendance', [ICTCoreController::class, 'attendance_index']);
    Route::post('/ictcore/attendance', [ICTCoreController::class, 'post_attendance']);
    Route::get('/ictcore/fees', [ICTCoreController::class, 'fee_message_index']);
    Route::post('/ictcore/fees', [ICTCoreController::class, 'post_fees']);

    //promotion
    Route::get('/promotion', [PromotionController::class, 'index']);
    Route::post('/promotion', [PromotionController::class, 'store']);
    Route::get('/template/create', [TemplateController::class, 'index']);
    Route::post('/template/create', [TemplateController::class, 'create']);
    Route::get('/template/list', [TemplateController::class, 'show']);
    Route::get('/message/edit/{id}', [TemplateController::class, 'edit']);
    Route::post('/message/update', [TemplateController::class, 'update']);
    Route::get('/message/delete/{id}', [TemplateController::class, 'delete']);
    Route::get('/message', [MessageController::class, 'index']);
    Route::post('/message', [MessageController::class, 'create']);
});


Route::get('/settings', [SettingsController::class, 'index']);
Route::post('/settings', [SettingsController::class, 'save']);
Route::get('/permission', [PermissionController::class, 'index']);
Route::post('/permission/create', [PermissionController::class, 'store']);
Route::get('/schedule', [SettingsController::class, 'get_schedule']);
Route::post('/schedule', [SettingsController::class, 'post_schedule']);

    // Accounting
Route::group(['middleware' => 'auth'], function () {
    Route::get('/accounting', [AccountingController::class, 'index'])->middleware('checkPermission:accounting');
    Route::post('/accounting', [AccountingController::class, 'store'])->middleware('checkPermission:accounting');
    Route::get('/accounting/sectors', [AccountingController::class, 'sectors'])->middleware('checkPermission:accounting');
    Route::post('/accounting/sectorcreate', [AccountingController::class, 'sectorCreate'])->middleware('checkPermission:accounting');
    Route::get('/accounting/sectorlist', [AccountingController::class, 'sectorList'])->middleware('checkPermission:accounting');
    Route::get('/accounting/sectoredit/{id}', [AccountingController::class, 'sectorEdit'])->middleware('checkPermission:accounting');
    Route::post('/accounting/sectorupdate', [AccountingController::class, 'sectorUpdate'])->middleware('checkPermission:accounting');
    Route::get('/accounting/sectordelete/{id}', [AccountingController::class, 'sectorDelete'])->middleware('checkPermission:accounting');

    Route::get('/accounting/income', [AccountingController::class, 'income'])->middleware('checkPermission:accounting');
    Route::post('/accounting/incomecreate', [AccountingController::class, 'incomeCreate'])->middleware('checkPermission:accounting');
    Route::get('/accounting/incomelist', [AccountingController::class, 'incomeList'])->middleware('checkPermission:accounting');
    Route::post('/accounting/incomelist', [AccountingController::class, 'incomeListPost'])->middleware('checkPermission:accounting');
    Route::get('/accounting/incomeedit/{id}', [AccountingController::class, 'incomeEdit'])->middleware('checkPermission:accounting');
    Route::post('/accounting/incomeupdate', [AccountingController::class, 'incomeUpdate'])->middleware('checkPermission:accounting');
    Route::get('/accounting/incomedelete/{id}', [AccountingController::class, 'incomeDelete'])->middleware('checkPermission:accounting');

    Route::get('/accounting/expence', [AccountingController::class, 'expence'])->middleware('checkPermission:accounting');
    Route::post('/accounting/expencecreate', [AccountingController::class, 'expenceCreate'])->middleware('checkPermission:accounting');
    Route::get('/accounting/expencelist', [AccountingController::class, 'expenceList'])->middleware('checkPermission:accounting');
    Route::post('/accounting/expencelist', [AccountingController::class, 'expenceListPost'])->middleware('checkPermission:accounting');
    Route::get('/accounting/expenceedit/{id}', [AccountingController::class, 'expenceEdit'])->middleware('checkPermission:accounting');
    Route::post('/accounting/expenceupdate', [AccountingController::class, 'expenceUpdate'])->middleware('checkPermission:accounting');
    Route::get('/accounting/expencedelete/{id}', [AccountingController::class, 'expenceDelete'])->middleware('checkPermission:accounting');
    Route::get('/accounting/report', [AccountingController::class, 'getReport'])->middleware('checkPermission:accounting');
    Route::get('/accounting/reportsum', [AccountingController::class, 'getReportsum'])->middleware('checkPermission:accounting');
    Route::get('/accounting/reportprint/{rtype}/{fdate}/{tdate}', [AccountingController::class, 'printReport'])->middleware('checkPermission:accounting');
    Route::get('/accounting/reportprintsum/{fdate}/{tdate}', [AccountingController::class, 'printReportsum'])->middleware('checkPermission:accounting');
});

    //Fees Related routes
Route::group(['middleware' => 'auth'], function () {
    Route::get('/fees/setup', [FeesController::class, 'getsetup'])->middleware('checkPermission:add_fess');
    Route::post('/fees/setup', [FeesController::class, 'postsetup'])->middleware('checkPermission:add_fess');
    Route::get('/fees/list', [FeesController::class, 'getList'])->middleware('checkPermission:view_fess');
    Route::post('/fees/list', [FeesController::class, 'postList'])->middleware('checkPermission:view_fess');
    Route::get('/fee/edit/{id}', [FeesController::class, 'getEdit'])->middleware('checkPermission:update_fess');
    Route::post('/fee/edit', [FeesController::class, 'postEdit'])->middleware('checkPermission:update_fess');
    Route::get('/fee/delete/{id}', [FeesController::class, 'getDelete'])->middleware('checkPermission:delete_fess');
    Route::get('/fee/collection', [FeesController::class, 'getCollection'])->middleware('checkPermission:add_fess');
    Route::post('/fee/collection', [FeesController::class, 'postCollection'])->middleware('checkPermission:add_fess');
    Route::get('/fee/getListjson/{class}/{type}', [FeesController::class, 'getListjson']);
    Route::get('/fee/getFeeInfo/{id}', [FeesController::class, 'getFeeInfo']);
    Route::get('/fee/getDue/{class}/{stdId}', [FeesController::class, 'getDue']);
    Route::get('/fees/view', [FeesController::class, 'stdfeeview'])->middleware('checkPermission:view_fess');
    Route::post('/fees/view', [FeesController::class, 'stdfeeviewpost'])->middleware('checkPermission:view_fess');
    Route::get('/fees/invoices', [FeesController::class, 'stdfeeinvoices'])->middleware('checkPermission:add_fess');
    Route::post('/fees/invoices', [FeesController::class, 'stdfeeinvoicespost'])->middleware('checkPermission:add_fess');
    Route::get('/fees/delete/{billNo}', [FeesController::class, 'stdfeesdelete']);
    Route::get('/fees/report', [FeesController::class, 'report']);
    Route::get('/fees/report/std/{regiNo}', [FeesController::class, 'reportstd']);
    Route::get('/fees/report/{sDate}/{eDate}', [FeesController::class, 'reportprint']);

    Route::get('/fee/vouchar', [FeesController::class, 'vouchar_index']);
    Route::get('/fee/vouchar/history', [FeesController::class, 'vouchar_history']);
    Route::get('/fees/paid/{billno}', [FeesController::class, 'vouchar_paid']);
    // Route::get('/fee/vouchar','feesController@getvouchar');
    Route::post('/fee/voucher', [FeesController::class, 'postvouchar']);
    Route::get('/fee/get_vouchar', [FeesController::class, 'createvoucher']);
    Route::get('/fee/get_vouchar/{id}', [FeesController::class, 'getvoucher']);
    Route::get('/f_vouchar/model/{f_id}', [FeesController::class, 'getfvoucher']);
    Route::get('/fees/details/{billNo}', [FeesController::class, 'billDetails']);
    Route::get('/fees/history/{billNo}', [FeesController::class, 'invoicehist']);
    Route::get('/fees/invoice/details/{billNo}', [FeesController::class, 'invoiceDetails']);
    Route::post('/fees/invoice/collect/{billNo}', [FeesController::class, 'invoiceCollect']);
    Route::get('/fees/classreport', [FeesController::class, 'classreportindex']);
    // Route::post('/fees/classreport','feesController@classreport');
    Route::post('/fees/classreport', [FeesController::class, 'classview']);
    Route::get('/fee/detail', [FeesController::class, 'detail']);

    Route::post('/fees/unpaid_notification', [FeesController::class, 'ictcorefees']);
    Route::post('/fee/unpaid_notification', [FeesController::class, 'sendnotification']);
    Route::get('/fee_detail', [FeesController::class, 'fee_detail']);
    Route::get('/family/vouchars/{family_id}', [FeesController::class, 'get_family_voucher']);
    Route::get('/family/vouchar_history/{family_id}', [FeesController::class, 'family_voucherhistory']);
    Route::post('/family/paid/{family_id}', [FeesController::class, 'family_vouchar_paid']);
    Route::get('/voucher/detail/{id}', [FeesController::class, 'family_vouchar_detail']);
    Route::get('/family/vouchar_print/{family_id}/{billno}', [FeesController::class, 'family_voucherprint']);
});

    //Admisstion routes
Route::middleware(['auth'])->group(function () {
    Route::get('/regonline', [AdmissionController::class, 'regonline']);
    Route::post('/regonline', [AdmissionController::class, 'Postregonline']);
    Route::get('/applicants', [AdmissionController::class, 'applicants']);
    Route::post('/applicants', [AdmissionController::class, 'postapplicants']);
    Route::get('/applicants/view/{id}', [AdmissionController::class, 'applicantview']);
    Route::get('/applicants/payment', [AdmissionController::class, 'payment']);
    Route::get('/applicants/delete/{id}', [AdmissionController::class, 'delete']);
    Route::get('/admitcard', [AdmissionController::class, 'admitcard']);
    Route::post('/printadmitcard', [AdmissionController::class, 'printAdmitCard']);
});

    //library routes
Route::middleware(['auth'])->group(function () {
    Route::get('/library/addbook', [LibraryController::class, 'getAddbook']);
    Route::post('/library/addbook', [LibraryController::class, 'postAddbook']);
    Route::get('/library/view', [LibraryController::class, 'getviewbook']);
    Route::get('/library/view-show', [LibraryController::class, 'postviewbook']);
    Route::get('/library/edit/{id}', [LibraryController::class, 'getBook']);
    Route::post('/library/update', [LibraryController::class, 'postUpdateBook']);
    Route::get('/library/delete/{id}', [LibraryController::class, 'deleteBook']);
    Route::get('/library/issuebook', [LibraryController::class, 'getissueBook']);

    //check availabe book
    Route::get('/library/issuebook-availabe/{code}/{quantity}', [LibraryController::class, 'checkBookAvailability']);
    Route::post('/library/issuebook', [LibraryController::class, 'postissueBook']);
    Route::get('/library/issuebookview', [LibraryController::class, 'getissueBookview']);
    Route::post('/library/issuebookview', [LibraryController::class, 'postissueBookview']);
    Route::get('/library/issuebookupdate/{id}', [LibraryController::class, 'getissueBookupdate']);
    Route::post('/library/issuebookupdate', [LibraryController::class, 'postissueBookupdate']);
    Route::get('/library/issuebookdelete/{id}', [LibraryController::class, 'deleteissueBook']);
    Route::get('/library/search', [LibraryController::class, 'getsearch']);
    Route::get('/library/search2', [LibraryController::class, 'getsearch']);
    Route::post('/library/search', [LibraryController::class, 'postsearch']);
    Route::post('/library/search2', [LibraryController::class, 'postsearch2']);
    Route::get('/library/reports', [LibraryController::class, 'getReports']);
    Route::get('/library/reports/fine', [LibraryController::class, 'getReportsFine']);
    Route::get('/library/reportprint/{do}', [LibraryController::class, 'Reportprint']);
    Route::get('/library/reports/fine/{month}', [LibraryController::class, 'ReportsFineprint']);
});


//Hostal Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dormitory', [DormitoryController::class, 'index']);
    Route::post('/dormitory/create', [DormitoryController::class, 'create']);
    Route::get('/dormitory/edit/{id}', [DormitoryController::class, 'edit']);
    Route::post('/dormitory/update', [DormitoryController::class, 'update']);
    Route::get('/dormitory/delete/{id}', [DormitoryController::class, 'delete']);
    Route::get('/dormitory/getstudents/{dormid}', [DormitoryController::class, 'getstudents']);
    Route::get('/dormitory/assignstd', [DormitoryController::class, 'stdindex']);
    Route::post('/dormitory/assignstd/create', [DormitoryController::class, 'stdcreate']);
    Route::get('/dormitory/assignstd/list', [DormitoryController::class, 'stdshow']);
    Route::post('/dormitory/assignstd/list', [DormitoryController::class, 'poststdShow']);
    Route::get('/dormitory/assignstd/edit/{id}', [DormitoryController::class, 'stdedit']);
    Route::post('/dormitory/assignstd/update', [DormitoryController::class, 'stdupdate']);
    Route::get('/dormitory/assignstd/delete/{id}', [DormitoryController::class, 'stddelete']);
    Route::get('/dormitory/fee', [DormitoryController::class, 'feeindex']);
    Route::post('/dormitory/fee', [DormitoryController::class, 'feeadd']);
    Route::get('/dormitory/fee/info/{regiNo}', [DormitoryController::class, 'feeinfo']);
    Route::get('/dormitory/report/std', [DormitoryController::class, 'reportstd']);
    Route::get('/dormitory/report/std/{dormId}', [DormitoryController::class, 'reportstdprint']);
    Route::get('/dormitory/report/fee', [DormitoryController::class, 'reportfee']);
    Route::get('/dormitory/report/fee/{dormId}/{month}', [DormitoryController::class, 'reportfeeprint']);
});


    //barcode generate
Route::middleware(['auth'])->group(function () {
    Route::get('/barcode', [BarcodeController::class, 'index']);
    Route::post('/barcode', [BarcodeController::class, 'generate']);

    // Holiday Routes
    Route::get('/holidays', [AttendanceController::class, 'holidayIndex']);
    Route::post('/holidays/create', [AttendanceController::class, 'holidayCreate']);
    Route::get('/holidays/delete/{id}', [AttendanceController::class, 'holidayDelete']);

    // Class Off Routes
    Route::get('/class-off', [AttendanceController::class, 'classOffIndex']);
    Route::post('/class-off/store', [AttendanceController::class, 'classOffStore']);
    Route::get('/class-off/delete/{id}', [AttendanceController::class, 'classOffDelete']);

    // Website Contents Routes
    Route::get('/site/dashboard', [SiteController::class, 'dashboard'])->name('site.dashboard');
    Route::resource('slider', SliderController::class);
    Route::get('/site/about-content', [SiteController::class, 'aboutContent'])->name('site.about_content');
    Route::post('/site/about-content', [SiteController::class, 'aboutContent']);
    Route::get('site/about-content/images', [SiteController::class, 'aboutContentImage'])->name('site.about_content_image');
    Route::post('site/about-content/images', [SiteController::class, 'aboutContentImage']);
    Route::post('site/about-content/images/{id}', [SiteController::class, 'aboutContentImageDelete'])->name('site.about_content_image_delete');
    Route::get('site/service', [SiteController::class, 'serviceContent'])->name('site.service');
    Route::post('site/service', [SiteController::class, 'serviceContent']);
    Route::get('site/statistic', [SiteController::class, 'statisticContent'])->name('site.statistic');
    Route::post('site/statistic', [SiteController::class, 'statisticContent']);
    Route::get('site/testimonial', [SiteController::class, 'testimonialIndex'])->name('site.testimonial');
    Route::post('site/testimonial', [SiteController::class, 'testimonialIndex']);
    Route::get('site/testimonial/create', [SiteController::class, 'testimonialCreate'])->name('site.testimonial_create');
    Route::post('site/testimonial/create', [SiteController::class, 'testimonialCreate']);
    Route::get('site/subscribe', [SiteController::class, 'subscribe'])->name('site.subscribe');
});


Route::middleware(['super_admin'])->group(function () {
    Route::get('/gradsystem', [GradesheetController::class, 'gradsystem']);
});


Route::get('/cronjob/feenotification', [CronjobController::class, 'feenotification']);

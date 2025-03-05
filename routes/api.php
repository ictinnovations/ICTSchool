<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\LevelController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\ResultController;





Route::post('authenticate', [UserController::class, 'login']);
Route::post('users', [UserController::class, 'create_user']);

Route::middleware('auth:api')->group(function () {

    // User API routes
    Route::get('users/profile', [UserController::class, 'profile']);
    Route::get('users/{user_id}', [UserController::class, 'get_user']);
    Route::get('users', [UserController::class, 'get_alluser']);
    Route::get('authenticate/cancel', [UserController::class, 'logout']);
    Route::put('users/{user_id}', [UserController::class, 'put_user']);

    // Attendance api routes
    Route::get('attendances', [AttendanceController::class, 'getallattendance']);
    Route::get('attendances/today', [AttendanceController::class, 'count_attendances']);
    Route::get('attendances/{class_level}/{section}/{shift}/{session}/{date}', [AttendanceController::class, 'attendance_view']);
    Route::post('attendances', [AttendanceController::class, 'attendance_create']);
    Route::get('attendances/{attendance_id}', [AttendanceController::class, 'get_attendance']);
    Route::put('attendances/{attendance_id}', [AttendanceController::class, 'update_attendance']);
    Route::delete('attendances/{attendance_id}', [AttendanceController::class, 'deleted']);
    Route::get('classes/{class_id}/attendances', [AttendanceController::class, 'get_attendance_classes']);
    Route::get('classes/{class_id}/attendances/history', [AttendanceController::class, 'classaten_history']);
    Route::get('classes/{class_id}/attendances_today', [AttendanceController::class, 'get_attendance_class_today']);
    Route::get('sections/{section_id}/attendances', [AttendanceController::class, 'get_attendance_section']);
    Route::get('sections/{section_id}/attendances/history', [AttendanceController::class, 'sectionaten_history']);
    Route::get('sections/{section_id}/attendances_today', [AttendanceController::class, 'get_attendance_section_today']);
    Route::put('sections/{section_id}/attendances_today/done', [AttendanceController::class, 'attendance_done']);
    Route::get('sections/{section_id}/attendances_today/done', [AttendanceController::class, 'get_attendance_done']);
    Route::post('sections/{section_id}/attendances_today/notification', [AttendanceController::class, 'notification']);
    Route::get('students/{student_id}/attendances', [AttendanceController::class, 'get_attendance_student']);
    Route::get('students/{student_id}/attendances_today', [AttendanceController::class, 'get_attendance_student_today']);

    //Student
    Route::get('branches/data', [BranchController::class, 'branches_data']);
    Route::get('students', [StudentController::class, 'all_students']);
    Route::get('students/count', [StudentController::class, 'count_students']);
    Route::get('students/fee', [StudentController::class, 'count_student_fee']);
    Route::get('students/{class_code}/{section}/{shift}/{session}', [StudentController::class, 'student_classwise']);
    Route::get('students/{student_id}', [StudentController::class, 'getstudent']);
    Route::put('students/{student_id}', [StudentController::class, 'update_student']);
    Route::get('students/{student_id}/subjects', [StudentController::class, 'getstudentsubjects']);
    Route::post('students/{student_id}/notifications', [StudentController::class, 'studentnotification']);

    //Teachers counts
    
    //Classes
    Route::get('classes', [ClassController::class, 'classes']);
    Route::get('classes/count', [ClassController::class, 'classes_count']);
    Route::get('classes/{class_id}', [ClassController::class, 'getclass']);
    Route::get('classes/{class_id}/sections', [ClassController::class, 'getclass_section']);
    Route::put('classes/{class_id}', [ClassController::class, 'update_class']);
    Route::post('classes/{class_id}/notifications', [ClassController::class, 'classwisenotification']);
    
    //Levels
    Route::get('levels', [LevelController::class, 'levels']);
    Route::get('levels/{level_id}', [LevelController::class, 'getlevel']);
    
    //Section
    Route::get('sections', [SectionController::class, 'section']);
    Route::get('sections/{section_id}', [SectionController::class, 'getsection']);
    Route::put('sections/{section_id}', [SectionController::class, 'putsection']);
    Route::get('sections/{section_id}/subjects', [SectionController::class, 'getsectionsubject']);
    Route::get('sections/{section_id}/students', [SectionController::class, 'getsectionstudent']);
    Route::get('sections/{section_id}/teachers', [SectionController::class, 'getsectionteacher']);
    Route::post('sections/{section_id}/notifications', [SectionController::class, 'sectionwisenotification']);
    
    //Teachers
    Route::get('teachers', [TeacherController::class, 'all_teachers']);
    Route::get('teachers/count', [TeacherController::class, 'count_teachers']);
    Route::get('teachers/{teacher_id}', [TeacherController::class, 'getteacher']);
    Route::put('teachers/{teacher_id}', [TeacherController::class, 'update_teacher']);
    Route::get('teachers/{teacher_id}/sections', [TeacherController::class, 'getsectionteacher']);
    Route::get('teachers/{teacher_id}/subjects', [TeacherController::class, 'getsubjectteacher']);
    Route::get('teachers/{teacher_id}/attendances', [TeacherController::class, 'getteacherdata']);
    Route::get('admin/attendances', [TeacherController::class, 'getadmindata']);

    //Exam
    Route::get('exams', [ExamController::class, 'getallexam']);
    Route::get('exams/{exam_id}', [ExamController::class, 'getexam']);

    //Message
    Route::get('messages', [MessageController::class, 'getallmessages']);
    Route::post('messages', [MessageController::class, 'postmessage']);
    Route::get('messages/{message_name}', [MessageController::class, 'getmessage']);
    Route::put('messages/{message_name}', [MessageController::class, 'putmessage']);
    Route::delete('messages/{message_name}', [MessageController::class, 'deletemessage']);

    //Notification
    Route::get('notifications', [NotificationController::class, 'getallnotification']);
    Route::post('notifications', [NotificationController::class, 'postnotification']);
    Route::get('notifications/{notification_id}', [NotificationController::class, 'getnotification']);
    Route::put('notifications/{notification_id}', [NotificationController::class, 'putnotification']);
    Route::delete('notifications/{notification_id}', [NotificationController::class, 'deletenotification']);

    //Result
    Route::get('results', [ResultController::class, 'getallresult']);
    Route::get('results/{result_id}', [ResultController::class, 'getresult']);
    Route::delete('results/{result_id}', [ResultController::class, 'deleteresult']);
    Route::post('results', [ResultController::class, 'postresult']);
    Route::put('results/{result_id}', [ResultController::class, 'putresult']);
});

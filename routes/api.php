<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('authenticate', 'Api\UserController@login');
Route::post('users', 'Api\UserController@create_user');

//Route::post('details', 'Api\UserController@details');

  Route::group(['middleware' => 'auth:api'], function(){
    	//user api routes
	Route::get('users/profile', 'Api\UserController@profile');
	Route::get('users/{user_id}', 'Api\UserController@get_user');
	Route::get('users', 'Api\UserController@get_alluser');
	Route::get('authenticate/cancel','Api\UserController@logout');
	Route::put('users/{user_id}', 'Api\UserController@put_user');
	
// Attendance api routes
  Route::get('attendances','Api\AttendanceController@getallattendance');
  Route::get('attendances/today','Api\AttendanceController@count_attendances');
	Route::get('attendances/{class_level}/{section}/{shift}/{session}/{date}','Api\AttendanceController@attendance_view');
	Route::post('attendances','Api\AttendanceController@attendance_create');
	Route::get('attendances/{attendance_id}','Api\AttendanceController@get_attendance');
	Route::put('attendances/{attendance_id}','Api\AttendanceController@update_attendance');
  Route::delete('attendances/{attendance_id}','Api\AttendanceController@deleted');
  Route::get('classes/{class_id}/attendances','Api\AttendanceController@get_attendance_classes');
  Route::get('classes/{class_id}/attendances/history','Api\AttendanceController@classaten_history');
  Route::get('classes/{class_id}/attendances_today','Api\AttendanceController@get_attendance_class_today');
  Route::get('sections/{section_id}/attendances','Api\AttendanceController@get_attendance_section');
  Route::get('sections/{section_id}/attendances/history','Api\AttendanceController@sectionaten_history');
  Route::get('students/{student_id}/attendances','Api\AttendanceController@get_attendance_student');
  Route::get('sections/{section_id}/attendances_today','Api\AttendanceController@get_attendance_section_today');
  Route::put('sections/{section_id}/attendances_today/done','Api\AttendanceController@attendance_done');
  Route::get('sections/{section_id}/attendances_today/done','Api\AttendanceController@get_attendance_done');
  Route::post('sections/{section_id}/attendances_today/notification','Api\AttendanceController@notification');
  Route::get('students/{student_id}/attendances_today','Api\AttendanceController@get_attendance_student_today');

   //student
   Route::get('branches/data','Api\BranchController@branches_data');
   Route::get('students','Api\StudentController@all_students');
   Route::get('students/count','Api\StudentController@count_students');
   Route::get('students/fess','Api\StudentController@count_student_fee');
   Route::get('students/{class_code}/{section}/{shift}/{session}','Api\StudentController@student_classwise');
   Route::get('students/{student_id}','Api\StudentController@getstudent');
   Route::put('students/{student_id}','Api\StudentController@update_student');
   Route::get('students/{student_id}/subjects','Api\StudentController@getstudentsubjects');
   Route::post('students/{student_id}/notifications','Api\StudentController@studentnotification');
   //teachers counts
    Route::get('teachers/count','Api\TeacherController@count_teachers');
   // classes
    Route::get('classes','Api\ClassController@classes');
    Route::get('classes/count','Api\ClassController@classes_count');
    Route::get('classes/{class_id}','Api\ClassController@getclass');
    Route::get('classes/{class_id}/sections','Api\ClassController@getclass_section');
    Route::put('classes/{class_id}','Api\ClassController@update_class');
    Route::post('classes/{class_id}/notifications','Api\ClassController@classwisenotification');

   // Levels
   Route::get('levels','Api\LevelController@levels');
   Route::get('levels/{level_id}','Api\LevelController@getlevel');

   //section
  Route::get('sections','Api\sectionController@section');
  Route::get('sections/{section_id}','Api\sectionController@getsection');
  Route::put('sections/{section_id}','Api\sectionController@putsection');
  Route::get('sections/{section_id}/subjects','Api\sectionController@getsectionsubject');
  Route::get('sections/{section_id}/students','Api\sectionController@getsectionstudent');
  Route::get('sections/{section_id}/teachers','Api\sectionController@getsectionteacher');
  Route::post('sections/{section_id}/notifications','Api\sectionController@sectionwisenotification');

  

  //Teachers
   Route::get('teachers','Api\TeacherController@all_teachers');
   Route::get('teachers/{teacher_id}','Api\TeacherController@getteacher');
   Route::put('teachers/{teacher_id}','Api\TeacherController@update_teacher');
   Route::get('teachers/{teacher_id}/sections','Api\TeacherController@getsectionteacher');
   Route::get('teachers/{teacher_id}/subjects','Api\TeacherController@getsubjectteacher');
   Route::get('teachers/{teacher_id}/attendances','Api\TeacherController@getteacherdata');
   Route::get('admin/attendances','Api\TeacherController@getadmindata');
   

//exam

  Route::get('exams','Api\ExamController@getallexam');
  Route::get('exams/{exam_id}','Api\ExamController@getexam');
  // Route::get('classes/{class_id}','Api\ClassController@getclass');
   //Route::put('classes/{class_id}','Api\ClassController@update_class');
   //message
   Route::get('messages','Api\MessageController@getallmessages');
   Route::post('messages','Api\MessageController@postmessage');
   Route::get('messages/{message_name}','Api\MessageController@getmessage');
   Route::put('messages/{message_name}','Api\MessageController@putmessage');
   Route::delete('messages/{message_name}','Api\MessageController@deletemessage');
   
//Notification
   Route::get('notifications','Api\NotificationController@getallnotification');
   Route::post('notifications','Api\NotificationController@postnotification');
   Route::get('notifications/{notification_id}','Api\NotificationController@getnotification');
   Route::put('notifications/{notification_id}','Api\NotificationController@putnotification');
   Route::delete('notifications/{notification_id}','Api\NotificationController@deletenotification');
   
   //Result
   Route::get('results','Api\ResultController@getallresult');
   Route::get('results/{result_id}','Api\ResultController@getresult'); 
   Route::delete('results/{result_id}','Api\ResultController@deleteresult');
   Route::post('results','Api\ResultController@postresult');
   Route::put('results/{result_id}','Api\ResultController@putresult');
   
   
   	
});

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('products', function () {
    return response(['Product 1', 'Product 2', 'Product 3'],200);
});*/

<?php
use Illuminate\Support\Facades\Input;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*Route::get('/', function () {
    return view('welcome');
});*/

/*Route::get('sessionchk', function () {
if (Auth::check() == true && Auth::user()->group == 'Admin') {
return Auth::user()->group;
}else{
return 'other';
}
   // return $data;
});*/

//Route::group(['middleware' => ['auth']], function () {

/*Route::group(['prefix' => 'activity', 'namespace' => 'jeremykenedy\LaravelLogger\App\Http\Controllers', 'middleware' => ['web', 'auth', 'activity']], function () {

    // Dashboards
    Route::get('/', 'LaravelLoggerController@showAccessLog')->name('activity');
    Route::get('/cleared', ['uses' => 'LaravelLoggerController@showClearedActivityLog'])->name('cleared');

    // Drill Downs
    Route::get('/log/{id}', 'LaravelLoggerController@showAccessLogEntry');
    Route::get('/cleared/log/{id}', 'LaravelLoggerController@showClearedAccessLogEntry');

    // Forms
    Route::delete('/clear-activity', ['uses' => 'LaravelLoggerController@clearActivityLog'])->name('clear-activity');
    Route::delete('/destroy-activity', ['uses' => 'LaravelLoggerController@destroyActivityLog'])->name('destroy-activity');
    Route::post('/restore-log', ['uses' => 'LaravelLoggerController@restoreClearedActivityLog'])->name('restore-activity');
});*/
Route::group(['middleware' => ['web','activity']], function(){ 

Route::get('/login/{user_id}/{d_id}','UsersController@dologin');
Route::get('/verification_code','UsersController@codeverify');
Route::post('/users/code_check','UsersController@code_check');
//});
Route::get('/branches','instituteController@branches');
Route::post('/branch','instituteController@createbranch');
Route::get('/attendance/today_delete','attendanceController@today_delete');
Route::get('/','HomeController@index')->name("login");
Route::get('/dashboard/','DashboardController@index');
Route::post('/users/login','UsersController@postSignin');
Route::get('/verify_code','UsersController@verify_code');
Route::post('/verified','UsersController@verified');
Route::get('/users/logout','UsersController@getLogout');
Route::get('/users','UsersController@show');
Route::post('/usercreate','UsersController@create');
Route::get('/useredit/{id}','UsersController@edit');
Route::post('/userupdate','UsersController@update');
Route::get('/userdelete/{id}','UsersController@delete');
});
//Route::get('/users/regi','UsersController@postRegi');

//Class routes
//Route::get('/users/regi','UsersController@postRegi');
Route::group(['middleware' => ['auth','activity']], function(){ 
/**
*Class Routes
**/
Route::get('/class/create','classController@index')->middleware('checkPermission:class_add');
Route::post('/class/create','classController@create')->middleware('checkPermission:class_add');
Route::post('/ajaxcreate/create','classController@ajaxcreate')->middleware('checkPermission:class_add');
Route::get('/class/list','classController@show')->middleware('checkPermission:class_view');
Route::get('/class/edit/{id}','classController@edit')->middleware('checkPermission:class_update');
Route::post('/class/update','classController@update')->middleware('checkPermission:class_update');
Route::get('/class/delete/{id}','classController@delete')/*->middleware('checkPermission:class_delete')*/;
});
Route::group(['middleware' => ['web','activity']], function(){ 

Route::get('/class/getsubjects/{class}','classController@getSubjects');
Route::get('/class/diary/{class_id}','classController@diary');
Route::get('/class/section/{class_id}','classController@getForsectionjoin');
Route::post('/class/diary/save','classController@diary_create');
});
// section routes
Route::group(['middleware' => ['auth','activity']], function(){ 
Route::get('/section/create','sectionController@index')->middleware('checkPermission:section_add');
Route::post('/section/create','sectionController@create')->middleware('checkPermission:section_add');
Route::get('/section/list','sectionController@show')->middleware('checkPermission:section_view');
Route::get('/get/section/{class_code}','sectionController@get_section')->middleware('checkPermission:section_view');
Route::get('/section/edit/{id}','sectionController@edit')->middleware('checkPermission:section_update');
Route::post('/section/update','sectionController@update')->middleware('checkPermission:section_update');
Route::get('/section/delete/{id}','sectionController@delete')->middleware('checkPermission:section_delete');
Route::get('/section/getList/{class}','sectionController@getsections')->middleware('checkPermission:class_add');
Route::get('/section/view-timetable/{id}','sectionController@view_timetable')->middleware('checkPermission:section_time_table');
Route::get('/section/getList/{class}/{session}','sectionController@getsections');
Route::get('/section/getList/{class}','sectionController@getsectionsc');
Route::get('/section/view-timetable/{id}','sectionController@view_timetable');


// level routes
Route::get('/level/create','levelController@index');
Route::post('/level/create','levelController@create');
Route::get('/level/list','levelController@show');
Route::get('/level/edit/{id}','levelController@edit');
Route::post('/level/update','levelController@update');
Route::get('/level/delete/{id}','levelController@delete');

//Subject routes
/*Route::get('/subject/create','subjectController@index');
Route::post('/subject/create','subjectController@create');
Route::get('/subject/list','subjectController@show');
Route::get('/subject/edit/{id}','subjectController@edit');
Route::post('/subject/update','subjectController@update');
Route::get('/subject/delete/{id}','subjectController@delete');*/

Route::get('/subject/create','subjectController@index')->middleware('checkPermission:subject_add');
Route::post('/subject/create','subjectController@create')->middleware('checkPermission:subject_add');
Route::get('/subject/list','subjectController@show')->middleware('checkPermission:subject_view');
Route::get('/subject/edit/{id}','subjectController@edit')->middleware('checkPermission:subject_update');
Route::post('/subject/update','subjectController@update')->middleware('checkPermission:subject_update');
Route::get('/subject/delete/{id}','subjectController@delete')->middleware('checkPermission:subject_delete');

/**
* Question routes
**/
Route::get('/question/create','QuestionController@create')->middleware('checkPermission:paper_add');
Route::post('/question/create','QuestionController@store')->middleware('checkPermission:paper_add');
Route::get('/paper/generate','QuestionController@generate')->middleware('checkPermission:paper_add');
Route::post('/paper/generate','QuestionController@post_generate')->middleware('checkPermission:paper_add');
Route::get('/question/list','QuestionController@list')->middleware('checkPermission:paper_view');
Route::post('/question/list','QuestionController@getlist')->middleware('checkPermission:paper_view');

Route::get('/question/edit/{id}','QuestionController@edit')->middleware('checkPermission:paper_update');
Route::post('/question/update','QuestionController@update')->middleware('checkPermission:paper_update');
Route::get('/question/delete/{id}','QuestionController@delete')->middleware('checkPermission:paper_delete');
Route::get('/chapter/getList/{class}','QuestionController@chapters')->middleware('checkPermission:paper_view');
});
Route::group(['middleware' => ['web','activity']], function(){ 

Route::get('/subject/getmarks/{subject}/{cls}','subjectController@getmarks');
Route::get('/subject/getList/{cls}','subjectController@getsubjects');

});
//Student routes
Route::group(['middleware' => ['auth','activity']], function(){ 
Route::get('/student/getRegi/{class}/{session}/{section}','studentController@getRegi');
Route::get('/student/create','studentController@index')->middleware('checkPermission:student_add');
Route::post('/student/create','studentController@create')->middleware('checkPermission:student_add');
Route::get('/student/list','studentController@show')->middleware('checkPermission:student_view');
Route::post('/student/list','studentController@getList');
Route::get('/student/view/{id}','studentController@view')->middleware('checkPermission:student_info');
Route::get('/student/access/{id}','studentController@access')->middleware('checkPermission:student_student_portal_access');
Route::get('/student/edit/{id}','studentController@edit')->middleware('checkPermission:student_update');
Route::post('/student/update','studentController@update')->middleware('checkPermission:student_update');
Route::get('/student/delete/{id}','studentController@delete')->middleware('checkPermission:student_delete');
Route::get('/student/create-file','studentController@index_file')->middleware('checkPermission:student_student_bulk_add');
Route::post('/student/create-file','studentController@create_file')->middleware('checkPermission:student_student_bulk_add');
Route::get('/student/csvexample','studentController@csvexample')->middleware('checkPermission:student_student_bulk_add');
Route::get('/family/list','studentController@family_list');
Route::get('/family/edit/{f_id}','studentController@family_edit');
Route::post('/family/update','studentController@family_update');
Route::get('/family/students/{f_id}','studentController@family_student_list');
Route::post('/family_discount/{f_id}','studentController@add_family_discount');
Route::post('/student/add/{f_id}','studentController@add_family_student');
Route::post('/students/shift/{f_id}','studentController@shift_student_family');
});
Route::group(['middleware' => ['web','activity']], function(){ 

Route::get('/student/getList/{class}/{section}/{shift}/{session}','studentController@getForMarks');
Route::get('/get/refral/{refral}','studentController@getrefral');
Route::get('/get/family_id/list/{refral}','studentController@f_id_list');
Route::get('/student/getsList/{class}/{section}/{shift}/{session}','studentController@getForMarksjoin');
Route::post('/student/search','studentController@search');
Route::post('/family/search','studentController@family');
Route::post('/family/student/search','studentController@familystudent');
Route::post('/get/family_id','studentController@get_family_id');
Route::post('/get/family/data','studentController@get_family_data');
Route::post('/sms/send','studentController@send_sms');

Route::get('/fee/getdiscountjson/{student_registration}','studentController@getdiscount');

// Teacher routes
Route::get('/teacher/getRegi/{class}/{session}/{section}','teacherController@getRegi');
});
Route::group(['middleware' => ['auth','activity']], function(){ 
	
	Route::get('/teacher/create','teacherController@index')->middleware('checkPermission:teacher_add');
	Route::post('/teacher/create','teacherController@create')->middleware('checkPermission:teacher_add');
	Route::post('/teacher/ajaxcreate','teacherController@ajaxcreate')->middleware('checkPermission:teacher_add');
});
Route::group(['middleware' => ['web','activity']], function(){ 

Route::get('/teacher/list','teacherController@show')->middleware('checkPermission:teacher_view');
Route::post('/teacher/list','teacherController@getList')->middleware('checkPermission:teacher_view');
Route::get('/get/teacher/{teacher_id}','teacherController@getteacherinfo')->middleware('checkPermission:teacher_add');

Route::get('/teacher/view/{id}','teacherController@view')->middleware('checkPermission:teacher_view');

});
Route::group(['middleware' => 'auth'], function(){ 
Route::get('/teacher/edit/{id}','teacherController@edit')->middleware('checkPermission:teacher_update');
Route::post('/teacher/update','teacherController@update')->middleware('checkPermission:teacher_update');
Route::get('/teacher/delete/{id}','teacherController@delete')->middleware('checkPermission:teacher_delete');
});
Route::group(['middleware' => ['web','activity']], function(){ 

Route::get('/teacher/getList/{class}/{section}/{shift}/{session}','teacherController@getForMarks');
});
Route::group(['middleware' => ['auth','activity']], function(){ 

Route::get('/teacher/create-file','teacherController@index_file')->middleware('checkPermission:teacher_bulk_add');
Route::post('/teacher/create-file','teacherController@create_file')->middleware('checkPermission:teacher_bulk_add');
Route::get('/teacher/access/{id}','teacherController@access')->middleware('checkPermission:teacher_portal_access');

Route::get('/teacher/create-timetable','teacherController@index_timetable')->middleware('checkPermission:teacher_timetable_add');
Route::post('/teacher/create_timetable','teacherController@create_timetable')->middleware('checkPermission:teacher_timetable_add');
Route::get('/timetable/edit/{timetable_id}','teacherController@edit_timetable');
Route::post('/timetable/update','teacherController@update_timetable');
Route::get('/timetable/delete/{timetable_id}','teacherController@delete_timetable');

Route::get('/teacher/diary/{teacher_id}','teacherController@diary_add');
Route::post('/teacher/diary','teacherController@diary_create');

Route::get('/teacher/getsubjects/{class_id}/{teacher_id}','teacherController@teachersubject');
Route::get('/teacher/getsections/{class_id}/{teacher_id}','teacherController@teachersection');
Route::get('/teacher/diary/show/{teacher_id}','teacherController@diaryshow');
Route::get('/diary/delete/{diary_id}','teacherController@delete_diary');


});
Route::group(['middleware' => ['web','activity']], function(){ 

Route::get('/teacher/view-timetable/{id}','teacherController@view_timetable');
Route::get('/section/getList/{class}/{session}','sectionController@getsections');
Route::get('/section/getList/{class}','sectionController@getsectionsc');

//student attendance
Route::get('/attendance/create','attendanceController@index')->middleware('checkPermission:add_student_attendance');
Route::post('/attendance/create','attendanceController@create')->middleware('checkPermission:add_student_attendance');
Route::get('/attendance/create-file','attendanceController@index_file');
Route::post('/attendance/create-file','attendanceController@create_file');
Route::get('/attendance/list','attendanceController@show')->middleware('checkPermission:teacher_timetable_view');
Route::post('/attendance/list','attendanceController@getlist')->middleware('checkPermission:view_student_attendance');
Route::get('/attendance/edit/{id}','attendanceController@edit')->middleware('checkPermission:teacher_timetable_view');
Route::post('/attendance/update','attendanceController@update')->middleware('checkPermission:teacher_timetable_view');
Route::get('/attendance/printlist/{class}/{section}/{shift}/{session}/{date}','attendanceController@printlist');
});
Route::group(['middleware' => ['auth','activity']], function(){ 
Route::get('/attendance/report','attendanceController@report');
Route::post('/attendance/report','attendanceController@getReport');
Route::get('/attendance/student_report','attendanceController@stdatdreportindex');
Route::get('/attendance/print_student_report/{b_form}','attendanceController@stdatdreport');
Route::get('/attendance_detail','attendanceController@attendance_detail');
/*Route::get('/attendance/report', 'attendanceController@report');
Route::post('/attendance/report', 'attendanceController@getReport');
*/
Route::get('/attendance/monthly-report', 'attendanceController@monthlyReport')->middleware('checkPermission:view_student_monthly_reports');


/**
* papers route
**/

Route::get('/paper/create','paperController@index');
Route::post('/paper/create','paperController@create');
Route::get('/paper/list','paperController@show');
Route::get('/paper/edit/{id}','paperController@edit');
Route::post('/paper/update','paperController@update');
Route::get('/paper/delete/{id}','paperController@delete');
Route::get('/paper/getList/{class}','paperController@getexams');
//Exam

Route::get('/exam/create','examController@index')->middleware('checkPermission:exam_add');
Route::post('/exam/create','examController@create')->middleware('checkPermission:exam_add');
Route::get('/exam/list','examController@show')->middleware('checkPermission:exam_view');
Route::get('/exam/edit/{id}','examController@edit')->middleware('checkPermission:exam_update');
Route::post('/exam/update','examController@update')->middleware('checkPermission:exam_update');
Route::get('/exam/delete/{id}','examController@delete')->middleware('checkPermission:exam_delete');
Route::get('/exam/getList/{class}','examController@getexams');




});


// Acadamic Year
Route::get('/academicYear','AcadamicYearController@index');
Route::get('/academicYear/create','AcadamicYearController@add')->name('year.add');
Route::post('/academicYear/create','AcadamicYearController@create')->name('year.create');
Route::get('/academicYear/list','AcadamicYearController@show');
Route::get('/academicYear/edit/{id}','AcadamicYearController@edit');
Route::post('/academicYear/update','AcadamicYearController@update')->name('year.update');
Route::get('/academicYear/delete/{id}','AcadamicYearController@delete');
Route::get('/academicYear/status/{id}','AcadamicYearController@status');
//GPA Routes
Route::get('/gpa','gpaController@index')->middleware('checkPermission:gpa_rule_add');
Route::post('/gpa/create','gpaController@create')->middleware('checkPermission:gpa_rule_add');
Route::get('/gpa/list','gpaController@show')->middleware('checkPermission:gpa_rule_view');
Route::get('/gpa/edit/{id}','gpaController@edit')->middleware('checkPermission:gpa_rule_update');
Route::post('/gpa/update','gpaController@update')->middleware('checkPermission:gpa_rule_update');
Route::get('/gpa/delete/{id}','gpaController@delete')->middleware('checkPermission:gpa_rule_delete');

//sms Routes
/*
Route::get('/sms','smsController@index');
Route::post('/sms/create','smsController@create');
Route::get('/sms/list','smsController@show');
Route::get('/sms/edit/{id}','smsController@edit');
Route::post('/sms/update','smsController@update');
Route::get('/sms/delete/{id}','smsController@delete');

Route::get('/sms','smsController@getsmssend');
Route::post('/sms/send','smsController@postsmssend');*/
Route::group(['middleware' => 'auth'], function(){ 


Route::get('/cron/run', function(){
    \Log::info('Executed at'.date('Y-m-d H:i:s'));
   \Artisan::call("Invoice:genrate");
     //return \Artisan::Output();
  	return redirect('/dashboard')->with('success',"Invoice Created Successfully");
   //return json_encode(auth()->user()->adminDashboardCount());
})->name('cron.run');

Route::get('/cron/invoices/months', function(){
	$months = Input::get('month');
	$f_id   = Input::get('family_id');
    \Log::info('Executed at'.date('Y-m-d H:i:s'));
     \Artisan::call("Invoice:months", ['arg_name' => ['month'=>$months, 'family_id'=>$f_id]]);
   //return \Artisan::Output();
    return redirect('/dashboard')->with('success',"Invoice Created Successfully");
   //return json_encode(auth()->user()->adminDashboardCount());
})->name('cron.run1');


Route::get('/smslog','smsController@getsmsLog');
Route::post('/smslog','smsController@postsmsLog');
Route::get('/smslog/delete/{id}','smsController@deleteLog');
});
//Mark routes
Route::get('/mark/create','markController@index')->middleware('checkPermission:add_marks');
Route::post('/mark/create','markController@create')->middleware('checkPermission:add_marks');

Route::post('/new/mark/create','markController@newcreate');
Route::get('/marks/section/{class}','markController@getForMarksjoin');
Route::get('/create/marks','markController@createmarks');

Route::get('/mark/m_create','markController@m_index')->middleware('checkPermission:add_marks');
Route::post('/mark/m_create','markController@m_create')->middleware('checkPermission:add_marks');

Route::get('/mark/list','markController@show')->middleware('checkPermission:view_marks');
Route::post('/mark/list','markController@getlist')->middleware('checkPermission:view_marks');

Route::get('/mark/m_list','markController@m_show')->middleware('checkPermission:view_marks');
Route::post('/mark/m_list','markController@m_getlist')->middleware('checkPermission:view_marks');

Route::get('/mark/edit/{id}','markController@edit')->middleware('checkPermission:update_marks');
Route::get('/mark/m_edit/{id}','markController@m_edit')->middleware('checkPermission:update_marks');
Route::post('/mark/update','markController@update')->middleware('checkPermission:update_marks');
Route::post('/mark/m_update','markController@m_update')->middleware('checkPermission:update_marks');
Route::get('/mark/delete/{id}','markController@delete')->middleware('checkPermission:delete_marks');

Route::get('/template/creates','markController@template');
Route::get('/template/message/edit/{message_id}','markController@edittemplate');

//Markssheet
Route::group(['middleware' => 'auth'], function(){ 
Route::get('/result/generate','gradesheetController@getgenerate');
Route::post('/result/generate','gradesheetController@postgenerate');
Route::post('/result/m_generate','gradesheetController@mpostgenerate');


Route::get('/result/search','gradesheetController@search');
Route::post('/result/search','gradesheetController@postsearch');

Route::get('/results','gradesheetController@searchpub');
Route::post('/results','gradesheetController@postsearchpub');


Route::get('/gradesheet','gradesheetController@index');
Route::post('/gradesheet','gradesheetController@stdlist');
Route::get('/gradesheet/print/{regiNo}/{exam}/{class}','gradesheetController@printsheet');
Route::get('/gradesheet/m_print/{regiNo}/{exam}/{class}','gradesheetController@m_printsheet');
});
//tabulation sheet
Route::group(['middleware' => 'auth'], function(){ 
Route::get('/tabulation','tabulationController@index');
Route::post('/tabulation','tabulationController@getsheet');


//settings
Route::get('/settings','settingsController@index');
Route::post('/settings','settingsController@save');

Route::get('/institute','instituteController@index');
Route::post('/institute','instituteController@save');

Route::get('/ictcore','ictcoreController@index');
Route::post('/ictcore','ictcoreController@create');
Route::post('/notification_type','ictcoreController@noti_create');
Route::get('/notification_type','ictcoreController@noti_index');

Route::get('/ictcore/attendance','ictcoreController@attendance_index');
Route::post('/ictcore/attendance','ictcoreController@post_attendance');


Route::get('/ictcore/fees','ictcoreController@fee_message_index');
Route::post('/ictcore/fees','ictcoreController@post_fees');






//promotion
Route::get('/promotion','promotionController@index');
Route::post('/promotion','promotionController@store');

Route::get('/template/create','templateController@index');
Route::post('/template/create','templateController@create');
Route::get('/template/list','templateController@show');
Route::get('/message/edit/{id}','templateController@edit');
Route::post('/message/update','templateController@update');
Route::get('/message/delete/{id}','templateController@delete');


Route::get('/message','messageController@index');
Route::post('/message','messageController@create');



//Accounting
//if(session()->all()['userRole']=='Admin'){

/*Route::get('/accounting/sectors', function () {
if (Auth::check() == true && Auth::user()->group == 'Admin') {
return Auth::user()->group;
}
   // return $data;
});*/

});
Route::get('/settings','settingsController@index');
Route::post('/settings','settingsController@save');
Route::get('/permission','permissionController@index');
Route::post('/permission/create','permissionController@store');
Route::get('/schedule','settingsController@get_schedule');
Route::post('/schedule','settingsController@post_schedule');
// Accounting

Route::group(['middleware' => 'auth'], function(){ 

Route::get('/accounting','accountingController@index')->middleware('checkPermission:accounting');
Route::post('/accounting','accountingController@store')->middleware('checkPermission:accounting');

Route::get('/accounting/sectors','accountingController@sectors')->middleware('checkPermission:accounting');
Route::post('/accounting/sectorcreate','accountingController@sectorCreate')->middleware('checkPermission:accounting');
Route::get('/accounting/sectorlist','accountingController@sectorList')->middleware('checkPermission:accounting');
Route::get('/accounting/sectoredit/{id}','accountingController@sectorEdit')->middleware('checkPermission:accounting');
Route::post('/accounting/sectorupdate','accountingController@sectorUpdate')->middleware('checkPermission:accounting');
Route::get('/accounting/sectordelete/{id}','accountingController@sectorDelete')->middleware('checkPermission:accounting');

Route::get('/accounting/income','accountingController@income')->middleware('checkPermission:accounting');
Route::post('/accounting/incomecreate','accountingController@incomeCreate')->middleware('checkPermission:accounting');
Route::get('/accounting/incomelist','accountingController@incomeList')->middleware('checkPermission:accounting');
Route::post('/accounting/incomelist','accountingController@incomeListPost')->middleware('checkPermission:accounting');
Route::get('/accounting/incomeedit/{id}','accountingController@incomeEdit')->middleware('checkPermission:accounting');
Route::post('/accounting/incomeupdate','accountingController@incomeUpdate')->middleware('checkPermission:accounting');
Route::get('/accounting/incomedelete/{id}','accountingController@incomeDelete')->middleware('checkPermission:accounting');

Route::get('/accounting/expence','accountingController@expence')->middleware('checkPermission:accounting');
Route::post('/accounting/expencecreate','accountingController@expenceCreate')->middleware('checkPermission:accounting');
Route::get('/accounting/expencelist','accountingController@expenceList')->middleware('checkPermission:accounting');
Route::post('/accounting/expencelist','accountingController@expenceListPost')->middleware('checkPermission:accounting');
Route::get('/accounting/expenceedit/{id}','accountingController@expenceEdit')->middleware('checkPermission:accounting');
Route::post('/accounting/expenceupdate','accountingController@expenceUpdate')->middleware('checkPermission:accounting');
Route::get('/accounting/expencedelete/{id}','accountingController@expenceDelete')->middleware('checkPermission:accounting');

Route::get('/accounting/report','accountingController@getReport')->middleware('checkPermission:accounting');
Route::get('/accounting/reportsum','accountingController@getReportsum')->middleware('checkPermission:accounting');

Route::get('/accounting/reportprint/{rtype}/{fdate}/{tdate}','accountingController@printReport')->middleware('checkPermission:accounting');
Route::get('/accounting/reportprintsum/{fdate}/{tdate}','accountingController@printReportsum')->middleware('checkPermission:accounting');

//}
});
//Fees Related routes
Route::group(['middleware' => 'auth'], function(){ 
Route::get('/fees/setup','feesController@getsetup')->middleware('checkPermission:add_fess');;
Route::post('/fees/setup','feesController@postsetup')->middleware('checkPermission:add_fess');;
Route::get('/fees/list','feesController@getList')->middleware('checkPermission:view_fess');;
Route::post('/fees/list','feesController@postList')->middleware('checkPermission:view_fess');;

Route::get('/fee/edit/{id}','feesController@getEdit')->middleware('checkPermission:update_fess');;
Route::post('/fee/edit','feesController@postEdit')->middleware('checkPermission:update_fess');;
Route::get('/fee/delete/{id}','feesController@getDelete')->middleware('checkPermission:delete_fess');;

Route::get('/fee/collection','feesController@getCollection')->middleware('checkPermission:add_fess');;
//Route::get('/fee/vouchar','feesController@getvouchar');
//Route::post('/fee/vouchar','feesController@gpostvouchar');
Route::post('/fee/collection','feesController@postCollection')->middleware('checkPermission:add_fess');;
Route::get('/fee/getListjson/{class}/{type}','feesController@getListjson');
Route::get('/fee/getFeeInfo/{id}','feesController@getFeeInfo');
Route::get('/fee/getDue/{class}/{stdId}','feesController@getDue');

Route::get('/fees/view','feesController@stdfeeview')->middleware('checkPermission:view_fess');;
Route::post('/fees/view','feesController@stdfeeviewpost')->middleware('checkPermission:view_fess');;


Route::get('/fees/invoices','feesController@stdfeeinvoices')->middleware('checkPermission:add_fess');;
Route::post('/fees/invoices','feesController@stdfeeinvoicespost')->middleware('checkPermission:add_fess');;


Route::get('/fees/delete/{billNo}','feesController@stdfeesdelete');

Route::get('/fees/report','feesController@report');
Route::get('/fees/report/std/{regiNo}','feesController@reportstd');
Route::get('/fees/report/{sDate}/{eDate}','feesController@reportprint');


Route::get('/fee/vouchar','feesController@vouchar_index');
Route::get('/fee/vouchar/history','feesController@vouchar_history');
Route::get('/fees/paid/{billno}','feesController@vouchar_paid');
//Route::get('/fee/vouchar','feesController@getvouchar');
Route::post('/fee/voucher','feesController@postvouchar');
Route::get('/fee/get_vouchar','feesController@createvoucher');
Route::get('/fee/get_vouchar/{id}','feesController@getvoucher');
Route::get('/f_vouchar/model/{f_id}','feesController@getfvoucher');

Route::get('/fees/details/{billNo}','feesController@billDetails');
Route::get('/fees/history/{billNo}','feesController@invoicehist');
Route::get('/fees/invoice/details/{billNo}','feesController@invoiceDetails');
Route::post('/fees/invoice/collect/{billNo}','feesController@invoiceCollect');
Route::get('/fees/classreport','feesController@classreportindex');
//Route::post('/fees/classreport','feesController@classreport');
Route::post('/fees/classreport','feesController@classview');
Route::get('/fee/detail','feesController@detail');


//Route::post('/fees/classview','feesController@classview');
Route::post('/fees/unpaid_notification','feesController@ictcorefees');
Route::post('/fee/unpaid_notification','feesController@sendnotification');
Route::get('/fee_detail','feesController@fee_detail');

Route::get('/family/vouchars/{family_id}','feesController@get_family_voucher');
Route::get('/family/vouchar_history/{family_id}','feesController@family_voucherhistory');
Route::post('/family/paid/{family_id}','feesController@family_vouchar_paid');
Route::get('/voucher/detail/{id}','feesController@family_vouchar_detail');
Route::get('/family/vouchar_print/{family_id}/{billno}','feesController@family_voucherprint');


});
//Admisstion routes
Route::group(['middleware' => 'auth'], function(){ 
Route::get('/regonline','admissionController@regonline');
Route::post('/regonline','admissionController@Postregonline');
Route::get('/applicants','admissionController@applicants');
Route::post('/applicants','admissionController@postapplicants');
Route::get('/applicants/view/{id}','admissionController@applicantview');
Route::get('/applicants/payment','admissionController@payment');
Route::get('/applicants/delete/{id}','admissionController@delete');
Route::get('/admitcard','admissionController@admitcard');
Route::post('/printadmitcard','admissionController@printAdmitCard');
});

//library routes
Route::group(['middleware' => 'auth'], function(){ 
Route::get('/library/addbook','libraryController@getAddbook');
Route::post('/library/addbook','libraryController@postAddbook');
Route::get('/library/view','libraryController@getviewbook');

Route::get('/library/view-show','libraryController@postviewbook');

Route::get('/library/edit/{id}','libraryController@getBook');
Route::post('/library/update','libraryController@postUpdateBook');
Route::get('/library/delete/{id}','libraryController@deleteBook');
Route::get('/library/issuebook','libraryController@getissueBook');

//check availabe book
Route::get('/library/issuebook-availabe/{code}/{quantity}','libraryController@checkBookAvailability');
Route::post('/library/issuebook','libraryController@postissueBook');

Route::get('/library/issuebookview','libraryController@getissueBookview');
Route::post('/library/issuebookview','libraryController@postissueBookview');
Route::get('/library/issuebookupdate/{id}','libraryController@getissueBookupdate');
Route::post('/library/issuebookupdate','libraryController@postissueBookupdate');
Route::get('/library/issuebookdelete/{id}','libraryController@deleteissueBook');

Route::get('/library/search','libraryController@getsearch');
Route::get('/library/search2','libraryController@getsearch');
Route::post('/library/search','libraryController@postsearch');
Route::post('/library/search2','libraryController@postsearch2');

Route::get('/library/reports','libraryController@getReports');
Route::get('/library/reports/fine','libraryController@getReportsFine');

Route::get('/library/reportprint/{do}','libraryController@Reportprint');
Route::get('/library/reports/fine/{month}','libraryController@ReportsFineprint');
});
//Hostel Routes
Route::group(['middleware' => 'auth'], function(){ 
Route::get('/dormitory','dormitoryController@index');
Route::post('/dormitory/create','dormitoryController@create');
Route::get('/dormitory/edit/{id}','dormitoryController@edit');
Route::post('/dormitory/update','dormitoryController@update');
Route::get('/dormitory/delete/{id}','dormitoryController@delete');

Route::get('/dormitory/getstudents/{dormid}','dormitoryController@getstudents');

Route::get('/dormitory/assignstd','dormitoryController@stdindex');
Route::post('/dormitory/assignstd/create','dormitoryController@stdcreate');
Route::get('/dormitory/assignstd/list','dormitoryController@stdshow');
Route::post('/dormitory/assignstd/list','dormitoryController@poststdShow');
Route::get('/dormitory/assignstd/edit/{id}','dormitoryController@stdedit');
Route::post('/dormitory/assignstd/update','dormitoryController@stdupdate');
Route::get('/dormitory/assignstd/delete/{id}','dormitoryController@stddelete');

Route::get('/dormitory/fee','dormitoryController@feeindex');
Route::post('/dormitory/fee','dormitoryController@feeadd');
Route::get('/dormitory/fee/info/{regiNo}','dormitoryController@feeinfo');

Route::get('/dormitory/report/std','dormitoryController@reportstd');
Route::get('/dormitory/report/std/{dormId}','dormitoryController@reportstdprint');
Route::get('/dormitory/report/fee','dormitoryController@reportfee');
Route::get('/dormitory/report/fee/{dormId}/{month}','dormitoryController@reportfeeprint');
});
//barcode generate
Route::group(['middleware' => 'auth'], function(){ 
Route::get('/barcode','barcodeController@index');
Route::post('/barcode','barcodeController@generate');

//holyday Routes
Route::get('/holidays', 'attendanceController@holidayIndex');
Route::post('/holidays/create', 'attendanceController@holidayCreate');
Route::get('/holidays/delete/{id}', 'attendanceController@holidayDelete');

//class off Routes
Route::get('/class-off', 'attendanceController@classOffIndex');
Route::post('/class-off/store', 'attendanceController@classOffStore');
Route::get('/class-off/delete/{id}', 'attendanceController@classOffDelete');




/**
     * Website contents routes
     */

    Route::get('/site/dashboard', 'SiteController@dashboard')
        ->name('site.dashboard');
        
    Route::resource('slider','SliderController');

    Route::get('/site/about-content', 'SiteController@aboutContent')
        ->name('site.about_content');
    Route::post('/site/about-content', 'SiteController@aboutContent')
        ->name('site.about_content');
    Route::get('site/about-content/images','SiteController@aboutContentImage')
        ->name('site.about_content_image');
    Route::post('site/about-content/images','SiteController@aboutContentImage')
        ->name('site.about_content_image');
    Route::post('site/about-content/images/{id}','SiteController@aboutContentImageDelete')
        ->name('site.about_content_image_delete');
    Route::get('site/service','SiteController@serviceContent')
        ->name('site.service');
    Route::post('site/service','SiteController@serviceContent')
        ->name('site.service');
    Route::get('site/statistic','SiteController@statisticContent')
        ->name('site.statistic');
    Route::post('site/statistic','SiteController@statisticContent')
        ->name('site.statistic');

    Route::get('site/testimonial','SiteController@testimonialIndex')
        ->name('site.testimonial');
    Route::post('site/testimonial','SiteController@testimonialIndex')
        ->name('site.testimonial');
    Route::get('site/testimonial/create','SiteController@testimonialCreate')
        ->name('site.testimonial_create');
    Route::post('site/testimonial/create','SiteController@testimonialCreate')
    ->name('site.testimonial_create');

    Route::get('site/subscribe','SiteController@subscribe')
        ->name('site.subscribe');

   // Route::resource('class_profile','ClassProfileController');
   // Route::resource('teacher_profile','TeacherProfileController');
    //Route::resource('event','EventController');
    /*Route::get('site/gallery','SiteController@gallery')
        ->name('site.gallery');
    Route::get('site/gallery/add-image','SiteController@galleryAdd')
        ->name('site.gallery_image');
    Route::post('site/gallery/add-image','SiteController@galleryAdd')
        ->name('site.gallery_image');
    Route::post('site/gallery/delete-images/{id}','SiteController@galleryDelete')
        ->name('site.gallery_image_delete');
    Route::get('site/contact-us','SiteController@contactUs')
        ->name('site.contact_us');
    Route::post('site/contact-us','SiteController@contactUs')
        ->name('site.contact_us');
    Route::get('site/fqa','SiteController@faq')
        ->name('site.faq');
    Route::post('site/fqa','SiteController@faq')
        ->name('site.faq');
    Route::post('site/faq/{id}','SiteController@faqDelete')
        ->name('site.faq_delete');
    Route::get('site/timeline','SiteController@timeline')
        ->name('site.timeline');
    Route::post('site/timeline','SiteController@timeline')
        ->name('site.timeline');
    Route::post('site/timeline/{id}','SiteController@timelineDelete')
        ->name('site.timeline_delete');
    Route::get('site/settings','SiteController@settings')
        ->name('site.settings');
    Route::post('site/settings','SiteController@settings')
        ->name('site.settings');
    Route::get('site/analytics','SiteController@analytics')
        ->name('site.analytics');
    Route::post('site/analytics','SiteController@analytics')
        ->name('site.analytics');*/



});


Route::group(['middleware' => 'super_admin'], function(){
Route::get('/gradsystem','gradesheetController@gradsystem');
});
Route::get('/cronjob/feenotification','cronjobController@feenotification');

<?php

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

Route::get('sessionchk', function () {
if (Auth::check() == true && Auth::user()->group == 'Admin') {
return Auth::user()->group;
}else{
return 'other';
}
   // return $data;
});
Route::get('/','HomeController@index')->name("login");
Route::get('/dashboard/','DashboardController@index');
Route::post('/users/login','UsersController@postSignin');
Route::get('/users/logout','UsersController@getLogout');
Route::get('/users','UsersController@show');
Route::post('/usercreate','UsersController@create');
Route::get('/useredit/{id}','UsersController@edit');
Route::post('/userupdate','UsersController@update');
Route::get('/userdelete/{id}','UsersController@delete');

//Route::get('/users/regi','UsersController@postRegi');

//Class routes
Route::group(['middleware' => 'admin'], function(){ 
Route::get('/class/create','classController@index');
Route::post('/class/create','classController@create');
Route::get('/class/list','classController@show');
Route::get('/class/edit/{id}','classController@edit');
Route::post('/class/update','classController@update');
Route::get('/class/delete/{id}','classController@delete');
});
Route::get('/class/getsubjects/{class}','classController@getSubjects');

// section routes
Route::group(['middleware' => 'admin'], function(){ 
/*Route::get('/section/create','sectionController@index');
Route::post('/section/create','sectionController@create');
Route::get('/section/list','sectionController@show');
Route::get('/section/edit/{id}','sectionController@edit');
Route::post('/section/update','sectionController@update');
Route::get('/section/delete/{id}','sectionController@delete');*/

// level routes
Route::get('/level/create','levelController@index');
Route::post('/level/create','levelController@create');
Route::get('/level/list','levelController@show');
Route::get('/level/edit/{id}','levelController@edit');
Route::post('/level/update','levelController@update');
Route::get('/level/delete/{id}','levelController@delete');

//Subject routes
Route::get('/subject/create','subjectController@index');
Route::post('/subject/create','subjectController@create');
Route::get('/subject/list','subjectController@show');
Route::get('/subject/edit/{id}','subjectController@edit');
Route::post('/subject/update','subjectController@update');
Route::get('/subject/delete/{id}','subjectController@delete');
});
Route::get('/subject/getmarks/{subject}/{cls}','subjectController@getmarks');


//Student routes
Route::group(['middleware' => 'admin'], function(){ 
Route::get('/student/getRegi/{class}/{session}/{section}','studentController@getRegi');
Route::get('/student/create','studentController@index');
Route::post('/student/create','studentController@create');
Route::get('/student/list','studentController@show');
Route::post('/student/list','studentController@getList');
Route::get('/student/view/{id}','studentController@view');

Route::get('/student/edit/{id}','studentController@edit');
Route::post('/student/update','studentController@update');
Route::get('/student/delete/{id}','studentController@delete');
Route::post('/student/create-file','studentController@create_file');
Route::get('/student/csvexample','studentController@csvexample');
});
Route::get('/student/getList/{class}/{section}/{shift}/{session}','studentController@getForMarks');

// Teacher routes
Route::get('/teacher/getRegi/{class}/{session}/{section}','teacherController@getRegi');
Route::group(['middleware' => 'admin'], function(){ 
Route::get('/teacher/create','teacherController@index');
Route::post('/teacher/create','teacherController@create');
});
Route::get('/teacher/list','teacherController@show');
Route::post('/teacher/list','teacherController@getList');
Route::get('/teacher/view/{id}','teacherController@view');
Route::group(['middleware' => 'admin'], function(){ 
Route::get('/teacher/edit/{id}','teacherController@edit');
Route::post('/teacher/update','teacherController@update');
Route::get('/teacher/delete/{id}','teacherController@delete');
});
Route::get('/teacher/getList/{class}/{section}/{shift}/{session}','teacherController@getForMarks');
Route::group(['middleware' => 'admin'], function(){ 
Route::get('/teacher/create-file','teacherController@index_file');
Route::post('/teacher/create-file','teacherController@create_file');

Route::get('/teacher/create-timetable','teacherController@index_timetable');
Route::post('/teacher/create_timetable','teacherController@create_timetable');
});
Route::get('/teacher/view-timetable/{id}','teacherController@view_timetable');


//student attendance
Route::get('/attendance/create','attendanceController@index');
Route::post('/attendance/create','attendanceController@create');
Route::get('/attendance/create-file','attendanceController@index_file');
Route::post('/attendance/create-file','attendanceController@create_file');
Route::get('/attendance/list','attendanceController@show');
Route::post('/attendance/list','attendanceController@getlist');
Route::get('/attendance/edit/{id}','attendanceController@edit');
Route::post('/attendance/update','attendanceController@update');
Route::get('/attendance/printlist/{class}/{section}/{shift}/{session}/{date}','attendanceController@printlist');
Route::group(['middleware' => 'admin'], function(){ 
Route::get('/attendance/report','attendanceController@report');
Route::post('/attendance/report','attendanceController@getReport');
});

//GPA Routes
Route::get('/gpa','gpaController@index');
Route::post('/gpa/create','gpaController@create');
Route::get('/gpa/list','gpaController@show');
Route::get('/gpa/edit/{id}','gpaController@edit');
Route::post('/gpa/update','gpaController@update');
Route::get('/gpa/delete/{id}','gpaController@delete');

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
Route::group(['middleware' => 'admin'], function(){ 
Route::get('/smslog','smsController@getsmsLog');
Route::post('/smslog','smsController@postsmsLog');
Route::get('/smslog/delete/{id}','smsController@deleteLog');
});
//Mark routes
Route::get('/mark/create','markController@index');
Route::post('/mark/create','markController@create');
Route::get('/mark/list','markController@show');
Route::post('/mark/list','markController@getlist');
Route::get('/mark/edit/{id}','markController@edit');
Route::post('/mark/update','markController@update');
Route::get('/mark/delete/{id}','markController@delete');

//Markssheet
Route::group(['middleware' => 'admin'], function(){ 
Route::get('/result/generate','gradesheetController@getgenerate');
Route::post('/result/generate','gradesheetController@postgenerate');


Route::get('/result/search','gradesheetController@search');
Route::post('/result/search','gradesheetController@postsearch');

Route::get('/results','gradesheetController@searchpub');
Route::post('/results','gradesheetController@postsearchpub');


Route::get('/gradesheet','gradesheetController@index');
Route::post('/gradesheet','gradesheetController@stdlist');
Route::get('/gradesheet/print/{regiNo}/{exam}/{class}','gradesheetController@printsheet');
});
//tabulation sheet
Route::group(['middleware' => 'admin'], function(){ 
Route::get('/tabulation','tabulationController@index');
Route::post('/tabulation','tabulationController@getsheet');


//settings
Route::get('/settings','settingsController@index');
Route::post('/settings','settingsController@save');

Route::get('/institute','instituteController@index');
Route::post('/institute','instituteController@save');

//promotion
Route::get('/promotion','promotionController@index');
Route::post('/promotion','promotionController@store');

Route::get('/template/create','templateController@index');
Route::post('/template/create','templateController@create');
Route::get('/template/list','templateController@show');
Route::get('/message/edit/{id}','templateController@edit');
Route::post('/message/update','templateController@update');
Route::get('/message/delete/{id}','templateController@delete');
//Accounting
//if(session()->all()['userRole']=='Admin'){

/*Route::get('/accounting/sectors', function () {
if (Auth::check() == true && Auth::user()->group == 'Admin') {
return Auth::user()->group;
}
   // return $data;
});*/

});

// Accounting

Route::group(['middleware' => 'admin'], function(){ 

Route::get('/accounting/sectors','accountingController@sectors');
Route::post('/accounting/sectorcreate','accountingController@sectorCreate');
Route::get('/accounting/sectorlist','accountingController@sectorList');
Route::get('/accounting/sectoredit/{id}','accountingController@sectorEdit');
Route::post('/accounting/sectorupdate','accountingController@sectorUpdate');
Route::get('/accounting/sectordelete/{id}','accountingController@sectorDelete');

Route::get('/accounting/income','accountingController@income');
Route::post('/accounting/incomecreate','accountingController@incomeCreate');
Route::get('/accounting/incomelist','accountingController@incomeList');
Route::post('/accounting/incomelist','accountingController@incomeListPost');
Route::get('/accounting/incomeedit/{id}','accountingController@incomeEdit');
Route::post('/accounting/incomeupdate','accountingController@incomeUpdate');
Route::get('/accounting/incomedelete/{id}','accountingController@incomeDelete');

Route::get('/accounting/expence','accountingController@expence');
Route::post('/accounting/expencecreate','accountingController@expenceCreate');
Route::get('/accounting/expencelist','accountingController@expenceList');
Route::post('/accounting/expencelist','accountingController@expenceListPost');
Route::get('/accounting/expenceedit/{id}','accountingController@expenceEdit');
Route::post('/accounting/expenceupdate','accountingController@expenceUpdate');
Route::get('/accounting/expencedelete/{id}','accountingController@expenceDelete');

Route::get('/accounting/report','accountingController@getReport');
Route::get('/accounting/reportsum','accountingController@getReportsum');

Route::get('/accounting/reportprint/{rtype}/{fdate}/{tdate}','accountingController@printReport');
Route::get('/accounting/reportprintsum/{fdate}/{tdate}','accountingController@printReportsum');

//}
});
//Fees Related routes
Route::group(['middleware' => 'admin'], function(){ 
Route::get('/fees/setup','feesController@getsetup');
Route::post('/fees/setup','feesController@postsetup');
Route::get('/fees/list','feesController@getList');
Route::post('/fees/list','feesController@postList');

Route::get('/fee/edit/{id}','feesController@getEdit');
Route::post('/fee/edit','feesController@postEdit');
Route::get('/fee/delete/{id}','feesController@getDelete');

Route::get('/fee/collection','feesController@getCollection');
Route::post('/fee/collection','feesController@postCollection');
Route::get('/fee/getListjson/{class}/{type}','feesController@getListjson');
Route::get('/fee/getFeeInfo/{id}','feesController@getFeeInfo');
Route::get('/fee/getDue/{class}/{stdId}','feesController@getDue');

Route::get('/fees/view','feesController@stdfeeview');
Route::post('/fees/view','feesController@stdfeeviewpost');
Route::get('/fees/delete/{billNo}','feesController@stdfeesdelete');

Route::get('/fees/report','feesController@report');
Route::get('/fees/report/std/{regiNo}','feesController@reportstd');
Route::get('/fees/report/{sDate}/{eDate}','feesController@reportprint');


Route::get('/fees/details/{billNo}','feesController@billDetails');
});
//Admisstion routes
Route::group(['middleware' => 'admin'], function(){ 
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
Route::group(['middleware' => 'admin'], function(){ 
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
Route::group(['middleware' => 'admin'], function(){ 
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
Route::group(['middleware' => 'admin'], function(){ 
Route::get('/barcode','barcodeController@index');
Route::post('/barcode','barcodeController@generate');
});

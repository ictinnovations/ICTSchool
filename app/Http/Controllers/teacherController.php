<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
//use Illuminate\Http\Request;
//use Illuminate\Foundation\Http\FormRequest;
use App\Student;
use App\Teacher;
use App\User;
use App\SectionModel;
use App\ClassModel;
use App\Subject;
use App\Timetable;
use App\Diary;
use DB;
use Hash;
use App\Ictcore_integration;
use App\Http\Controllers\ictcoreController;
use Carbon\Carbon;
class foobar1{

}
Class formfoo3{

}
class teacherController extends BaseController {

	public function __construct() {
		/*$this->beforeFilter('csrf', array('on'=>'post'));
		$this->beforeFilter('auth');
		$this->beforeFilter('userAccess',array('only'=> array('delete')));*/
		$this->middleware('auth');
		$this->middleware('auth',array('only'=> array('delete')));
	}
	/**
	* Display a listing of the resource.
	*
	* @return Response
	*/
	public function index()
	{
		return View('app.teacherCreate');
	}

	public  function getRegi($class,$session,$section)
	{
		$ses =trim($session);
		$stdcount = Student::select(DB::raw('count(*) as total'))->where('class','=',$class)->where('session','=',$ses)->first();

		$stdseccount = Student::select(DB::raw('count(*) as total'))->where('class','=',$class)->where('session','=',$ses)->where('section','=',$section)->first();
		$r = intval($stdcount->total)+1;
		if(strlen($r)<2)
		{
		$r='0'.$r;
		}
		$c = intval($stdseccount->total)+1;
		$cl=substr($class,2);

		$foo = array();
		if(strlen($cl)<2) {
		$foo[0]= substr($ses, 2) .'0'.$cl.$r;
		}
		else
		{
		$foo[0]=  substr($ses, 2) .$cl.$r;
		}
		if(strlen($c)<2) {
		$foo[1] ='0'.$c;
		}
		else
		{
		$foo[1] =$c;
		}

		return $foo;

	}

	/**
	* Show the form for creating a new resource.
	*
	* @return Response
	*/
	public function create()
	{

		$rules=[//'regiNo' => 'required',
		'fname' => 'required',
		//'lname' => 'required',
		//'gender' => 'required',
		//'dob' => 'required',
		
		'photo' => 'mimes:jpeg,jpg,png',
		'phne' => 'required',
		//'fatherName' => 'required',
		//'fatherCellNo' => 'required',
		//'presentAddress' => 'required',
		'emails'    =>'nullable|email|unique:teacher,email'
		//'emails'    =>'nullable|email|unique:teacher'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/teacher/create')->withErrors($validator)->withInput();
		}
		else {

			//exit;

			if(Input::file('photo')!=""){
				$fileName=Input::get('fname').'.'.Input::file('photo')->getClientOriginalExtension();
			}else{
				$fileName='';
			}
//
			$teacher = new Teacher;
			$teacher->firstName= Input::get('fname');
			$teacher->lastName= Input::get('lname');
			if(Input::get('lname')==''){
				$teacher->lastName='';
			}
			$teacher->gender= Input::get('gender');
			if(Input::get('gender')==''){
				$teacher->gender='';
			}
			$teacher->religion= Input::get('religion');
			if(Input::get('religion')==''){
				$teacher->religion="";
			}
			$teacher->bloodgroup= Input::get('bloodgroup');
			if(Input::get('bloodgroup')==''){
				$teacher->bloodgroup="";

			}
			$teacher->nationality= Input::get('nationality');
			if(Input::get('nationality')==''){
				$teacher->nationality="";
			}
			$teacher->dob= Input::get('dob');
			if(Input::get('dob')==''){
				$teacher->dob="";
			}
			$teacher->photo= $fileName;
			$teacher->nationality= Input::get('nationality');
			if(Input::get('nationality')==''){

				$teacher->nationality= "";

			}
			$teacher->phone= Input::get('phne');
			$teacher->email= Input::get('emails');
			if(Input::get('emails')==''){
				$teacher->email="";
			}

			$teacher->fatherName= Input::get('fatherName');
			if(Input::get('fatherName')==''){
				$teacher->fatherName='';
			}
			$teacher->fatherCellNo= Input::get('fatherCellNo');
			if(Input::get('fatherCellNo')==""){
				$teacher->fatherCellNo="";
			}
			$teacher->presentAddress= Input::get('presentAddress');
			if(Input::get('presentAddress')==''){
				$teacher->presentAddress="";
			}
			$teacher->parmanentAddress= Input::get('parmanentAddress');
			if(Input::get('parmanentAddress')==''){
				$teacher->parmanentAddress="";
			}

			$hasTeacher = Teacher::where('phone','=',Input::get('phne'))->first();
			if ($hasTeacher)
			{
				$messages = $validator->errors();
				$messages->add('Duplicate!', 'Teacher already exits with this Phone number.');
				return Redirect::to('/teacher/create')->withErrors($messages)->withInput();
			}

			/*if(Input::get('emails')!=''){
			$hasTeacher1 = Teacher::where('email',Input::get('emails'))->whereNotNull('email')->orwhere('email','<>','')->first();
			echo "<pre>";print_r($hasTeacher1);exit;

			if ($hasTeacher1)
			{
				$messages = $validator->errors();
				$messages->add('Duplicate!', 'Teacher already exits with this Email.');
				return Redirect::to('/teacher/create')->withErrors($messages)->withInput();
			}
		}*/
			else {
				$teacher->save();
				if(Input::file('photo')!=""){
				Input::file('photo')->move(base_path() .'/public/images/teacher',$fileName);
			}
			//echo request()->photo->move(public_path('images/'), $fileName);


			$user = new User;
			$user->firstname = Input::get('fname');
			$user->lastname  = Input::get('lname');
			if( Input::get('lname')==''){
				$user->lastname='';
			}

			$user->email     =     Input::get('emails');
			if(Input::get('emails')==''){
				$user->email = "";
			}

			$user->login     = Input::get('fname').'_'.Input::get('lname');
			$user->group     = 'Teacher';
			$user->group_id  = $teacher->id;
			$user->password  =	Hash::make(Input::get('phne'));
			$user->save();

			/*$ictcore_integration = Ictcore_integration::select("*")->first();
			if(!empty($ictcore_integration) && $ictcore_integration->ictcore_url && $ictcore_integration->ictcore_user && $ictcore_integration->ictcore_password){ 

				$ict  = new ictcoreController();
				$data = array(
				'first_name' => $teacher->firstName,
				'last_name' => $teacher->lastName,
				'phone'     => $teacher->phone,
				'email'     => $teacher->email,
				);
				$contact_id = $ict->ictcore_api('contacts','POST',$data );

				$message = 'School name'.'<br>'.'Login Name: '. $user->login.'Password: '.'123456';
				$data = array(
				'name' => 'School Name',
				'data' => $message,
				'type'     => 'plain',
				'description'     => 'testing message',
				);

				$text_id = $ict->ictcore_api('messages/texts','POST',$data );

				$data = array(
				'name' => 'School Name',
				'text_id' => $text_id
				);

				$program_id = $ict->ictcore_api('programs/sendsms','POST',$data );

				$data = array(
				'title' => 'User Detail',
				'program_id' => $program_id,
				'account_id'     => 1,
				'contact_id'     => $contact_id,
				'origin'     => 1,
				'direction'     => 'outbound',
				);
				$transmission_id = $ict->ictcore_api('transmissions','POST',$data );
				//echo "================================================================transmission==========================================";
				// print_r($transmission_id);
				//GET transmissions/{transmission_id}/send
				//$transmission_send = $ict->ictcore_api('transmissions/'.$transmission_id.'/send','POST',$data=array() );
			}*/

			return Redirect::to('/teacher/create')->with("success","Teacher Created Succesfully.");


			}
		}
	}



	public function ajaxcreate()
	{

		$rules=[//'regiNo' => 'required',
		'fname' => 'required',
		'photo' => 'mimes:jpeg,jpg,png',
		'phne' => 'required',
		'emails'    =>'nullable|email|unique:teacher,email'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
		  return response()->json($validator->errors(), 422);

			//return Redirect::to('/teacher/create')->withErrors($validator)->withInput();
		}
		else {

			//exit;

			
				$fileName='';
			//
			$teacher = new Teacher;
			$teacher->firstName= Input::get('fname');
			$teacher->lastName= Input::get('lname');
			if(Input::get('lname')==''){
				$teacher->lastName='';
			}
			$teacher->gender= Input::get('gender');
			if(Input::get('gender')==''){
				$teacher->gender='';
			}
			$teacher->religion= Input::get('religion');
			if(Input::get('religion')==''){
				$teacher->religion="";
			}
			$teacher->bloodgroup= Input::get('bloodgroup');
			if(Input::get('bloodgroup')==''){
				$teacher->bloodgroup="";

			}
			$teacher->nationality= Input::get('nationality');
			if(Input::get('nationality')==''){
				$teacher->nationality="";
			}
			$teacher->dob= Input::get('dob');
			if(Input::get('dob')==''){
				$teacher->dob="";
			}
			$teacher->photo= $fileName;
			$teacher->nationality= Input::get('nationality');
			if(Input::get('nationality')==''){

				$teacher->nationality= "";

			}
			$teacher->phone= Input::get('phne');
			$teacher->email= Input::get('emails');
			if(Input::get('emails')==''){
				$teacher->email="";
			}

			$teacher->fatherName= Input::get('fatherName');
			if(Input::get('fatherName')==''){
				$teacher->fatherName='';
			}
			$teacher->fatherCellNo= Input::get('fatherCellNo');
			if(Input::get('fatherCellNo')==""){
				$teacher->fatherCellNo="";
			}
			$teacher->presentAddress= Input::get('presentAddress');
			if(Input::get('presentAddress')==''){
				$teacher->presentAddress="";
			}
			$teacher->parmanentAddress= Input::get('parmanentAddress');
			if(Input::get('parmanentAddress')==''){
				$teacher->parmanentAddress="";
			}

			$hasTeacher = Teacher::where('phone','=',Input::get('phne'))->first();
			if ($hasTeacher)
			{
				$messages = $validator->errors();
				$messages->add('Duplicate!', 'Teacher already exits with this Phone number.');
				//return Redirect::to('/teacher/create')->withErrors($messages)->withInput();
			     return response()->json($messages, 422);

			}else {
				$teacher->save();
				
			}
			//return Redirect::to('/teacher/create')->with("success","Teacher Created Succesfully.");

			$teacherlist  = DB::table('teacher')->get();
				$html='';
				foreach($teacherlist as $techr){

					$html .='<option value="'.$techr->id.'"';
					if($techr->id ==$teacher->id){
						$html .='selected';
					}
					$html .='>'.$techr->firstName.'</option>';
				}
			return response()->json(array('message'=>'success','new_id'=>$teacher->id,'teacherList'=>$html), 200);


			}
		}
	
	/**
	* Display the specified resource.
	*
	* @param  int  $id
	* @return Response
	*/
	public function show()
	{
	$teachers = DB::table('teacher')
	->select(DB::raw('teacher.*'))
	->get();
	return View("app.teacherList",compact('teachers'));
	}
	public function getteacherinfo($teacher_id)
	{
		$teacher = DB::table('teacher')
					->select(DB::raw('teacher.*'))
					->where('id',$teacher_id)
					->first();
		$html = '';
		$html .='<tr>
		<td>'.$teacher->firstName.''.$teacher->lastName.'</td>
		<td>'.$teacher->phone.'</td>
		<td>'.$teacher->email.'</td>
		</tr>
		';
		return $html;
		//foreach($teachers as )
	}
	public function getList()
	{
		$rules = [
		'class' => 'required',
		'section' => 'required',
		'shift' => 'required',
		'session' => 'required'


		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails()) {
			return Redirect::to('/student/list')->withInput(Input::all())->withErrors($validator);
		} else {

			$students = DB::table('Student')
			->join('Class', 'Student.class', '=', 'Class.code')
			->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
			'Class.Name as class', 'Student.presentAddress', 'Student.gender', 'Student.religion')
			->where('isActive', '=', 'Yes')
			->where('class',Input::get('class'))
			->where('section',Input::get('section'))
			->where('shift',Input::get('shift'))
			->where('session',trim(Input::get('session')))
			->get();
			if(count($students)<1)
			{
			return Redirect::to('/student/list')->withInput(Input::all())->with('error','No Students Found!');

			}
			else {
				$classes = ClassModel::pluck('name','code');
				$formdata = new formfoo3;
				$formdata->class=Input::get('class');
				$formdata->section=Input::get('section');
				$formdata->shift=Input::get('shift');
				$formdata->session=trim(Input::get('session'));
				//return View::Make("app.studentList", compact('students','classes','formdata'));
				return View("app.studentList", compact('students','classes','formdata'));
			}
		}

	}

	public function view($id)
	{
	$teacher=	DB::table('teacher')
	//->join('Class', 'Student.class', '=', 'Class.code')
	->select('*')
	->where('id','=',$id)->first();

	//return View::Make("app.studentView",compact('student'));
	return View("app.teacherView",compact('teacher'));
	}
	/**
	* Show the form for editing the specified resource.
	*
	* @param  int  $id
	* @return Response
	*/
	public function edit($id)
	{
	$classes = ClassModel::pluck('name','code');
	$teacher= Teacher::find($id);
	//dd($teacher);
	$sections = SectionModel::select('name')->get();
	//return View::Make("app.studentEdit",compact('student','classes'));
	return View("app.teacherEdit",compact('teacher'));
	}


	/**
	* Update the specified resource in storage.
	*
	* @param  int  $id
	* @return Response
	*/
	public function update()
	{

	$rules=[
	'fname' => 'required',
	//'lname' => 'required',
	//'gender' => 'required',
	//'religion' => 'required',
	//'bloodgroup' => 'required',
	//'nationality' => 'required',
	'phone' => 'required',
	//'email' => 'required',
	//'dob' => 'required',
	///'fatherName' => 'required',
	//'fatherCellNo' => 'required',
	//'presentAddress' => 'required',
	'email'    =>'nullable|email|unique:teacher,email,'.Input::get('id')
	//'parmanentAddress' => 'required'
	];
	$validator = \Validator::make(Input::all(), $rules);
	if ($validator->fails())
	{
	return Redirect::to('/teacher/edit/'.Input::get('id'))->withErrors($validator);
	}
	else {

	$teacher = Teacher::find(Input::get('id'));

	if(Input::hasFile('photo'))
	{

	if(substr(Input::file('photo')->getMimeType(), 0, 5) != 'image')
	{
	$messages = $validator->errors();
	$messages->add('Notvalid!', 'Photo must be a image,jpeg,jpg,png!');
	return Redirect::to('/teacher/edit/'.Input::get('id'))->withErrors($messages);
	}
	else {

	$fileName=Input::get('fname').'.'.Input::file('photo')->getClientOriginalExtension();
	$teacher->photo = $fileName;
	Input::file('photo')->move(base_path() .'/public/images',$fileName);
	}

	}
	else {
	$teacher->photo= Input::get('oldphoto');

	}
	//$student->regiNo=Input::get('regiNo');
	//$student->rollNo=Input::get('rollNo');
	/*$teacher->firstName= Input::get('fname');
	$teacher->lastName= Input::get('lname');
	$teacher->gender= Input::get('gender');
	$teacher->religion= Input::get('religion');
	$teacher->bloodgroup= Input::get('bloodgroup');
	$teacher->nationality= Input::get('nationality');
	$teacher->dob= Input::get('dob');
	$teacher->nationality= Input::get('nationality');
	$teacher->phone= Input::get('phone');
	$teacher->email= Input::get('email');

	$teacher->fatherName= Input::get('fatherName');
	$teacher->fatherCellNo= Input::get('fatherCellNo');
	$teacher->presentAddress= Input::get('presentAddress');
	$teacher->parmanentAddress= Input::get('parmanentAddress');*/





	$teacher->firstName= Input::get('fname');
	$teacher->lastName= Input::get('lname');
	if(Input::get('lname')==''){
		$teacher->lastName= "";
	}
	$teacher->gender= Input::get('gender');
	if(Input::get('gender')==""){
		$teacher->lastName= "";
	}
	$teacher->religion= Input::get('religion');
	if(Input::get('religion')==''){
	$teacher->religion="";
	}
	$teacher->bloodgroup= Input::get('bloodgroup');
	if(Input::get('bloodgroup')==''){
	$teacher->bloodgroup="";

	}
	$teacher->nationality= Input::get('nationality');
	if(Input::get('nationality')==''){
	$teacher->nationality="";
	}
	$teacher->dob= Input::get('dob');
	if(Input::get('dob')==''){
			$teacher->dob="";
	}
	//	$teacher->photo= $fileName;
	$teacher->nationality= Input::get('nationality');
	if(Input::get('nationality')==''){

	$teacher->nationality= "";

	}
	$teacher->phone= Input::get('phone');
	$teacher->email= Input::get('email');
	if(Input::get('email')==''){
	$teacher->email="";
	}

	$teacher->fatherName= Input::get('fatherName');
	if( Input::get('fatherName')==""){
		$teacher->fatherName="";
	}
	$teacher->fatherCellNo= Input::get('fatherCellNo');
	if(Input::get('fatherCellNo')==""){
		$teacher->fatherCellNo="";
	}
	$teacher->presentAddress= Input::get('presentAddress');
	if(Input::get('presentAddress')==""){
		$teacher->presentAddress="";
	}
	$teacher->parmanentAddress= Input::get('parmanentAddress');
	if(Input::get('parmanentAddress')==''){
	$teacher->parmanentAddress="";
	}





	//$teacher->save();
	if($teacher->save()){

		$users =  User::where('group_id',Input::get('id'))->first();
		if($users){
			$user_id = $users->id;
			$user = User::find($user_id);
				$user->firstname = Input::get('fname');
				$user->lastname  = Input::get('lname');
				if(Input::get('lname')==""){
					$user->lastname  ="";
				}
				
				if(Input::get('email')!=''){
					$user->email     =     Input::get('email');
				}

				//$user->login     = Input::get('fname').'_'.Input::get('lname');
				//$user->group     = 'Teacher';
				//$user->group_id  = $teacher->id;
				//$user->password  =	Hash::make(Input::get('phone'));
				$user->save();
		}
	}

	return Redirect::to('/teacher/list')->with("success","Teacher Updated Succesfully.");
	}


	}


	/**
	* Remove the specified resource from storage.
	*
	* @param  int  $id
	* @return Response
	*/
	public function delete($id)
	{
	$teacher = Teacher::find($id);
	$teacher->delete();

	return Redirect::to('/teacher/list')->with("success","Teacher Deleted Succesfully.");
	}

	/**
	* Display the specified resource.
	*
	* @param  int  $id
	* @return Response
	*/
	public function getForMarks($class,$section,$shift,$session)
	{
	$students= Student::select('regiNo','rollNo','firstName','middleName','lastName')->where('isActive','=','Yes')->where('class','=',$class)->where('section','=',$section)->where('shift','=',$shift)->where('session','=',$session)->get();
	return $students;
	}

	public function index_file()
	{
	//return View::Make('app.attendanceCreateFile');
	return View('app.teacherCreateFile');
	}

	/**
	* Show the form for creating a new resource.
	*
	* @return Response
	*/
	public function create_file()
	{

	$file = Input::file('fileUpload');
	$ext = strtolower($file->getClientOriginalExtension());
	$validator = \Validator::make(array('ext' => $ext),array('ext' => 'in:xls,xlsx'));

	if ($validator->fails()) {
	return Redirect::to('teacher/create-file')->withErrors($validator);
	}else {
	try{
	$toInsert = 0;
	$data = \Excel::load(Input::file('fileUpload'), function ($reader) { })->get();
	if(!empty($data) && $data->count()){
	DB::beginTransaction();
	try {
	foreach ($data->toArray() as $raw) {
	$teacherData= [
	'firstName' => $raw['firstname'],
	'lastName' => $raw['lastname'],
	'gender' =>    $raw['gender'],
	'phone' => $raw['phone'],
	'email' => $raw['email']
	];
	$hasTeacher = Teacher::where('email','=',$raw['email'])->first();
	if ($hasTeacher)
	{

	}else{
	Teacher::insert($teacherData);
	$toInsert++;
	}
	}
	DB::commit();
	} catch (Exception $e) {
	DB::rollback();
	$errorMessages = new \Illuminate\Support\MessageBag;
	$errorMessages->add('Error', 'Something went wrong!');
	return Redirect::to('/teacher/create-file')->withErrors($errorMessages);

	// something went wrong
	}
	}
	if($toInsert){
	return Redirect::to('/teacher/create-file')->with("success", $toInsert.' Teacher data upload successfully.');
	}
	$errorMessages = new \Illuminate\Support\MessageBag;
	$errorMessages->add('Validation', 'File is empty!!!');
	return Redirect::to('/teacher/create-file')->withErrors($errorMessages);
	}catch (Exception $e) {
	$errorMessages = new \Illuminate\Support\MessageBag;
	$errorMessages->add('Error', 'Something went wrong!');
	return Redirect::to('/teacher/create-file')->withErrors($errorMessages);
	}
	}

	}

	public function index_timetable()
	{
	$classes = DB::table('Class')
	->select(DB::raw('Class.*'))
	->get();

	$sections  = DB::table('section')
	->select(DB::raw('section.*'))
	->get();

	$subjects  = DB::table('Subject')
	->select(DB::raw('Subject.*'))
	->get();

	$teachers = DB::table('teacher')
	->select(DB::raw('teacher.*'))
	->get();
	//dd($teachers);
	$timetable=array();
	return View("app.teacherTimetable",compact("classes","sections","teachers","subjects","timetable"));

	}

	public function create_timetable()
	{


		//echo "<pre>";print_r(Input::get('day'));

		//

		$days = Input::get('day');
		$rules=[//'regiNo' => 'required',
		'teacher' => 'required',
		'class' => 'required',
		'section' => 'required',
		'subject' => 'required',
		'startt' => 'required',
		'endt' => 'required',
		'day' => 'required',
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
		return Redirect::to('/teacher/create-timetable')->withErrors($validator)->withInput();
		}
		else {


				//$timetable->day= Input::get('day');

				/*$hasTimetable = Timetable::where('teacher_id','=',Input::get('teacher'))->where('class_id','=',Input::get('class'))->first();
				if ($hasTimetable)
				{
				$messages = $validator->errors();
				$messages->add('Duplicate!', 'Teacher already exits with this Email.');
				return Redirect::to('/teacher/create')->withErrors($messages)->withInput();
				}
				else {*/
				foreach($days as $day){

				$timetable = new Timetable;
				$timetable->teacher_id= Input::get('teacher');
				$timetable->class_id= Input::get('class');
				$timetable->section_id= Input::get('section');
				$timetable->subject_id= Input::get('subject');
				$timetable->stattime= Input::get('startt');
				$timetable->endtime= Input::get('endt');
				$timetable->day = $day;
				$timetable->save();

			} 



			//echo request()->photo->move(public_path('images/'), $fileName);
			return Redirect::to('/teacher/create-timetable')->with("success","Time Table Created Succesfully.");
			//	}


		}
	}
	public function view_timetable($id)
	{
		if(Input::get('class')!='' && Input::get('section')!=''){
           $teacher_name =  array();
			$class = Input::get('class');
			$timetables = DB::table('timetable')
			->join('teacher', 'timetable.teacher_id', '=', 'teacher.id')
			->join('Subject', 'Subject.id', '=', 'timetable.subject_id')
			//->join('Class', 'Class.id', '=', 'timetable.class_id')
			->join('section', 'section.id', '=', 'timetable.section_id')
			->select('teacher.*','timetable.stattime','timetable.endtime','timetable.day','timetable.id as timetable_id','Subject.name AS subname' , 'section.name as section_id', 'section.class_code as classname')
			->where('timetable.class_id',Input::get('class'))
			->where('timetable.section_id',Input::get('section'))
			/*	->where('section',Input::get('section'))
			->where('shift',Input::get('shift'))
			->where('session',trim(Input::get('session')))*/
			->get();
		}else{
			$class = '';
			$teacher_name =  DB::table('teacher')->select('firstName','lastName')->where('id',$id)->first();
			$timetables = DB::table('timetable')
			->join('teacher', 'timetable.teacher_id', '=', 'teacher.id')
			->join('Subject', 'Subject.id', '=', 'timetable.subject_id')
			//->join('Class', 'Class.id', '=', 'timetable.class_id')
			->join('section', 'section.id', '=', 'timetable.section_id')
			->select('teacher.*','timetable.stattime','timetable.endtime','timetable.day','timetable.id as timetable_id','Subject.name AS subname' , 'section.name as section_id', 'section.class_code as classname')
			->where('timetable.teacher_id',$id)
			/*	->where('section',Input::get('section'))
			->where('shift',Input::get('shift'))
			->where('session',trim(Input::get('session')))*/
			->get();
	    }
		// $timetables = DB::table('timetable')->where('timetable.teacher_id',$id)->get();
		//echo "<pre>";print_r($timetables); exit;
		return View("app.teacherViewtimetable",compact('timetables','teacher_name','class'));
	}

	public function edit_timetable($timetable_id)
	{
 		
		 $classes = DB::table('Class')
		->select(DB::raw('Class.*'))
		->get();

		

		$teachers = DB::table('teacher')
		->select(DB::raw('teacher.*'))
		->get();
		//dd($teachers);
		$timetable = DB::table('timetable')->where('id',$timetable_id)->first();

		$subjects  = DB::table('Subject')
		->where('class',$timetable->class_id)
		->get();
		$sections  = DB::table('section')
		->select(DB::raw('section.*'))->where('class_code',$timetable->class_id)
		->get();

		//echo "<pre>";print_r($timetable);exit;
		return View("app.timetableEdit",compact("classes","sections","teachers","subjects","timetable"));
	}


	public function update_timetable()
	{


		//echo "<pre>";print_r(Input::get('day'));

		$days = Input::get('day');


		$rules=[//'regiNo' => 'required',
		'teacher' => 'required',
		'class' => 'required',
		'section' => 'required',
		'subject' => 'required',
		'startt' => 'required',
		'endt' => 'required',
		'day' => 'required',
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
		return Redirect::to('/timetable/edit/'.Input::get('tid'))->withErrors($validator)->withInput();
		}
		else {


				//$timetable->day= Input::get('day');

				/*$hasTimetable = Timetable::where('teacher_id','=',Input::get('teacher'))->where('class_id','=',Input::get('class'))->first();
				if ($hasTimetable)
				{
				$messages = $validator->errors();
				$messages->add('Duplicate!', 'Teacher already exits with this Email.');
				return Redirect::to('/teacher/create')->withErrors($messages)->withInput();
				}
				else {*/
				//foreach($days as $day){

				$timetable = Timetable::find(Input::get('tid'));
				$timetable->teacher_id= Input::get('teacher');
				$timetable->class_id= Input::get('class');
				$timetable->section_id= Input::get('section');
				$timetable->subject_id= Input::get('subject');
				$timetable->stattime= Input::get('startt');
				$timetable->endtime= Input::get('endt');
				$timetable->day = Input::get('day');
				$timetable->save();

			//} 



			//echo request()->photo->move(public_path('images/'), $fileName);
			return Redirect::to('/teacher/view-timetable/'.Input::get('teacher'))->with("success","Time Table updated Succesfully.");
			//	}


		}
	}

	public function delete_timetable($timetable_id)
	{
		$timetable = Timetable::find($timetable_id);
		//$timetable
		//echo "<pre>";print_r($timetable);exit;
		$teacher_id ='';
		if(!empty($timetable)){
				$teacher_id = 	$timetable->teacher_id;
				$timetable->delete();

		}
		return Redirect::to('/teacher/view-timetable/'.$teacher_id)->with("success","Time Table deleted Succesfully.");

	}
 		



	public function access($id)
	{
	   $teacher= Teacher::find($id);
	   if(!empty($teacher) && $teacher->count()>0){
	   	$chk_teacher  = User::where('login',$teacher->firstName.$teacher->lastName)->where('group_id',$teacher->id)->first();
	      if($chk_teacher  = User::where('login',$teacher->firstName.$teacher->lastName)->where('group_id',$teacher->id)->count()>0){
	      	   return Redirect::to('/teacher/list')->with("error","Already have Accessed .");

	      }
	        if( $teacher->email!=''){
	        	$email = $teacher->email;
	        }else{
	        	$email = $teacher->phone;
	        }
	        $user = new User;
	        $user->firstname = $teacher->firstName;
	        $user->lastname  = $teacher->lastName;
	        $user->email     = NULL;
	      	$user->login     = $teacher->firstName.$teacher->lastName;
	      	$user->group     =  'Teacher';
	      	$user->group_id  = $teacher->id ;
	      	$user->access    = 1 ;
	        $user->password  =	Hash::make($teacher->phone);
	        $user->save();

	            $ictcore_integration = Ictcore_integration::select("*")->first();
	                 
				if(!empty($ictcore_integration) && $ictcore_integration->ictcore_url !='' && $ictcore_integration->ictcore_user !='' && $ictcore_integration->ictcore_password !=''){ 

					 $ict  = new ictcoreController();
					 	$data = array(
						'first_name' => $teacher->firstName,
						'last_name' => $teacher->lastName,
						'phone'     => $teacher->phone,
						'email'     => '',
						);
						$contact_id = $ict->ictcore_api('contacts','POST',$data );

	                   $message = 'School name'.'<br>'.'Login Name: '.  $teacher->firstName.$teacher->lastName.' Password: '.$teacher->phone;
	                    $data = array(
						'name' => 'School Name',
						'data' => $message,
						'type'     => 'plain',
						'description'     => 'testing message',
						);

	                  $text_id = $ict->ictcore_api('messages/texts','POST',$data );

	                  $data = array(
						'name' => 'School Name',
						'text_id' => $text_id
						);

	                    $program_id = $ict->ictcore_api('programs/sendsms','POST',$data );

						$data = array(
						'title' => 'User Detail',
						'program_id' => $program_id,
						'account_id'     => 1,
						'contact_id'     => $contact_id,
						'origin'     => 1,
						'direction'     => 'outbound',
						);
						$transmission_id = $ict->ictcore_api('transmissions','POST',$data );
						
	             
	            }

	   	   return Redirect::to('/teacher/list')->with("success","Student Moblie Access Created.");

	   }
	   return Redirect::to('/teacher/list')->with("error","Student not found.");
	}
	/**
    * Diary Show
    **/
    public function diaryshow($teacher_id)
    {
    	$diaries  = Diary::join('Class','diaries.class','=','Class.code')
    						->join('section','diaries.section','=','section.id')
    						->join('Subject','diaries.subject','=','Subject.id')
						    ->select('diaries.id','diaries.diary','diaries.diary_date','Class.name as class_name','section.name as section_name','Subject.name as subject_name')
						    ->where('diaries.diary_date',Carbon::today()->toDateString())
						    ->where('diaries.teacher_id',$teacher_id)
						    ->get(); 
		return view('app.teacher.index',compact('diaries','teacher_id'));

    }
	/**
	* teacher add diary date class and section wise
	**/
	public function diary_add($teacher_id)
	{
		$teacher_sections = array();
		$teachers_class   = array();
		$teacher_subjects = array();

		$teacher_classes = DB::table('timetable')->where('teacher_id',$teacher_id)->get();
		if($teacher_classes){

			$sections  = array();
			$classes   = array();
			$subjects  = array();

			foreach($teacher_classes as $teacher_timetable){
				$sections[] = $teacher_timetable->section_id;
				$classes[]  = $teacher_timetable->class_id;
				$subjects[] = $teacher_timetable->subject_id;
			}
			$teacher_sections = DB::table('section')->whereIn('id',$sections)->get();
			$teachers_class   = DB::table('Class')->whereIn('code',$classes)->get();
			$teacher_subjects = DB::table('Subject')->whereIn('id',$subjects)->get();
			//echo "<pre>";print_r($teachers_class);exit;
		}

		return view('app.teacher.diary',compact('teacher_sections','teachers_class','teacher_subjects','teacher_id'));
	}

	/**
	* Create diary
	**/
	public function diary_create()
	{
		$rules=[//'regiNo' => 'required',
		'class' => 'required',
		'section.*' => 'required',
		'subject' => 'required',
		'description' => 'required',
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
		return Redirect::to('/timetable/edit/'.Input::get('tid'))->withErrors($validator)->withInput();
		}
		else{
				$teacher_id = Input::get('id');
				$sections   = Input::get('section');
				$count      = 0;
				foreach($sections as $section){
					$getolddiary  = Diary::where('section',$section)
									->where('subject',Input::get('subject'))
									->where('teacher_id',$teacher_id)
									->where('class',Input::get('class'))
									->where('diary_date',Carbon::today()->toDateString())
									->count();
					if($getolddiary==0){

						$diary               =  new Diary;
						$diary->subject      =  Input::get('subject');
						$diary->section      =  $section;
						$diary->class        =  Input::get('class');
						$diary->teacher_id   =  $teacher_id;
						$diary->diary        =  Input::get('description');
						$diary->diary_date   =  Carbon::now();
						$diary->save();
					}else{
						$count++;
					}

				}
				if($count==count($sections)){
					return Redirect::to('/teacher/diary/'.$teacher_id)->with("success","Diary allready Created.")->withInput();
				}
					return Redirect::to('/teacher/diary/'.$teacher_id)->with("success","Diary Created Succesfully.")->withInput();
					//return Redirect::to('/teacher/diary/'.$teacher_id)->withErrors($validator)->withInput();

		}
	}
	/**
	** get teacher wise subject
	***/

	public function teachersubject($class,$teacher_id)
	{


		$teacher_subjects = array();
		$teacher_classes = DB::table('timetable')->where('teacher_id',$teacher_id)->where('class_id',$class)->get();
		if($teacher_classes){

			$sections  = array();
			foreach($teacher_classes as $teacher_timetable){
				$subjects[] = $teacher_timetable->subject_id;
			}
			$teacher_subjects = DB::table('Subject')->whereIn('id',$subjects)->get();
			//echo "<pre>";print_r($teachers_class);exit;
		}
		
		return $teacher_subjects;
	}

	/**
	** get teacher wise section
	***/
	public function teachersection($class,$teacher_id)
	{

		$teacher_sections = array();
		$teacher_classes = DB::table('timetable')->where('teacher_id',$teacher_id)->where('class_id',$class)->get();
		if($teacher_classes){

			$sections  = array();
			foreach($teacher_classes as $teacher_timetable){
				$sections[] = $teacher_timetable->section_id;
			}
			$teacher_sections = DB::table('section')->whereIn('id',$sections)->get();
			//echo "<pre>";print_r($teachers_class);exit;
		}
		
		return $teacher_sections;
	}

	/**
	* DELEte Diary
	**/
	public function delete_diary($id)
	{
		$get_teacher = Diary::find($id);
		$teacher_id = $get_teacher->teacher_id;
		$dele = DB::table('diaries')->where('id',$id)->delete();
		
		return Redirect::to('/teacher/diary/show/'.$teacher_id)->with("success","Diary Deleted Succesfully.")->withInput();

	}
}

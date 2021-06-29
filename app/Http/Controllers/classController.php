<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\ClassModel;
use App\Subject;
use DB;
use App\Student;
use App\SectionModel;
use App\GPA;
use App\Marks;
use App\Ictcore_fees;
use App\Ictcore_integration;
use App\Message;
use App\Diary;
use Carbon\Carbon;

class classController extends BaseController {

	public function __construct() {
		/*$this->beforeFilter('csrf', array('on'=>'post'));
		$this->beforeFilter('auth');
		$this->beforeFilter('userAccess',array('only'=> array('delete')));*/
		
	        $this->middleware('auth');
            //$this->middleware('userAccess',array('only'=> array('delete')));
	}
	/**
	* Display a listing of the resource.
	*
	* @return Response
	*/
	public function index()
	{
		return View('app.classCreate');
	}


	/**
	* Show the form for creating a new resource.
	*
	* @return Response
	*/
	public function create()
	{
		$rules=[
			'name' => 'required',
			'code' => 'required|max:20',
			//'description' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			//return Redirect::to('/class/create')->withInput()->withErrors($validator);
			return Redirect::to('/class/list')->withInput()->withErrors($validator);
		}
		else {
			$clcode = 'cl'.Input::get('code');
			$cexists=ClassModel::select('*')->where('code','=',$clcode)->get();
			if(count($cexists)>0){

				$errorMessages = new \Illuminate\Support\MessageBag;
				$errorMessages->add('deplicate', 'Class all ready exists!!');
				//return Redirect::to('/class/create')->withErrors($errorMessages);
				return Redirect::to('/class/list')->withErrors($errorMessages);
			}
			else {
				$class = new ClassModel;
				$class->name = Input::get('name');
				$class->code = $clcode;
				$class->description = Input::get('description');
				if( Input::get('description')==''){
					$class->description ='';
				}
				$class->save();
				//return Redirect::to('/class/create')->with("success", "Class Created Succesfully.");
				return Redirect::to('/class/list')->with("success", "Class Created Succesfully.");
			}
		}
	}

	public function ajaxcreate()
	{
		
//echo "<pre>";print_r(Input::all());
		$rules=[
			'name' => 'required',
			'code' => 'required|max:20',
			//'description' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			 return response()->json($validator->errors(), 422);
		}
		else {
			$clcode = 'cl'.Input::get('code');
			$cexists=ClassModel::select('*')->where('code','=',$clcode)->get();
			if(count($cexists)>0){

				$errorMessages = new \Illuminate\Support\MessageBag;
				$errorMessages->add('deplicate', 'Class all ready exists!!');
				//return Redirect::to('/class/create')->withErrors($errorMessages);
				 return response()->json($errorMessages, 422);
			}
			else {
				$class = new ClassModel;
				$class->name = Input::get('name');
				$class->code = $clcode;
				$class->description = Input::get('description');
				if( Input::get('description')==''){
					$class->description ='';
				}
				$class->save();
				$classlist  = DB::table('Class')->get();
				$html='';
				foreach($classlist as $clas){

					$html .='<option value="'.$clas->code.'"';
					if($clas->code ==$class->code){
						$html .='selected';
					}
					$html .='>'.$clas->name.'</option>';
				}
				//
				//return Redirect::to('/class/create')->with("success", "Class Created Succesfully.");
				return response()->json(array('message'=>'success','new_id'=>$class->code,'classlist'=>$html), 200);

			}
		}
	}




	/**
	* Store a newly created resource in storage.
	*
	* @return Response
	*/
	public function show()
	{
		//$Classes = ClassModel::orderby('code','asc')->get();
		$Classes = DB::table('Class')
		->select(DB::raw('Class.id,Class.code,Class.name,Class.description,(select count(Student.id) from Student where class=Class.code)as students'))
		->get();

		$class = array();
		
		//return View::Make('app.classList',compact('Classes'));
		return View('app.classList',compact('Classes','class'));
	}



	/**
	* Show the form for editing the specified resource.
	*
	* @param  int  $id
	* @return Response
	*/
	public function edit($id)
	{
		$class = ClassModel::find($id);
		$Classes = DB::table('Class')
		->select(DB::raw('Class.id,Class.code,Class.name,Class.description,(select count(Student.id) from Student where class=Class.code)as students'))
		->get();

		
		
		//return View::Make('app.classEdit',compact('class'));
		//return View('app.classEdit',compact('class'));
		return View('app.classList',compact('class','Classes'));
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
			'name' => 'required',
			'description' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/class/edit/'.Input::get('id'))->withErrors($validator);
		}
		else {
			$class = ClassModel::find(Input::get('id'));
			$class->name= Input::get('name');

			$class->description=Input::get('description');
			if( Input::get('description')==''){
					$class->description ='';
				}
			$class->save();
			return Redirect::to('/class/list')->with("success","Class Updated Succesfully.");

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
		$class = ClassModel::select(DB::raw('Class.id,Class.code,Class.name,Class.description,(select count(Student.id) from Student where class=Class.code)as students'))
							->find($id);
		echo "<pre>";print_r($class->students);
		if($class->students==0){
			//$class->delete();
			return Redirect::to('/class/list')->with("success","Class Deleted Succesfully.");
		}else{

			return Redirect::to('/class/list')->with("error","Class Not deleted first delete all dependances then delete Class.");

		}
	}

	public function getSubjects($class)
	{
	
		$subjects = Subject::select('id','name','code')->where('class',$class)->orderby('code','asc')->get();
		return $subjects;
	}

	public function diary($class)
	{
		return view('app.classdiary',compact('class'));
	}

	public function getForsectionjoin($class)
	{
		
		$teacher_classes = DB::table('timetable')->where('class_id',$class)->get();
		if($teacher_classes){

			$sections    = array();
			

			foreach($teacher_classes as $teacher_timetable){
				$sections[] = $teacher_timetable->section_id;
			}
		}



		$sections        = SectionModel::/*join('timetable','section.id','=','timetable.section_id')*/
		select('section.id','section.name')->where('section.class_code','=',$class)->whereIn('id',$sections)->get();
		$output  ='';
		$output .='<input type="hidden" name="class" value="'.$class.'">';
		if(empty($sections->toArray())){
				return '404';
				exit;
			}
		foreach($sections as $section){
			
			$subjecname = '';
			//echo getsubjecclass($class);
			if(empty(getsubjecclass($class))){
				return '404';
				exit;
			}
			for($i=0;$i<count(getsubjecclass($class)['sub_name']);$i++){
				$teachers   = DB::table('timetable')
				->join('teacher','timetable.teacher_id','=','teacher.id')
				->select('timetable.teacher_id','teacher.firstName','teacher.lastName')
				->where('section_id',$section->id)
				->where('subject_id',getsubjecclass($class)['sub_name'][$i]['id'])
				->first();
				if(!empty($teachers)){
					$teacher_id   = $teachers->teacher_id;
					$teacher_name = $teachers->firstName.''.$teachers->lastName;
				}else{
					$teacher_id   = '';
					$teacher_name = '';
				}

				$getolddiary  = Diary::where('section',$section->id)
									->where('subject',getsubjecclass($class)['sub_name'][$i]['id'])
								  //->where('teacher_id',$teacher_id)
									->where('class',$class)
									->where('diary_date',Carbon::today()->toDateString());
									//->count();
				if($getolddiary->count()>0){
					$value   = $getolddiary->first()->diary; 
					$output .='<input type="hidden" value="'.$getolddiary->first()->id.'" name="dairy_id[]">' ;
				}else{
					$value   = '';
				    $output .='<input type="hidden" value="" name="dairy_id[]">' ;

				}
					/*$url = url('/').'/create/marks?sub_id='.getsubjecclass($class)['sub_name'][$i]['id'].'&class='.$class.'&section='.$section->id;
					$link = "'".$url."','enter marks','width=1500','height=500'";
					$subjecname .='&nbsp;  ';
					$subjecname .='<a href="'.$url.'" onclick="window.open('."$link".'); 
		            return false;">'.getsubjecclass($class)['sub_name'][$i]['name'].'</a>';*/
				$output .='<tr><td>'.$section->name.'<input type="hidden" value="'.$section->id.'" name="section[]"><input type="hidden" value="'.getsubjecclass($class)['sub_name'][$i]['id'].'" name="subject[]"><input type="hidden" name="teacher_id[]" value="'.$teacher_id.'"></td><td>'.$teacher_name.'</td><td>'.getsubjecclass($class)['sub_name'][$i]['name'].' <textarea name="description[]" required>'.$value.'</textarea></td></tr>'; 

			}
			//$output .='<tr><td>'.$section->name.'</td><td>'.$subjecname.'</td></tr>'; 
		}
		return $output;
	}

	/**
	* Create diary
	**/
	public function diary_create()
	{
		$rules=[//'regiNo' => 'required',
		
		'section.*' => 'required',
		'subject.*' => 'required',
		'description.*' => 'required',
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
		return Redirect::to('/timetable/edit/'.Input::get('tid'))->withErrors($validator)->withInput();
		}
		else{
			// echo "<pre>";print_r(Input::all());exit;
				$teacher_id = Input::get('teacher_id');
				$section = Input::get('section');
				$class = Input::get('class');
				$subject = Input::get('subject');
				$description = Input::get('description');
				$count = 0;

				//foreach($sections as $section){
				for($i=0;$i<count($section);$i++){
					$getolddiary  = Diary::where('section',$section[$i])
									->where('subject',$subject[$i])
									->where('teacher_id',$teacher_id[$i])
									->where('class',$class)
									->where('diary_date',Carbon::today()->toDateString());
									//->count();
					if($getolddiary->count()==0){

						$diary               =  new Diary;
					}else{
						$diary               =  Diary::find($getolddiary->first()->id);
						$count++;
					}
						$diary->subject      =  $subject[$i];
						$diary->section      =  $section[$i];
						$diary->class        =  $class;
						$diary->teacher_id   =  $teacher_id[$i];
						$diary->diary        =  $description[$i];
						$diary->diary_date   =  Carbon::now();
						$diary->save();

				}
				if($count==count($section)){
					return Redirect::to('/class/diary/'.$class)->with("success","Diary Updated Succesfully.")->withInput();
				}
					return Redirect::to('/class/diary/'.$class)->with("success","Diary Created Succesfully.")->withInput();
					//return Redirect::to('/teacher/diary/'.$teacher_id)->withErrors($validator)->withInput();

		}
	}

}

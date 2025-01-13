<?php
namespace App\Http\Controllers;
use DB;
use App\Models\SectionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
//
class sectionController extends BaseController {

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
		$class = DB::table('Class')
		->select(DB::raw('name,code'))
		->get();
		$sections = SectionModel::get();
		$teacher_ids = array();
		foreach($sections as $section){
         $teacher_ids[] = $section->teacher_id;
		}
		$teachers = DB::table('teacher')
						->join('users','teacher.id','=','users.group_id')
	    				->select('teacher.id','teacher.firstName','teacher.lastName')/*->whereNotIn('id',$teacher_ids)*/
	    				->get();
		return View('app.sectionCreate',compact('class','teachers'));
		//echo "this is section controller";
	}
	/**
	* Show the form for creating a new resource.
	*
	* @return Response
	*/
	public function create(Request $request)
	{
		$rules=[
			'name' => 'required',
			'class'=> 'required',
			//'teacher_id'=>
			//'description' => 'required',
			'teacher_id' => 'required'
		];
		$validator = \Validator::make($request->all(), $rules);
		if ($validator->fails())
		{
			//return Redirect::to('/section/create')->withInput()->withErrors($validator);
			return Redirect::to('/section/list')->withInput()->withErrors($validator);
		}
		else {
			$sname = $request->input('name');
			$sexists=SectionModel::select('*')->where('name','=',$sname)->where('class_code','=',$request->input('class'))->get();
			if(count($sexists)>0){

				$errorMessages = new \Illuminate\Support\MessageBag;
				$errorMessages->add('deplicate', 'Section all ready exists!!');
				//return Redirect::to('/section/create')->withErrors($errorMessages);
				return Redirect::to('/section/list')->withErrors($errorMessages);
			}
			else {
				$class = new SectionModel;
				$class->name = $request->input('name');
				$class->class_code = $request->input('class');
				$class->description = $request->input('description');
				if($request->input('description')==''){
					$class->description = '';
				}
				$class->teacher_id = $request->input('teacher_id');
				$class->save();
				//return Redirect::to('/section/create')->with("success", "Section Created Succesfully.");
				return Redirect::to('/section/list')->with("success", "Section Created Succesfully.");
			}
		}

	}
	/**
	* Store a newly created resource in storage.
	*
	* @return Response
	*/
	public function show(Request $request)
	{
		//$Classes = ClassModel::orderby('code','asc')->get();
		$sections = DB::table('section')
		->leftjoin('teacher','section.teacher_id','=','teacher.id')
		//->select(DB::raw('section.id,section.class_code,section.name,section.description'))
		->select(DB::raw('section.id,section.class_code,section.name,section.teacher_id,section.description,(select count(Student.id) from Student where class=section.class_code And section=section.id)as students'),'teacher.firstName','teacher.lastName')
		->get();
		
		$class = DB::table('Class')
		->select(DB::raw('name,code'))
		->get();
		$sectionss = SectionModel::get();
		$teacher_ids = array();
		foreach($sectionss as $section){
         $teacher_ids[] = $section->teacher_id;
		}
		$teachers = DB::table('teacher')
						->join('users','teacher.id','=','users.group_id')
	    				->select('teacher.id','teacher.firstName','teacher.lastName')/*->whereNotIn('id',$teacher_ids)*/
	    				->get();
	   	$section = array();
		//dd($sections);
		//return View::Make('app.classList',compact('Classes'));

		// echo "<pre>";print_r($class);exit;
		return View('app.sectionList',compact('sections','section','class','teacher_ids','teachers','sectionss'));
	}
	public function get_section($class_code)
	{
		//$Classes = ClassModel::orderby('code','asc')->get();
		$sections = DB::table('section')
		->leftjoin('teacher','section.teacher_id','=','teacher.id')
		//->select(DB::raw('section.id,section.class_code,section.name,section.description'))
		->select(DB::raw('section.id,section.class_code,section.name,section.description,section.teacher_id,(select count(Student.id) from Student where class=section.class_code And section=section.id AND session='.get_current_session()->id.')as students'),'teacher.firstName','teacher.lastName')
		->where('class_code',$class_code)
		->get();
		$html = '';
		foreach($sections as $section){
			$teacher_id = $section->teacher_id;
			$fun = "onclick=getteacherinfo('$teacher_id')";
			$html .= '<tr>
              <td>'.$section->name.'</td>
              <td>'.$section->description.'</td>
              <td>'.count_student($section->id,$section->class_code).'</td>
              <td><a href="#" '.$fun.' >'.$section->firstName.''.$section->lastName.'</a></td>
              </tr>';
		}
		//dd($sections);
		//return View::Make('app.classList',compact('Classes'));

		//echo "<pre>";print_r($sections);exit;
		return $html;
	}



	/**
	* Show the form for editing the specified resource.
	*
	* @param  int  $id
	* @return Response
	*/
	public function edit($id)
	{
		$section = SectionModel::find($id);
		$class = DB::table('Class')
		->select(DB::raw('name,code'))
		->get();
		$teachers = DB::table('teacher')->join('users','teacher.id','=','users.group_id')
	    ->select('teacher.id','teacher.firstName','teacher.lastName')->get();
		$sections = DB::table('section')
		->leftjoin('teacher','section.teacher_id','=','teacher.id')
		//->select(DB::raw('section.id,section.class_code,section.name,section.description'))
		->select(DB::raw('section.id,section.class_code,section.name,section.teacher_id,section.description,(select count(Student.id) from Student where class=section.class_code And section=section.id)as students'),'teacher.firstName','teacher.lastName')
		->get();
		//return View::Make('app.classEdit',compact('class'));
		//return View('app.sectionEdit',compact('section','class','teachers'));
		return View('app.sectionList',compact('sections','section','class','teachers'));
	}


	/**
	* Update the specified resource in storage.
	*
	* @param  int  $id
	* @return Response
	*/
	public function update(Request $request)
	{
		$rules=[
			'name' => 'required',
			//'description' => 'required',
			'teacher_id' => 'required'
		];
		$validator = \Validator::make($request->all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/section/edit/'.$request->input('id'))->withErrors($validator);
		}
		else {
			$section = SectionModel::find($request->input('id'));
			$section->name= $request->input('name');
            $section->class_code = $request->input('class');
			$section->description=$request->input('description');
			if($request->input('description')==''){
				$section->description='';
			}
			$section->teacher_id = $request->input('teacher_id');
			$section->save();
			return Redirect::to('/section/list')->with("success","Section Updated Succesfully.");

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

		
		$section = SectionModel::select(DB::raw('section.id,section.class_code,section.name,section.description,(select count(Student.id) from Student where class=section.class_code And section=section.id)as students'))
		->find($id);
		if($section->students==0){
			$section->delete();
			return Redirect::to('/section/list')->with("success","Section Deleted Succesfully.");

		}else{

			return Redirect::to('/section/list')->with("error","Section Not deleted first delete all dependances then delete Section.");

		}
		//
	}

	public function getsections($class,$session){

      //$section= SectionModel::select('id','name')->where('class_code','=',$class)->get();
		$section= DB::table('section')
		//->select(DB::raw('section.id,section.class_code,section.name,section.description'))

		->select(DB::raw('section.id,section.name,(select count(Student.id) from Student where  section=section.id And class=section.class_code  AND session='.$session. ' AND isActive='.'"yes"'.')as students'))
       ->where('section.class_code','=',$class)
      // ->where('isActive','yes')
		->get();
		//print_r($section);exit;
	return $section;
	}

	public function getsectionsc($class){

      $section= SectionModel::select('id','name')->where('class_code','=',$class)->get();
		
		//print_r($section);exit;
	return $section;
	}

	public function view_timetable($id)
	{
		$teacher_name =  array();
		$timetables = DB::table('timetable')
		->join('teacher', 'timetable.teacher_id', '=', 'teacher.id')
		->join('Subject', 'Subject.id', '=', 'timetable.subject_id')
		//->join('Class', 'Class.id', '=', 'timetable.class_id')
		->join('section', 'section.id', '=', 'timetable.section_id')
		->select('teacher.*','timetable.stattime','timetable.endtime','timetable.day','timetable.id as timetable_id','Subject.name AS subname' , 'section.name as section_id', 'section.class_code as classname')
		->where('timetable.section_id',$id)
		/*	->where('section',$request->input('section'))
		->where('shift',$request->input('shift'))
		->where('session',trim($request->input('session')))*/
		->get();
		// $timetables = DB::table('timetable')->where('timetable.teacher_id',$id)->get();
		//echo "<pre>";print_r($timetables); exit;
		return View("app.teacherViewtimetable",compact('timetables','teacher_name'));
	}

}

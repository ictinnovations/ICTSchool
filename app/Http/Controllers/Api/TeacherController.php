<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
//use App\Api_models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\ClassModel;
use App\Subject;
use App\Attendance;
use App\Teacher;
use App\SectionModel;
use DB;
use Excel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class TeacherController extends Controller
{

	public function __construct() 
	{

	 //  $this->middleware('auth:api');

	}
   public $successStatus = 200;

   /**
	 * student_classwise api
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function all_teachers()
	{
	  $teachers = DB::table('teacher')->select('id','firstName','lastName','gender','dob','email','phone','fatherName','fatherCellNo','presentAddress')->paginate(20);
	  
	  if(count($teachers)<1)
	  {
		 return response()->json(['error'=>'No teachers Found!'], 404);
	  }
	  else {
		  return response()->json($teachers,200);
	  }
	}
	/**
	 * count teachers api
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function count_teachers()
	{
	    $teachers = DB::table('teacher')->count();
		return response()->json($teachers,200);
	}

	/**
	 * student_classwise api
	 *
	 * @return \Illuminate\Http\Response
	 */
	/*public function student_classwise($class_level,$section,$shift,$session)
	{
		$students = DB::table('Student')
	  ->join('Class', 'Student.class', '=', 'Class.code')
	  ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
	  'Class.Name as class', 'Student.presentAddress', 'Student.gender', 'Student.religion')
	  ->where('class',$class_level)
	  ->where('section',$section)
	  ->where('shift',$shift)
	  ->where('session',trim($session))
	  ->get();
	  if(count($students)<1)
	  {
		 return response()->json(['error'=>'No Students Found!'], 404);
	  }
	  else 
	  {
		  return response()->json($students,200);
	  }
	}*/
	public function getteacher($teacher_id)
	{
		  $teacher = DB::table('teacher')->select('id','firstName','lastName','gender','dob','email','phone','fatherName','fatherCellNo','presentAddress')->where('id','=',$teacher_id)->first();
		
		if(!is_null($teacher) && count($teacher)>0){
		   return response()->json($teacher,200);
		}else{
		return response()->json(['error'=>'teacher Not Found'], 404);
	   }
	}

	/*public function getstudentsubjects($student_id)
	{
		 //$student = Student::find($student_id);
		 $student = Student::find($student_id);
		  
		 $subject = DB::table('Subject')->select('code','name','type','class','stdgroup')->where('class',$student->class)->where('stdgroup',$student->group)->get();
		if(!is_null($subject) && count($subject)>0){
		  	 return response()->json(['subjects'=>$subject]);
		}else{
			return response()->json(['error'=>'Subject Not Found'], 404);
	   }
	}*/

	public function update_teacher($teacher_id)
	{
		//return response()->json(['student'=>$student_id]);
		$rules=[
		'firstname'      => 'required',
		'lastname'       => 'required',
		'gender'         => 'required',
		'dob'            => 'required',
		'email'          => 'required',
		'phone'          => 'required',
		'presentaddress' => 'required',
		'fathername'     =>'required',
		'fathercellno'   => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return response()->json($validator->errors(), 422);
		}
		else{
			$teacher = Teacher::select('id','firstName','lastName','gender','dob','email','phone','fatherName','fatherCellNo','presentAddress')->where('id',$teacher_id);
			$teacher->firstName = Input::get('firstname');
			$teacher->lastName= Input::get('lastname');
			$teacher->gender= Input::get('gender');
			$teacher->dob= Input::get('dob');
			$teacher->phone= Input::get('phone');
			$teacher->email= Input::get('email');
			$teacher->presentAddress= Input::get('presentaddress');
			$teacher->fatherName= Input::get('fathername');
			$teacher->fatherName= Input::get('fathercellno');
			$teacher->save();
			return response()->json($teacher,200);
		}
	}
	public function getsectionteacher($teacher_id){

		/*$teacher = DB::table('timetable')
		->join('Class', 'timetable.class_id', '=', 'Class.code')
		->join('section', 'timetable.section_id', '=', 'section.id')
		->select('Class.id as class_id','Class.name as class', 'section.id as section_id','section.name as section')
		->where('timetable.teacher_id',$teacher_id)->groupby('timetable.section_id')->get();
           */
		$teacher = DB::table('section')
		->join('Class', 'section.class_code', '=', 'Class.code')
		//->join('section', 'timetable.section_id', '=', 'section.id')
		->select('Class.id as class_id','Class.name as class', 'section.id as section_id','section.name as section');
		if($teacher_id!='admin'){
	    $teacher = $teacher->where('section.teacher_id',$teacher_id);		
		}
		$teacher = $teacher->get();
		if(!is_null($teacher) && count($teacher)>0){
			return response()->json($teacher,200);
		}else{
			return response()->json(['error'=>'Section Not assigned yet'], 404);
		}
	}  


	public function getsubjectteacher($teacher_id){

		$teacher = DB::table('timetable')
		->join('Class', 'timetable.class_id', '=', 'Class.code')
		->join('Subject', 'timetable.subject_id', '=', 'Subject.id')
		->select('Subject.name as subject', 'Class.name as class')
		->where('timetable.teacher_id',$teacher_id)->groupby('timetable.class_id')->get();
		if(!is_null($teacher) && count($teacher)>0){
			return response()->json($teacher);
		}else{
			return response()->json(['error'=>'teacher Not Found'], 404);
		}
	} 

	public function getteacherdata($teacher_id)
	{
		/*$teachers = DB::table('timetable')
		->join('Class', 'timetable.class_id', '=', 'Class.code')
		->join('section', 'timetable.section_id', '=', 'section.id')
		->select('Class.id as class_id','Class.name as class', 'section.id as section_id','section.name as section')
		->where('timetable.teacher_id',$teacher_id)->groupby('timetable.section_id')->get();		
		*/

		$teachers = DB::table('section')
		->join('Class', 'section.class_code', '=', 'Class.code')
		//->join('section', 'timetable.section_id', '=', 'section.id')
		->select('Class.id as class_id','Class.name as class', 'section.id as section_id','section.name as section');
		if($teacher_id!='admin'){
	    $teachers = $teachers->where('section.teacher_id',$teacher_id);		
		}
		$teachers =$teachers->orderBy('Class.code','DESC');
		$teachers =$teachers->get();
		$sections  = array();
		$attendances_b  = array();
		 if($teachers->count()>0){
		 	$i=0;
		 	$now   = Carbon::now();
             $year  =  $now->year;
             $year  =  get_current_session()->id;
		foreach($teachers as $teacher ){
          $sections[] = $teacher->section_id;
          //$count_student1 = array();
         $class = DB::table('Class')->where('id',$teacher->class_id)->first();
          $count_student1 =  DB::table('Student')->select(DB::raw('COUNT(id) as total_student'))->where('isActive','yes')->where('class',$class->code)->where('section',$teacher->section_id)->where('session',$year)->first();
          // $count_student =  $count_student1->total_attendance;
          //$count_student[] =$count_student1->toArray();
          $attendances_a = DB::table('Attendance')
             ->join('Class', 'Attendance.class_id', '=', 'Class.id')
		     ->join('section', 'Attendance.section_id', '=', 'section.id')
             ->select(DB::raw('COUNT(*) as total_attendance,
                           SUM(Attendance.status="Absent") as absent,
                           SUM(Attendance.status="Present" ) as present ,
                           SUM(Attendance.status="Late" ) as late ,
                           SUM(Attendance.status="Leave" ) as leaves ,
                           SUM(Attendance.coments="sick_leave" OR Attendance.coments="leave") as leavesr'),'section.id as section_id','section.name as section','Class.id as class_id','Class.name as class')->where('Attendance.session',get_current_session()->id)->where('Attendance.section_id',$teacher->section_id)->where('date',Carbon::today()->toDateString())->first();
           //$tst[] = $attendances_a[$i]->total_attendance;
           //$attendances_a = $attendances_a + $count_student; 
         
           if($attendances_a->total_attendance==0){
           	 $attendances_b[] = array('total_attendance'=>0,'absent'=>0,'present'=>0,'late'=>0,'leaves'=>0,'section_id'=>$teacher->section_id,'section'=>$teacher->section,'class_id'=>$teacher->class_id,'class'=>$teacher->class,'total_student'=>$count_student1->total_student);
           }else{
           	$attendances_b[] = array('total_attendance'=>$attendances_a->total_attendance,'absent'=>$attendances_a->absent,'present'=>$attendances_a->present,'late'=>$attendances_a->late,'leaves'=>$attendances_a->leaves,'section_id'=>$teacher->section_id,'section'=>$teacher->section,'class_id'=>$teacher->class_id,'class'=>$teacher->class,'total_student'=>$count_student1->total_student);
           //	$attendances_b[] =;
           }
            /**
            * Get teacher timetable
            **/

            /* $teacher_timetbale = array();
           $teacher_timetbale   = DB::table('timetable')
			->join('Class', 'timetable.class_id', '=', 'Class.code')
			->join('Subject', 'timetable.subject_id', '=', 'Subject.id')
			->select('Subject.name as subject', 'Class.name as class')
			->where('timetable.teacher_id',$teacher_id)->groupby('timetable.class_id')->get();
			if(!is_null($teacher_timetbale) && count($teacher_timetbale)>0){
				$teacher_timetbale['timetable'][] = $teacher_timetbale;
			}else{
				$teacher_timetbale['timetable'] = array();
			}*/

          // $attendances_b['total_student'.'_'.$teacher->section] =$count_student1->total_student; 
          // $attendances_b['76']=65;
           //$merged = $attendances_b->merge($count_student);
//echo "<pre>";print_r($attendances_b);exit;
           //array_push($attendances_b,$count_student1);//($attendances_b,$count_student1);
          // $resultArray[$i] = $attendances_b;
              //$result[] = $attendances_b + $count_student1;
			// array_push($attendances_b,'rer');
    // $a = array_merge($attendances_b, $count_student1);

           $i++;
		}
		
		
		//echo "<pre>";print_r($attendances_b);exit;
		//$mrge = array_merge($attendances_b,$attendances_d);
		
   

		return response()->json($attendances_b,200);
	      $merage = $attendances_a;
		
			if(!empty($merage)){
				return response()->json(array($merage,$teacher_timetbale),200);
			}else{
				return response()->json(['error'=>'teacher Not Found'], 404);
			}
		}else{
			 return response()->json(['error'=>'teacher Not Found'], 404);

		}
	}     
}


			
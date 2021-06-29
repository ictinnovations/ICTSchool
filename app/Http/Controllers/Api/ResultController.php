<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
//use App\Api_models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Exam;
use App\ClassModel;
use App\SectionModel;
use App\Subject;
use App\Marks;
use App\GPA;
use DB;
use Excel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ResultController extends Controller
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
		public function getallresult()
		{
			$classes2 = ClassModel::orderby('code','asc')->pluck('name','code');
			$subjects = Subject::where('class',Input::get('class'))->pluck('name','code');
			$marks=	DB::table('Marks')
			->join('Student', 'Marks.regiNo', '=', 'Student.regiNo')
			->join('Class', 'Marks.class', '=', 'Class.code')
			->join('section', 'Marks.section', '=', 'section.id')
			//->leftjoin('Subject', 'Marks.subject', '=', 'Subject.code')
			->select('Marks.id','Marks.regiNo','Marks.subject','Student.rollNo','Student.firstName','Student.lastName', 'Class.name as class','section.name as section', 'Marks.written','Marks.mcq','Marks.practical','Marks.ca','Marks.total','Marks.grade','Marks.point','Marks.Absent')
			->where('Student.isActive', '=', 'Yes');
			$marks->when(request('class', false), function ($q, $class) { 

			$classc = DB::table('Class')->select('*')->where('id','=',$class)->first();
			return $q->where('Student.class',  $classc->code);
			});
			$marks->when(request('section', false), function ($q, $section) { 
			return $q->where('Student.section', $section);
			});
			$marks->when(request('regiNo', false), function ($q, $regiNo) { 
			return $q->where('Student.regiNo', $regiNo);
			});
			$marks->when(request('exam', false), function ($q, $exam) { 
			return $q->where('Marks.exam', $exam);
			});
			$marks->when(request('subject', false), function ($q, $subject) { 
			return $q->where('Marks.subject',$subject);
			});
			$marks=$marks->paginate(20);
			if(count($marks)<1)
			{
				return response()->json(['error'=>'No result Found!'], 404);
			}else{
				return response()->json($marks,200);
			}
		}
		public function getresult($result_id)
		{
			//$student = Student::find($student_id);
			$marks = DB::table('Marks')
			->join('Student', 'Marks.regiNo', '=', 'Student.regiNo')
			->join('Class', 'Marks.class', '=', 'Class.code')
			->join('section', 'Marks.section', '=', 'section.id')
			->join('Subject', 'Marks.subject', '=', 'Subject.code')
			->select('Marks.id','Marks.regiNo','Student.rollNo','Student.firstName','Student.lastName', 'Class.name as class','section.name as section','Subject.name as subject', 'Marks.written','Marks.mcq','Marks.practical','Marks.ca','Marks.total','Marks.grade','Marks.point','Marks.Absent')
			->where('Student.isActive', '=', 'Yes')
			->where('Marks.id','=',$result_id)
			->first();
			if(!is_null($marks) && count($marks)>0){
				return response()->json($marks,200);
			}else{
				return response()->json(['error'=>'result Not Found'], 404);
			}
		}

		public function postresult()
		{

			$rules=[
			'class_id' => 'required',
			'section_id' => 'required',
			'session' => 'required',
			'regiNo' => 'required',
			'exam_id' => 'required',
			'subject_code' => 'required',
			'written' => 'required',
			'mcq' => 'required',
			'practical' =>'required',
			'ca' =>'required'
			];
			$validator = \Validator::make(Input::all(), $rules);
			if ($validator->fails())
			{
				return response()->json($validator->errors(), 422);
			}
			else 
			{
				$class = DB::table('Class')->select('*')->where('id','=',Input::get('class_id'))->first();

				$subGradeing = Subject::select('gradeSystem')->where('code',Input::get('subject_code'))->where('class','=',$class->code)->first();

				//return response()->json($subGradeing);

				if($subGradeing->gradeSystem=="1")
				{
					$gparules = GPA::select('gpa','grade','markfrom')->where('for',"1")->get();
				}
				else {
					$gparules = GPA::select('gpa','grade','markfrom')->where('for',"2")->get();
				}

				$regiNos = Input::get('regiNo');
				$writtens=Input::get('written');
				$mcqs =Input::get('mcq');
				$practicals=Input::get('practical');
				$cas=Input::get('ca');
				$isabsent = Input::get('absent');

				$isAddbefore = Marks::where('regiNo','=',$regiNos)->where('exam','=',Input::get('exam_id'))->where('subject','=',Input::get('subject_code'))->first();

				if($isAddbefore)
				{

				}
				else 
				{
					$marks = new Marks;
					$marks->class=$class->code;
					$marks->section=Input::get('section_id');
					$marks->shift='Morning';
					$marks->session=trim(Input::get('session'));
					$marks->regiNo=$regiNos;
					$marks->exam=Input::get('exam_id');
					$marks->subject=Input::get('subject_code');
					$marks->written=$writtens;
					$marks->mcq = $mcqs;
					$marks->practical=$practicals;
					$marks->ca=$cas;
					$isExcludeClass=Input::get('class_id');
					$totalmark = $writtens+$mcqs+$practicals+$cas;
					$marks->total=$totalmark;

					foreach ($gparules as $gpa) {
						if ($totalmark >= $gpa->markfrom){
							$marks->grade=$gpa->gpa;
							$marks->point=$gpa->grade;
							break;
						}
					}
					if($isabsent!=="")
					{
						$marks->Absent=$isabsent;
					}
					$marks->save();
					return response()->json(['success'=>"Result save Succesfully.",'id' => $marks->id],200);
				}
			}
		}

		public function putresult($result_id)
		{

			$rules=[
			'class_id' => 'required',
			'section_id' => 'required',
			'session' => 'required',
			'regiNo' => 'required',
			'exam_id' => 'required',
			'subject_code' => 'required',
			'written' => 'required',
			'mcq' => 'required',
			'practical' =>'required',
			'ca' =>'required'
			];
			$validator = \Validator::make(Input::all(), $rules);
			if ($validator->fails())
			{
				return response()->json($validator->errors(), 422);
			}
			else
			{
				$class = DB::table('Class')->select('*')->where('id','=',Input::get('class_id'))->first();

				$subGradeing = Subject::select('gradeSystem')->where('code',Input::get('subject_code'))->where('class','=',$class->code)->first();

				//return response()->json($subGradeing);

				if($subGradeing->gradeSystem=="1")
				{
					$gparules = GPA::select('gpa','grade','markfrom')->where('for',"1")->get();
				}
				else 
				{
					$gparules = GPA::select('gpa','grade','markfrom')->where('for',"2")->get();
				}
				$regiNos = Input::get('regiNo');
				$writtens=Input::get('written');
				$mcqs =Input::get('mcq');
				$practicals=Input::get('practical');
				$cas=Input::get('ca');
				$isabsent = Input::get('absent');
				$marks = Marks::find($result_id);
				$marks->class=$class->code;
				$marks->section=Input::get('section_id');
				$marks->shift='Morning';
				$marks->session=trim(Input::get('session'));
				$marks->regiNo=$regiNos;
				$marks->exam=Input::get('exam_id');
				$marks->subject=Input::get('subject_code');
				$marks->written=$writtens;
				$marks->mcq = $mcqs;
				$marks->practical=$practicals;
				$marks->ca=$cas;
				$isExcludeClass=Input::get('class_id');
				$totalmark = $writtens+$mcqs+$practicals+$cas;
				$marks->total=$totalmark;
				foreach ($gparules as $gpa) 
				{
					if ($totalmark >= $gpa->markfrom)
					{
						$marks->grade=$gpa->gpa;
						$marks->point=$gpa->grade;
						break;
					}
				}
				if($isabsent!=="")
				{
					$marks->Absent=$isabsent;
				}
				$marks->save();
				return response()->json($marks,200);
			}
		}

		public function deleteresult($result_id)
		{
			$result = Marks::find($result_id);
			if(!is_null($result) && $result->count()>0){
				DB::table('Marks')->where('id','=',$result_id)->delete();
				return response()->json(['success'=>"Result deleted Succesfully."],200);
			}else{
				return response()->json(['error'=>'notification Not Found'], 404);

			}
		}
}



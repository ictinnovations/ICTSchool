<?php
namespace App\Http\Controllers;
use DB;
use App\Models\Marks;
use App\Models\Student;
use App\Models\Subject;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class tabulationController extends BaseController {

	public function __construct() {
		//$this->beforeFilter('auth');
		$this->middleware('auth');
	}
	/**
	* Display a listing of the resource.
	*
	* @return Response
	*/
	public function index()
	{
		$formdata = new formfoo;
		$formdata->class="";
		$formdata->section="";
		$formdata->shift="";
		$formdata->exam="";
		$formdata->session="";

		$classes = ClassModel::pluck('name','code');

		//return View::Make('app.tabulationsheet',compact('classes','formdata'));
		return View('app.tabulationsheet',compact('classes','formdata'));

	}


	/**
	* Show the form for creating a new resource.
	*
	* @return Response
	*/
	public function getsheet(Request $request)
	{

		$inputs=$request->all();
		$input=(object)$inputs;
		$subjects = Subject::select('name')->where('class',$input->class)->orderby('code','asc')->get();
		if(count($subjects)<1)
		{
			return  Redirect::to('/tabulation')->withInput($request->all())->with("error","There are not subjects for this class!");
		}

		$students = Student::select('regiNo','firstName','middleName','lastName')->where('class',$input->class)->where('section',$input->section)->where('session',trim($input->session))
		->where('shift',$input->shift)
		->where('isActive','Yes')->get();

		if(count($students)<1)
		{
			return  Redirect::to('/tabulation')->withInput($request->all())->with("error","There are not student for this class!");
		}
		$exam = DB::table('exam')->where('id',$input->exam)->first();
		$merit = DB::table('MeritList')
		->select('regiNo','grade','point','totalNo')
		->where('exam',$input->exam)
		->where('class',$input->class)
		->where('session',trim($input->session))
		->orderBy('point', 'DESC')
		->orderBy('totalNo', 'DESC') ;

		//echo "<pre>";print_r($merit->get());exit;
		if($merit->count()==0)
		{
			return  Redirect::to('/tabulation')->withInput($request->all())->with("error","Marks not submit or result not generate for this exam!");
		}else{
				$merit =$merit->get();
		}
		foreach($students as $student)
		{
			$marks=Marks::select('written','mcq','practical','ca','total','grade','point')->where('regiNo',$student->regiNo)->where('exam',$input->exam)->orderby('subject','asc')->get();
			if(count($marks)<1)
			{
				return  Redirect::to('/tabulation')->withInput($request->all())->with("error","Marks not submited yet!");
			}
			/*$marks = DB::table('Marks')
			->join('MeritList', 'Marks.regiNo', '=', 'MeritList.regiNo')
			->select('Marks.written','Marks.mcq', 'Marks.practical', 'Marks.ca', 'Marks.total', 'Marks.grade', 'Marks.point', 'MeritList.totalNo', 'MeritList.grade as tgrade','MeritList.point as tpoint')
			->where('Marks.regiNo',$student->regiNo)
			->where('Marks.exam', '=',$input->exam)
			->orderby('Marks.subject','asc')
			->get();*/


			$meritdata = new Meritdata();
			$position = 0;
			foreach ($merit as $m) {
				$position++;
				if ($m->regiNo === $student->regiNo) {
					$meritdata->regiNo = $m->regiNo;
					$meritdata->point = $m->point;
					$meritdata->grade = $m->grade;
					$meritdata->position = $position;
					$meritdata->totalNo = $m->totalNo;
					break;
				}
			}

			$student->marks=$marks;
			$student->meritdata=$meritdata;



		}
       //echo "<pre>";print_r($student);
       //echo "<pre>";print_r($merit);

       //exit;
		$cl = ClassModel::Select('name')->where('code',$input->class)->first();
		$input->class=$cl->name;
		$fileName=$input->class.'-'.$input->section.'-'.$input->session.'-'.$input->exam;
		// return $students;
		Excel::create($fileName, function($excel) use($input,$subjects,$students) {

			$excel->sheet('New sheet', function($sheet) use ($input,$subjects,$students) {

				$sheet->loadView('app.excel',compact('subjects','input','students'));

			});

		})->export('xls');
	}
}

<?php

namespace App\Http\Controllers;

use Storage;
use App\Models\GPA;
use App\Models\Marks;
use App\Models\Message;
use App\Models\Student;
use App\Models\Subject;
use App\Models\ClassModel;
use App\Models\Ictcore_fees;
use App\Models\SectionModel;
use Illuminate\Http\Request;
use App\Models\Ictcore_integration;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ictcoreController;

class foobar4
{
}
class markController extends BaseController
{


	public function __construct()
	{
		/*$this->beforeFilter('csrf', array('on'=>'post'));
		$this->beforeFilter('auth');*/
		$this->middleware('auth');
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{

		$classes = ClassModel::select('code', 'name')->orderby('code', 'asc')->get();
		$subjects = Subject::all();
		$class_code = $request->input('class_id');
		if ($class_code != '') {
			$sections = DB::table('section')->where('class_code', $class_code)->get();
		} else {
			$eections = array();
		}

		$section   = $request->input('section');
		$session   = $request->input('session');
		$exam      = $request->input('exam');

		if ($exam  != '' && $class_code != '') {
			$exams = DB::table('exam')->where('id', $exam)->get();
		} else {
			$exams = array();
		}
		//return View::Make('app.markCreate',compact('classes','subjects'));
		return View('app.markCreate', compact('classes', 'subjects', 'class_code', 'section', 'session', 'exam', 'sections', 'exams'));
	}
	public function m_index(Request $request)
	{
		//echo "<pre>";print_r(getsubjecclass('cl1'));exit;
		$classes = ClassModel::select('code', 'name')->orderby('code', 'asc')->get();
		$subjects = Subject::all();
		$class_code = $request->input('class_id');
		if ($class_code != '') {
			$sections = DB::table('section')->where('class_code', $class_code)->get();
		} else {
			$eections = array();
		}
		$section = $request->input('section');
		$session = $request->input('session');
		$exam = $request->input('exam');
		if ($exam != '' && $class_code != '') {
			$exams = DB::table('exam')->where('id', $exam)->get();
		} else {
			$exams = array();
		}
		//return View::Make('app.markCreate',compact('classes','subjects'));
		return View('app.mmarkCreate', compact('classes', 'subjects', 'class_code', 'section', 'session', 'exam', 'sections', 'exams'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create(Request $request)
	{
		$rules = [
			'class'     => 'required',
			'section'   => 'required',
			'shift'     => 'required',
			'session'   => 'required',
			'regiNo'    => 'required',
			'exam'      => 'required',
			'subject'   => 'required',
			'written'   => 'required',
			'mcq'       => 'required',
			'practical' => 'required',
			'ca'        => 'required'
		];
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			return Redirect::to('/mark/create?class_id=' . $request->input('class') . '&section=' . $request->input('section') . '&session=' . $request->input('session') . '&exam=' . $request->input('exam'))->withErrors($validator);
		} else {
			$subGradeing = Subject::select('gradeSystem')->where('code', $request->input('subject'))->where('class', $request->input('class'))->first();
			if ($subGradeing->gradeSystem == "1") {
				$gparules = GPA::select('gpa', 'grade', 'markfrom')->where('for', "1")->get();
			} else if ($subGradeing->gradeSystem == "2") {
				$gparules = GPA::select('gpa', 'grade', 'markfrom')->where('for', "2")->get();
			} else {
				$gparules = GPA::select('gpa', 'grade', 'markfrom')->where('for', $subGradeing->gradeSystem)->get();
			}

			//	 $totalMark = Input
			$len = count($request->input('regiNo'));

			$regiNos    = $request->input('regiNo');
			$writtens   = $request->input('written');
			$mcqs       = $request->input('mcq');
			$practicals = $request->input('practical');
			$cas        = $request->input('ca');
			$isabsent   = $request->input('absent');
			$counter    = 0;

			for ($i = 0; $i < $len; $i++) {
				$isAddbefore = Marks::where('regiNo', '=', $regiNos[$i])->where('exam', '=', $request->input('exam'))->where('subject', '=', $request->input('subject'))->first();
				if ($isAddbefore) {
				} else {
					$marks = new Marks;
					$marks->class = $request->input('class');
					$marks->section = $request->input('section');
					$marks->shift = $request->input('shift');
					$marks->session = trim($request->input('session'));
					$marks->regiNo = $regiNos[$i];
					$marks->exam = $request->input('exam');
					$marks->subject = $request->input('subject');
					$marks->written = $writtens[$i];
					$marks->mcq = $mcqs[$i];
					$marks->practical = $practicals[$i];
					$marks->ca = $cas[$i];
					$isExcludeClass = $request->input('class');
					if ($isExcludeClass == "cl3" ||  $isExcludeClass == "cl4" || $isExcludeClass == "cl5") {
						$totalmark = $writtens[$i] + $mcqs[$i] + $practicals[$i] + $cas[$i];
					} else {
						//$totalmark = ((($writtens[$i]+$mcqs[$i])*80)/100)+$practicals[$i]+$cas[$i];
						$totalmark = $writtens[$i] + $mcqs[$i] + $practicals[$i] + $cas[$i];
					}
					$marks->total = $totalmark;
					//echo "<pre>d";print_r($gparules->toArray());
					foreach ($gparules as $gpa) {

						if ($totalmark >= $gpa->markfrom) {
							$marks->grade = $gpa->gpa;
							$marks->point = $gpa->grade;
							break;
						}
					}

					if ($isabsent[$i] !== "") {
						$marks->Absent = $isabsent[$i];
					}
					//   echo "<pre>";print_r($marks);exit;
					$marks->save();
					$counter++;
				}
			}
			if ($counter == $len) {
				return Redirect::to('/mark/create?class_id=' . $request->input('class') . '&section=' . $request->input('section') . '&session=' . $request->input('session') . '&exam=' . $request->input('exam'))->with("success", $counter . "'s student mark save Succesfully.");
			} else {
				$already = $len - $counter;
				return Redirect::to('/mark/create?class_id=' . $request->input('class') . '&section=' . $request->input('section') . '&session=' . $request->input('session') . '&exam=' . $request->input('exam'))->with("success", $counter . " students mark save Succesfully and " . $already . " Students marks already saved.</strong>");
			}
		}
	}

	public function m_create(Request $request)
	{
		$rules = [
			'class'       => 'required',
			'section'     => 'required',
			'shift'       => 'required',
			'session'     => 'required',
			'regiNo'      => 'required',
			'exam'        => 'required',
			'subject'     => 'required',
			'written'     => 'required',
			'total_marks' => 'required',
			//'mcq' => 'required',
			//'practical' =>'required',
			//'ca' =>'required'
		];
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			return Redirect::to('/mark/m_create?class_id=' . $request->input('class') . '&section=' . $request->input('section') . '&session=' . $request->input('session') . '&exam=' . $request->input('exam'))->withErrors($validator);
		} else {
			//echo "<pre>";
			//////print_r($request->all());
			//exit;
			$total_marks = $request->input('total_marks');
			if ($total_marks == 100) {
				$grade = 1;
			}
			if ($total_marks == 50) {
				$grade = 2;
			}
			if ($total_marks == 75) {
				$grade = 3;
			}
			if ($total_marks == 30) {
				$grade = 4;
			}
			if ($total_marks == 25) {
				$grade = 5;
			}
			if ($total_marks == 20) {
				$grade = 6;
			}
			if ($total_marks == 15) {
				$grade = 7;
			}
			if ($total_marks == 10) {
				$grade = 8;
			}
			if ($total_marks == 5) {
				$grade = 9;
			}
			//$subGradeing = Subject::select('gradeSystem')->where('code',$request->input('subject'))->where('class',$request->input('class'))->first();
			$gparules = GPA::select('gpa', 'grade', 'markfrom')->where('for', $grade)->orderBy('markfrom', 'desc')->get();
			//echo "<pre>";print_r($gparules->toArray());
			/*if($subGradeing->gradeSystem=="1")
			{
				$gparules = GPA::select('gpa','grade','markfrom')->where('for',"1")->get();

			}
			else if($subGradeing->gradeSystem=="2") {
				$gparules = GPA::select('gpa','grade','markfrom')->where('for',"2")->get();
			}else{
				$gparules = GPA::select('gpa','grade','markfrom')->where('for',$subGradeing->gradeSystem)->get();

			}*/

			//	 $totalMark = Input
			$len = count($request->input('regiNo'));

			$regiNos = $request->input('regiNo');
			$writtens = $request->input('written');
			$mcqs = $request->input('mcq');
			$practicals = $request->input('practical');
			$cas = $request->input('ca');
			$isabsent = $request->input('absent');
			$counter = 0;

			for ($i = 0; $i < $len; $i++) {
				$isAddbefore = Marks::where('regiNo', '=', $regiNos[$i])->where('exam', '=', $request->input('exam'))->where('subject', '=', $request->input('subject'))->first();
				if ($isAddbefore) {
				} else {
					$marks = new Marks;
					$marks->class = $request->input('class');
					$marks->section = $request->input('section');
					$marks->shift = $request->input('shift');
					$marks->session = trim($request->input('session'));
					$marks->regiNo = $regiNos[$i];
					$marks->exam = $request->input('exam');
					$marks->subject = $request->input('subject');
					$marks->written = '';
					$marks->mcq = '';
					$marks->practical = '';
					$marks->ca = '';
					$marks->obtain_marks = $writtens[$i];
					$marks->total_marks = $total_marks;
					$marks->ca = '';
					$isExcludeClass = $request->input('class');

					$marks->total = $writtens[$i];
					//echo "<pre>d";print_r($gparules->toArray());
					foreach ($gparules as $gpa) {

						if ($writtens[$i] >= $gpa->markfrom) {
							$marks->grade = $gpa->gpa;
							$marks->point = $gpa->grade;
							break;
						}
					}
					if ($isabsent[$i] !== "") {
						$marks->Absent = $isabsent[$i];
					}
					//echo "<pre>";print_r($marks);exit;
					//$test[] = $marks;
					$marks->save();
					$counter++;
				}
			}
			//echo "<pre>";print_r($test);
			//exit;
			if ($counter == $len) {
				return Redirect::to('/mark/m_create?class_id=' . $request->input('class') . '&section=' . $request->input('section') . '&session=' . $request->input('session') . '&exam=' . $request->input('exam'))->with("success", $counter . "'s student mark save Succesfully.");
			} else {
				$already = $len - $counter;
				return Redirect::to('/mark/m_create?class_id=' . $request->input('class') . '&section=' . $request->input('section') . '&session=' . $request->input('session') . '&exam=' . $request->input('exam'))->with("success", $counter . " students mark save Succesfully and " . $already . " Students marks already saved.</strong>");
			}
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

		$formdata = new foobar4;
		$formdata->class = "";
		$formdata->section = "";
		$formdata->shift = "";
		$formdata->session = "";
		$formdata->subject = "";
		$formdata->exam = "";
		$classes = ClassModel::select('code', 'name')->orderby('code', 'asc')->get();
		//$subjects = Subject::lists('name','code');
		$marks = array();


		//$formdata["class"]="";
		//return View::Make('app.markList',compact('classes','marks','formdata'));
		return View('app.markList', compact('classes', 'marks', 'formdata'));
	}
	public function m_show()
	{

		$formdata = new foobar4;
		$formdata->class = "";
		$formdata->section = "";
		$formdata->shift = "";
		$formdata->session = "";
		$formdata->subject = "";
		$formdata->exam = "";
		$classes = ClassModel::select('code', 'name')->orderby('code', 'asc')->get();
		//$subjects = Subject::lists('name','code');
		$marks = array();


		//$formdata["class"]="";
		//return View::Make('app.markList',compact('classes','marks','formdata'));
		return View('app.mmarkList', compact('classes', 'marks', 'formdata'));
	}

	public function getlist(Request $request)
	{
		$rules = [
			'class' => 'required',
			'section' => 'required',
			'shift' => 'required',
			'session' => 'required',
			'exam' => 'required',
			'subject' => 'required',

		];
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			return Redirect::to('/mark/list/')->withErrors($validator);
		} else {
			$classes2 = ClassModel::orderby('code', 'asc')->pluck('name', 'code');
			$subjects = Subject::where('class', $request->input('class'))->pluck('name', 'code');
			$marks =	DB::table('Marks')
				->join('Student', 'Marks.regiNo', '=', 'Student.regiNo')
				->select('Marks.id', 'Marks.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Marks.written', 'Marks.mcq', 'Marks.practical', 'Marks.ca', 'Marks.total', 'Marks.grade', 'Marks.point', 'Marks.Absent')
				->where('Student.isActive', '=', 'Yes')
				->where('Student.class', '=', $request->input('class'))
				->where('Marks.class', '=', $request->input('class'))
				->where('Marks.section', '=', $request->input('section'))
				//->Where('Marks.shift','=',$request->input('shift'))
				->where('Marks.session', '=', trim($request->input('session')))
				->where('Marks.subject', '=', $request->input('subject'))
				->where('Marks.exam', '=', $request->input('exam'))
				->get();

			$formdata = new foobar4;
			$formdata->class = $request->input('class');
			$formdata->section = $request->input('section');
			$formdata->shift = $request->input('shift');
			$formdata->session = $request->input('session');
			$formdata->subject = $request->input('subject');
			$formdata->exam = $request->input('exam');

			if (count($marks) == 0) {
				$noResult = array("noresult" => "No Results Found!!");
				//return Redirect::to('/mark/list')->with("noresult","No Results Found!!");
				//return View::Make('app.markList',compact('classes2','subjects','marks','noResult','formdata'));
				return View('app.markList', compact('classes2', 'subjects', 'marks', 'noResult', 'formdata'));
			}

			//return View::Make('app.markList',compact('classes2','subjects','marks','formdata'));
			return View('app.markList', compact('classes2', 'subjects', 'marks', 'formdata'));
		}
	}


	public function m_getlist(Request $request)
	{
		$rules = [
			'class' => 'required',
			'section' => 'required',
			'shift' => 'required',
			'session' => 'required',
			'exam' => 'required',
			'subject' => 'required',

		];
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			return Redirect::to('/mark/m_list/')->withErrors($validator);
		} else {
			$classes2 = ClassModel::orderby('code', 'asc')->pluck('name', 'code');
			$subjects = Subject::where('class', $request->input('class'))->pluck('name', 'code');
			$marks    =	DB::table('Marks')
				->join('Student', 'Marks.regiNo', '=', 'Student.regiNo')
				->select('Marks.id', 'Marks.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Marks.written', 'Marks.mcq', 'Marks.practical', 'Marks.ca', 'Marks.total', 'Marks.obtain_marks', 'Marks.total_marks', 'Marks.grade', 'Marks.point', 'Marks.Absent')
				->where('Student.isActive', '=', 'Yes')
				->where('Student.class', '=', $request->input('class'))
				->where('Marks.class', '=', $request->input('class'))
				->where('Marks.section', '=', $request->input('section'))
				//->Where('Marks.shift','=',$request->input('shift'))
				->where('Marks.session', '=', trim($request->input('session')))
				->where('Marks.subject', '=', $request->input('subject'))
				->where('Marks.exam', '=', $request->input('exam'))
				->get();

			$formdata          = new foobar4;
			$formdata->class   = $request->input('class');
			$formdata->section = $request->input('section');
			$formdata->shift   = $request->input('shift');
			$formdata->session = $request->input('session');
			$formdata->subject = $request->input('subject');
			$formdata->exam    = $request->input('exam');

			if (count($marks) == 0) {
				$noResult = array("noresult" => "No Results Found!!");
				//return Redirect::to('/mark/list')->with("noresult","No Results Found!!");
				//return View::Make('app.markList',compact('classes2','subjects','marks','noResult','formdata'));
				return View('app.mmarkList', compact('classes2', 'subjects', 'marks', 'noResult', 'formdata'));
			}

			//return View::Make('app.markList',compact('classes2','subjects','marks','formdata'));
			return View('app.mmarkList', compact('classes2', 'subjects', 'marks', 'formdata'));
		}
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$marks =	DB::table('Marks')
			->join('Student', 'Marks.regiNo', '=', 'Student.regiNo')
			->select('Marks.id', 'Marks.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Marks.subject', 'Marks.class', 'Marks.written', 'Marks.mcq', 'Marks.practical', 'Marks.ca', 'Marks.total', 'Marks.grade', 'Marks.point', 'Marks.Absent')
			->where('Marks.id', '=', $id)
			->first();

		//return View::Make('app.markEdit',compact('marks'));
		return View('app.markEdit', compact('marks'));
	}
	public function m_edit($id)
	{
		$marks =	DB::table('Marks')
			->join('Student', 'Marks.regiNo', '=', 'Student.regiNo')
			->select('Marks.id', 'Marks.regiNo', 'Marks.obtain_marks', 'Marks.total_marks', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Marks.subject', 'Marks.class', 'Marks.written', 'Marks.mcq', 'Marks.practical', 'Marks.ca', 'Marks.total', 'Marks.grade', 'Marks.point', 'Marks.Absent')
			->where('Marks.id', '=', $id)
			->first();

		//return View::Make('app.markEdit',compact('marks'));
		return View('app.mmarkEdit', compact('marks'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request)
	{
		$rules = [
			'written' => 'required',
			'mcq' => 'required',
			'practical' => 'required',
			'ca' => 'required',
			'subject' => 'required',
			'class' => 'required'
		];
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			return Redirect::to('/mark/edit/' . $request->input('id'))->withErrors($validator);
		} else {

			$marks = Marks::find($request->input('id'));
			//get subject grading system
			$subGradeing = Subject::select('gradeSystem', 'class')->where('code', $request->input('subject'))->where('class', $request->input('class'))->first();
			if ($subGradeing->gradeSystem == "1") {
				$gparules = GPA::select('gpa', 'grade', 'markfrom')->where('for', "1")->get();
			} else {
				$gparules = GPA::select('gpa', 'grade', 'markfrom')->where('for', "2")->get();
			}
			//end
			$marks->written = $request->input('written');
			$marks->mcq = $request->input('mcq');
			$marks->practical = $request->input('practical');
			$marks->ca = $request->input('ca');

			$isExcludeClass = $subGradeing->class;
			if ($isExcludeClass == "cl3" ||  $isExcludeClass == "cl4" || $isExcludeClass == "cl5") {
				$totalmark = $request->input('written') + $request->input('mcq') + $request->input('practical') + $request->input('ca');
			} else {
				//$totalmark = ((($request->input('written')+$request->input('mcq'))*80)/100)+$request->input('practical')+$request->input('ca');
				$totalmark = $request->input('written') + $request->input('mcq') + $request->input('practical') + $request->input('ca');
			}
			$marks->total = $totalmark;
			foreach ($gparules as $gpa) {
				if ($totalmark >= $gpa->markfrom) {
					$marks->grade = $gpa->gpa;
					$marks->point = $gpa->grade;
					break;
				}
			}
			$marks->Absent = $request->input('Absent');

			$marks->save();
			return Redirect::to('/mark/list')->with("success", "Marks Update Sucessfully.");
		}
	}
	public function m_update(Request $request)
	{
		$rules = [
			'written' => 'required',
			//'mcq' => 'required',
			//'practical' =>'required',
			///'ca' =>'required',
			'subject' => 'required',
			'class' => 'required',
			'total_marks' => 'required',
		];
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			return Redirect::to('/mark/m_edit/' . $request->input('id'))->withErrors($validator);
		} else {
			$marks = Marks::find($request->input('id'));
			//get subject grading system
			//$subGradeing = Subject::select('gradeSystem','class')->where('code',$request->input('subject'))->where('class',$request->input('class'))->first();
			$total_marks = $request->input('total_marks');
			if ($total_marks == 100) {
				$grade = 1;
			}
			if ($total_marks == 50) {
				$grade = 2;
			}
			if ($total_marks == 75) {
				$grade = 3;
			}
			if ($total_marks == 30) {
				$grade = 4;
			}
			if ($total_marks == 25) {
				$grade = 5;
			}
			if ($total_marks == 20) {
				$grade = 6;
			}
			if ($total_marks == 15) {
				$grade = 7;
			}
			if ($total_marks == 10) {
				$grade = 8;
			}
			if ($total_marks == 5) {
				$grade = 9;
			}
			//$subGradeing = Subject::select('gradeSystem')->where('code',$request->input('subject'))->where('class',$request->input('class'))->first();
			$gparules = GPA::select('gpa', 'grade', 'markfrom')->where('for', $grade)->orderBy('markfrom', 'desc')->get();
			//echo "<pre>";print_r($gparules->toArray());

			//end
			$marks->obtain_marks = $request->input('written');
			//$marks->total = $request->input('written');
			$marks->total_marks = $request->input('total_marks');
			//$marks->ca=$request->input('ca');


			$marks->total = $request->input('written');
			foreach ($gparules as $gpa) {
				if ($request->input('written') >= $gpa->markfrom) {
					$marks->grade = $gpa->gpa;
					$marks->point = $gpa->grade;
					break;
				}
			}
			$marks->Absent = $request->input('Absent');

			$marks->save();
			return Redirect::to('/mark/m_list')->with("success", "Marks Update Sucessfully.");
		}
	}

	public function getForMarksjoin($class)
	{
		$sections  = SectionModel::select('id', 'name')->where('class_code', '=', $class)->get();
		//$sections['subjects'] = Subject::select('id','name')->where('class','=',$class)->get();

		/* $students=	DB::table('Student')
		->leftjoin('Marks', 'Student.regiNo', '=', 'Marks.regiNo')
		->select('Student.id', 'Student.regiNo','Student.rollNo','Student.firstName','Student.middleName','Student.lastName',
		'Student.discount_id','Marks.written','Marks.written','Marks.mcq','Marks.practical','Marks.ca','Marks.Absent')
		->where('Student.section','=',$section)->where('Student.shift','=',$shift)->where('Student.session','=',$session)->get();

	*/
		//print_r(getsubjecclass($class)['sub_name']);
		//echo count(getsubjecclass($class)['sub_name']);

		//for($i=0;$i<count(getsubjecclass($class)['sub_name']);$i++){
		//$subjecname .= getsubjecclass($class)['sub_name'][$i]['name'];
		//}
		//echo $subjecname;
		//if(count(getsubjecclass($class)['sub_name']))



		$output = '';
		foreach ($sections as $section) {
			$subjecname = '';
			for ($i = 0; $i < count(getsubjecclass($class)['sub_name']); $i++) {

				$url = url('/') . '/create/marks?sub_id=' . getsubjecclass($class)['sub_name'][$i]['id'] . '&class=' . $class . '&section=' . $section->id;
				$link = "'" . $url . "','enter marks','width=1500','height=500'";
				$subjecname .= '&nbsp;  ';
				$subjecname .= '<a href="#' . $url . '" onclick="window.open(' . "$link" . '); 
	              return false;">' . getsubjecclass($class)['sub_name'][$i]['name'] . '</a>';
			}
			$output .= '<tr><td>' . $section->name . '</td><td>' . $subjecname . '</td></tr>';
		}
		return $output;
	}

	public function createmarks(Request $request)
	{

		//echo "<pre>";print_r(getsubjecclass('cl1'));exit;
		$class = ClassModel::select('id', 'name')->where('code', $request->get('class'))->first();

		$exams = DB::table('exam')->where('section_id', $request->get('section'))->where('class_id', $class->id)->get();
		$param1 = $request->get('exam');
		$param2 = $request->get('total_marks');
		$session = $request->get('session');
		$subject_id = $request->get('sub_id');
		$class_code = $request->get('class');
		$section = $request->get('section');
		$students = array();
		if ($request->get('show')) {

			$students = DB::table('Student')
				//->leftjoin('Marks','Student.regiNo','=','Marks.regiNo')
				->leftJoin('Marks', function ($join) use ($param1, $subject_id) {
					$join->on('Student.regiNo', '=', 'Marks.regiNo');
					$join->on('Marks.exam', '=', DB::raw("'" . $param1 . "'"));
					$join->on('Marks.subject', '=', DB::raw("'" . $subject_id . "'"));
				})

				->select(DB::raw("CONCAT(Student.firstName,' ',Student.lastName) as fullname"), 'Student.regiNo as student_id', 'Marks.*')
				//->where('Marks.exam',$request->get('exam'))
				->where('Student.session', get_current_session()->id)
				->where('Student.class', $request->get('class'))
				->where('Student.section', $request->get('section'))
				->groupBy('Student.regiNo')
				->get();

			//echo "<pre>hgg";print_r($students);exit;
		}
		//return View::Make('app.markCreate',compact('classes','subjects'));
		return View('app.markscreate', compact('class', 'exams', 'subject_id', 'students', 'param1', 'param2', 'session', 'class_code', 'section'));
	}


	public function newcreate(Request $request)
	{
		//echo "<pre>";print_r($request->input('sms'));exit;



		$rules = [
			'class'       => 'required',
			'section'     => 'required',
			'shift'       => 'required',
			'session'     => 'required',
			'regiNo'      => 'required',
			'exam'        => 'required',
			'subject'     => 'required',
			'written'     => 'required',
			'total_marks' => 'required',
		];
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			return Redirect::to('/create/marks?class=' . $request->input('class') . '&section=' . $request->input('section') . '&session=' . $request->input('session') . '&exam=' . $request->input('exam') . '&sub_id=' . $request->input('subject'))->withErrors($validator);
		} else {
			$getexam       = DB::table('exam')->where('id', $request->input('exam'))->first();
			$exam_name = $getexam->type;
			$total_marks   = $request->input('total_marks');
			if ($total_marks == 100) {
				$grade = 1;
			}
			if ($total_marks == 50) {
				$grade = 2;
			}
			if ($total_marks == 75) {
				$grade = 3;
			}
			if ($total_marks == 30) {
				$grade = 4;
			}
			if ($total_marks == 25) {
				$grade = 5;
			}
			if ($total_marks == 20) {
				$grade = 6;
			}
			if ($total_marks == 15) {
				$grade = 7;
			}
			if ($total_marks == 10) {
				$grade = 8;
			}
			if ($total_marks == 5) {
				$grade = 9;
			}
			$gparules = GPA::select('gpa', 'grade', 'markfrom')->where('for', $grade)->orderBy('markfrom', 'desc')->get();

			$len = count($request->input('regiNo'));

			$regiNos = $request->input('regiNo');
			$writtens = $request->input('written');
			//$mcqs =$request->input('mcq');
			//$practicals=$request->input('practical');
			//$cas=$request->input('ca');
			$isabsent = $request->input('absent');
			$sms = $request->input('sms');
			//print_r($isabsent);exit;
			$counter  = 0;

			for ($i  = 0; $i < $len; $i++) {
				$isAddbefore = Marks::where('regiNo', '=', $regiNos[$i])->where('exam', '=', $request->input('exam'))->where('subject', '=', $request->input('subject'))->first();

				if ($isAddbefore) {
					$marks = Marks::find($isAddbefore->id);
				} else {
					$marks = new Marks;
				}
				$marks->class = $request->input('class');
				$marks->section = $request->input('section');
				$marks->shift = $request->input('shift');
				$marks->session = trim($request->input('session'));
				$marks->regiNo = $regiNos[$i];
				$marks->exam = $request->input('exam');
				$marks->subject = $request->input('subject');
				$marks->written = '';
				$marks->mcq = '';
				$marks->practical = '';
				$marks->ca = '';
				$marks->obtain_marks = $writtens[$i];
				$marks->total_marks = $total_marks;
				$marks->ca = '';
				$isExcludeClass = $request->input('class');

				$marks->total = $writtens[$i];
				//echo "<pre>d";print_r($gparules->toArray());
				foreach ($gparules as $gpa) {

					if ($writtens[$i] >= $gpa->markfrom) {
						$marks->grade = $gpa->gpa;
						$marks->point = $gpa->grade;
						break;
					}
				}
				if ($isabsent[$regiNos[$i]] == "yes") {
					$marks->Absent = $isabsent[$regiNos[$i]];
					$writtens[$i]  = 0;
					$marks->total = $writtens[$i];
					$marks->obtain_marks = $writtens[$i];
				}
				//echo "<pre>";print_r($marks);exit;
				//$test[] = $marks;
				if ($marks->save()) {
					if ($sms[$regiNos[$i]] == "yes") {
						$send_sms = $this->send_sms($regiNos[$i], $total_marks, $writtens[$i], $request->input('subject'), $exam_name);
					}
					$counter++;
				}
				//}


			}
			//echo "<pre>";print_r($test);
			//exit;
			if ($counter == $len) {
				return Redirect::to('/mark/m_create?class_id=' . $request->input('class') . '&section=' . $request->input('section') . '&session=' . $request->input('session') . '&exam=' . $request->input('exam'))->with("success", $counter . "'s student mark save Succesfully.");
			} else {
				$already = $len - $counter;
				return Redirect::to('/mark/m_create?class_id=' . $request->input('class') . '&section=' . $request->input('section') . '&session=' . $request->input('session') . '&exam=' . $request->input('exam'))->with("success", $counter . " students mark save Succesfully and " . $already . " Students marks already saved.</strong>");
			}
		}
	}


	public function send_sms($regiNo, $total, $obtain, $sub, $exam_name)
	{


		$student = DB::table('Student')->where('regiNo', $regiNo)->first();
		$subject = DB::table('Subject')->where('id', $sub)->first();

		$phone   = $student->fatherCellNo;

		//$message = 'your Child '.$student->firstName.' '.$student->lastName. ' subject '.$subject->name.' obtains marks '.$obtain.' out of '.$total.' marks ';

		$col_msg = DB::table('message')->where('name', 'mark_notification')->first();
		if (empty($col_msg)) {
			$message = 'your Child ' . $student->firstName . ' ' . $student->lastName . ' subject ' . $subject->name . ' obtains marks ' . $obtain . ' out of ' . $total . ' marks ';
		} else {
			$message = $col_msg->description;
			$msg1 = str_replace("[student_name]", $student->firstName . '' . $student->lastName, $message);
			$msg2 = str_replace("[marks]", $obtain, $msg1);
			$msg3 = str_replace("[outoff]", $total, $msg2);
			$msg4 = str_replace("[subjects]", $subject->name, $msg3);
			$message = str_replace("[exam]", $exam_name, $msg4);
		}




		$body    = $message;
		$ict     = new ictcoreController();
		$i       = 0;
		$attendance_noti     = DB::table('notification_type')->where('notification', 'fess')->first();
		$ictcore_fees        = Ictcore_fees::select("*")->first();
		$ictcore_integration = Ictcore_integration::select("*")->where('type', 'sms');
		if ($ictcore_integration->count() > 0) {
			$ictcore_integration = $ictcore_integration->first();
		} else {
			return 404;
		}
		$contacts = array();
		$contacts1 = array();
		$i = 0;
		if (preg_match("~^0\d+$~", $phone)) {
			$to = preg_replace('/0/', '92', $phone, 1);
		} else {
			$to = $phone;
		}
		if (strlen(trim($to)) == 12) {
			$contacts = $to;
		}

		$msg = $body;
		if ($ictcore_integration->method != 'ictcore') {
			$snd_msg  = $ict->verification_number_telenor_sms($to, $msg, 'SidraSchool', $ictcore_integration->ictcore_user, $ictcore_integration->ictcore_password, 'sms');
		} else {
			$send_msg_ictcore = sendmesssageictcore($student->firstName, $student->lastName, $to, $msg, 'marks');
		}

		return 200;
	}

	public function template()
	{

		$message = Message::where('name', 'mark_notification')->first();
		if (!empty($message)) {
			return Redirect::to('/message/edit/' . $message->id);
		}
		return View('app.markstemplate');
	}
	public function edittemplate($id)
	{
		$message = Message::find($id);
		//return View::Make('app.classEdit',compact('class'));
		return View('app.messageEdit', compact('message'));
	}
}

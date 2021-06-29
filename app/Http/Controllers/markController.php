<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Student;
use App\ClassModel;
use App\Subject;
use App\SectionModel;
use App\GPA;
use App\Marks;
use App\Ictcore_fees;
use App\Ictcore_integration;
use App\Message;
use Storage;
use DB;
use App\Http\Controllers\ictcoreController;
Class foobar4{

}
class markController extends BaseController {


	public function __construct() {
		/*$this->beforeFilter('csrf', array('on'=>'post'));
		$this->beforeFilter('auth');*/
		$this->middleware('auth');
	}
	/**
	* Display a listing of the resource.
	*
	* @return Response
	*/
	public function index()
	{
		
		$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
		$subjects = Subject::all();
		$class_code =Input::get('class_id');
		if($class_code !=''){
           $sections = DB::table('section')->where('class_code',$class_code)->get();
		}else{
			$eections = array();
		}

		$section   = Input::get('section');
		$session   = Input::get('session');
		$exam      = Input::get('exam');

		if($exam  !='' && $class_code!=''){
			$exams = DB::table('exam')->where('id',$exam)->get();
		}else{
			$exams = array();
		}
		//return View::Make('app.markCreate',compact('classes','subjects'));
		return View('app.markCreate',compact('classes','subjects','class_code','section','session','exam','sections','exams'));
	}
	public function m_index()
	{
		//echo "<pre>";print_r(getsubjecclass('cl1'));exit;
		$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
		$subjects = Subject::all();
		$class_code =Input::get('class_id');
		if($class_code !=''){
           $sections = DB::table('section')->where('class_code',$class_code)->get();
		}else{
			$eections = array();
		}
		$section =Input::get('section');
		$session =Input::get('session');
		$exam =Input::get('exam');
		if($exam !='' && $class_code!=''){
			$exams = DB::table('exam')->where('id',$exam)->get();
		}else{
			$exams = array();
		}
		//return View::Make('app.markCreate',compact('classes','subjects'));
		return View('app.mmarkCreate',compact('classes','subjects','class_code','section','session','exam','sections','exams'));
	}


	/**
	* Show the form for creating a new resource.
	*
	* @return Response
	*/
	public function create()
	{
		$rules=[
			'class'     => 'required',
			'section'   => 'required',
			'shift'     => 'required',
			'session'   => 'required',
			'regiNo'    => 'required',
			'exam'      => 'required',
			'subject'   => 'required',
			'written'   => 'required',
			'mcq'       => 'required',
			'practical' =>'required',
			'ca'        =>'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/mark/create?class_id='.Input::get('class').'&section='.Input::get('section').'&session='.Input::get('session').'&exam='.Input::get('exam'))->withErrors($validator);
		}
		else {
			$subGradeing = Subject::select('gradeSystem')->where('code',Input::get('subject'))->where('class',Input::get('class'))->first();
			if($subGradeing->gradeSystem=="1")
			{
				$gparules = GPA::select('gpa','grade','markfrom')->where('for',"1")->get();

			}
			else if($subGradeing->gradeSystem=="2") {
				$gparules = GPA::select('gpa','grade','markfrom')->where('for',"2")->get();
			}else{
				$gparules = GPA::select('gpa','grade','markfrom')->where('for',$subGradeing->gradeSystem)->get();

			}

			//	 $totalMark = Input
			$len = count(Input::get('regiNo'));

			$regiNos    = Input::get('regiNo');
			$writtens   = Input::get('written');
			$mcqs       = Input::get('mcq');
			$practicals = Input::get('practical');
			$cas        = Input::get('ca');
			$isabsent   = Input::get('absent');
			$counter    = 0;

			for ( $i=0; $i< $len;$i++) {
				$isAddbefore = Marks::where('regiNo','=',$regiNos[$i])->where('exam','=',Input::get('exam'))->where('subject','=',Input::get('subject'))->first();
				if($isAddbefore)
				{

				}
				else {
					$marks = new Marks;
					$marks->class = Input::get('class');
					$marks->section = Input::get('section');
					$marks->shift = Input::get('shift');
					$marks->session = trim(Input::get('session'));
					$marks->regiNo = $regiNos[$i];
					$marks->exam = Input::get('exam');
					$marks->subject = Input::get('subject');
					$marks->written = $writtens[$i];
					$marks->mcq = $mcqs[$i];
					$marks->practical = $practicals[$i];
					$marks->ca = $cas[$i];
					$isExcludeClass = Input::get('class');
					if($isExcludeClass=="cl3" ||  $isExcludeClass=="cl4" || $isExcludeClass=="cl5")
					{
						$totalmark = $writtens[$i]+$mcqs[$i]+$practicals[$i]+$cas[$i];
					}
					else
					{
						//$totalmark = ((($writtens[$i]+$mcqs[$i])*80)/100)+$practicals[$i]+$cas[$i];
						$totalmark = $writtens[$i]+$mcqs[$i]+$practicals[$i]+$cas[$i];
					}
					$marks->total=$totalmark;
					//echo "<pre>d";print_r($gparules->toArray());
					foreach ($gparules as $gpa) {

						if ($totalmark >= $gpa->markfrom){
							$marks->grade = $gpa->gpa;
							$marks->point = $gpa->grade;
							break;
						}
					}

					if($isabsent[$i]!== "")
					{
						$marks->Absent = $isabsent[$i];
					}
                    //   echo "<pre>";print_r($marks);exit;
					$marks->save();
					$counter++;
				}
			}
			if($counter==$len)
			{
				return Redirect::to('/mark/create?class_id='.Input::get('class').'&section='.Input::get('section').'&session='.Input::get('session').'&exam='.Input::get('exam'))->with("success",$counter."'s student mark save Succesfully.");
			}
			else {
				$already=$len-$counter;
				return Redirect::to('/mark/create?class_id='.Input::get('class').'&section='.Input::get('section').'&session='.Input::get('session').'&exam='.Input::get('exam'))->with("success",$counter." students mark save Succesfully and ".$already." Students marks already saved.</strong>");
			}
		}
	}

    public function m_create()
	{
		$rules=[
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
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/mark/m_create?class_id='.Input::get('class').'&section='.Input::get('section').'&session='.Input::get('session').'&exam='.Input::get('exam'))->withErrors($validator);
		}
		else {
			//echo "<pre>";
			//////print_r(Input::all());
			//exit;
			$total_marks = Input::get('total_marks');
			if($total_marks==100){
				$grade = 1;
			}
			if($total_marks==50){
				$grade = 2;
			}
			if($total_marks==75){
				$grade = 3;
			}
			if($total_marks==30){
				$grade = 4;
			}
			if($total_marks==25){
				$grade = 5;
			}
			if($total_marks==20){
				$grade = 6;
			}
			if($total_marks==15){
				$grade = 7;
			}
			if($total_marks==10){
				$grade = 8;
			}
			if($total_marks==5){
				$grade = 9;
			}
			//$subGradeing = Subject::select('gradeSystem')->where('code',Input::get('subject'))->where('class',Input::get('class'))->first();
			$gparules = GPA::select('gpa','grade','markfrom')->where('for',$grade )->orderBy('markfrom','desc')->get();
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
			$len = count(Input::get('regiNo'));

			$regiNos = Input::get('regiNo');
			$writtens=Input::get('written');
			$mcqs =Input::get('mcq');
			$practicals=Input::get('practical');
			$cas=Input::get('ca');
			$isabsent = Input::get('absent');
			$counter=0;

			for ( $i=0; $i< $len;$i++) {
				$isAddbefore = Marks::where('regiNo','=',$regiNos[$i])->where('exam','=',Input::get('exam'))->where('subject','=',Input::get('subject'))->first();
				if($isAddbefore)
				{

				}
				else {
					$marks = new Marks;
					$marks->class = Input::get('class');
					$marks->section = Input::get('section');
					$marks->shift = Input::get('shift');
					$marks->session = trim(Input::get('session'));
					$marks->regiNo = $regiNos[$i];
					$marks->exam = Input::get('exam');
					$marks->subject = Input::get('subject');
					$marks->written = '';
					$marks->mcq = '';
					$marks->practical = '';
					$marks->ca = '';
					$marks->obtain_marks = $writtens[$i];
					$marks->total_marks = $total_marks;
					$marks->ca = '';
					$isExcludeClass = Input::get('class');
					
					$marks->total=$writtens[$i];
					//echo "<pre>d";print_r($gparules->toArray());
					foreach ($gparules as $gpa) {

						if ($writtens[$i] >= $gpa->markfrom){
							$marks->grade = $gpa->gpa;
							$marks->point = $gpa->grade;
							break;
						}
					}
					if($isabsent[$i]!== "")
					{
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
			if($counter==$len)
			{
				return Redirect::to('/mark/m_create?class_id='.Input::get('class').'&section='.Input::get('section').'&session='.Input::get('session').'&exam='.Input::get('exam'))->with("success",$counter."'s student mark save Succesfully.");
			}
			else {
				$already=$len-$counter;
				return Redirect::to('/mark/m_create?class_id='.Input::get('class').'&section='.Input::get('section').'&session='.Input::get('session').'&exam='.Input::get('exam'))->with("success",$counter." students mark save Succesfully and ".$already." Students marks already saved.</strong>");
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
		$formdata->class="";
		$formdata->section="";
		$formdata->shift="";
		$formdata->session="";
		$formdata->subject="";
		$formdata->exam="";
		$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
		//$subjects = Subject::lists('name','code');
		$marks=array();


		//$formdata["class"]="";
		//return View::Make('app.markList',compact('classes','marks','formdata'));
		return View('app.markList',compact('classes','marks','formdata'));
	}
	public function m_show()
	{

		$formdata = new foobar4;
		$formdata->class="";
		$formdata->section="";
		$formdata->shift="";
		$formdata->session="";
		$formdata->subject="";
		$formdata->exam="";
		$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
		//$subjects = Subject::lists('name','code');
		$marks=array();


		//$formdata["class"]="";
		//return View::Make('app.markList',compact('classes','marks','formdata'));
		return View('app.mmarkList',compact('classes','marks','formdata'));
	}

	public function getlist()
	{
		$rules=[
			'class' => 'required',
			'section' => 'required',
			'shift' => 'required',
			'session' => 'required',
			'exam' => 'required',
			'subject' => 'required',

		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/mark/list/')->withErrors($validator);
		}
		else {
			$classes2 = ClassModel::orderby('code','asc')->pluck('name','code');
			$subjects = Subject::where('class',Input::get('class'))->pluck('name','code');
			$marks=	DB::table('Marks')
			->join('Student', 'Marks.regiNo', '=', 'Student.regiNo')
			->select('Marks.id','Marks.regiNo','Student.rollNo', 'Student.firstName','Student.middleName','Student.lastName', 'Marks.written','Marks.mcq','Marks.practical','Marks.ca','Marks.total','Marks.grade','Marks.point','Marks.Absent')
			->where('Student.isActive', '=', 'Yes')
			->where('Student.class','=',Input::get('class'))
			->where('Marks.class','=',Input::get('class'))
			->where('Marks.section','=',Input::get('section'))
		         //->Where('Marks.shift','=',Input::get('shift'))
			->where('Marks.session','=',trim(Input::get('session')))
			->where('Marks.subject','=',Input::get('subject'))
			->where('Marks.exam','=',Input::get('exam'))
			->get();

			$formdata = new foobar4;
			$formdata->class=Input::get('class');
			$formdata->section=Input::get('section');
			$formdata->shift=Input::get('shift');
			$formdata->session=Input::get('session');
			$formdata->subject=Input::get('subject');
			$formdata->exam=Input::get('exam');

			if(count($marks)==0)
			{
				$noResult = array("noresult"=>"No Results Found!!");
				//return Redirect::to('/mark/list')->with("noresult","No Results Found!!");
				//return View::Make('app.markList',compact('classes2','subjects','marks','noResult','formdata'));
				return View('app.markList',compact('classes2','subjects','marks','noResult','formdata'));
			}

			//return View::Make('app.markList',compact('classes2','subjects','marks','formdata'));
			return View('app.markList',compact('classes2','subjects','marks','formdata'));
		}
	}


	public function m_getlist()
	{
		$rules=[
			'class' => 'required',
			'section' => 'required',
			'shift' => 'required',
			'session' => 'required',
			'exam' => 'required',
			'subject' => 'required',

		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/mark/m_list/')->withErrors($validator);
		}
		else {
			$classes2 = ClassModel::orderby('code','asc')->pluck('name','code');
			$subjects = Subject::where('class',Input::get('class'))->pluck('name','code');
			$marks    =	DB::table('Marks')
			->join('Student', 'Marks.regiNo', '=', 'Student.regiNo')
			->select('Marks.id','Marks.regiNo','Student.rollNo', 'Student.firstName','Student.middleName','Student.lastName', 'Marks.written','Marks.mcq','Marks.practical','Marks.ca','Marks.total','Marks.obtain_marks','Marks.total_marks','Marks.grade','Marks.point','Marks.Absent')
			->where('Student.isActive', '=', 'Yes')
			->where('Student.class','=',Input::get('class'))
			->where('Marks.class','=',Input::get('class'))
			->where('Marks.section','=',Input::get('section'))
		         //->Where('Marks.shift','=',Input::get('shift'))
			->where('Marks.session','=',trim(Input::get('session')))
			->where('Marks.subject','=',Input::get('subject'))
			->where('Marks.exam','=',Input::get('exam'))
			->get();

			$formdata          = new foobar4;
			$formdata->class   = Input::get('class');
			$formdata->section = Input::get('section');
			$formdata->shift   = Input::get('shift');
			$formdata->session = Input::get('session');
			$formdata->subject = Input::get('subject');
			$formdata->exam    = Input::get('exam');

			if(count($marks)==0)
			{
				$noResult = array("noresult"=>"No Results Found!!");
				//return Redirect::to('/mark/list')->with("noresult","No Results Found!!");
				//return View::Make('app.markList',compact('classes2','subjects','marks','noResult','formdata'));
				return View('app.mmarkList',compact('classes2','subjects','marks','noResult','formdata'));
			}

			//return View::Make('app.markList',compact('classes2','subjects','marks','formdata'));
			return View('app.mmarkList',compact('classes2','subjects','marks','formdata'));
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
		$marks=	DB::table('Marks')
		->join('Student', 'Marks.regiNo', '=', 'Student.regiNo')
		->select('Marks.id','Marks.regiNo','Student.rollNo', 'Student.firstName','Student.middleName','Student.lastName','Marks.subject','Marks.class', 'Marks.written','Marks.mcq','Marks.practical','Marks.ca','Marks.total','Marks.grade','Marks.point','Marks.Absent')
		->where('Marks.id','=',$id)
		->first();

		//return View::Make('app.markEdit',compact('marks'));
		return View('app.markEdit',compact('marks'));


	}
	public function m_edit($id)
	{
		$marks=	DB::table('Marks')
		->join('Student', 'Marks.regiNo', '=', 'Student.regiNo')
		->select('Marks.id','Marks.regiNo','Marks.obtain_marks','Marks.total_marks','Student.rollNo', 'Student.firstName','Student.middleName','Student.lastName','Marks.subject','Marks.class', 'Marks.written','Marks.mcq','Marks.practical','Marks.ca','Marks.total','Marks.grade','Marks.point','Marks.Absent')
		->where('Marks.id','=',$id)
		->first();

		//return View::Make('app.markEdit',compact('marks'));
		return View('app.mmarkEdit',compact('marks'));


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
			'written' => 'required',
			'mcq' => 'required',
			'practical' =>'required',
			'ca' =>'required',
			'subject' => 'required',
			'class' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/mark/edit/'.Input::get('id'))->withErrors($validator);
		}
		else {

			$marks = Marks::find(Input::get('id'));
			//get subject grading system
			$subGradeing = Subject::select('gradeSystem','class')->where('code',Input::get('subject'))->where('class',Input::get('class'))->first();
			if($subGradeing->gradeSystem=="1")
			{
				$gparules = GPA::select('gpa','grade','markfrom')->where('for',"1")->get();

			}
			else {
				$gparules = GPA::select('gpa','grade','markfrom')->where('for',"2")->get();
			}
			//end
			$marks->written=Input::get('written');
			$marks->mcq = Input::get('mcq');
			$marks->practical=Input::get('practical');
			$marks->ca=Input::get('ca');

			$isExcludeClass=$subGradeing->class;
			if($isExcludeClass=="cl3" ||  $isExcludeClass=="cl4" || $isExcludeClass=="cl5")
			{
				$totalmark =Input::get('written')+Input::get('mcq')+Input::get('practical')+Input::get('ca');
			}
			else
			{
				//$totalmark = (((Input::get('written')+Input::get('mcq'))*80)/100)+Input::get('practical')+Input::get('ca');
				 $totalmark =Input::get('written')+Input::get('mcq')+Input::get('practical')+Input::get('ca');

			}
			$marks->total=$totalmark;
			foreach ($gparules as $gpa) {
				if ($totalmark >= $gpa->markfrom){
					$marks->grade=$gpa->gpa;
					$marks->point=$gpa->grade;
					break;
				}
			}
			$marks->Absent=Input::get('Absent');

			$marks->save();
			return Redirect::to('/mark/list')->with("success","Marks Update Sucessfully.");

		}
	}
	public function m_update()
	{
		$rules=[
			'written' => 'required',
			//'mcq' => 'required',
			//'practical' =>'required',
			///'ca' =>'required',
			'subject' => 'required',
			'class' => 'required',
			'total_marks' => 'required',
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/mark/m_edit/'.Input::get('id'))->withErrors($validator);
		}
		else {
			$marks = Marks::find(Input::get('id'));
			//get subject grading system
			//$subGradeing = Subject::select('gradeSystem','class')->where('code',Input::get('subject'))->where('class',Input::get('class'))->first();
			$total_marks = Input::get('total_marks');
			if($total_marks==100){
				$grade = 1;
			}
			if($total_marks==50){
				$grade = 2;
			}
			if($total_marks==75){
				$grade = 3;
			}
			if($total_marks==30){
				$grade = 4;
			}
			if($total_marks==25){
				$grade = 5;
			}
			if($total_marks==20){
				$grade = 6;
			}
			if($total_marks==15){
				$grade = 7;
			}
			if($total_marks==10){
				$grade = 8;
			}
			if($total_marks==5){
				$grade = 9;
			}
			//$subGradeing = Subject::select('gradeSystem')->where('code',Input::get('subject'))->where('class',Input::get('class'))->first();
			$gparules = GPA::select('gpa','grade','markfrom')->where('for',$grade )->orderBy('markfrom','desc')->get();
           //echo "<pre>";print_r($gparules->toArray());

			//end
			$marks->obtain_marks=Input::get('written');
			//$marks->total = Input::get('written');
			$marks->total_marks=Input::get('total_marks');
			//$marks->ca=Input::get('ca');

			
			$marks->total=Input::get('written');
			foreach ($gparules as $gpa) {
				if (Input::get('written') >= $gpa->markfrom){
					$marks->grade=$gpa->gpa;
					$marks->point=$gpa->grade;
					break;
				}
			}
			$marks->Absent=Input::get('Absent');

			$marks->save();
			return Redirect::to('/mark/m_list')->with("success","Marks Update Sucessfully.");

		}
	}

	public function getForMarksjoin($class)
	{
		$sections  = SectionModel::select('id','name')->where('class_code','=',$class)->get();
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

	

		$output ='';
		foreach($sections as $section){
			$subjecname = '';
			for($i=0;$i<count(getsubjecclass($class)['sub_name']);$i++){
				
				$url = url('/').'/create/marks?sub_id='.getsubjecclass($class)['sub_name'][$i]['id'].'&class='.$class.'&section='.$section->id;
				$link = "'".$url."','enter marks','width=1500','height=500'";
				$subjecname .='&nbsp;  ';
				$subjecname .='<a href="#'.$url.'" onclick="window.open('."$link".'); 
	              return false;">'.getsubjecclass($class)['sub_name'][$i]['name'].'</a>';
			}
			$output .='<tr><td>'.$section->name.'</td><td>'.$subjecname.'</td></tr>'; 
		}
		return $output;
	}

	public function createmarks(Request $request){

		//echo "<pre>";print_r(getsubjecclass('cl1'));exit;
		$class = ClassModel::select('id','name')->where('code',$request->get('class'))->first();
		
		$exams = DB::table('exam')->where('section_id',$request->get('section'))->where('class_id',$class->id)->get();
		$param1 = $request->get('exam');
		$param2 = $request->get('total_marks');
		$session = $request->get('session');
		$subject_id = $request->get('sub_id');
		$class_code = $request->get('class');
		$section = $request->get('section');
		$students = array();
		if($request->get('show')){
			
			$students = DB::table('Student')
						//->leftjoin('Marks','Student.regiNo','=','Marks.regiNo')
						->leftJoin('Marks', function($join) use ($param1,$subject_id)
					    {
					        $join->on('Student.regiNo', '=', 'Marks.regiNo');
					        $join->on('Marks.exam','=',DB::raw("'".$param1."'"));
					        $join->on('Marks.subject','=',DB::raw("'".$subject_id."'"));

					    })

						->select(DB::raw("CONCAT(Student.firstName,' ',Student.lastName) as fullname"),'Student.regiNo as student_id','Marks.*')
						//->where('Marks.exam',$request->get('exam'))
						->where('Student.session',get_current_session()->id)
						->where('Student.class',$request->get('class'))
						->where('Student.section',$request->get('section'))
						->groupBy('Student.regiNo')
						->get();

						//echo "<pre>hgg";print_r($students);exit;
		}
		//return View::Make('app.markCreate',compact('classes','subjects'));
		return View('app.markscreate',compact('class','exams','subject_id','students','param1','param2','session','class_code','section'));
	}


	public function newcreate()
	{
		//echo "<pre>";print_r(Input::get('sms'));exit;

		

		$rules=[
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
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/create/marks?class='.Input::get('class').'&section='.Input::get('section').'&session='.Input::get('session').'&exam='.Input::get('exam').'&sub_id='.Input::get('subject'))->withErrors($validator);
		}
		else {
			$getexam       = DB::table('exam')->where('id',Input::get('exam'))->first();
				$exam_name = $getexam->type;
			$total_marks   = Input::get('total_marks');
			if($total_marks==100){
				$grade = 1;
			}
			if($total_marks==50){
				$grade = 2;
			}
			if($total_marks==75){
				$grade = 3;
			}
			if($total_marks==30){
				$grade = 4;
			}
			if($total_marks==25){
				$grade = 5;
			}
			if($total_marks==20){
				$grade = 6;
			}
			if($total_marks==15){
				$grade = 7;
			}
			if($total_marks==10){
				$grade = 8;
			}
			if($total_marks==5){
				$grade = 9;
			}
			$gparules = GPA::select('gpa','grade','markfrom')->where('for',$grade )->orderBy('markfrom','desc')->get();
           
			$len = count(Input::get('regiNo'));

			$regiNos = Input::get('regiNo');
			$writtens=Input::get('written');
			//$mcqs =Input::get('mcq');
			//$practicals=Input::get('practical');
			//$cas=Input::get('ca');
			$isabsent = Input::get('absent');
			$sms = Input::get('sms');
			//print_r($isabsent);exit;
			$counter  = 0;

			for ( $i  = 0; $i< $len;$i++) {
				$isAddbefore = Marks::where('regiNo','=',$regiNos[$i])->where('exam','=',Input::get('exam'))->where('subject','=',Input::get('subject'))->first();
				
				if($isAddbefore)
				{
					$marks = Marks::find($isAddbefore->id);
				}
				else {
					$marks = new Marks;
				}
					$marks->class = Input::get('class');
					$marks->section = Input::get('section');
					$marks->shift = Input::get('shift');
					$marks->session = trim(Input::get('session'));
					$marks->regiNo = $regiNos[$i];
					$marks->exam = Input::get('exam');
					$marks->subject = Input::get('subject');
					$marks->written = '';
					$marks->mcq = '';
					$marks->practical = '';
					$marks->ca = '';
					$marks->obtain_marks = $writtens[$i];
					$marks->total_marks = $total_marks;
					$marks->ca = '';
					$isExcludeClass = Input::get('class');
					
					$marks->total=$writtens[$i];
					//echo "<pre>d";print_r($gparules->toArray());
					foreach ($gparules as $gpa) {

						if ($writtens[$i] >= $gpa->markfrom){
							$marks->grade = $gpa->gpa;
							$marks->point = $gpa->grade;
							break;
						}
					}
					if($isabsent[$regiNos[$i]]== "yes")
					{
						$marks->Absent = $isabsent[$regiNos[$i]];
						$writtens[$i]  = 0;
						$marks->total=$writtens[$i];
						$marks->obtain_marks = $writtens[$i];
					}
                    //echo "<pre>";print_r($marks);exit;
					//$test[] = $marks;
					if($marks->save()){
					    if($sms[$regiNos[$i]]== "yes"){
					    	$send_sms = $this->send_sms($regiNos[$i],$total_marks,$writtens[$i],Input::get('subject'),$exam_name);
						}
						$counter++;
					}
				//}

				
			}
			//echo "<pre>";print_r($test);
				//exit;
			if($counter==$len)
			{
				return Redirect::to('/mark/m_create?class_id='.Input::get('class').'&section='.Input::get('section').'&session='.Input::get('session').'&exam='.Input::get('exam'))->with("success",$counter."'s student mark save Succesfully.");
			}
			else {
				$already=$len-$counter;
				return Redirect::to('/mark/m_create?class_id='.Input::get('class').'&section='.Input::get('section').'&session='.Input::get('session').'&exam='.Input::get('exam'))->with("success",$counter." students mark save Succesfully and ".$already." Students marks already saved.</strong>");
			}
		}
	}


	public function send_sms($regiNo,$total,$obtain,$sub,$exam_name)
	{

	
		$student = DB::table('Student')->where('regiNo',$regiNo)->first();
		$subject = DB::table('Subject')->where('id',$sub)->first();
		
		$phone   = $student->fatherCellNo;
		
		//$message = 'your Child '.$student->firstName.' '.$student->lastName. ' subject '.$subject->name.' obtains marks '.$obtain.' out of '.$total.' marks ';
		
		$col_msg = DB::table('message')->where('name','mark_notification')->first();
			if(empty($col_msg)){
				$message = 'your Child '.$student->firstName.' '.$student->lastName. ' subject '.$subject->name.' obtains marks '.$obtain.' out of '.$total.' marks ';
	      	}else{
	      		$message =$col_msg->description;
	      		$msg1 = str_replace("[student_name]",$student->firstName.''.$student->lastName,$message);
	      		$msg2 = str_replace("[marks]",$obtain,$msg1);
	      		$msg3 = str_replace("[outoff]",$total,$msg2);
	      		$msg4 = str_replace("[subjects]",$subject->name,$msg3);
	      		$message = str_replace("[exam]",$exam_name,$msg4);
	      	}




		$body    = $message;
		$ict     = new ictcoreController();
		$i       = 0;
		$attendance_noti     = DB::table('notification_type')->where('notification','fess')->first();
		$ictcore_fees        = Ictcore_fees::select("*")->first();
		$ictcore_integration = Ictcore_integration::select("*")->where('type','sms');
		if($ictcore_integration->count()>0){
			$ictcore_integration = $ictcore_integration->first();
		}else{
			return 404;
		}
		$contacts = array();
		$contacts1 = array();
		$i=0;
		if (preg_match("~^0\d+$~", $phone)) {
			$to = preg_replace('/0/', '92', $phone, 1);
		}else {
			$to =$phone;  
		}
		if(strlen(trim($to))==12){
			$contacts = $to;
		}
		
		$msg = $body ;
		if($ictcore_integration->method!='ictcore'){
		$snd_msg  = $ict->verification_number_telenor_sms($to,$msg,'SidraSchool',$ictcore_integration->ictcore_user,$ictcore_integration->ictcore_password,'sms');
		
		}else{
			$send_msg_ictcore = sendmesssageictcore($student->firstName,$student->lastName,$to,$msg,'marks');
		}

		return 200;
	}

	public function template()
	{
		
		$message = Message::where('name','mark_notification')->first();
		if(!empty($message)){
			return Redirect::to('/message/edit/'.$message ->id);
		}
		return View('app.markstemplate');
	}
	public function edittemplate($id)
	{
		$message = Message::find($id);
		//return View::Make('app.classEdit',compact('class'));
		return View('app.messageEdit',compact('message'));
	}


}

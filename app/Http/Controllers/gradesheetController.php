<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\ClassModel;
use App\Subject;
use App\Student;
use App\Marks;
use App\GPA;
use App\MeritList;
use App\Ictcore_fees;
use App\Ictcore_integration;
use Storage;
use DB;
use App\Http\Controllers\ictcoreController;
Class formfoo5{

}
Class Meritdata{

}
class gradesheetController extends BaseController {
 public $data = array();
	public function __construct() {
		/*$this->beforeFilter('csrf', array('on'=>'post'));
		$this->beforeFilter('auth',array('except' => array('searchpub','postsearchpub','printsheet')));*/
               $this->middleware('auth',array('except' => array('searchpub','postsearchpub','printsheet')));
	}
	/**
	* Display a listing of the resource.
	*
	* @return Response
	*/
	public function index()
	{
		$formdata = new formfoo5;
		$formdata->class="";
		$formdata->section="00";
		$formdata->shift="";
		$formdata->exam="";
		$formdata->session="";
		$formdata->type="";
		$students=array();
		$classes = ClassModel::pluck('name','code');
        // echo "<pre>";print_r($classes);exit;
         if(Storage::disk('local')->exists('/public/grad_system.txt')){
			          $contant = Storage::get('/public/grad_system.txt');
			          $data = explode('<br>',$contant );

						//echo "<pre>";print_r($data);
						$gradsystem = $data[0]; 
					}else{
				      $gradsystem ='';
					}
		//return View::Make('app.gradeSheet',compact('classes','formdata','students'));
		if(Input::get('class')!='' && Input::get('section')!=''){
		$formdata->class   = Input::get('class');
		$formdata->section = Input::get('section');
		}
		$regiNo  = Input::get('regiNo');

		return View('app.gradeSheet',compact('classes','formdata','students','gradsystem','regiNo'));
	}


	/**
	* Show the form for creating a new resource.
	*
	* @return Response
	*/
	public function stdlist()
	{
		$rules=[
			'class' => 'required',
			'section' => 'required',
			'exam' => 'required',
			'session' => 'required'


		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			$formdata = new formfoo5;
			$formdata->class=Input::get('class');
			$formdata->section=Input::get('section');
			$formdata->exam=Input::get('exam');
			$formdata->session=Input::get('session');
			if(Input::get('regiNo_f')!='' && Input::get('section_f')!='' && Input::get('class_f')!=''){
				return Redirect::to('/gradesheet?class='.Input::get('class_f').'&section='.Input::get('section_f').'&regiNo='.Input::get('regiNo_f'))->withErrors($validator);
			}
			return Redirect::to('/gradesheet')->withErrors($validator);
		}
		else {
         // echo "<pre>";print_r(Input::all());
          //exit;
			if(Input::get('send_sms')=='yes'){
				$send = $this->send_sms(Input::get('class'),Input::get('section'),Input::get('exam'),Input::get('session'));
			   // echo "<pre>";print_r($send);
			    //exit;
			}
			if(is_array(Input::get('exam'))){
				 $exams_ids =implode(',',Input::get('exam')) ;
				$ispubl  = DB::table('MeritList')
				->select('regiNo','exam')
				->where('class','=',Input::get('class'))
				->where('session','=',trim(Input::get('session')))
				->where('section_id','=',trim(Input::get('section')))
				->whereIn('exam',Input::get('exam'))
				->orderBy('created_at','desc')
				->groupBy('regiNo')
				->get();
		    }else{
		    	$exams_ids='';
		    	$ispubl  = DB::table('MeritList')
				->select('regiNo','exam')
				->where('class','=',Input::get('class'))
				->where('session','=',trim(Input::get('session')))
				->where('exam','=',Input::get('exam'))
			    ->where('section_id','=',trim(Input::get('section')))
               
				->get();
		    }
		   // echo "<pre>";print_r($ispubl);
		    //exit;
			if(count($ispubl)>0) {
				
				$classes = ClassModel::pluck('name', 'code');
				$students = DB::table('Student')
				->join('Marks', 'Student.regiNo', '=', 'Marks.regiNo')
				->select(DB::raw('DISTINCT(Student.regiNo)'), 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.group', 'Student.section', 'Marks.shift', 'Marks.class')
				->where('Student.isActive', '=', 'Yes')
				->where('Student.class', '=', Input::get('class'))
				//->where('Marks.class', '=', Input::get('class'))
				->where('Student.section', '=', Input::get('section'))
				->where('Student.session', '=', trim(Input::get('session')))
				->where('Marks.exam', '=', Input::get('exam'));
				if(Input::get('regiNo_f')!='' && Input::get('section_f')!='' && Input::get('class_f')!=''){
					$students =$students->where('Student.regiNo',Input::get('regiNo_f')); 
				}

				$students =$students->get();

				$formdata = new formfoo5;
				$formdata->class = Input::get('class');
				$formdata->section = Input::get('section');
				$formdata->session = Input::get('session');
				if(is_array(Input::get('exam'))){
					$formdata->exam = Input::get('exam')[0];
			    }else{
			    	$formdata->exam = Input::get('exam');
			    }
				$formdata->type = Input::get('type');
				$formdata->postclass = array_get($classes, Input::get('class'));

				//return View::Make('app.gradeSheet', compact('classes', 'formdata', 'students'));
				 if(Storage::disk('local')->exists('/public/grad_system.txt')){
			          $contant = Storage::get('/public/grad_system.txt');
			          $data = explode('<br>',$contant );

						//echo "<pre>";print_r($data);
						$gradsystem = $data[0]; 
					}else{
				      $gradsystem ='';
					}
					$type = Input::get('type');
					
                  // exit;
				
					$regiNo = Input::get('regiNo_f');
				return View('app.gradeSheet', compact('classes', 'formdata', 'students','gradsystem','type','exams_ids','regiNo'));
			}
			else
			{
				if(Input::get('regiNo_f')!='' && Input::get('section_f')!='' && Input::get('class_f')!=''){
					return Redirect::to('/gradesheet?class='.Input::get('class_f').'&section='.Input::get('section_f').'&regiNo='.Input::get('regiNo_f'))->withInput()->with("noresult", "Results Not Published Yet!");
				}
				return Redirect::to('/gradesheet')->withInput()->with("noresult", "Results Not Published Yet!");
			}


		}
	}





	/**
** Send result sms
**/

public function send_sms($class,$section,$exam,$session)
{

	//echo "heelo";
//echo $section;
        $examed    = DB::table('exam')->where('id',$exam)->first();
		$exam_name =  $examed->type;
		
          $students = DB::table('Student')
                      ->where('Student.isActive', '=', 'Yes')
				      ->where('Student.class', '=', $class)
				      ->where('Student.section', '=', $section)
				      ->where('Student.session', '=', $session)
				      ->get();
				      $rr  = array();
				    foreach($students as $student){
				    	//echo $student->regiNo;
				      	$marks = DB::table('Marks')
				      			->join('Subject', 'Marks.subject', '=', 'Subject.id')
				      			->select('Marks.section','Marks.exam','Marks.regiNo','Marks.shift', 'Marks.class', 'Marks.section','Marks.obtain_marks','Marks.total_marks','Subject.name as subject_name')
				      			//->where('Marks.class',   '=', $class)
								->where('Subject.class', '=', $class)
								//->where('Marks.section', '=', $section)
								->where('Marks.session', '=', $session)
								->where('Marks.exam',    '=', $exam)
								->where('Marks.regiNo',  '=', $student->regiNo)
								->get();
								//echo "<pre>";print_r($marks);

								$save_data = array();
								foreach($marks as $mark){
									$save_data[] =array('total'=>$mark->total_marks,'obtain'=>$mark->obtain_marks,'subject'=>$mark->subject_name);
								    //$rr[] = $save_data;
								}

								$test  = $this->send_noti($save_data,$student->fatherCellNo,$student->firstName.''.$student->lastName );
								//echo "<pre>";print_r($test);
								//$newCompete = array('phone'=>$student->fatherCellNo);
								
								//$rr[] = array()
				      }
				     // echo "<pre>";print_r();
				/*->join('Marks', 'Student.regiNo', '=', 'Marks.regiNo')
				->join('Subject', 'Marks.subject', '=', 'Subject.code')
				->select('Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.group', 'Marks.shift', 'Marks.class', 'Marks.section','Marks.obtain_marks','Marks.total_marks','Subject.name as subject_name')
				->where('Student.isActive', '=', 'Yes')
				->where('Student.class', '=', $class)
				->where('Marks.class', '=', $class)
				->where('Subject.class', '=', $class)
				->where('Marks.section', '=', $section)
				->where('Marks.session', '=', $session)
				->where('Marks.exam', '=', $exam)
				->get();*/
				/*foreach($students as $student){
					if($student)
				}*/
				//echo "<pre>";print_r($rr);
				//exit;
	}

	public function send_noti($data=array(),$phone,$name){
          $this->data = $data;
          //return $this->data;

        $subject = '';
        $message1 = '';
        $message2 = '';
        $message3 = '';
         
		if(!empty($data)){
         $message = 'your child [name]';
         $sub     = " subject [sub]";
         $obtain  = ' obtains marks [obt]';
         $total   = ' out of  [total] marks';
			 $message = str_replace("[name]",$name,$message);
         
			for($i=0;$i<count($data);$i++){
				
				 //$message1 .= str_replace("[sub]",$data[$i]['subject'],$message);
				 $message1 .= ' subject '.$data[$i]['subject'].' obtatain marks:'.$data[$i]['obtain'].' out of:' .$data[$i]['total'] ."\n";
				 //$message2 .= str_replace("[obt]",$data[$i]['obtain'],$message1);
				 //$message3 .= str_replace("[total]",$data[$i]['total'],$message2);
				 //$subject2 .= str_replace("[number]",$dta['obtain'],$subject1);
				 //$subject3 .= str_replace("[total]",$dta['total'],$subject2);
			}
			$body = $message."\n". $message1  ;
			//return  $body  ;
			//exit;
			$ict     = new ictcoreController();
			$i       =0;
			$attendance_noti     = DB::table('notification_type')->where('notification','fess')->first();
			$ictcore_fees        = Ictcore_fees::select("*")->first();
			$ictcore_integration = Ictcore_integration::select("*")->where('type','sms');
			//echo $ictcore_integration->method;
			//exit;
			if($ictcore_integration->count()>0){
				$ictcore_integration = $ictcore_integration->first();
			}else{
				//return Redirect::to('fee_detail?action=unpaid')->withErrors("Sms credential not found");
				return 404;
			}
				//$group_id = $ict->telenor_apis('group','','','','','');
				$contacts = array();
				$contacts1 = array();
				$i=0;
			

			if (preg_match("~^0\d+$~", $phone)) {
					$to = preg_replace('/0/', '92', $phone, 1);
				}else {
					$to =$phone;  
				}
				//$contacts1[] = $to;
				if(strlen(trim($to))==12){
					$contacts = $to;
					//$i++;
				}
				//$comseprated= implode(',',$contacts);
				//$group_contact_id = $ict->telenor_apis('add_contact',$group_id,$contacts,'','','');
		}else{
			return 403;
		}
			/*$col_msg = DB::table('message')->first();
			if(empty($col_msg)){
				$msg = $body ;
	      	}else{
	      		$body 
	      	}*/
	      	$msg = $body ;
			/*if($fee_msg->count()>0 && $fee_msg->first()->description!=''){
				$msg = $fee_msg->first()->description;
			}else{
				$msg = "please submit your child  fee for this month";
			}*/
			if($ictcore_integration->method!='ictcore'){
				$snd_msg  = $ict->verification_number_telenor_sms($to,$msg,'SidraSchool',$ictcore_integration->ictcore_user,$ictcore_integration->ictcore_password,'sms');
			}else{

			    $send_msg_ictcore = sendmesssageictcore($name,'',$to,$msg,'result');

			}
			//$campaign      = $ict->telenor_apis('campaign_create',$group_id,'',$msg,'','sms');
			//$send_campaign = $ict->telenor_apis('send_msg','','','','',$campaign);
			//session()->forget('upaid');
			return 200;
	}

	public  function gradeCalculator($point,$gparules)
	{
		$grade=0;
		foreach ($gparules as $gpa) {
			if ($point >= $gpa->grade){
				$grade=$gpa->gpa;
				break;
			}
		}
		return $grade;
	}
	public  function pointCalculator($marks,$gparules)
	{

		$point=0;
		foreach ($gparules as $gpa) {


			if ($marks >= $gpa->markfrom){
				$point=$gpa->grade;
				break;
			}
		}

		return $point;
	}
	public  function gpaCalculator($marks,$gparules)
	{
		$gpacal= array();
      //dd($marks);
		foreach ($gparules as $gpa) {
			
			if ($marks >= $gpa->markfrom){
				$gpacal[0]=$gpa->grade;
				$gpacal[1]=$gpa->gpa;
				break;
			}
		}
		return $gpacal;
	}
	/**
	* Store a newly created resource in storage.
	*
	* @return Response
	*/
	/*public function printsheet($regiNo,$exam,$class)
	{
		 $student = DB::table('Student')
		->join('Class', 'Student.class', '=', 'Class.code')
		->select( 'Student.regiNo','Student.rollNo','Student.dob', 'Student.firstName','Student.middleName','Student.lastName','Student.fatherName','Student.motherName', 'Student.group','Student.shift','Student.class as classcode','Class.Name as class','Student.section','Student.session','Student.extraActivity')
		->where('Student.regiNo','=',$regiNo)
		->where('Student.class','=',$class)
		->where('Student.isActive', '=', 'Yes')
		->first();
		if(!is_null($student)) {

			$merit = DB::table('MeritList')
			->select('regiNo', 'grade', 'point', 'totalNo')
			->where('exam', $exam)
			->where('class', $class)
			->where('session', trim($student->session))
			->where('regiNo',$regiNo)
			->orderBy('point', 'DESC')
			->orderBy('totalNo', 'DESC')->get();
			if (is_null($student)  || is_null($merit) ) {
				return Redirect::back()->with('noresult', 'Result Not Found!');
			} else {
				$meritdata = new Meritdata();
				$position = 0;
				foreach ($merit as $m) {
					$position++;
					if ($m->regiNo === $regiNo) {
						$meritdata->regiNo = $m->regiNo;
						$meritdata->point = $m->point;
						$meritdata->grade = $m->grade;
						$meritdata->position = $position;
						$meritdata->totalNo = $m->totalNo;
						break;
					}
				}

				//sub group need to implement
				$subjects = Subject::select('name', 'code', 'subgroup', 'totalfull')->where('class', '=', $student->classcode)->get();

				$overallSubject = array();
				$subcollection = array();

				$banglatotal = 0;
				$banglatotalhighest = 0;
				$banglaArray = array();
				$blextra = array();

				$englishtotal = 0;
				$englishtotalhighest = 0;
				$englishArray = array();
				$enextra = array();

				$totalHighest = 0;
				$isBanglaFail=false;
				$isEnglishFail=false;
				foreach ($subjects as $subject) {
					$submarks = Marks::select('written', 'mcq', 'practical', 'ca', 'total', 'point', 'grade')->where('regiNo', '=', $student->regiNo)
					->where('subject', '=', $subject->code)->where('exam', '=', $exam)->where('class', '=', $class)->first();
					$maxMarks = Marks::select(DB::raw('max(total) as highest'))->where('class', '=', $class)->where('session', '=', $student->session)
					->where('subject', '=', $subject->code)->where('exam', '=', $exam)->first();

					$submarks["highest"] = $maxMarks->highest;
					$submarks["subcode"] = $subject->code;

					$submarks["subname"] = $subject->name;


					if ($this->getSubGroup($subjects, $subject->code) === "Bangla") {
						if($submarks->grade=="F")
						{
							$isBanglaFail=true;
						}

						$banglatotal += $submarks->total;
						$banglatotalhighest += $submarks->highest;

						$bangla = array($submarks->subcode, $submarks->subname, $submarks->written, $submarks->mcq, $submarks->ca, $submarks->practical);
						array_push($banglaArray, $bangla);

					} else if ($this->getSubGroup($subjects, $subject->code) === "English") {
						if($submarks->grade==="F")
						{
							$isEnglishFail=true;
						}
						$englishtotal += $submarks->total;
						$englishtotalhighest += $submarks->highest;

						$english = array($submarks->subcode, $submarks->subname, $submarks->written, $submarks->mcq, $submarks->ca, $submarks->practical);
						array_push($englishArray, $english);

						//array_push($subcollection, $submarks);



					} else {
						$totalHighest += $maxMarks->highest;
						array_push($subcollection, $submarks);

							//print_r($submarks);

					}


				}
				$gparules = GPA::select('gpa', 'grade', 'markfrom')->get();
				  //dd($gparules);
				$subgrpbl = false;
				if ($banglatotal > 0) {

					$blt = floor($banglatotal / 2);
					$totalHighest += $banglatotalhighest;
					$gcal = $this->gpaCalculator($blt, $gparules);
                 
					$subgrpbl = true;
					array_push($blextra, $banglatotal);
					array_push($blextra, $banglatotalhighest);
					if($isBanglaFail)
					{
						array_push($blextra, "0.00");
						array_push($blextra, "F");
					}
					else {
						if(isset($gcal[0])){
						array_push($blextra, $gcal[0]);
						array_push($blextra, $gcal[1]);
					}
                        }


				}
				$subgrpen = false;
				if ($englishtotal > 0) {
					$ent = floor($englishtotal / 2);
					$totalHighest += $englishtotalhighest;
					$gcal = $this->gpaCalculator($ent, $gparules);
					$subgrpen = true;
					array_push($enextra, $englishtotal);
					array_push($enextra, $englishtotalhighest);
					if($isEnglishFail)
					{
						array_push($enextra, "0.00");
						array_push($enextra, "F");

					}
					else {
						if(isset($gcal[0])){
						array_push($enextra, $gcal[0]);
						array_push($enextra, $gcal[1]);
					}

					}


				}
				$extra = array($exam, $subgrpbl, $totalHighest, $subgrpen, $student->extraActivity);
				$query="select left(MONTHNAME(STR_TO_DATE(m, '%m')),3) as month, count(regiNo) AS present from ( select 01 as m union all select 02 union all select 03 union all select 04 union all select 05 union all select 06 union all select 07 union all select 08 union all select 09 union all select 10 union all select 11 union all select 12 ) as months LEFT OUTER JOIN Attendance ON MONTH(Attendance.date)=m and Attendance.regiNo ='".$regiNo."' GROUP BY m";
				$attendance=DB::select(DB::RAW($query));
				//return View::Make('app.stdgradesheet', compact('student', 'extra', 'meritdata', 'subcollection', 'blextra', 'banglaArray', 'enextra', 'englishArray','attendance'));
              //  dd($englishArray);

				
				

			print_r($banglaArray);
              return View('app.stdgradesheet', compact('student', 'extra', 'meritdata', 'subcollection', 'blextra', 'banglaArray', 'enextra', 'englishArray','attendance'));
			
			}
		}
		else
		{
			//echo "<h1 style='text-align: center;color: red'>Result Not Found</h1>";
			return  Redirect::back()->with('noresult','Result Not Found!');

		}
	}*/


	public function printsheet($regiNo,$exam,$class)
	{
        $examed  = DB::table('exam')->where('id',$exam)->first();
		$exam_name =  $examed->type;
		$student =	DB::table('Student')
		 ->join('Class', 'Student.class', '=', 'Class.code')
		 ->join('section','Student.section','=','section.id')
		 ->select( 'Student.photo','Student.regiNo','Student.rollNo','Student.dob', 'Student.firstName','Student.middleName','Student.lastName','Student.fatherName','Student.motherName', 'Student.group','Student.shift','Student.class as classcode','Class.Name as class','Student.section','Student.session','Student.extraActivity','section.name as section_name')
		 ->where('Student.regiNo','=',$regiNo)
		 ->where('Student.class','=',$class)
		 ->where('Student.isActive', '=', 'Yes');
		 //->first();
       // echo "<pre>";print_r($student->first());exit;
		if($student->count()>0) {
           $student = $student->first();
           $section = $student->section;

			$merit = DB::table('MeritList')
			->select('id','regiNo', 'grade', 'point', 'totalNo','section_id')
			->where('exam', $exam)
			->where('class', $class)
			->where('session', trim($student->session))
			->where('section_id', trim($section))
			//->where('regiNo',$regiNo)
			//->orderBy('point', 'DESC')
			//->orderBy('point')
			->orderBy('totalNo', 'DESC')
			->get();
			//->orderBy('totalNo', 'DESC')->get();
			//echo "<pre>";print_r($merit);exit;
			if (empty($student)  || empty($merit)) {
				return Redirect::back()->with('noresult', 'Result Not Found!');
			} else {
				$meritdata = new Meritdata();
				$position  = 0;
				foreach ($merit as $m) {
					$position++;
					//$test[] = $m->section_id .'==='. $section."909".$m->regiNo .'=== '.$regiNo;
					
					if($m->regiNo === $regiNo && $m->section_id == $section) {
						$meritdata->id = $m->id;
						$meritdata->regiNo = $m->regiNo;
						$meritdata->point = $m->point;
						$meritdata->grade = $m->grade;
						$meritdata->position = $position;
						$meritdata->totalNo = $m->totalNo;
						break;
					}
				}
				//echo $m->section_id .'==='. $section."909".$m->regiNo .'=== '.$regiNo;
					 //echo "<pre>";print_r($meritdata);
					 //exit;
             
              //print_r($meritdata);
             // exit;
				//sub group need to implement
				$subjects = Subject::select('name', 'code', 'subgroup', 'totalfull')->where('class', '=', $student->classcode)->get();
				//echo "<pre>";print_r($subjects->toArray() );exit;
				$overallSubject = array();
				$subcollection = array();

				$banglatotal = 0;
				$banglatotalhighest = 0;
				$urdu = 0;
				$banglaArray = array();
				$blextra = array();

				$englishtotal = 0;
				$englishtotalhighest = 0;
				$english_total = 0;
				$englishArray = array();
				$enextra = array();

				$totalHighest = 0;
				$totalourall = 0;
				$isBanglaFail=false;
				$isEnglishFail=false;
				foreach ($subjects as $subject) {
					$submarks = Marks::select('written', 'mcq', 'practical', 'ca', 'total', 'point', 'grade')->where('regiNo', '=', $student->regiNo)
					->where('subject', '=', $subject->code)->where('exam', '=', $exam)->where('class', '=', $class)->first();
					$maxMarks = Marks::select(DB::raw('max(total) as highest'))->where('class', '=', $class)->where('session', '=', $student->session)
					->where('subject', '=', $subject->code)->where('exam', '=', $exam)->first();

					$submarks["highest"] = $maxMarks->highest;
					$submarks["subcode"] = $subject->code;

					$submarks["subname"] = $subject->name;
					$submarks["outof"] = $subject->totalfull;


					if ($this->getSubGroup($subjects, $subject->code) === "Bangla") {

						if($submarks->grade=="F")
						{
							$isBanglaFail=true;
						}

						$banglatotal += $submarks->total;
						$banglatotalhighest += $submarks->highest;
                         $urdu += $subject->totalfull;
						$bangla = array($submarks->subcode, $submarks->subname, $submarks->written, $submarks->mcq, $submarks->ca, $submarks->practical,$subject->totalfull);
						array_push($banglaArray, $bangla);

					} else if ($this->getSubGroup($subjects, $subject->code) === "English") {
						if($submarks->grade==="F")
						{
							$isEnglishFail=true;
						}
						$englishtotal += $submarks->total;
						$englishtotalhighest += $submarks->highest;
                        $english_total += $subject->totalfull;
						$english = array($submarks->subcode, $submarks->subname, $submarks->written, $submarks->mcq, $submarks->ca, $submarks->practical,$subject->totalfull);
						array_push($englishArray, $english);

					} else {
						$totalHighest += $maxMarks->highest;
						$totalourall +=$subject->totalfull;
						array_push($subcollection, $submarks);
					}
					$outof[] = $subject->totalfull;
				}
				$gparules = GPA::select('gpa', 'grade', 'markfrom')->get();
				$subgrpbl = false;

				if ($banglatotal > 0) {

					$blt = floor($banglatotal / 2);
					$totalHighest += $banglatotalhighest;
					$totalourall +=$urdu;
					$gcal = $this->gpaCalculator($blt, $gparules);

					$subgrpbl = true;
					array_push($blextra, $banglatotal);
					//array_push($blextra, $banglatotalhighest);
					array_push($blextra, $urdu);
                   // echo $gcal[1].'uuu';
					if($isBanglaFail)
					{
						array_push($blextra, "0.00");
						array_push($blextra, "F");
					}
					else {
						array_push($blextra, $gcal[0]);
						array_push($blextra, $gcal[1]);
					}
				}
				$subgrpen = false;
				if ($englishtotal > 0) {
					$ent = floor($englishtotal / 2);
					$totalHighest += $englishtotalhighest;
					$totalourall += $english_total;
					$gcal = $this->gpaCalculator($ent, $gparules);
					$subgrpen = true;
					array_push($enextra, $englishtotal);
					//array_push($enextra, $englishtotalhighest);
					array_push($enextra, $english_total);

					//echo $ent.'uuu'.print_r($gcal,true);
					//exit;
					if($isEnglishFail)
					{
						array_push($enextra, "0.00");
						array_push($enextra, "F");

					}
					else {
						array_push($enextra, $gcal[0]);
						array_push($enextra, $gcal[1]);

					}
				}

                //echo "<pre>";print_r($englishArray);
                //echo "<pre>f";print_r($banglaArray);
               //exit;
				$extra = array($exam_name, $subgrpbl, $totalHighest, $subgrpen, $student->extraActivity,$totalourall);
				$query="select left(MONTHNAME(STR_TO_DATE(m, '%m')),3) as month, count(regiNo) AS present from ( select 01 as m union all select 02 union all select 03 union all select 04 union all select 05 union all select 06 union all select 07 union all select 08 union all select 09 union all select 10 union all select 11 union all select 12 ) as months LEFT OUTER JOIN Attendance ON MONTH(Attendance.date)=m and Attendance.regiNo ='".$regiNo."' and  Attendance.status IN ('Present','present','late','Late') GROUP BY m";
				$attendance=DB::select(DB::RAW($query));
				//echo "<pre>";print_r($subcollection);
				//exit;
				return View('app.stdgradesheet', compact('student', 'extra', 'meritdata', 'subcollection', 'blextra', 'banglaArray', 'enextra', 'englishArray','attendance'));

			}
		}
		else
		{
			//echo "<h1 style='text-align: center;color: red'>Result Not Found</h1>";
			return  Redirect::back()->with('noresult','Result Not Found!');

		}
	}


	public  function  getgenerate()
	{
		$classes = ClassModel::pluck('name','code');
		//return View::Make('app.resultgenerate',compact('classes'));
		  if(Storage::disk('local')->exists('/public/grad_system.txt')){
			          $contant = Storage::get('/public/grad_system.txt');
			          $data = explode('<br>',$contant );

						//echo "<pre>";print_r($data);
						$gradsystem = $data[0]; 
					}else{
				      $gradsystem ='';
					}

		 $formdata = new formfoo5;
		$formdata->class="";
		$formdata->section="";
		$formdata->shift="";
		$formdata->exam="";
		$formdata->session="";
		$formdata->type="";
		return View('app.resultgenerate',compact('classes','gradsystem','formdata'));
	}

	public  function getSubGroup($subjects,$subject)
	{
		$group="";
		foreach($subjects as $sub)
		{
			if($sub->code===$subject)
			{
				$group=$sub->subgroup;
				break;
			}
		}
		return $group;
	}
	public  function getSubjectTotalno($subjects,$subject)
	{
		$total="";
		foreach($subjects as $sub)
		{
			if($sub->code===$subject)
			{
				$total=$sub->totalfull;
				break;
			}
		}
		return $total;
	}
	public  function  postgenerate()
	{
		$rules = [
			'class' => 'required',
			'exam' => 'required',
			//'section' => 'required',
			'session' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails()) {
			return Redirect::to('/result/generate')->withErrors($validator)->withInput();
		} else {
			$isGenerated=DB::table('MeritList')
			->select('regiNo')
			->where('class', '=', Input::get('class'))
			->where('session', '=', trim(Input::get('session')))
			->where('exam', '=', Input::get('exam'))
			//->where('section_id', '=', 1)
			->get();
			if(count($isGenerated)==0)
			{
				$subjects           = Subject::select('name', 'code', 'type', 'subgroup')->where('class', '=', Input::get('class'))->get();
				$sectionsHas        = Student::select('section')->where('class', '=', Input::get('class'))->where('session', trim(Input::get('session')))->where('isActive', '=', 'Yes')->distinct()->orderBy('section', 'asc')->get();
				$sectionMarksSubmit = Marks::select('section')->where('class', '=', Input::get('class'))->where('session', trim(Input::get('session')))->where('exam',Input::get('exam'))->distinct()->get();
				//dd($sectionsHas);
				if (count($sectionsHas)==count($sectionMarksSubmit))
				{
					$isAllSubSectionMarkSubmit =false;
					$notSubSection='';
					foreach ($sectionsHas as $section) {
						$marksubmit = Marks::select('subject')->where('class', '=', Input::get('class'))->where('section',$section->section)->where('session', trim(Input::get('session')))->where('exam',Input::get('exam'))->distinct()->get();

						if(count($subjects) == count($marksubmit))
						{
							$isAllSubSectionMarkSubmit = true;
							continue;
						}
						else{
							$notSubSection=$section->section;
							$isAllSubSectionMarkSubmit =false;
							break;
						}
					}
					if ($isAllSubSectionMarkSubmit) {
						$fourthsubjectCode = "";
						foreach ($subjects as $subject) {
							if ($subject->type === "Electives") {
								$fourthsubjectCode = $subject->code;
							}
						}


						$students = Student::select('regiNo')
						->join('section','Student.section','=','section.id')
						->select('Student.*','section.name')
						->where('Student.class', '=', Input::get('class'))
						->where('Student.session', '=', trim(Input::get('session')))
						->where('Student.isActive', '=', 'Yes')->get();
                      //  echo "<pre>";print_r($students->toArray());exit;
						if (count($students) != 0) {
							$marksSubmitStudents=Marks::select('Marks.regiNo')
													->join('Student', 'Marks.regiNo', '=', 'Student.regiNo')
													->where('Student.isActive', '=', 'Yes')
													->where('Student.class', '=', Input::get('class'))
													->where('Marks.class', '=', Input::get('class'))
													->where('Marks.session', '=', trim(Input::get('session')))
													->where('Marks.exam', '=', Input::get('exam'))
													->distinct()
													->get();

							if(count($students)==count($marksSubmitStudents))
							{
								$gparules = GPA::select('gpa', 'grade', 'markfrom')->get();
								$foobar = array();
								foreach ($students as $student) {
									$marks = Marks::select('subject', 'grade', 'point', 'total')->where('regiNo', '=', $student->regiNo)->where('exam', '=', Input::get('exam'))->get();

									$totalpoint  = 0;
									$totalmarks  = 0;
									$subcounter  = 0;
									$banglamark  = 0;
									$englishmark = 0;
									$isfail      = false;
									foreach ($marks as $mark) {


										if ($this->getSubGroup($subjects, $mark->subject) === "Bangla") {
											$banglamark += $mark->total;

										} else if ($this->getSubGroup($subjects, $mark->subject) === "English") {
											$englishmark += $mark->total;
										} else {
											if ($mark->subject === $fourthsubjectCode) {
												if ($mark->point >= 2.00) {
													$totalmarks += $mark->total;
													$totalpoint += $mark->point - 2;


												} else {
													$totalmarks += $mark->total;
												}
												$subcounter--;

											} else {
												$totalmarks += $mark->total;
												$totalpoint += $mark->point;

											}

										}


										$subcounter++;

										if ($mark->subject !== $fourthsubjectCode && $mark->grade === "F") {
											$isfail = true;
										}
									}


									if ($banglamark > 0) {
										$blmarks = floor($banglamark / 2);


										$totalmarks += $banglamark;

										$totalpoint += $this->pointCalculator($blmarks, $gparules);

										$subcounter--;

									}


									if ($englishmark > 0) {
										$enmarks = floor($englishmark / 2);
										$totalmarks += $englishmark;
										$totalpoint += $this->pointCalculator($enmarks, $gparules);
										$subcounter--;
									}
									$grandPoint = ($totalpoint / $subcounter);
									if ($isfail) {
										$grandGrade = $this->gradnGradeCal(0.00, $gparules);
									} else {
										$grandGrade = $this->gradnGradeCal($grandPoint, $gparules);
									}
									$merit          = new MeritList;
									$merit->class   = Input::get('class');
									$merit->session = trim(Input::get('session'));
									$merit->exam    = Input::get('exam');
									$merit->regiNo  = $student->regiNo;
									$merit->totalNo = $totalmarks;
									$merit->point   = $grandPoint;
									$merit->grade   = $grandGrade;
									$merit->section_id   = $student->section;
                                // echo "<pre>";print_r($merit );
									$merit->save();
                                    $test[] = $merit;
								}

								 //echo "<pre>";print_r($test );
									//exit;

							}
							else {

								return Redirect::to('/result/generate')->withInput()->with("noresult", "All students examintaion marks not submited yet!!");
							}
						}
						else
						{
							return Redirect::to('/result/generate')->withInput()->with("noresult", "There is no students in this class!!");
						}
						return Redirect::to('/result/generate')->with("success", "Result Generate and Publish Successfull.");
					}
					else
					{
						return Redirect::to('/result/generate')->withInput()->with("noresult", "Section ".$notSubSection." all subjects marks not submited yet!!");

					}
				}
				else{
					return Redirect::to('/result/generate')->withInput()->with("noresult", "All sections marks not submited yet!!");
				}
			}
			else{
				return Redirect::to('/result/generate')->withInput()->with("noresult", "Result already generated for this class,session and exam!");
			}
		}
	}

	public function gradnGradeCal($grandPoint)
	{
		return $grandPoint;
		$grade="";
		if($grandPoint>=5.00)
		{
			$grade="A+";
			return $grade;
		}
		$lowarray   = array("0.00","1.00","2.00","3.00","3.50","4.00");
		$higharray  = array("1.00","2.00","3.00","3.50","4.00","5.00");
		$gradearray = array("F","D","C","B","A-","A");

		for($i = 0;$i < count($lowarray);$i++)
		{
			if($grandPoint >= $lowarray[$i] && $grandPoint<$higharray[$i])
			{
				$grade=$gradearray[$i];
			}
		}

		return $grade;
	}

	public function search()
	{
		$formdata = new formfoo5;
		$formdata->exam="";
		$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
		//return View::Make('app.resultsearch',compact('formdata','classes'));
		return View('app.resultsearch',compact('formdata','classes'));
	}
	public function postsearch()
	{
		$rules=[

			'exam' => 'required',
			'regiNo' => 'required',
			'class' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/result/search')->withErrors($validator)->withInput(Input::all());
		}
		else {
			return Redirect::to('/gradesheet/print/'.Input::get('regiNo').'/'.Input::get('exam').'/'.Input::get('class'));
		}
	}
	public function searchpub()
	{
		$formdata = new formfoo5;
		$formdata->exam="";
		$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
		//return View::Make('app.resultsearchpublic',compact('formdata','classes'));
		return View('app.resultsearchpublic',compact('formdata','classes'));
	}
	public function postsearchpub()
	{

		$rules=[
		 'exam' => 'required',
		 'regiNo' => 'required',
		 'class' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/results')->withErrors($validator)->withInput(Input::all());
		}
		else {


			return Redirect::to('/gradesheet/print/'.Input::get('regiNo').'/'.Input::get('exam').'/'.Input::get('class'));
		}
	}
	public function gradsystem()
	{
	    //return View('app.resultsearchpublic',compact(''));
	}
	public function m_printsheet($regiNo,$exam,$class)
	{
        $examed    = DB::table('exam')->where('id',$exam)->first();
		$exam_name =  $examed->type;
		$student   =	DB::table('Student')
		 ->join('Class', 'Student.class', '=', 'Class.code')
		 ->join('section','Student.section','=','section.id')
		 ->select('Student.photo','Student.regiNo','Student.rollNo','Student.dob', 'Student.firstName','Student.middleName','Student.lastName','Student.fatherName','Student.motherName', 'Student.group','Student.shift','Student.class as classcode','Class.Name as class','Student.section','Student.session','Student.extraActivity','section.name as section_name')
		 ->where('Student.regiNo','=',$regiNo)
		 ->where('Student.class', '=',$class)
		 ->where('Student.isActive', '=', 'Yes');
		 //->first();
        //echo "<pre>";print_r($student->first());exit;
		if($student->count()>0) {
           $student = $student->first();
           $section = $student->section;
			$merit = DB::table('MeritList')
			->select('regiNo', 'grade', 'point', 'totalNo','section_id')
			->where('exam', $exam)
			->where('class', $class)
			->where('session', trim($student->session))
			->where('section_id', trim($section))
			//->where('regiNo',$regiNo)
			//->orderBy('point', 'DESC')
			//->orderBy('point')
			->orderBy('totalNo', 'DESC')->get();
			//->orderBy('totalNo', 'DESC')->get();
			//echo "<pre>";print_r($merit);exit;
			if (empty($student)  || empty($merit)) {
				return Redirect::back()->with('noresult', 'Result Not Found!');
			} else {
				$meritdata = new Meritdata();
				$position  = 0;
				//echo "<pre>";print_r($merit->toArray());
				foreach ($merit as $m) {
					$position++;
					//$test[] = $m->section_id .'==='. $section."909".$m->regiNo .'=== '.$regiNo;
					
					if($m->regiNo === $regiNo && $m->section_id == $section) {
						$meritdata->regiNo = $m->regiNo;
						$meritdata->point = $m->point;
						$meritdata->grade = $m->grade;
						$meritdata->position = $position;
						$meritdata->totalNo = $m->totalNo;
						break;
					}
				}
				//echo $m->section_id .'==='. $section."909".$m->regiNo .'=== '.$regiNo;
					// echo "<pre>";print_r($meritdata);
					//exit;
             
              //print_r($meritdata);
             // exit;
				//sub group need to implement
				$subjects = Subject::select('id as code','name', 'code as codee', 'subgroup', 'totalfull')->where('class', '=', $student->classcode)->get();

				$overallSubject = array();
				$subcollection = array();

				$banglatotal = 0;
				$banglatotalhighest = 0;
				$urdu = 0;
				$banglaArray = array();
				$blextra = array();

				$englishtotal = 0;
				$englishtotalhighest = 0;
				$english_total = 0;
				$englishArray = array();
				$enextra = array();

				$totalHighest = 0;
				$totalourall = 0;
				$isBanglaFail=false;
				$isEnglishFail=false;
				//echo $exam."<pre>";print_r($subjects->toArray());
				///exit;
				foreach ($subjects as $subject) {
					$submarks = Marks::select('written', 'mcq', 'practical', 'ca', 'total', 'point', 'grade','total_marks')->where('regiNo', '=', $student->regiNo)
					->where('subject', '=', $subject->code)
					->where('exam', '=', $exam)
					->where('class', '=', $class)
					->first();
					//echo $exam."<pre>";print_r($submarks->toArray());exit;
					if(!empty($submarks)){
					$maxMarks = Marks::select(DB::raw('max(total) as highest'))->where('class', '=', $class)->where('session', '=', $student->session)
					->where('subject', '=', $subject->code)->where('exam', '=', $exam)->first();

					//echo "<pre>";print_r($submarks);exit;
					$submarks["highest"] = $maxMarks->highest;
					$submarks["subcode"] = $subject->code;

					$submarks["subname"] = $subject->name;
					$submarks["outof"]   = $submarks->total_marks;

					//echo $this->getSubGroup($subjects, $subject->code);
					if ($this->getSubGroup($subjects, $subject->code) === "Bangla") {

						if($submarks->grade=="F")
						{
							$isBanglaFail=true;
						}

						$banglatotal += $submarks->total;
						$banglatotalhighest += $submarks->highest;
                         $urdu += $subject->totalfull;
						$bangla = array($submarks->subcode, $submarks->subname, $submarks->written, $submarks->mcq, $submarks->ca, $submarks->practical,$subject->totalfull);
						array_push($banglaArray, $bangla);

					} else if ($this->getSubGroup($subjects, $subject->code) === "English") {
						if($submarks->grade==="F")
						{
							$isEnglishFail=true;
						}
						$englishtotal += $submarks->total;
						$englishtotalhighest += $submarks->highest;
                        $english_total += $subject->totalfull;
						$english = array($submarks->subcode, $submarks->subname, $submarks->written, $submarks->mcq, $submarks->ca, $submarks->practical,$subject->totalfull);
						array_push($englishArray, $english);

					} else {
						$totalHighest += $maxMarks->highest;
						$totalourall +=$submarks->total_marks;
						array_push($subcollection, $submarks);
					}
					$outof[] = $subject->totalfull;
				}
				}
				//exit;
				$gparules = GPA::select('gpa', 'grade', 'markfrom')->get();
				$subgrpbl = false;

				if ($banglatotal > 0) {

					$blt = floor($banglatotal / 2);
					$totalHighest += $banglatotalhighest;
					$totalourall +=$urdu;
					$gcal = $this->gpaCalculator($blt, $gparules);

					$subgrpbl = true;
					array_push($blextra, $banglatotal);
					//array_push($blextra, $banglatotalhighest);
					array_push($blextra, $urdu);
                   // echo $gcal[1].'uuu';
					if($isBanglaFail)
					{
						array_push($blextra, "0.00");
						array_push($blextra, "F");
					}
					else {
						array_push($blextra, $gcal[0]);
						array_push($blextra, $gcal[1]);
					}
				}
				$subgrpen = false;
				if ($englishtotal > 0) {
					$ent = floor($englishtotal / 2);
					$totalHighest += $englishtotalhighest;
					$totalourall += $english_total;
					$gcal = $this->gpaCalculator($ent, $gparules);
					$subgrpen = true;
					array_push($enextra, $englishtotal);
					//array_push($enextra, $englishtotalhighest);
					array_push($enextra, $english_total);

					//echo $ent.'uuu'.print_r($gcal,true);
					//exit;
					if($isEnglishFail)
					{
						array_push($enextra, "0.00");
						array_push($enextra, "F");

					}
					else {
						array_push($enextra, $gcal[0]);
						array_push($enextra, $gcal[1]);

					}
				}

                //echo "<pre>";print_r($englishArray);
                //echo "<pre>f";print_r($banglaArray);
               //exit;
				$extra = array($exam_name, $subgrpbl, $totalHighest, $subgrpen, $student->extraActivity,$totalourall);
				$query="select left(MONTHNAME(STR_TO_DATE(m, '%m')),3) as month, count(regiNo) AS present from ( select 01 as m union all select 02 union all select 03 union all select 04 union all select 05 union all select 06 union all select 07 union all select 08 union all select 09 union all select 10 union all select 11 union all select 12 ) as months LEFT OUTER JOIN Attendance ON MONTH(Attendance.date)=m and Attendance.regiNo ='".$regiNo."' and  Attendance.status IN ('Present','present','late','Late') GROUP BY m";
				$attendance=DB::select(DB::RAW($query));
				////////////echo "<pre>";print_r($meritdata);
				//exit;
				if(Input::get('type')=='sigle' || Input::get('type')==''):
				return View('app.mstdgradesheet', compact('student', 'extra', 'meritdata', 'subcollection', 'blextra', 'banglaArray', 'enextra', 'englishArray','attendance'));
				else:
					//echo "<pre>";print_r($this->combined_results(Input::get('type'),$regiNo,$exam,$class));
                     $data = $this->combined_results(Input::get('type'),$regiNo,$exam,$class);
                     $result= $data['result_data'];
                     $attendance= $data['attendance'];
                     return View('app.mstdgradesheetc', compact('result','attendance'));

                endif;




			}
		}
		else
		{
			//echo "<h1 style='text-align: center;color: red'>Result Not Found</h1>";
			return  Redirect::back()->with('noresult','Result Not Found!');

		}
	}
public function combined_results($type,$regiNo,$exam,$class)
{

	/*$examed    = DB::table('exam')->where('id',$exam)->first();
	$exam_name =  $examed->type;
	$exam_ids = array('4','37','38','39','40');
	$student   =	DB::table('Student')
	->join('Class', 'Student.class', '=', 'Class.code')
	->join('section','Student.section','=','section.id')
	->select('Student.regiNo','Student.rollNo','Student.dob', 'Student.firstName','Student.middleName','Student.lastName','Student.fatherName','Student.motherName', 'Student.group','Student.shift','Student.class as classcode','Class.Name as class','Student.section','Student.session','Student.extraActivity','section.name as section_name')
	->where('Student.regiNo','=',$regiNo)
	->where('Student.class', '=',$class)
	->where('Student.isActive', '=', 'Yes');
	if($student->count()>0) {
		$student = $student->first();
		$section = $student->section;
		foreach($exam_ids as $exm):
		$merit[$exm] = DB::table('MeritList')
		->select('regiNo', 'grade', 'point', 'totalNo','section_id')
		->where('exam', $exm)
		->where('class', $class)
		->where('session', trim($student->session))
		->where('section_id', trim($section))
		//->where('regiNo',$regiNo)
		//->orderBy('point', 'DESC')
		//->orderBy('point')
		->orderBy('totalNo', 'DESC')
		->orderBy('created_at', 'ASC')
		->get();
		endforeach;
		echo "<pre>";print_r($merit);
		exit;
		//->orderBy('totalNo', 'DESC')->get();
		//echo "<pre>";print_r($merit);exit;
			if (empty($student)  || empty($merit)) {
				return Redirect::back()->with('noresult', 'Result Not Found!');
			}else{
				$meritdata = new Meritdata();
				$position  = 0;
				foreach ($merit as $m) {
					$position++;
					//$test[] = $m->section_id .'==='. $section."909".$m->regiNo .'=== '.$regiNo;
					if($m->regiNo === $regiNo && $m->section_id == $section) {
						$meritdata->regiNo = $m->regiNo;
						$meritdata->point = $m->point;
						$meritdata->grade = $m->grade;
						$meritdata->position = $position;
						$meritdata->totalNo = $m->totalNo;
						break;
					}
				}
				$subjects = Subject::select('name', 'code', 'subgroup', 'totalfull')->where('class', '=', $student->classcode)->get();

				$overallSubject = array();
				$subcollection = array();

				$banglatotal = 0;
				$banglatotalhighest = 0;
				$urdu = 0;
				$banglaArray = array();
				$blextra = array();

				$englishtotal = 0;
				$englishtotalhighest = 0;
				$english_total = 0;
				$englishArray = array();
				$enextra = array();
				$totalHighest = 0;
				$totalourall = 0;
				$isBanglaFail=false;
				$isEnglishFail=false;
				foreach ($subjects as $subject) {
					$submarks = Marks::select('written', 'mcq', 'practical', 'ca', 'total', 'point', 'grade','total_marks')->where('regiNo', '=', $student->regiNo)
					->where('subject', '=', $subject->code)->where('exam', '=', $exam)->where('class', '=', $class)->first();
					$maxMarks = Marks::select(DB::raw('max(total) as highest'))->where('class', '=', $class)->where('session', '=', $student->session)
					->where('subject', '=', $subject->code)->where('exam', '=', $exam)->first();

					$submarks["highest"] = $maxMarks->highest;
					$submarks["subcode"] = $subject->code;

					$submarks["subname"] = $subject->name;
					$submarks["outof"] = $submarks->total_marks;
					$totalHighest += $maxMarks->highest;
					$totalourall +=$submarks->total_marks;
					array_push($subcollection, $submarks);
					$outof[] = $subject->totalfull;
				}
		    }
		    echo "dsd";
    }else{
	return  Redirect::back()->with('noresult','Result Not Found!');
	}*/
	$exams_array = explode(',',Input::get('examps_ids'));
	$result_data = DB::table('Student')
       ->join('Class', 'Student.class', '=', 'Class.code')
       ->join('section','Student.section','=','section.id')
       ->join('Marks','Student.regiNo','=','Marks.regiNo')
       // ->join('Marks','Student.regiNo','=','Marks.regiNo')
       //->join('MeritList','Marks.regiNo','=','MeritList.regiNo')
       ->join('Subject','Marks.subject','=','Subject.code')
       ->join('exam','Marks.exam','=','exam.id')
       ->select('Student.regiNo','Student.rollNo','Student.dob', 'Student.firstName','Student.middleName','Student.lastName','Student.fatherName','Student.motherName', 'Student.group','Student.shift','Student.class as classcode','Class.Name as class','Student.section','Student.session','Student.extraActivity','section.name as section_name','Marks.total','Marks.grade','Marks.point','Marks.total_marks','Marks.obtain_marks',DB::raw('MONTH(Marks.created_at) as month'),/*'MeritList.totalNo','MeritList.grade','MeritList.point',*/'exam.type','exam.id as exam_id','Subject.code as subject_code','Subject.name as subject_name')
       ->where('Marks.class',$class)
       ->where('Subject.class', '=', $class)
       ->where('Marks.regiNo', '=', $regiNo)
       ->whereIn('Marks.exam', $exams_array)
        // ->where('Student.class',$class)
       ->get();
       foreach($result_data as $result){
        $exam_name   = $result->exam_id;
	        //if($result->subject_name=='urdu'){
	          $ary[$result->subject_name][] = array('regiNo'=>$result->regiNo,'rollNo'=>$result->rollNo,'firstName'=>$result->firstName,'fatherName'=>$result->fatherName,'classcode'=>$result->classcode,'class'=>$result->class,'session'=>$result->session,'section_name'=>$result->section_name,'total'=>$result->total,'grade'=>$result->grade,'point'=>$result->point,'total_marks'=>$result->total_marks,'obtain_marks'=>$result->obtain_marks ,'month'=>$result->month,'type'=>$result->type,'subject_code'=>$result->subject_code,'subject_name'=>$result->subject_name);
	          $res[$exam_name][]=$result; 
	        //}
       }

       //$extra      = array($exam_name, $subgrpbl, $totalHighest, $subgrpen, $student->extraActivity,$totalourall);
	   $query      = "select left(MONTHNAME(STR_TO_DATE(m, '%m')),3) as month, count(regiNo) AS present from ( select 01 as m union all select 02 union all select 03 union all select 04 union all select 05 union all select 06 union all select 07 union all select 08 union all select 09 union all select 10 union all select 11 union all select 12 ) as months LEFT OUTER JOIN Attendance ON MONTH(Attendance.date)=m and Attendance.regiNo ='".$regiNo."' and  Attendance.status IN ('Present','present','late','Late') GROUP BY m";
	   $attendance = DB::select(DB::RAW($query));
    //echo "<pre>";print_r($ary);
	         return array('result_data'=>$ary,'attendance'=>$attendance);


      //echo "<pre>";print_r($res);
}


	public  function  mpostgenerate()
	{
		$rules = [
			'class'   => 'required',
			'exam'    => 'required',
			'session' => 'required',
			'section' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails()) {
			return Redirect::to('/result/generate')->withErrors($validator)->withInput();
		} else {
			$isGenerated=DB::table('MeritList')
			->select('regiNo')
			->where('class', '=', Input::get('class'))
			->where('session', '=', trim(Input::get('session')))
			->where('exam', '=', Input::get('exam'))
			->where('section_id', '=', Input::get('section'))
			->get();
			if(count($isGenerated)==0)
			{
				 $subjects            = Subject::select('name', 'code', 'type', 'subgroup')->where('class', '=', Input::get('class'))->get();
				 $sectionsHas         = Student::select('section')->where('class', '=', Input::get('class'))->where('section', '=', Input::get('section'))->where('session', trim(Input::get('session')))->where('isActive', '=', 'Yes')->distinct()->orderBy('section', 'asc')->get();
				 $sectionMarksSubmit  = Marks::select('section')->where('class', '=', Input::get('class'))->where('section', '=', Input::get('section'))->where('session', trim(Input::get('session')))->where('exam',Input::get('exam'))->distinct()->get();
				 
				 //echo "ee<pre>";print_r($sectionsHas->toArray());
				// echo "ew<pre>";print_r($sectionMarksSubmit->toArray());
				// exit;
				//dd($sectionsHas);
				if (count($sectionsHas)==count($sectionMarksSubmit))
				{
					$isAllSubSectionMarkSubmit =false;
					$notSubSection='';
					foreach ($sectionsHas as $section) {
						$marksubmit = Marks::select('subject')->where('class', '=', Input::get('class'))->where('section',$section->section)->where('session', trim(Input::get('session')))->where('exam',Input::get('exam'))->distinct()->get();

						if(count($subjects) == count($marksubmit))
						{
							$isAllSubSectionMarkSubmit = true;
							continue;
						}
						else{
							$notSubSection=$section->section;
							$isAllSubSectionMarkSubmit =false;
							break;
						}
					}
					if ($isAllSubSectionMarkSubmit) {
						$fourthsubjectCode = "";
						foreach ($subjects as $subject) {
							if ($subject->type === "Electives") {
								$fourthsubjectCode = $subject->code;
							}
						}


						$students = Student::select('regiNo')
									->join('section','Student.section','=','section.id')
									->select('Student.*','section.name')
									->where('Student.class',    '=', Input::get('class'))
									->where('Student.section',  '=', Input::get('section'))
									->where('Student.session',  '=', trim(Input::get('session')))
									->where('Student.isActive', '=', 'Yes')
									->get();
                      //  echo "<pre>";print_r($students->toArray());exit;
						if (count($students) != 0) {
							$marksSubmitStudents=Marks::select('Marks.regiNo')
							->join('Student', 'Marks.regiNo', '=', 'Student.regiNo')
							->where('Student.isActive', '=', 'Yes')
							->where('Student.class', '=', Input::get('class'))
							->where('Marks.class', '=', Input::get('class'))
							->where('Marks.session', '=', trim(Input::get('session')))
							->where('Marks.exam', '=', Input::get('exam'))
							->distinct()
							->get();
								//echo count($students).'mm'.count($marksSubmitStudents);exit;
							if(count($students)==count($marksSubmitStudents))
							{
								$gparules = GPA::select('gpa', 'grade', 'markfrom')->get();
								$foobar   = array();
								foreach ($students as $student) {
									
									$marks 			= Marks::select('subject', 'grade', 'point', 'total','total_marks')->where('regiNo', '=', $student->regiNo)->where('exam', '=', Input::get('exam'))->get();
									$totalpoint     = 0;
									$totalmarks     = 0;
									$subcounter     = 0;
									$banglamark     = 0;
									$englishmark    = 0;
									$totalexammarks = 0;
									$isfail 		= false;

									foreach ($marks as $mark) {
										if ($this->getSubGroup($subjects, $mark->subject) === "Bangla") {
											$banglamark += $mark->total;

										} else if ($this->getSubGroup($subjects, $mark->subject) === "English") {
											$englishmark += $mark->total;
										} else {
											if ($mark->subject === $fourthsubjectCode) {
												if ($mark->point >= 2.00) {
													$totalmarks += $mark->total;
                                                    $totalexammarks +=$mark->total_marks;
													//$totalpoint += $mark->point - 2;


												} else {
													$totalmarks += $mark->total;
													$totalexammarks +=$mark->total_marks;
												}
												$subcounter--;

											} else {
												$totalmarks += $mark->total;
												$totalpoint += $mark->point;
												$totalexammarks +=$mark->total_marks;

											}

										}

 
										$subcounter++;

										if ($mark->subject !== $fourthsubjectCode && $mark->grade === "F") {
											$isfail = true;
										}
									}


									if ($banglamark > 0) {

										$blmarks = floor($banglamark / 2);

										$totalmarks += $banglamark;

										$totalpoint += $this->pointCalculator($blmarks, $gparules);

										$subcounter--;
									}


									if ($englishmark > 0) {

										$enmarks = floor($englishmark / 2);
										$totalmarks += $englishmark;
										$totalpoint += $this->pointCalculator($enmarks, $gparules);
										$subcounter--;
									}
									//echo "emarks".$subcounter ."gpa".print_r( $gparules,true) ;
                                    $point = array('4','3.5','3','2.5','2');
                                    //$percent  = array('100'=>'A+',)
                                    if( $subcounter ==0){
                                    	return Redirect::to('/result/generate')->withInput()->with("noresult", "please add GPA rule in setting");
                                    }
									$grandPoint = ($totalpoint / $subcounter);
									if ($isfail) {
										$grandGrade = $this->gradnGradeCal(0.00, $gparules);
									} else {

										$grandGrade = $this->gradnGradeCal($grandPoint, $gparules);
									}
									
									$grandtotal     = $totalmarks/$totalexammarks * 100;
                                   
								     if ($grandtotal <= 100 && $grandtotal >= 95){
								     		
								     		$grade = 'A+';
								     		//$gpoint = '4.00' 
								     }
								     elseif ($grandtotal >= 90 &&$grandtotal < 95){
								     	$grade = 'A';
								     }
								     elseif ($grandtotal < 90 && $grandtotal >= 80){
								     	$grade = 'B+';
								     }
								     elseif ($grandtotal <= 79  && $grandtotal >= 70){
								     	$grade = 'B';
								     }
								     elseif ($grandtotal <= 69 && $grandtotal >= 60 ){
								     	$grade = 'C';
								     }else{
								     	$grade = 'F';
								     }



									//if($grandtotal)
									//echo "<pre>dd".$grandPoint ;print_r($grandGrade);
									//echo "grade = ".$grade;
									$merit          		= new MeritList;
									$merit->class   		= Input::get('class');
									$merit->session 		= trim(Input::get('session'));
									$merit->exam    		= Input::get('exam');
									$merit->regiNo  		= $student->regiNo;
									$merit->totalNo 		= $totalmarks;
									$merit->point   		= $grandPoint;
									//$merit->grade   = $grandGrade;
									$merit->grade   		= $grade;
									$merit->section_id   	= $student->section;
                                // echo "<pre>";print_r($merit );
									$merit->save();
                                    //$test[] = $merit;
								 

								}
													//echo "<pre>";print_r($test );
									//exit;
							}
							else {

								return Redirect::to('/result/generate')->withInput()->with("noresult", "All students examintaion marks not submited yet!!");
							}
						}
						else
						{
							return Redirect::to('/result/generate')->withInput()->with("noresult", "There is no students in this class!!");
						}
						return Redirect::to('/result/generate')->with("success", "Result Generate and Publish Successfull.");
					}
					else
					{
						return Redirect::to('/result/generate')->withInput()->with("noresult", "Section ".$notSubSection." all subjects marks not submited yet!!");

					}
				}
				else{
					return Redirect::to('/result/generate')->withInput()->with("noresult", "All sections marks not submited yet!!");
				}
			}
			else{
				return Redirect::to('/result/generate')->withInput()->with("noresult", "Result already generated for this class,session and exam!");
			}
		}
	}

}

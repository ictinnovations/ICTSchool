<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use App\Student;
use App\User;
use App\SectionModel;
use App\ClassModel;
use App\Referal;
use App\FeeSetup;
use App\AdmissionfeeCollection;
use Hash;
use DB;
use App\Ictcore_integration;
use App\Http\Controllers\ictcoreController;
use Carbon\Carbon;
class foobar{

}
Class formfoo2{

}
class studentController extends BaseController {

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
		$classes = ClassModel::select('name','code')->get();
		
		$section = SectionModel::select('id','name')->where('class_code','=','cl1')->get();
		//$sections = SectionModel::select('name')->get();
		$family_id = Input::get('family_id');
		$families = DB::table('Student')
					->join('Class', 'Student.class', '=', 'Class.code')
					->join('section', 'Student.section', '=', 'section.id')
					->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.family_id',
					'Class.Name as class', 'Student.presentAddress', 'Student.gender', 'Student.about_family','section.name')
					->where('Student.isActive', '=', 'Yes')
					->groupBy('Student.fatherCellNo')
					->groupBy('Student.family_id')
					//->having('Student.family_id', '<', 3)
					->get();
		//return View::Make('app.studentCreate',compact('classes'));
		return View('app.studentCreate',compact('classes','section','families','family_id'));
	}
//

	public function search(Request $request)
	{

		
			$query = Input::get('query');
			$output="";
			$data=DB::table('Student')
			->join('Class','Student.class','=','Class.code')
			->select('Student.*','Class.name as class')
			->where('Student.isActive', '=', 'Yes')
			//->where('Student.firstName','LIKE','%'.$query."%")
			//->orwhere('Student.lastName','LIKE','%'.$query."%")
			//->orWhere(CONCAT('Student.firstName', " ", 'Student.lastName'), 'LIKE', '%'.$query.'%')
		    ->WhereRaw("concat(Student.firstName, ' ', Student.lastName) like '%$query%' ")
			->orwhere('Student.fatherName','LIKE','%'.$query."%")
			->orwhere('Student.b_form','LIKE','%'.$query."%")
			->orwhere('Student.session','LIKE','%'.$query."%")
			->orwhere('Student.class','LIKE','%'.$query."%")
			->orwhere('Student.dob','LIKE','%'.$query."%")
			->orwhere('Student.parmanentAddress','LIKE','%'.$query."%")
			->orwhere('Student.discount_id','LIKE','%'.$query."%")
			->orwhere('Student.regiNo','LIKE','%'.$query."%")
			->orderBy('Student.session', 'desc')
			->orderBy('Student.class', 'asc')
			->limit(20)
			->get();
			//return Response($output);
			 $output = '<ul class="dropdown-menu" id="searchlist" style="display:block; position:relative;font-size: 2rem !important;">';
		      foreach($data as $row)
		      {
		      	$section = DB::table('section')->where('id',$row->section)->first();
		       $output .= '
		       <li data-sid="'.$row->id.'"><a href="#">'.$row->firstName.''.$row->lastName.' (class = '.$row->class.')'.'(sectoion = '.$section->name.')'.'(regiNo = '.$row->regiNo.')'.'(session = '.$row->session.')'.'</a></li>
		       ';
		      }
		      $output .= '</ul>';
		      echo $output;
		
	}
	public function familystudent(Request $request)
	{

		
			$query = Input::get('query');
			$output="";
			$data=DB::table('Student')
			->join('Class','Student.class','=','Class.code')
			->select('Student.*','Class.name as class')
			->where('Student.isActive', '=', 'Yes')
			//->where('Student.firstName','LIKE','%'.$query."%")
			//->orwhere('Student.lastName','LIKE','%'.$query."%")
			//->orWhere(CONCAT('Student.firstName', " ", 'Student.lastName'), 'LIKE', '%'.$query.'%')
		    ->WhereRaw("concat(Student.firstName, ' ', Student.lastName) like '%$query%' ")
			->orwhere('Student.fatherName','LIKE','%'.$query."%")
			//->orwhere('Student.b_form','LIKE','%'.$query."%")
			//->orwhere('Student.session','LIKE','%'.$query."%")
			//->orwhere('Student.class','LIKE','%'.$query."%")
			//->orwhere('Student.dob','LIKE','%'.$query."%")
			//->orwhere('Student.parmanentAddress','LIKE','%'.$query."%")
			//->orwhere('Student.discount_id','LIKE','%'.$query."%")
			->orwhere('Student.regiNo','LIKE','%'.$query."%")
			//->orderBy('Student.session', 'desc')
			//->orderBy('Student.class', 'asc')
			->limit(20)
			->get();
			//return Response($output);
			 $output = '';
		      foreach($data as $row)
		      {
		      	$section = DB::table('section')->where('id',$row->section)->first();
		       	$output .= '
		       		<tr data-sid="'.$row->id.'"><td><input name="regino[]" type="checkbox" value="'.$row->id.'"></td><td>'.$row->firstName.''.$row->lastName.' </td><td>'.$row->regiNo.'</td><td>'.$row->class.'</td><td>'.$section->name.'</td></tr>'
		       ;
		      }
		     // $output .= '</ul>';
		      echo $output;
		
	}

	public function family(Request $request)
	{

		
			$query = Input::get('query');
			$output="";
			$data=DB::table('Student')->where('fatherCellNo','LIKE','%'.$query."%")
			->groupBy('fatherCellNo')
			->limit(20)
			->get();
			//return Response($output);
			 $output = '<ul class="dropdown-menu" id="searchlist" style="display:block; position:relative">';
		      foreach($data as $row)
		      {
		      	//$section = DB::table('section')->where('id',$row->section)->first();
		       if($row->family_id==''){
		       	$row->family_id = hexdec(substr(uniqid(rand(), true), 5, 5));
		       }
		       $output .= '<li data-check="'.$row->fatherCellNo.'"  data-familyid="'.$row->family_id.'" data-father="'.$row->fatherName.'" data-phone="'.$row->fatherCellNo.'" data-mother_name="'.$row->motherName.'" data-mother_phone="'.$row->motherCellNo.'" data-localGuardian="'.$row->localGuardian.'" data-localGuardianCell="'.$row->localGuardianCell.'"><a href="#">'.$row->fatherName.' ('.$row->fatherCellNo.')'.'</a></li>';
		      }
		      $output .= '</ul>';
		      echo $output;
		
	}
	public function get_family_id()
	{
		// return '98';
		$query = Input::get('query');
		///return $query;
		if(strlen($query)>=10 ){

			$referalname  = '';
			$referalid    = '';
			$fathername   = '';
			$fatherphone  = '';
			$localg       = '';
			$unique_code  = '';
		$data=DB::table('Student')->where('fatherCellNo','LIKE','%'.$query.'%')
		 ->first();
		 if(!empty($data)){
		 	$unique_code = $data->family_id;
		 	$fathername  = $data->fatherName;
		 	$fatherphone = $data->fatherCellNo;
		 	$localg      = $data->localGuardianCell;
		 	
		 	if($unique_code=='' || $unique_code==NULL){
		 		//$unique_code =	str_random(6);
		 		//$unique_code =	hexdec(substr(uniqid(rand(), true), 5, 5));
		 	}
		 	$check_referals = DB::table('referals')->where('family_id',$unique_code)->first();
		 	if(!empty($check_referals)){
		 		$referals   = $check_referals->refral_id;
		 		$referals_info = DB::table('Student')->where('family_id',$referals)->first();
		 		$referalname = $referals_info->fatherName .'('.$referals_info->family_id.')';
		 		$referalid   = $referals_info->family_id ;
		
		 	}else{
		 		$referalname = '';
		 		$referalid  = '';
		 	}
		 }else{
		 	//$unique_code =	 hexdec(substr(uniqid(rand(), true), 5, 5));
		 }
		 //echo $unique_code;
		 return array('unique_code'=>$unique_code,'referalname'=>$referalname,'referalid'=>$referalid,'fathername'=>$fathername ,'fatherphone'=>$fatherphone,'localg'=>$localg);

		}
		//echo strlen($query);
	}
	public function get_family_data()
	{
		// return '98';
		$query = Input::get('query');

		//$ex = explode('(', $query)
		//preg_match_all('\[(\d+)=>([\d,]+)\]', $query, $matches);
			/*$query = trim(trim($query, '['), ']');
			$query = explode(', ', $query);*/

			//$text = '[This] is a [test] string, [eat] my [shorts].';
//preg_match_all("/\[[^\]*\]/", $query, $matches);
//var_dump($matches[0]);

//$phonenumberlist = '[0761234567, 072999999, 0731111111]';
/*$text = 'ignore everything except this(text)';
preg_match('#\((.*?)\)#', $text, $match);
if(isset($match[0])){
	$q = $match[1];
}else{
	$q = $query;
}*/

if( preg_match( '!\(([^\)]+)\)!', $query, $match ) ){
    $query = $match[1];
}
//print $query ;
		//return $text ;
		if(strlen($query)>=3 ){

			$referalname  = '';
			$referalid    = '';
			$fathername   = '';
			$fatherphone  = '';
			$localg       = '';
			$unique_code  = '';
		$data=DB::table('Student')->where('family_id','LIKE','%'.$query.'%')
		 ->first();
		 if(!empty($data)){
		 	$unique_code = $data->family_id;
		 	$fathername  = $data->fatherName;
		 	$fatherphone = $data->fatherCellNo;
		 	$localg      = $data->localGuardianCell;
		 	
		 	if($unique_code=='' || $unique_code==NULL){
		 		//$unique_code =	str_random(6);
		 		//$unique_code =	hexdec(substr(uniqid(rand(), true), 5, 5));
		 	}
		 	$check_referals = DB::table('referals')->where('family_id',$unique_code)->first();
		 	if(!empty($check_referals)){
		 		$referals   = $check_referals->refral_id;
		 		$referals_info = DB::table('Student')->where('family_id',$referals)->first();
		 		$referalname = $referals_info->fatherName .'('.$referals_info->family_id.')';
		 		$referalid   = $referals_info->family_id ;
		
		 	}else{
		 		$referalname = '';
		 		$referalid  = '';
		 	}
		 }else{
		 	//$unique_code =	 hexdec(substr(uniqid(rand(), true), 5, 5));
		 }
		 //echo $unique_code;
		 return array('unique_code'=>$unique_code,'referalname'=>$referalname,'referalid'=>$referalid,'fathername'=>$fathername ,'fatherphone'=>$fatherphone,'localg'=>$localg);

		}
		//echo strlen($query);
	}

	public function getrefral($refral)
	{
		//$refral = Input::get('query');
		$data=DB::table('Student')
		        ->where('fatherName','LIKE','%'.$refral.'%')
		        ->orwhere('family_id','LIKE','%'.$refral.'%')
		        ->groupBy('fatherName')
		        ->limit(20)
		        ->get();
		        return response()->json($data);
		$autocom = array();
		if($data->count()>0){
			$data = $data->get();
			foreach($data as $ref){
				$autocom[] = $ref->fatherName.'('.$ref->family_id.')';
			}

			//$json = implode(',',$autocom);
			$json = '"'.implode('","', $autocom).'"';
			//$json =  join($autocom, '","');
			return $json;
		}

	}
	public function f_id_list($f_id)
	{
		//$refral = Input::get('query');
		$data=DB::table('Student')
		        //->where('fatherName','LIKE','%'.$refral.'%')
		        ->where('family_id','LIKE','%'.$f_id.'%')
		        ->groupBy('family_id')
		        ->limit(20)
		        ->get();
		        return response()->json($data);
		$autocom = array();
		if($data->count()>0){
			$data = $data->get();
			foreach($data as $ref){
				$autocom[] = $ref->fatherName.'('.$ref->family_id.')';
			}

			//$json = implode(',',$autocom);
			$json = '"'.implode('","', $autocom).'"';
			//$json =  join($autocom, '","');
			return $json;
		}

	}
	public  function getRegi($class,$session,$section)
	{

		
		$ses         = trim($session);
		$stdcount    = Student::select(DB::raw('count(*) as total'))->where('class','=',$class)->where('session','=',$ses)->first();
		$stdseccount = Student::select(DB::raw('count(*) as total'))->where('class','=',$class)->where('session','=',$ses)->where('section','=',$section)->first();
		$regiNolast  = Student::where('class','=',$class)->where('session','=',$ses)->where('section','=',$section)->orderBy('id', 'desc')->first();
		$r           = intval($stdcount->total)+1;
		//echo substr($regiNolast->regiNo,4);
		
		if(strlen($r)<2)
		{
			$r = '0'.$r;
		}
		    $c     = intval($stdseccount->total)+1;




		$cl = substr($class,2);
       
        if(!empty($regiNolast) && $r == substr($regiNolast->regiNo,4)){
         	$r   = intval($stdcount->total)+2;
         	$c   = intval($stdseccount->total)+2;
		}
		$foo = array();
		if(strlen($cl)<2) {
			$foo[0] = substr(date("Y"), 2).get_current_session()->id .'0'.$cl.$r;
		}
		else
		{
			$foo[0] =  substr(date("Y"), 2).get_current_session()->id .$cl.$r;
		}
		if(strlen($c)<2) {
			$foo[1] ='0'.$c;
		}
		else
		{
			$foo[1] = $c;
		}

		$regicheck     = Student::select('*')->where('regiNo','=',$foo[0])->where('class','=',$class)->where('session','=',$ses);
		
		$regichecks    = Student::select('*')->where('rollNo','=',$foo[1])->where('class','=',$class)->where('session','=',$ses)->where('section','=',$section);

		if(!empty($regicheck->first()) && $regicheck->count()>0){
			$cnt    = $regicheck->count() +1;
			$foo[0] = $regicheck->first()->regiNo + $cnt ; 

		}
		if(!empty($regichecks->first()) && $regichecks->count()>0){
			$cnts   = $regichecks->count() + 1;
			$foo[1] = $regicheck->first()->rollNo + $cnts ; 

		}
		return $foo;
	}

	/**
	* Show the form for creating a new resource.
	*
	* @return Response
	*/
	public function create(Request $request)
	{

		$rules=[
		
		'regiNo'      => 'required',
		'fname'       => 'required',
		//'lname' => 'required',
		'gender'      => 'required',
		//'religion' => 'required',
		//'bloodgroup' => 'required',
		//'nationality' => 'required',
		//'dob' => 'required',
		'session'       => 'required',
		'class'         => 'required',
		'section'       => 'required',
		'rollNo'        => 'required',
		'shift'         => 'required',
		'photo'         => 'mimes:jpeg,jpg,png',
		//'b_form' => 'required',
		'fatherName'   => 'required',
		'fatherCellNo' => 'required',
		'adfee'        => 'integer',
		//'motherName' => 'required',
		//'motherCellNo' => 'required',
		//'presentAddress' => 'required',
		//'parmanentAddress' => 'required'
	];

	   $messsages = array(
		'lname.required'=>'The Last Name field is required',
		'fname.required'=>'The First Name field is required',
		'adfee.integer'=>'The admission fee must be an integer.',
		
	);

	

	$validator = \Validator::make(Input::all(), $rules,$messsages);
	$validator = \Validator::make(Input::all(), $rules,$messsages);


	//$messages = array( 'lname' => 'The Lastname field is required' );
	//$validator = \Validator::make(Input::all(), $rules, $messages);
	//$validator = \Validator::make(Input::all(), $rules);
	if ($validator->fails())
	{
		return Redirect::to('/student/create')->withErrors($validator)->withInput();
	}
	else {
		if( preg_match( '!\(([^\)]+)\)!', Input::get('refer_by'), $match ) ){
    			$refer_by = $match[1];
		}else{
			$refer_by =Input::get('refer_by');
		}
		if( preg_match( '!\(([^\)]+)\)!', Input::get('family_id'), $match ) ){
    			$family_id = $match[1];
		}else{
			$family_id =Input::get('family_id');
		}

		if($family_id ==$refer_by ){

			return Redirect::to('/student/create')->withInput()->withErrors('Family Id And Refer Id same ');

		}

		$check_ids = DB::table('Student')
            			->where('family_id', '=', $family_id)
						//->where('fatherCellNo', '=', Input::get('fatherCellNo'))
						->get();
			    $get_family_ids = array();
			//echo "<pre>".Input::get('family_id');print_r($check_ids->toArray());
			if(count($check_ids->toArray())>0){
				foreach($check_ids as $f_id){
					if($f_id->fatherCellNo==Input::get('fatherCellNo')){
						$get_family_ids[] = $f_id->family_id;
					}
				}
	            //echo "<pre>".$family_id;print_r($get_family_ids);exit;
				if(!in_array($family_id, $get_family_ids)){

						return Redirect::to('/student/create')->withInput()->withErrors('Family Id Already Assign Other Family please select different ID');
				}
			}
//echo "testr";exit;

		if(Input::file('photo')!=''){

		$fileName=Input::get('regiNo').'.'.Input::file('photo')->getClientOriginalExtension();
		
		}else{
			$fileName='';
		}
        
		$student = new Student;
		$student->regiNo = Input::get('regiNo');
		$student->discount_id = Input::get('discount_id');
		if(Input::get('discount_id')==''){
			$student->discount_id = 0;
		}
		
		/*if(Input::get('discount_id') ==''){
			$student->discount_id = NULL;
		}*/
		$student->firstName = Input::get('fname');

		$student->middleName = Input::get('mname');
		if(Input::get('mname') ==''){
			$student->middleName = "";
		}
		$student->lastName = Input::get('lname');
		if(Input::get('lname') ==''){
			$student->lastName = "";
		}
		$student->gender = Input::get('gender');
		
		$student->religion = Input::get('religion');

		if(Input::get('religion') ==''){
			$student->religion = "";
		}
		$student->bloodgroup = Input::get('bloodgroup');

		if(Input::get('bloodgroup')==''){
		$student->bloodgroup="";

		}
		$student->dob         = Input::get('dob');
		if(Input::get('dob')==''){
			$student->dob         = '';
		}
		$student->session     = get_current_session()->id;
		//$student->session= trim(Input::get('session'));
		$student->class       = Input::get('class');
		$student->section     = Input::get('section');
		$student->group       = Input::get('group');
		$student->rollNo      = Input::get('rollNo');
		$student->shift       = Input::get('shift');

		$student->photo       = $fileName;
		$student->nationality = Input::get('nationality');
		if(Input::get('nationality') ==''){
			$student->nationality="";
		}
		$student->extraActivity= Input::get('extraActivity');
		if(Input::get('extraActivity') ==''){
			$student->extraActivity = "";
		}
		$student->remarks= Input::get('remarks');
       if(Input::get('remarks') ==''){
			$student->remarks = "";
		}
		$student->b_form= Input::get('b_form');
		if(Input::get('b_form')==''){
			$student->b_form         = '';
		}
		$student->fatherName= Input::get('fatherName');
		$student->fatherCellNo= Input::get('fatherCellNo');
		
		$student->motherName= Input::get('motherName' );
		if(Input::get('motherName')==''){
			$student->motherName= "";
			
		}
		$student->motherCellNo= Input::get('motherCellNo');
		if(Input::get('motherCellNo')==''){
			$student->motherCellNo="";
		}
		$student->localGuardian= Input::get('localGuardian');
		if(Input::get('localGuardian')==''){
			$student->localGuardian="";
		}
		$student->localGuardianCell= Input::get('localGuardianCell');
		if(Input::get('localGuardianCell') ==''){
			$student->localGuardianCell="";
		}

		$student->family_id=$family_id;
		$student->about_family=Input::get('familyc');

		$student->presentAddress= Input::get('presentAddress');
		if(Input::get('presentAddress')==''){
			$student->presentAddress         = '';
		}
		$student->parmanentAddress= Input::get('parmanentAddress');
		if(Input::get('parmanentAddress')==''){
			$student->parmanentAddress='';
		}
		$student->isActive= "Yes";

		$hasStudent = Student::where('regiNo','=',Input::get('regiNo'))->where('class','=',Input::get('class'))->first();
		if ($hasStudent)
		{
			$messages = $validator->errors();
			$messages->add('Duplicate!', 'Student already exits with this registration no.');
			return Redirect::to('/student/create')->withErrors($messages)->withInput();
		}
		else {
			$student->save();
			if( Input::file('photo')!=''){
             Input::file('photo')->move(base_path() .'/public/images',$fileName);
         	}
         	if(Input::get('adfee')>0){
         		$admissionfee = new AdmissionfeeCollection;
         		$admissionfee->student_id = $student->id;
         		$admissionfee->admission_fee = Input::get('adfee');
         		$admissionfee->save(); 
         	}
         	if(Input::get('family_id')!='' && Input::get('refer_by')!='' /*&& Input::get('f_status')=='new'*/){

         		$saverefral              = new Referal;
         		$saverefral->student_id  = $student->id;
         		$saverefral->family_id   = $family_id;
         		$saverefral->refral_id   = $refer_by;
         		$saverefral->save(); 
         	}
               /*  $user = new User;

                $user->firstname = Input::get('fname');
                $user->lastname  = Input::get('lname');
                $user->email =     Input::get('regiNo').'@gmail.com';
              	$user->login     = Input::get('regiNo');
              	$user->group     =  'Student';
                $user->password  =	Hash::make(Input::get('regiNo'));
                $user->save();

                 $ictcore_integration = Ictcore_integration::select("*")->first();
                 
			if(!empty($ictcore_integration) && $ictcore_integration->ictcore_url !='' && $ictcore_integration->ictcore_user !='' && $ictcore_integration->ictcore_password !=''){ 

							 $ict  = new ictcoreController();
							 	$data = array(
								'first_name' => $student->firstName,
								'last_name' => $student->lastName,
								'phone'     => $student->fatherCellNo,
								'email'     => '',
								);
								$contact_id = $ict->ictcore_api('contacts','POST',$data );

                               $message = 'School name'.'<br>'.'Login Name: '. Input::get('regiNo').'Password: '.Input::get('regiNo');
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





			return Redirect::to('/student/create')->with("success","Student Admited Succesfully.");
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
	$students=array();
	$classes = ClassModel::pluck('name','code');
	$formdata = new formfoo2;
	$formdata->class="";
	$formdata->section="";
	$formdata->shift="";
	$formdata->session="";
	//return View::Make("app.studentList",compact('students','classes','formdata'));
	return View("app.studentList",compact('students','classes','formdata'));
}
public function getList()
{
	
if(Input::get('search')==''){
	$rules = [
		'class' => 'required',
		'section' => 'required',
		'shift' => 'required',
		'session' => 'required'


	];
}else{
	$rules = [
	
	];
}
	$validator = \Validator::make(Input::all(), $rules);
	if ($validator->fails()) {
		return Redirect::to('/student/list')->withInput(Input::all())->withErrors($validator);
	} else {

		if(Input::get('search')=='yes'){
         //echo Input::get('student_name');
            /*$exp       = explode('(',Input::get('student_name'));
         	$class     = explode(')',$exp[1]);
         	$section   = explode(')',$exp[2]);
         	$regiNo    = explode(')',$exp[3]);
         	$session   = explode(')',$exp[4]);
         	$class1    = explode('=',$class[0]);
         	$section1  = explode('=',$section[0]);
         	$regiNo1   = explode('=',$regiNo[0]);
         	$session1  = explode('=',$session[0]);*/

	        $students = DB::table('Student')
			->join('Class', 'Student.class', '=', 'Class.code')
			->join('section', 'Student.section', '=', 'section.id')
			->select('Student.id','Student.family_id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
			'Class.Name as class','Class.code as class_code', 'Student.presentAddress', 'Student.gender', 'Student.session','section.name','section.id as section_id')
			->where('Student.isActive', '=', 'Yes')
			->where('Student.id',trim(Input::get('student_name')))
			//->where('session',trim($session1[1]))
			//->where('regiNo',trim($regiNo1[1]))
			->first();
			//echo "<pre>ffdf";print_r($students);
			//exit;
         	return Redirect::to('student/view/'.$students->id);
		}else{
		$class_code	=Input::get('class');
		$section_id=Input::get('section');
		$shift=Input::get('shift');
		$session_year =trim(Input::get('session'));
		$students = DB::table('Student')
		->join('Class', 'Student.class', '=', 'Class.code')
		->join('section', 'Student.section', '=', 'section.id')
		->select('Student.id','Student.family_id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
		'Class.Name as class', 'Student.presentAddress', 'Student.gender', 'Student.religion','section.name')
		->where('Student.isActive', '=', 'Yes')
		->where('Student.class',$class_code)
		->where('Student.section',$section_id)
		->where('Student.shift',$shift)
		->where('Student.session',$session_year)
		->get();
		}
		if(count($students)<1)
		{
			return Redirect::to('/student/list')->withInput(Input::all())->with('error','No Students Found!');

		}
		else {
			$classes            = ClassModel::pluck('name','code');
			$formdata           = new formfoo2;
			$formdata->class    = Input::get('class');
			$formdata->section  = Input::get('section');
			$formdata->shift    = Input::get('shift');
			$formdata->session  = trim(Input::get('session'));
			$month=8;
			$fee_name=2;
			//return View::Make("app.studentList", compact('students','classes','formdata'));
			return View("app.studentList", compact('students','classes','formdata','month','fee_name'));
		}
	}

}

public function family_list()
{
	$students = DB::table('Student')
					->join('Class', 'Student.class', '=', 'Class.code')
					->join('section', 'Student.section', '=', 'section.id')
					->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.family_id',
					'Class.Name as class', 'Student.presentAddress', 'Student.gender', 'Student.about_family','section.name')
					->where('Student.isActive', '=', 'Yes')
					->groupBy('Student.fatherCellNo')
					->groupBy('Student.family_id')
					//->having('Student.family_id', '<', 3)
					->get();
		return View("app.familyList", compact('students'));

}
public function family_student_list($family_id)
{
	/*$students = DB::table('Student')
					->join('Class', 'Student.class', '=', 'Class.code')
					->join('section', 'Student.section', '=', 'section.id')
					->leftjoin('feesSetup','Student.class','=','feesSetup.class')
					->select('Student.id','Student.discount_id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
		'Class.Name as class','Class.code as class_code', 'Student.presentAddress','Student.section', 'Student.gender', 'Student.religion','section.name','feesSetup.fee')
					->where('Student.isActive', '=', 'Yes')
					->where('Student.family_id', '=', $family_id)
					->orwhere('Student.fatherCellNo', '=', $family_id)
					->get();*/

		$students = DB::table('Student')
					->join('Class', 'Student.class', '=', 'Class.code')
					->join('section', 'Student.section', '=', 'section.id')
					//->leftjoin('feesSetup','Student.class','=','feesSetup.class')
					->leftJoin('feesSetup', function($join){
					    $join->on('Student.class', '=', 'feesSetup.class');
					    //$join->on('feesSetup.type','=',DB::raw("Monthly"));
					     $join->where('feesSetup.type', '=', "Monthly");
					})
					->select('Student.id','Student.family_id','Student.discount_id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
		'Class.Name as class','Class.code as class_code', 'Student.presentAddress','Student.section', 'Student.gender', 'Student.religion','section.name','section.id as section_id','feesSetup.fee')
					->where('Student.isActive', '=', 'Yes')
					//->where('Student.family_id', '=', $family_id)
					//->orwhere('Student.fatherCellNo', '=', $family_id)
					->where(function($q) use( $family_id) {
				        $q->where('Student.family_id', '=', $family_id)
				        ->orWhere('Student.fatherCellNo', '=', $family_id);
				      })
					->get();
		//echo "<pre>";print_r($students);exit;
		return View("app.familystudentList", compact('students','family_id'));

}

public function add_family_discount($family_id){

	$ids = Input::get('student_id');
	$dis = Input::get('discount');
	$i=0;
	//echo "<pre>";print_r($ids);
	//echo "<pre>";print_r($dis);
	 foreach($ids  as $id){
	 	//echo $dis[$i];
	 	//echo "<br>";
	 	//echo $id;
	 	$student = Student::find($id);
	 	$student->discount_id = $dis[$i];
	 	if($dis[$i]==''){
	 		$student->discount_id = 0;
	 	}
		
		$student->save();
	 	$i++;

	 }
	 //exit;

	 return Redirect::to('/family/students/'.$family_id)->with("success","Family Discount Added Succesfully.");



}




public function view($id)
{
	$student = DB::table('Student')
	->join('Class', 'Student.class', '=', 'Class.code')
	->join('section', 'Student.section', '=', 'section.id')
	->join('acadamic_year', 'Student.session', '=', 'acadamic_year.id')
	//->leftjoin('Attendance', 'Student.regiNo', '=', 'Attendance.regiNo')
	->select('Student.id', 'Student.regiNo','Student.rollNo','Student.firstName','Student.middleName','Student.lastName',
	'Student.fatherName','Student.motherName', 'Student.fatherCellNo','Student.motherCellNo','Student.localGuardianCell',
	'Class.Name as class','Class.code as class_code','Student.presentAddress','Student.gender','Student.religion','Student.section','Student.shift',
	'Student.group','Student.dob','Student.bloodgroup','Student.nationality','Student.photo','Student.extraActivity','Student.remarks',
	'Student.localGuardian','Student.parmanentAddress','section.name as section_name','acadamic_year.title as session','acadamic_year.id as session_id')
	->where('Student.id','=',$id)
	->first();
	$attendances = DB::table('Attendance')->where('Attendance.date',Carbon::today()->toDateString())->where('regiNo',$student->regiNo)->first();
	   //return View::Make("app.studentView",compact('student'));
	$fees  = FeeSetup::select('id','title')->where('class','=',$student->class_code)->where('type','=','Monthly')->first();

	$now   = Carbon::now();
             $year  =  $now->year;
            $month =  $now->month;
            if(!empty($fees)){
            	$fee_name =  $fees->id;
        	}else{
        		$fee_name ='';
        	}
    /*$fee = DB::table('feesSetup')
		       ->where('class',Input::get('class'))
		       ->where('type','Monthly')
		       ->first();*/
	return View("app.studentView",compact('student','attendances','year','month','fee_name'));
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
	$student= Student::find($id);
	
	$sections = SectionModel::select('id','name')->where('class_code','=',$student->class)->get();
	//$sections = $sections->toArray();
      // $sections = SectionModel::pluck('id', 'name')->where('class_code','=',$student->class);
	//echo "<pre>";print_r($sections);
	//dd($student);
	//$sections = SectionModel::select('name')->get();
	//return View::Make("app.studentEdit",compact('student','classes'));
	return View("app.studentEdit",compact('student','classes','sections'));
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
		'gender' => 'required',
		//'religion' => 'required',
		//'bloodgroup' => 'required',
		//'nationality' => 'required',
		//'dob' => 'required',
		'session' => 'required',
		'class' => 'required',
		'section' => 'required',
		'rollNo' => 'required',
		'shift' => 'required',
		//'b_form' => 'required',
		'fatherName' => 'required',
		'fatherCellNo' => 'required',
		//'motherName' => 'required',
		//'motherCellNo' => 'required',
		//'presentAddress' => 'required',
		//'parmanentAddress' => 'required'
	];
	$validator = \Validator::make(Input::all(), $rules);
	if ($validator->fails())
	{
		return Redirect::to('/student/edit/'.Input::get('id'))->withErrors($validator);
	}
	else {

		$student = Student::find(Input::get('id'));

		if(Input::hasFile('photo'))
		{

			if(substr(Input::file('photo')->getMimeType(), 0, 5) != 'image')
			{
				$messages = $validator->errors();
				$messages->add('Notvalid!', 'Photo must be a image,jpeg,jpg,png!');
				return Redirect::to('/student/edit/'.Input::get('id'))->withErrors($messages);
			}
			else {

				$fileName=Input::get('regiNo').'.'.Input::file('photo')->getClientOriginalExtension();
				$student->photo = $fileName;
				Input::file('photo')->move(base_path() .'/public/images',$fileName);
			}

		}
		else {
			$student->photo= Input::get('oldphoto');

		}
		//$student->regiNo=Input::get('regiNo');
		//$student->rollNo=Input::get('rollNo');
		/*$student->firstName= Input::get('fname');
		$student->middleName= Input::get('mname');
		$student->lastName= Input::get('lname');
		$student->gender= Input::get('gender');
		$student->religion= Input::get('religion');
		$student->bloodgroup= Input::get('bloodgroup');
		$student->nationality= Input::get('nationality');
		$student->dob= Input::get('dob');
		$student->session= trim(Input::get('session'));
		$student->class= Input::get('class');
		$student->section= Input::get('section');
		$student->group= Input::get('group');
		$student->nationality= Input::get('nationality');
		$student->extraActivity= Input::get('extraActivity');
		$student->remarks= Input::get('remarks');

		$student->fatherName= Input::get('fatherName');
		$student->fatherCellNo= Input::get('fatherCellNo');
		$student->motherName= Input::get('motherName');
		$student->motherCellNo= Input::get('motherCellNo');
		$student->localGuardian= Input::get('localGuardian');
		$student->localGuardianCell= Input::get('localGuardianCell');
		$student->shift= Input::get('shift');

		$student->presentAddress= Input::get('presentAddress');
		$student->parmanentAddress= Input::get('parmanentAddress');*/

		$student->firstName = Input::get('fname');

		$student->middleName = Input::get('mname');
		if(Input::get('mname') ==''){
			$student->middleName = "";
		}
		$student->lastName = Input::get('lname');
		if(Input::get('lname')==''){
			$student->lastName ="";
		}
		$student->gender= Input::get('gender');
		
		$student->religion= Input::get('religion');

		if(Input::get('religion') ==''){
			$student->religion = "";
		}
		$student->bloodgroup= Input::get('bloodgroup');

		if(Input::get('bloodgroup')==''){
		$student->bloodgroup="";

		}
		$student->dob= Input::get('dob');
		if(Input::get('dob')==''){
			$student->dob='';
		}
		$student->session= trim(Input::get('session'));
		$student->class= Input::get('class');
		$student->section= Input::get('section');
		$student->group= Input::get('group');
		//$student->rollNo= Input::get('rollNo');
		$student->shift= Input::get('shift');

		//$student->photo= $fileName;
		$student->nationality= Input::get('nationality');
		if(Input::get('nationality') ==''){
			$student->nationality="";
		}
		$student->extraActivity= Input::get('extraActivity');
		if(Input::get('extraActivity') ==''){
			$student->extraActivity = "";
		}
		$student->remarks= Input::get('remarks');
       if(Input::get('remarks') ==''){
			$student->remarks = "";
		}
		$student->b_form= Input::get('b_form');
		if(Input::get('b_form')==''){
			$student->b_form= "";
		}
		$student->fatherName= Input::get('fatherName');
		$student->fatherCellNo= Input::get('fatherCellNo');
		
		$student->motherName= Input::get('motherName' );
		if(Input::get('motherName')==''){
			$student->motherName= "";
			
		}
		$student->motherCellNo= Input::get('motherCellNo');
		if(Input::get('motherCellNo')==''){
			$student->motherCellNo="";
		}
		$student->localGuardian= Input::get('localGuardian');
		if(Input::get('localGuardian')==''){
			$student->localGuardian="";
		}
		$student->localGuardianCell= Input::get('localGuardianCell');
		if(Input::get('localGuardianCell') ==''){
			$student->localGuardianCell="";
		}

		$student->presentAddress= Input::get('presentAddress');
		if(Input::get('presentAddress')==''){
			$student->presentAddress= "";
		}
		$student->parmanentAddress= Input::get('parmanentAddress');
		if(Input::get('parmanentAddress')==''){
			$student->parmanentAddress='';
		}
        $student->discount_id = Input::get('discount_id');
		if(Input::get('discount_id') ==''){
			$student->discount_id = 0;
		}

		$student->save();

		return Redirect::to('/student/list')->with("success","Student Updated Succesfully.");
	}


}


/**
* Show the form for editing the specified resource.
*
* @param  int  $id
* @return Response
*/
public function family_edit($family_id)
{
	$classes = ClassModel::pluck('name','code');
	$student= Student::where('family_id', '=', $family_id)
						->orwhere('fatherCellNo', '=', $family_id)->first();
	$sections = SectionModel::select('id','name')->where('class_code','=',$student->class)->get();
	//$sections = $sections->toArray();
      // $sections = SectionModel::pluck('id', 'name')->where('class_code','=',$student->class);
	//echo "<pre>";print_r($sections);
	//dd($student);
	//$sections = SectionModel::select('name')->get();
	//return View::Make("app.studentEdit",compact('student','classes'));
	return View("app.familyEdit",compact('student','classes','sections','family_id'));
}

public function family_update()
{
	$rules=[
		'familb'      => 'required',
		'f-name'      => 'required',
		'cell_phone'  => 'required',
		'adfamily_id' => 'required',
	];
	$validator = \Validator::make(Input::all(), $rules);
	if ($validator->fails())
	{
		return Redirect::to('family/edit/'.Input::get('family_id'))->withErrors($validator);
	}
	else {
		$family_id   = trim(Input::get('family_id'));
		$family_idn  = trim(Input::get('adfamily_id'));
		$fathername  = trim(Input::get('f-name'));
		$cell_phone  = trim(Input::get('cell_phone'));


			$check_ids = DB::table('Student')
            			->where('family_id', '<>', Input::get('family_id'))
						->orwhere('fatherCellNo', '<>', Input::get('family_id'))
				/*->where(function($q) use( $family_id) {
			        $q->where('family_id', '<>', $family_id);
			        ->orwhere('fatherCellNo', '<>', $family_id);
			    })*/
				->get();
			    $get_family_ids = array();
			foreach($check_ids as $f_id){

				/*if( Input::get('adfamily_id') === $f_id->family_id && ($f_id->family_id==$family_id || $family_id===$f_id->fatherCellNo)){
							echo "ewew";
				}else{
					echo "dsds";
				}*/
				if($f_id->family_id!='' && ($f_id->family_id!=$family_id )){
					$get_family_ids[] = $f_id->family_id;
				}
			}
            //echo "<pre>".$family_id;print_r($get_family_ids);exit;
			if(in_array(Input::get('adfamily_id'), $get_family_ids)){

					return Redirect::to('family/edit/'.Input::get('family_id'))->withErrors('Family Id Already Assign Other Family plase select different ID');
			}



		DB::table('Student')
            		//->where('family_id', '=', Input::get('family_id'))
					//->orwhere('fatherCellNo', '=', Input::get('family_id'))
					->where(function($q) use( $family_id) {
				        $q->where('Student.family_id', '=', $family_id)
				        ->orWhere('Student.fatherCellNo', '=', $family_id);
				    })
            		->update(['about_family' => Input::get('familb'),'family_id' => $family_idn,'fatherName' =>$fathername,'fatherCellNo' =>$cell_phone ]);

			return Redirect::to('/family/list')->with("success","Family Updated Succesfully.");	
	}

}

public function add_family_student($f_id)
{

	//..echo $f_id;
	//print_r(Input::get('regino'));

	$fm = DB::table('Student')
            		
		->where(function($q) use( $f_id) {
				$q->where('Student.family_id', '=', $f_id)
				->orWhere('Student.fatherCellNo', '=', $f_id);
		})
		->first();

		$family_id =$fm->family_id ;
		$fatherName =$fm->fatherName ;
		$fatherCellNo = $fm->fatherCellNo;

		DB::table('Student')
			  ->whereIn('id',Input::get('regino'))
			  ->update(['family_id' => $family_id,'fatherName' =>$fatherName,'fatherCellNo' =>$fatherCellNo ]);

			  return Redirect::to('/family/students/'.$f_id)->with("success","Family Updated Succesfully.");	
		//echo "<pre>";print_r($fm);
            		//->update(['about_family' => Input::get('familb'),'family_id' => $family_idn,'fatherName' =>$fathername,'fatherCellNo' =>$cell_phone ]);
}
public function shift_student_family($f_id)
{

	echo "<pre>";print_r(Input::all());
	$fm = DB::table('Student')
            		
		->where(function($q) use( $f_id) {
				$q->where('Student.family_id', '=', Input::get('f_id'))
				->orWhere('Student.fatherCellNo', '=', Input::get('f_phone'));
		})
		->first();
		if(empty($fm)){

			return Redirect::to('family/students/'.$f_id)->with("error","Family Not Found.");

		}else{
			$sids = Input::get('sid');
			///foreach($sids as $id){
			DB::table('Student')
			  ->whereIn('id',$sids)
			  ->update(['family_id' => $fm->family_id,'fatherName' =>$fm->fatherName,'fatherCellNo' =>$fm->fatherCellNo ]);
			//}
			return Redirect::to('/family/students/'.$f_id)->with("success","Student Shift  Succesfully.");	

		}
	//..echo $f_id;
	//print_r(Input::get('regino'));

	/*$fm = DB::table('Student')
            		
		->where(function($q) use( $f_id) {
				$q->where('Student.family_id', '=', $f_id)
				->orWhere('Student.fatherCellNo', '=', $f_id);
		})
		->first();

		$family_id =$fm->family_id ;
		$fatherName =$fm->fatherName ;
		$fatherCellNo = $fm->fatherCellNo;

		DB::table('Student')
			  ->whereIn('id',Input::get('regino'))
			  ->update(['family_id' => $family_id,'fatherName' =>$fatherName,'fatherCellNo' =>$fatherCellNo ]);

			  return Redirect::to('/family/students/'.$f_id)->with("success","Family Updated Succesfully.");	*/
		//echo "<pre>";print_r($fm);
            		//->update(['about_family' => Input::get('familb'),'family_id' => $family_idn,'fatherName' =>$fathername,'fatherCellNo' =>$cell_phone ]);
}



/**
* Remove the specified resource from storage.
*
* @param  int  $id
* @return Response
*/
public function delete($id)
{
	$student = Student::find($id);
	$student->isActive= "No";
	$student->save();

	return Redirect::to('/student/list')->with("success","Student Deleted Succesfully.");
}

/**
* Display the specified resource.
*
* @param  int  $id
* @return Response
*/
public function getForMarks($class,$section,$shift,$session)
{
	$students= Student::select('regiNo','rollNo','firstName','middleName','lastName','discount_id')->where('isActive','=','Yes')->where('class','=',$class)->where('section','=',$section)->where('shift','=',$shift)->where('session','=',$session)->get();
	return $students;
}

public function getForMarksjoin($class,$section,$shift,$session)
{
	$students= Student::select('regiNo','rollNo','firstName','middleName','lastName','discount_id')->where('isActive','=','Yes')->where('class','=',$class)->where('section','=',$section)->where('shift','=',$shift)->where('session','=',$session)->get();
	/* $students=	DB::table('Student')
	->leftjoin('Marks', 'Student.regiNo', '=', 'Marks.regiNo')
	->select('Student.id', 'Student.regiNo','Student.rollNo','Student.firstName','Student.middleName','Student.lastName',
	'Student.discount_id','Marks.written','Marks.written','Marks.mcq','Marks.practical','Marks.ca','Marks.Absent')
	->where('Student.section','=',$section)->where('Student.shift','=',$shift)->where('Student.session','=',$session)->get();

*/
	return $students;
}
public function getdiscount($reg)
{
	$discount= Student::select('discount_id')->where('isActive','=','Yes')->where('regiNo','=',$reg)->first();
	return $discount;
}

public function index_file(){
	return View('app.studentCreateFile');


}
public function create_file(){

$file = Input::file('fileUpload');
			$ext = strtolower($file->getClientOriginalExtension());
			$validator = \Validator::make(array('ext' => $ext),array('ext' => 'in:xls,xlsx,csv'));

			if ($validator->fails()) {
				return Redirect::to('student/create-file')->withErrors($validator);
			}else {
				    try{
						$toInsert = 0;
			            $data = \Excel::load(Input::file('fileUpload'), function ($reader) { })->get();

			             

			                if(!empty($data) && $data->count()){
								DB::beginTransaction();
								try {
			                        foreach ($data->toArray() as $raw) {
                                      //echo "<pre>";print_r($raw);exit;
									$studentData= [
											'class' => $raw['class'],
											'section' => $raw['section'],
											'session' =>    $raw['session'],
											'regiNo' => $raw['registration'],
											'rollNo' => $raw['nocardroll_no'],
                                            'shift' => 'Morning',
                                            'isActive'=>'Yes',
											'group' => $raw['group'],
											'firstName' => $raw['first_name'],
											'lastName' =>    $raw['last_name'],
											 'Gender' => $raw['gender'],
											 'fatherName' => $raw['father_name'],
											 'fatherCellNo' => $raw['fathers_mobile_no'],
											 'family_id' => $raw['family_id']

										];
										$hasStudent = Student::where('rollNo','=',$raw['nocardroll_no'])->where('session',$raw['session'])->first();
											if ($hasStudent)
											{
												$errorMessages = new \Illuminate\Support\MessageBag;
									             $errorMessages->add('Error', 'Doublication rollNo ');
									            return Redirect::to('/student/create-file')->withErrors($errorMessages);
											}else{
												Student::insert($studentData);
												$toInsert++;
											}
			                        }
			                         
										 DB::commit();
								} catch (Exception $e) {
									DB::rollback();
									$errorMessages = new \Illuminate\Support\MessageBag;
									 $errorMessages->add('Error', 'Something went wrong!');
									return Redirect::to('/student/create-file')->withErrors($errorMessages);

									// something went wrong
								}
			            }
					   if($toInsert){
			                return Redirect::to('/student/create-file')->with("success", $toInsert.' Student data upload successfully.');
			            }
						$errorMessages = new \Illuminate\Support\MessageBag;
						 $errorMessages->add('Validation', 'File is empty!!!');
						return Redirect::to('/student/create-file')->withErrors($errorMessages);
	                }catch (Exception $e) {
						  $errorMessages = new \Illuminate\Support\MessageBag;
						  $errorMessages->add('Error', 'Something went wrong!');
						   return Redirect::to('/student/create-file')->withErrors($errorMessages);
	                }
		}

	
}

public function csvexample(){
/*if(Storage::disk('local')->exists('/public/accounting.txt')){


}*/
$headers = array(
              //'Content-Type: application/csv',
              'location:http://localhost/newschool/student/create-file'
            );
 //header("location: index.php");
    return response()->download(storage_path('app/public/' . 'student.csv'), 'student.csv',['location'=>'http://localhost/newschool/student/create-file']);


 //return response()->download(storage_path('app/public/' . 'student.csv'));

}

public function access($id)
{
   $student= Student::find($id);
   if(!empty($student) && count($student)>0){
   	$chk_studnet  = User::where('login',$student->firstName.$student->lastName)->where('group_id',$student->id)->first();
      if(count($chk_studnet)>0){
      	   return Redirect::to('/student/list')->with("error","Already have Accessed .");

      }
        $user = new User;
        $user->firstname = $student->firstName;
        $user->lastname  = $student->lastName;
        $user->email     =     $student->regiNo;
      	$user->login     = $student->firstName.$student->lastName;
      	$user->group     =  'Student';
      	$user->group_id  = $student->id ;
      	$user->access    = 1 ;
        $user->password  =	Hash::make($student->regiNo);
        $user->save();

            $ictcore_integration = Ictcore_integration::select("*")->first();
                 
			if(!empty($ictcore_integration) && $ictcore_integration->ictcore_url !='' && $ictcore_integration->ictcore_user !='' && $ictcore_integration->ictcore_password !=''){ 

				 $ict  = new ictcoreController();
				 	$data = array(
					'first_name' => $student->firstName,
					'last_name'  => $student->lastName,
					'phone'      => $student->fatherCellNo,
					'email'      => '',
					);
					$contact_id = $ict->ictcore_api('contacts','POST',$data );

                   $message = 'School name'.'<br>'.'Login Name: '.  $student->firstName.$student->lastName.' Password: '.$student->regiNo;
                    $data   = array(
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

   	   return Redirect::to('/student/list')->with("success","Teacher Moblie Access Created.");

   }
   return Redirect::to('/student/list')->with("error","Teacher not found.");
}

public function send_sms()
{
    $ictcore_integration  = Ictcore_integration::select("*")->where('type','sms')->first();
    $ict                  = new ictcoreController();
    $snd_msg  = $ict->verification_number_telenor_sms(Input::get('phone'),Input::get('message'),'SidraSchool',$ictcore_integration->ictcore_user,$ictcore_integration->ictcore_password,'sms');
    //echo "<pre>";print_r( $snd_msg);
    //exit;
    if($snd_msg->response!='OK'){
    $error = "Whoops!";
	return Redirect::to('/student/view/'.Input::get('id'))->with("error"," $error Message Not send");
    }else{
     return Redirect::to('/student/view/'.Input::get('id'))->with("success","  Message sended");

    }
}

}

<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ictcoreController;

//use App\Api_models\User;

use Illuminate\Support\Facades\Auth;

use Validator;
use App\ClassModel;
use App\Message;
use App\Subject;
use App\Attendance;
use App\Student;
use App\Ictcore_integration;
use App\SectionModel;
use DB;
use Excel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class StudentController extends Controller
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
	public function all_students()
	{

		
		 $students = DB::table('Student')
          ->join('Class', 'Student.class', '=', 'Class.code')
		  ->join('section', 'Student.section', '=', 'section.id')
		  ->select('Student.id', 'Student.regiNo', 'Student.rollNo','Student.b_form as Bform', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
		  'Class.Name as class','Student.section' ,'section.name as section_name','Student.session','Student.group' ,'Student.presentAddress', 'Student.gender', 'Student.religion','Student.fatherCellNo')->where('Student.isActive','Yes');

		  $students->when(request('regiNo', false), function ($q, $regiNo) { 
            return $q->where('regiNo', $regiNo);
          });
          $students->when(request('class', false), function ($q, $class) { 
          	
              $classc = DB::table('Class')->select('*')->where('id','=',$class)->first();

            return $q->where('Student.class',  $classc->code);
          });
          $students->when(request('section', false), function ($q, $section) { 
            return $q->where('Student.section', $section);
          });
          $students->when(request('session', false), function ($q, $session) { 
            return $q->where('Student.session', get_current_session()->id);
          });
          $students->when(request('group', false), function ($q, $group) { 
            return $q->where('Student.group', $group);
          });

          $students->when(request('name', false), function ($q, $name) { 
            return $q->where('Student.firstName', 'like', '%' .$name.'%');
          });

           $students->when(request('cnic', false), function ($q, $cnic) { 
            return $q->where('Student.b_form',$cnic);
          }); 
           $students->when(request('mobile', false), function ($q, $mobile) { 
            return $q->where('Student.fatherCellNo',$mobile);
          });

         // ('name', 'like', '%' . Input::get('name') . '%')
          $students = $students->get();
		
		 // ->get();
		  if(count($students)<1)
		  {
		     return response()->json(['code'=>401,'error'=>'No Students Found!'], 401);
		  }
		  else {
			  return response()->json($students,200);
		  }
	}

    /**
    * Count student
    **/
    public function count_students()
    {
       $tstudent['overall']  =  Student::where('isActive','Yes')->count();
       $tstudent['current']  =  Student::where('isActive','Yes')->where('session',get_current_session()->id)->count();
        if($tstudent['overall']==0)
        {
            return response()->json(['code'=>401,'error'=>'No Students Found!'], 401);
        }
        else {
            return response()->json($tstudent,200);
        }
    }
	/**
	 * student_classwise api
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function student_classwise($class_level,$section,$shift,$session)
	{
		  $session =get_current_session()->id;
          $students = DB::table('Student')
		  ->join('Class', 'Student.class', '=', 'Class.code')
		  ->select('Student.id','Student.session', 'Student.regiNo', 'Student.rollNo','Student.b_form as Bform', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
		  'Class.Name as class', 'Student.presentAddress', 'Student.gender', 'Student.religion')
		  ->where('Student.isActive','Yes')
          ->where('class',$class_level)
		  ->where('section',$section)
		  ->where('shift',$shift)
		  ->where('session',trim($session))
		  ->get();
		  if(count($students)<1)
		  {
		     return response()->json(['error'=>'No Students Found!'], 404);
		  }
		  else {
			  return response()->json($students,200);
		  }
	}
    public function getstudent($student_id)
    {
         //$student = Student::find($student_id);
    	  $student = DB::table('Student')
                	 ->join('Class', 'Student.class', '=', 'Class.code')
            		 ->select('Student.id', 'Student.regiNo', 'Student.rollNo','Student.b_form as Bform', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
            		  'Class.Name as class','Student.section','Student.session' ,'Student.group','Student.session','Student.presentAddress','Student.dob','Student.gender', 'Student.religion')
            		  ->where('Student.isActive','Yes')
                      ->where('Student.id',$student_id)
                      ->first();

        if(!empty($student)){
           return response()->json($student,200);
        }else{
        return response()->json(['error'=>'Student Not Found'], 404);
       }
    }

    public function getstudentsubjects($student_id)
    {
         //$student = Student::find($student_id);
    	 $student = Student::find($student_id);
          
       $subject = DB::table('Subject')->select('code','name','type','class','stdgroup')->where('class',$student->class)->where('stdgroup',$student->group)->get();

    	
    	 /*->join('Class', 'Student.class', '=', 'Class.code')
		  ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
		  'Class.Name as class','Student.section' ,'Student.presentAddress', 'Student.gender', 'Student.religion')
		    ->where('Student.id',$student_id)->first();*/

        if(!is_null($subject) && count($subject)>0){
           return response()->json($subject,200);
        }else{
        return response()->json(['error'=>'Subject Not Found'], 404);
       }
    }

	public function update_student($student_id)
	{
		//return response()->json(['student'=>$student_id]);
		$rules=[
    		'firstname' => 'required',
    		'lastname' => 'required',
            'dob'   => 'required',
            'regiNo'  => 'required',
            'rollNo' => 'required',
            'gender' => 'required',
            'religion' => 'required',
    		'gender' => 'required',
    		'session' => 'required',
    		'class' => 'required',
    		'section' => 'required',
    		'presentaddress' => 'required',
    		'fathercellno'  =>'required',
    		'fathername'  =>'required',
            'mothername' => 'required',
            'mothercellno' => 'required',
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
            return response()->json($validator->errors(), 422);
		}
		else{
			$student = Student::select('id', 'regiNo', 'rollNo', 'firstName', 'middleName', 'lastName', 'fatherName', 'motherName', 'fatherCellNo', 'motherCellNo', 'localGuardianCell',
          'class','section' ,'group','session','presentAddress','dob','gender', 'religion')->where('Student.isActive','Yes')->where('Student.id',$student_id)->first();
			$student->firstName = Input::get('firstname');
			$student->lastName= Input::get('lastname');
            $student->dob= Input::get('dob');
            $student->regiNo= Input::get('regiNo');
            $student->rollNo= Input::get('rollNo');
            $student->gender= Input::get('gender');
			$student->religion= Input::get('religion');
			$student->session= get_current_session()->id;
			$student->class= Input::get('class');
			$student->section= Input::get('section');
			$student->group= Input::get('group');
			$student->presentAddress= Input::get('presentaddress');
		    $student->fatherCellNo= Input::get('fathercellno');
			$student->fatherName= Input::get('fathername');
            $student->motherName= Input::get('mothername');
            $student->motherCellNo= Input::get('mothercellno');
			$student->save();
			return response()->json($student,200);
		}
	}


	public function studentnotification($student_id){
		 $rules=[
            'name'    =>'required',
            'type'    => 'required',
            'message' =>'required'

            ];
        $validator = \Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
         return response()->json($validator->errors(), 422);
        }
        else{
    		/*$drctry = storage_path('app/public/messages/');
             $mimetype      = mime_content_type($drctry.Input::get('message'));
            if($mimetype =='audio/x-wav' || $mimetype=='audio/wav'){ */
                $ictcore_integration = Ictcore_integration::select("*")->first();
                 
                if(!empty($ictcore_integration) && $ictcore_integration->ictcore_url !='' && $ictcore_integration->ictcore_user !='' && $ictcore_integration->ictcore_password !=''){ 

                    $ict         = new ictcoreController();
                    $postmethod  = new NotificationController();

                    $student=   DB::table('Student')
                    ->select('*')
                    ->where('isActive','Yes')
                    ->where('id', $student_id)
                    ->first();
                    $data = array(
                    'first_name' => $student->firstName,
                    'last_name' => $student->lastName,
                    'phone'     => $student->fatherCellNo,
                    'email'     => '',
                    );
                    $contact_id = $ict->ictcore_api('contacts','POST',$data );
                    return $postmethod->postnotificationmethod(Input::get('name'),Input::get('type'),Input::get('message'),'single',$contact_id);

           		/* }else{
                     return response()->json("ERROR:Please Upload Correct file",415 );
                 }*/
             }else{

                return response()->json(['Error'=>"Please Add Intigration  in Setting. Notification not send"],400);

             }
        }
    }
	/*public function studentnotification($student_id){
		 $rules=[
            'name'        => 'required',
            'recording'   =>'required'

            ];
        $validator = \Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
         return response()->json($validator->errors(), 422);
        }
        else{

                      $student = Student::find($student_id);

                      if(is_null($student)){
                         return response()->json(['error'=>'Student Not Found'], 404);
                         exit;
                      }
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mimetype      = $finfo->buffer(base64_decode(Input::get('recording')));

                     if($mimetype =='audio/x-wav' || $mimetype=='audio/wav'){ 
                         $ict  = new ictcoreController();
                    // $headers = apache_request_headers();
                     //dd($headers['Authorization']);
                        $filename ='notification_class_'.time();//'recordingn5QzxE.wav';//tempnam(public_path('recording/'), 'recording'). ".wav";

                      file_put_contents(public_path('recording/').$filename.'.wav', base64_decode(Input::get('recording')));

                         //      unlink(public_path('recording/'.$filename));

                    
                        sleep(2);
                        $data = array(
                                     'name' => Input::get('name'),
                                     'description' => Input::get('description'),
                                     );

                         $recording_id  =  $ict->ictcore_api('messages/recordings','POST',$data );
                         $name          =  base_path() .'/public/recording/'.$filename.".wav";


                         $finfo         =  new \finfo(FILEINFO_MIME_TYPE);
                         $mimetype      =  $finfo->file($name);

                         $cfile         =  curl_file_create($name, $mimetype, basename($name));
                         $data          =  array( $cfile);
                         $result        =  $ict->ictcore_api('messages/recordings/'.$recording_id.'/media','PUT',$data );
                         $recording_id  =  $result ;
                        if(!is_array($recording_id )){

                          $data = array(
                                     'name' => Input::get('title'),
                                     'recording_id' => $recording_id,
                                     );
                         $program_id = $ict->ictcore_api('programs/voicemessage','POST',$data );
                         if(!is_array( $program_id )){
                          $program_id = $program_id;
                         }else{
                            return response()->json("ERROR: Program not Created" );
                         
                         }
                        }else{
                            return response()->json("ERROR: Recording not Created" );
                                          
                        }

                    $notificationData= [
                                    'name' => Input::get('name'),
                                    'description' =>Input::get('description'),
                                    'recording' => $filename.".wav",
                                    'ictcore_program_id' => $program_id,
                                    'ictcore_recording_id' => $recording_id,
                                ];

                              $notification_id = Message::insertGetId($notificationData);
                                
                    $student=   DB::table('Student')
                        ->select('*')
                        ->where('isActive','Yes')
                        ->where('id', $student_id)
                        ->first();
                        
                            $data = array(
                            'first_name' => $student->firstName,
                            'last_name' => $student->lastName,
                            'phone'     => $student->fatherCellNo,
                            'email'     => '',
                            );


                            $contact_id = $ict->ictcore_api('contacts','POST',$data );

                       $data = array(
								'title' => 'Attendance',
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
								$transmission_send = $ict->ictcore_api('transmissions/'.$transmission_id.'/send','POST',$data=array() );

                return response()->json(['success'=>"Nofication Sended Succesfully."],200);

            }else{
                    return response()->json("ERROR:Please Upload Correct file",415 );

            }
        }
	}   */

    /**
    * Count paid or un paid student
    **/

    public function count_student_fee()
    {
        $now             =  Carbon::now();
        $year            =  get_current_session()->id;
        $year1            =  $now->year;
         $month           =  $now->month;
        $all_section =  DB::table('Class')->select( '*')->get();
        //$student_all =    DB::table('Student')->select( '*')->where('class','=',Input::get('class'))->where('section','=',Input::get('section'))->where('session','=',$student->session)->get();
        $ourallpaid =0;
        $ourallunpaid=0;
        if(count($all_section)>0){
            $i=0;
            
            
          
            foreach($all_section as $section){
                 $paid =0;
                 $unpaid=0;
                 $total_s=0;
             $student_all = DB::table('Student')->select( '*')->where('class','=',$section->code)/*->where('section','=',$section->id)/**/->where('session','=',$year)
              //->where('Student.session','=',$year)
             ->where('Student.isActive','=','Yes')
             ->get();
               $resultArray[$section->code.'_'.$section->name."_".'total']=0;
                $resultArray[$section->code.'_'.$section->name."_".'unpaid']=0;
                $resultArray[$section->code.'_'.$section->name."_".'paid'] =  0;
                if(count($student_all) >0){
                    foreach($student_all as $stdfees){
                        $student =  DB::table('billHistory')->Join('stdBill', 'billHistory.billNo', '=', 'stdBill.billNo')
                        ->select( 'billHistory.billNo','billHistory.month','billHistory.fee','billHistory.lateFee','stdBill.class as class1','stdBill.payableAmount','stdBill.billNo','stdBill.payDate','stdBill.regiNo')
                        // ->whereYear('stdBill.payDate', '=', 2017)
                        ->where('stdBill.regiNo','=',$stdfees->regiNo)->whereYear('stdBill.payDate', '=', $year1)->where('billHistory.month','=',$month)->where('billHistory.month','<>','-1')
                        //->orderby('stdBill.payDate')
                        ->get();
                        if(count($student)>0 ){
                            foreach($student as $rey){
                                //$status[] = "paid".'_'.$stdfees->regiNo."_";
                                //$resultArray[$i] = get_object_vars($stdfees);
                                //array_push($resultArray[$i],'Paid',$rey->payDate,$rey->billNo,$rey->fee);
                                $resultArray[$section->code.'_'.$section->name."_".'paid'] =  ++$paid;
                                //$yes ='yes';
                               $ourallpaid = ++$ourallpaid;
                            }
                        }else{
                            //$status[$i] = "unpaid".'_'.$stdfees->regiNo."_";
                            //$resultArray[] = get_object_vars($stdfees);
                            //array_push($resultArray[$i],'unPaid');
                            
                            //$resultArray[$section->class_code.'_'.$section->name."_".'paid'] =  0;
                            $resultArray[$section->code.'_'.$section->name."_".'unpaid']=++$unpaid;
                            $ourallunpaid =++$ourallunpaid;
                        }
                        $resultArray[$section->code.'_'.$section->name."_".'total']=++$total_s;
                    }
                }else{
                  $resultArray[$section->code.'_'.$section->name."_".'total']=0;
                  $resultArray[$section->code.'_'.$section->name."_".'unpaid']=0;
                  $resultArray[$section->code.'_'.$section->name."_".'paid'] =  0;

                }
            //$resultArray[] = get_object_vars($section);
            //array_push($resultArray[$i],$total,$paid,$unpaid);
            $scetionarray[] = array('section'=>$section->name,'class'=>$section->code);
            $resultArray1[] = array('total'=> $resultArray[$section->code.'_'.$section->name."_".'total'],'unpaid'=>$resultArray[$section->code.'_'.$section->name."_".'unpaid'],'paid'=>$resultArray[$section->code.'_'.$section->name."_".'paid']);

            }
            
        }
        else{
            $resultArray = array();
        }

        return response()->json(['ourallunpaid'=>$ourallunpaid,'ourallpaid'=>$ourallpaid],200);
    }
}


	        
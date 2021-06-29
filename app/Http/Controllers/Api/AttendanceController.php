<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use App\Http\Controllers\Controller;

//use App\Api_models\User;
use Closure;
use Illuminate\Support\Facades\Auth;

use Validator;
use App\ClassModel;
use App\Subject;
use App\Attendance;
use App\Student;
use App\SectionModel;
use App\Ictcore_attendance;
use App\Ictcore_integration;
use App\SMSLog;
use App\SectionAttendance;
use DB;
use Excel;
use Illuminate\Support\Collection;
use App\Http\Controllers\ictcoreController;

use Carbon\Carbon;

class AttendanceController extends Controller
{

	public function __construct() 
	{

	 //  $this->middleware('auth:api');

	}
   public $successStatus = 200;

			/**
			 * attendance_create api
			 *
			 * @return \Illuminate\Http\Response
			 */
			public function attendance()
			{
					$classes=array();
					$classes2 =ClassModel::pluck('name');
					$section = SectionModel::pluck('name');
					$attendance=array();
					return response()->json(['sections' => $section,'classes'=>$classes2]);
			}

			/**
			 * attendance_create api
			 *
			 * @return \Illuminate\Http\Response
			 */
			public function count_attendances()
			{
					//echo Carbon::now()->format('Y-m-d');
 		            $totalabsent        = Attendance::where('date',Carbon::now()->format('Y-m-d'))->where('status','Absent')->count();
 		            $totallate          = Attendance::where('date',Carbon::now()->format('Y-m-d'))->where('status','Late')->count();
 		            $totalpresent       = Attendance::where('date',Carbon::now()->format('Y-m-d'))->where('status','Present')->count();
					return response()->json(['absent' => $totalabsent ,'late'=>$totallate,'present'=>$totalpresent]);
			}
			
			public function getallattendance()
			{

				$attendance = DB::table('Student')
				->join('Attendance', 'Student.regiNo', '=', 'Attendance.regiNo')
				->select( 'Attendance.id','Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName','Student.class','Attendance.status','Attendance.date')->where('isActive','Yes');

					 $attendance->when(request('regiNo', false), function ($q, $regiNo) { 
						return $q->where('Student.regiNo', $regiNo);
					  });
					   $attendance->when(request('class', false), function ($q, $class) { 
						 $classc = DB::table('Class')->select('*')->where('id','=',$class)->first();
						return $q->where('Student.class',  $classc->code);
					  });
					   $attendance->when(request('date', false), function ($q, $date) { 

						return $q->where('Attendance.date',  $date);
					  });

					   $attendance->when(request('session', false), function ($q, $session) { 

						return $q->where('Attendance.session',  $session);
					  });

					   $attendance->when(request('section', false), function ($q, $section) { 
						return $q->where('Student.section', $section);
					  });

					   $attendance->when(request('name', false), function ($q, $name) { 
						return $q->where('Student.firstName', 'like', '%' .$name.'%');
					  });
					/*->where('Student.class','=',Input::get('class'))
					->where('Student.section','=',Input::get('section'))
					->Where('Student.shift','=','Morning')
					->where('Student.session','=',trim(Input::get('session')))
					->where('Student.isActive', '=', 'Yes')
					->where('Attendance.date', '=', $date)*/
					$attendance=$attendance->paginate(20);
				if($attendance->isEmpty()) {
				  return response()->json(['error'=>'Attendance Not Found'], 404);
				}else{
					 return response()->json($attendance,200);
			 	}
			}
		 	/**
			 * attendance_create api
			 *
			 * @return \Illuminate\Http\Response
			 */
		public function attendance_create(Request $request)
		{
			$rules = [
			    'class_id'  => 'required',
				'section_id'=> 'required',
				'regiNo'    => 'required',
				'date'      => 'required',
				'session'   => 'required',
				'status'    => 'required'
			];
			$validator = \Validator::make(Input::all(), $rules);
			if ($validator->fails()) 
			{
					 return response()->json($validator->errors(), 422);
			} else 
			{
				//$response = $next($request);

            //$response = $response instanceof RedirectResponse ? $response : response($response);

                //return Carbon::parse(Input::get('date'))->format('Y-m-d') ; 
                if(request()->getHttpHost()=='localhost' || request()->getHttpHost()=='school.ictcore.org'){
              		//$attendance =  Attendance::where('date',Carbon::parse(Input::get('date'))->format('Y-m-d'))->delete();
				}
					$absentStudents = array();
					$students = Input::get('regiNo');
					$status = Input::get('status');
					$class_id = Input::get('class_id');
					$section_id = Input::get('section_id');
					$comments = Input::get('comments');
					//$presentDate = $this->parseAppDate(Input::get('date'));
					$presentDate = Carbon::parse(Input::get('date'))->format('Y-m-d');
					 // return response()->json($presentDate);

						$std = DB::table('Student')->where('regiNo',$students)->count();
						if($std==0){
							return response()->json(['error'=>'Student not found'], 404);

						}
					  if($status =='Absent' || $status =='absent' || $status =='leave' || $status =='Leave') {

						$atten = DB::table('Attendance')->where('date','=',$presentDate)->where('regiNo','=',$students)->first();
                        //return response()->json($atten,200);
						if(empty($atten)){
							$attenData= [
								'date' => $presentDate,
								'class_id' => $class_id,
								'section_id'=> $section_id,
								'regiNo' => $students,
								//'session'=>Input::get('session'),
								'session'=>get_current_session()->id,
								'status' =>$status,
								'coments' =>$comments,
								'created_at' => Carbon::now()
							];

						$attendence_id = Attendance::insertGetId($attenData);
						}else{
						 return response()->json(['error'=>'Attendance already added'], 400);
						}
						if(request()->getHttpHost()=='localhost' || request()->getHttpHost()=='school.ictcore.org'){
						//if(request()->getHttpHost()=='school.ictcore.org'){
                              $student =	DB::table('Student')
								->join('Class', 'Student.class', '=', 'Class.code')
								->select( 'Student.regiNo','Student.rollNo','Student.firstName','Student.middleName','Student.lastName','Student.fatherCellNo','Student.fatherName','Class.Name as class')
								->where('Student.regiNo','=',$students)
								->where('Student.section','=',$section_id)
								->where('Student.session','=',get_current_session()->id)
								->first();

							   //$this->sendnotification($student);
						} 
						/*$ictcore_integration = Ictcore_integration::select("*")->first();
                 
						if(!empty($ictcore_integration) && $ictcore_integration->ictcore_url !='' && $ictcore_integration->ictcore_user !='' && $ictcore_integration->ictcore_password !=''){ 
							   $student =	DB::table('Student')
								->join('Class', 'Student.class', '=', 'Class.code')
								->select( 'Student.regiNo','Student.rollNo','Student.firstName','Student.middleName','Student.lastName','Student.fatherCellNo','Class.Name as class')
								->where('Student.regiNo','=',$students)->where('student.section','=',$section_id)
								//->where('class',Input::get('class'))
								->first();
							
								 $data = array(
									   'first_name' => $student->firstName,
										'last_name' => $student->lastName,
										'phone'     => $student->fatherCellNo,
										'email'     => '',
									);

								   $ict  = new ictcoreController();

								$ictcore_attendance= Ictcore_attendance::select("*")->first();

								if($ictcore_attendance->ictcore_program_id!=''){
									
								   //$contact_id = $ict->ictcore_api('contacts','POST',$data );
									/*$data = array(
										   'title'       => 'Attendance',
										   'program_id'  => $ictcore_attendance->ictcore_program_id,
											'account_id' => 1,
											'contact_id' => $contact_id,
											'origin'     => 1,
											'direction'  => 'outbound',
										);*/

									 //$transmission_id = $ict->ictcore_api('transmissions','POST',$data );

									 

									 //$transmission_send = $ict->ictcore_api('transmissions/'.$transmission_id.'/send','POST',$data=array() );

									 /*if(!is_array($transmission_send)){

										$status1 = "Completed";
									 }else{
										$status1 ="Pending";
									 }
									$msg =$ictcore_attendance->recording;
									 $smsLog = new SMSLog();
									 $smsLog->type      = "Attendanceapi";
									 $smsLog->sender    = "ictcore";
									 $smsLog->message   = $msg;
									 $smsLog->recipient = $student->fatherCellNo;
									 $smsLog->regiNo    = $students;
									 $smsLog->status    = $status1;
									 $smsLog->save();*/
							
						return response()->json(['success'=>"Students attendance save Succesfully.",'id' => $attendence_id]);
					/*}else{

					  return response()->json(['Error'=>"Please Add Attendance Message in Setting."]);

					}
				}else{

					  return response()->json(['Error'=>"Please Add Intigration  in Setting. Notification send failed"],400);

					}*/

				}else if($status == 'Present' || $status =='present' || $status =='Late' || $status =='late'){
					//}
					
					$atten = DB::table('Attendance')->where('date','=',$presentDate)->where('regiNo','=',$students)->first();
					if(empty($atten)){
					
						$attenData= [
						    'class_id' => $class_id,
						    'section_id'=> $section_id,
							//'session'=>Input::get('session'),
							'session'=>get_current_session()->id,
							'date' => $presentDate,
							'regiNo' => $students,
							'status' =>$status,
							//'coments' =>NULL,
							'created_at' => Carbon::now()
						];
						 //return response()->json($attenData);

						$attendence_id = Attendance::insertGetId($attenData);

				}else{
					 return response()->json(['error'=>'Attendance already added'], 400);
				}
				
				/*}
				catch (Exception $e) 
				{
					DB::rollback();
					$errorMessages = new Illuminate\Support\MessageBag;
					 $errorMessages->add('Error', 'Something went wrong!');
					return response()->json(['error'=>withErrors($errorMessages)], 400);

				}*/
			}else{
				 return response()->json(['error'=>'Wrong Status'], 400);

			}
				  return response()->json(['success'=>"Students attendance save Succesfully.",'id' => $attendence_id]);
			}
		}

			public function sendnotification($student)
			{
				 $get_msg  = DB::table('ictcore_attendance')->first();
                 $ictcore_attendance  = Ictcore_attendance::select("*")->first();
                 $ictcore_integration_sms = Ictcore_integration::select("*")->where('type','sms')->first();
                 $ictcore_integration_voice = Ictcore_integration::select("*")->where('type','voice')->first();
                 $ict                 = new ictcoreController();
                 $snd_msg ='';
               if (preg_match("~^0\d+$~", $student->fatherCellNo)) {
                	$to = preg_replace('/0/', '92', $student->fatherCellNo, 1);
                }else {
                    $to =$student->fatherCellNo;  
                }
				$data = array(
					   'first_name' => $student->firstName,
						'last_name' => $student->lastName,
						'phone'     => $to,
						'email'     => '',
					);
				$contact_id = $ict->ictcore_api('contacts','POST',$data );
                            //if($student->status=="Absent"){
                             $program_id = 'program_id =>'.$get_msg->ictcore_program_id;
                             $msg=$get_msg->recording;
                             
                            
                             $data = array(
                                        'title'      => 'Attendance',
                                        'program_id' =>$get_msg->ictcore_program_id,
                                        'account_id' => 1,
                                        'contact_id' => $contact_id,
                                        'origin'     => 1,
                                        'direction'  => 'outbound',
                                    );
                             

                             $transmission_id = $ict->ictcore_api('transmissions','POST',$data );
                            
                            $transmission_send = $ict->ictcore_api('transmissions/'.$transmission_id.'/send','POST',$data=array() );
                            if(!empty($transmission_send->error)){
                                $status =$transmission_send->error->message;
                            }else{
                                $status = "Completed";
                            }

                                 //echo "bhutta<pre>".$status;exit;
                            //$msg    = $recoding;
                            $smsLog = new SMSLog();
                            $smsLog->type      = "Attendancehello";
                            $smsLog->sender    = "ictcore voice";
                            $smsLog->message   = $msg;
                            $smsLog->recipient = $student->fatherCellNo;
                            $smsLog->regiNo    = $student->regiNo;
                            $smsLog->status    = $status;
                            $smsLog->save();

                            $get_msg  = DB::table('ictcore_attendance')->first();

	                        $name     = $student->firstName.' '.$student->lastName;
	                       
	                        $msg      =  str_replace("<<parent>>",$student->fatherName,$get_msg->description);
	                        $msg      =  str_replace("<<name>>",$name,$msg);
	                       // echo "<pre>";print_r($msg);
	                      //  exit;
	                        if (preg_match("~^0\d+$~", $student->fatherCellNo)) {
		                        $to = preg_replace('/0/', '92', $student->fatherCellNo, 1);
		                    }else {
		                        $to =$student->fatherCellNo;  
		                    }
		                     
		                    if(strlen($to)==12){
		                        $snd_msg  = $ict->verification_number_telenor_sms($to,$msg,'SidraSchool',$ictcore_integration_sms->ictcore_user,$ictcore_integration_sms->ictcore_password,'sms');
		                    }
		                        $smsLog = new SMSLog();
		                        $smsLog->type      = "Attendance";
		                        $smsLog->sender    = "telenor ";
		                        $smsLog->message   = $msg;
		                        $smsLog->recipient = $student->fatherCellNo;
		                        $smsLog->regiNo    = $student->regiNo;
		                        $smsLog->status    = $snd_msg;
		                        $smsLog->save();
			}
		
			/**
			 * attendance_view api
			 *
			 * @return \Illuminate\Http\Response
			 */
			public function attendance_view($class_level,$section,$shift,$session,$date)
			{
			   $date = $this->parseAppDate($date);
			   $session = get_current_session()->id;
				$attendance = \App\Student::with(['attendance' => function($query) use($date){
					 $query->where('date','=',$date);
				}])
				->where('class','=',$class_level)
				->where('section','=',$section)
				->Where('shift','=',$shift)
				->where('session','=',trim($session))
				->where('isActive', '=', 'Yes')
				//->where('isActive', '=', 'Yes')->with('attendance')
				->get();
				$classes2 = ClassModel::select('code','name')->orderby('code','asc')->pluck('name','code');
				$s_attendence = array();
				foreach($attendance as $atd)
				{
					if(count($atd->attendance)){

					  $att = 'Present';

					}else{

						 $att = 'Absent';
					}
				  $s_attendence[] = array('RegiNo'=>$atd->regiNo,'RollNo'=>$atd->rollNo,'Name'=>$atd->firstName.' '.$atd->lastName,'Is Present'=>$att);
				}

				return response()->json(['attendance'=>$s_attendence]);
			
			}

			public function get_attendance($attendance_id){
				 
				 $std_atd = DB::table('Student')
				->join('Attendance', 'Attendance.regiNo', '=', 'Student.regiNo')
				->select('Attendance.id','Student.RegiNo','Student.rollNo','Student.firstName','Student.lastName','Student.class','Student.section','Attendance.status', 'Attendance.date')
                ->where('Student.isActive','Yes')
				->where('Attendance.id','=',$attendance_id)
				->get();
				  //return response()->json(['error'=> $std_atd], 401);

				if($std_atd->isEmpty()) {
				  return response()->json(['error'=>'Attendance Not Found'], 404);
				}else{

				  /*foreach($std_atd as $atd){
						$att = 'Present';
						$s_attendence[] = array('RegiNo'=>$atd->RegiNo,'RollNo'=>$atd->rollNo,'Name'=>$atd->firstName.' '.$atd->lastName,'Is Present'=>$att,'Date'=>$atd->date);

				  }*/
				   return response()->json($std_atd,200);
				}
			}
//where('date',Carbon::today())
           	public function get_attendance_classes($class_id){
				   $classc = DB::table('Class')->select('*')->where('id','=',$class_id)->first();
				   $attendance = DB::table('Student')
				   ->join('Attendance', 'Student.regiNo', '=', 'Attendance.regiNo')
				   ->select( 'Attendance.id','Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName','Student.class','Attendance.status','Attendance.date')
                   ->where('Student.isActive','Yes')
                   ->where('Student.class',  $classc->code);
               
					 /*$attendance->when(request('regiNo', false), function ($q, $regiNo) { 
						return $q->where('Student.regiNo', $regiNo);
					  });
					   $attendance->when(request('class', false), function ($q, $class) { 
						 $classc = DB::table('Class')->select('*')->where('id','=',$class)->first();
						return $q->where('Student.class',  $classc->code);
					  });*/
					   $attendance->when(request('date', false), function ($q, $date) { 

						return $q->where('Attendance.date',  $date);
					  });

					   $attendance->when(request('session', false), function ($q, $session) { 

						return $q->where('Attendance.session',  get_current_session()->id);
					  });

					   $attendance->when(request('section', false), function ($q, $section) { 
						return $q->where('Student.section', $section);
					  });

					   $attendance->when(request('name', false), function ($q, $name) { 
						return $q->where('Student.firstName', 'like', '%' .$name.'%');
					  });
					/*->where('Student.class','=',Input::get('class'))
					->where('Student.section','=',Input::get('section'))
					->Where('Student.shift','=','Morning')
					->where('Student.session','=',trim(Input::get('session')))
					->where('Student.isActive', '=', 'Yes')
					->where('Attendance.date', '=', $date)*/
					$attendance=$attendance->get();
				if($attendance->isEmpty()) {
				  return response()->json(['error'=>'Attendance Not Found'], 404);
				}else{
					 return response()->json($attendance,200);
			 	}
			}

			public function classaten_history($class_id)
			{
                   $classc = DB::table('Class')->select('*')->where('id','=',$class_id)->first();
				   $attendances_a = DB::table('Attendance')->select(DB::raw('count(id) as absent'))->where('session',get_current_session()->id)->where('class_id',$class_id)->where('status','Absent')/*->where('date',Carbon::today())*/->first();
				   $attendances_p = DB::table('Attendance')->select(DB::raw('count(id) as present'))->where('session',get_current_session()->id)->where('class_id',$class_id)->where('status','Present')/*->where('date',Carbon::today())*/->first();
			       $data = array('Absent'=>$attendances_a->absent,'Present'=>$attendances_p->present);
			      if(empty($data)) {
				  return response()->json(['error'=>'Attendance Not Found'], 404);
				}else{
					 return response()->json($data,200);
			 	}
			} 
//
			public function get_attendance_class_today($class_id)
			{
               $classc = DB::table('Class')->select('*')->where('id','=',$class_id)->first();
				$attendance = DB::table('Student')
				->select(DB::raw("Student.id as student_id ,Student.regiNo, Student.rollNo, Student.firstName, Student.middleName, Student.lastName,Student.class,Attendance.status,Attendance.date,Class.id as class_id" ))
				->join('Class','Student.class','=', 'Class.code')
				->leftJoin('Attendance',function ($join) {
					$join->on('Attendance.regiNo', '=' , 'Student.regiNo') ;
					$join->where('Attendance.date','=',Carbon::today()->toDateString()) ;
				})
				->where('Student.isActive','Yes')
				->where('Student.class',  $classc->code)->where('Student.session',get_current_session()->id)->get();
				
				if($attendance->isEmpty()) {
					return response()->json(['error'=>'Attendance Not Found'], 404);
				}else{
					return response()->json($attendance,200);
				}
			}

			public function get_attendance_section($section_id)
			{
				  // $classc = DB::table('Class')->select('*')->where('id','=',$class_id)->first();
				 $attendance = DB::table('Student')
				->join('Attendance', 'Student.regiNo', '=', 'Attendance.regiNo')
				->select( 'Attendance.id','Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName','Student.class','Attendance.status','Attendance.date')
                ->where('Student.section',  $section_id)
                ->where('Student.isActive','Yes');
                //->get();
					 /*$attendance->when(request('regiNo', false), function ($q, $regiNo) { 
						return $q->where('Student.regiNo', $regiNo);
					  });
					   $attendance->when(request('class', false), function ($q, $class) { 
						 $classc = DB::table('Class')->select('*')->where('id','=',$class)->first();
						return $q->where('Student.class',  $classc->code);
					  });*/
					   $attendance->when(request('date', false), function ($q, $date) { 

						return $q->where('Attendance.date',  $date);
					  });

					   $attendance->when(request('session', false), function ($q, $session) { 

						return $q->where('Attendance.session',  get_current_session()->id);
					  });

					   $attendance->when(request('section', false), function ($q, $section) { 
						return $q->where('Student.section', $section);
					  });

					  /* $attendance->when(request('name', false), function ($q, $name) { 
						return $q->where('Student.firstName', 'like', '%' .$name.'%');
					  });*/
					/*->where('Student.class','=',Input::get('class'))
					->where('Student.section','=',Input::get('section'))
					->Where('Student.shift','=','Morning')
					->where('Student.session','=',trim(Input::get('session')))
					->where('Student.isActive', '=', 'Yes')
					->where('Attendance.date', '=', $date)*/
					$attendance=$attendance->get();
				if($attendance->isEmpty()) {
				  return response()->json(['error'=>'Attendance Not Found'], 404);
				}else{
					 return response()->json($attendance,200);
			 	}
			}


			public function get_attendance_section_today($section_id)
			{
                $now        = Carbon::now();
                $year       =  $now->year;
                $year       =  get_current_session()->id;
                $month      =  $now->month;
				$attendance = DB::table('Student')
				->select(DB::raw("Student.id as student_id ,Student.regiNo, Student.rollNo, Student.firstName, Student.middleName, Student.lastName,Student.class,Student.session,Attendance.status,Attendance.date,Class.id as class_id" ))
				->join('Class','Student.class','=', 'Class.code')
				->leftJoin('Attendance',function ($join) {
					$join->on('Attendance.regiNo', '=' , 'Student.regiNo') ;
					$join->where('Attendance.date','=',Carbon::today()->toDateString()) ;
				})
				->where('Student.isActive','Yes')
				->where('Student.section',  $section_id)
				->where('Student.session', $year)
				->get();
				
				if($attendance->isEmpty()) {
					return response()->json(['error'=>'Attendance Not Found'], 404);
				}else{

		                $attendances_a = DB::table('Attendance')
		                 ->join('Class', 'Attendance.class_id', '=', 'Class.id')
				          //->join('section', 'Attendance.section_id', '=', 'section.id')
		                 ->select(DB::raw('COUNT(*) as total_attendance,
		                           SUM(Attendance.status="Absent") as absent,
		                           SUM(Attendance.status="Present" ) as present ,
		                           SUM(Attendance.coments="sick_leave" OR Attendance.coments="leave") as leaves'))->where('Attendance.session',$year)->where('Attendance.section_id',$section_id)->where('date',Carbon::today()->toDateString())->first();
			            if($attendances_a->total_attendance==0){
			           	 $attendances_b[] = array('total_attendance'=>0,'absent'=>0,'present'=>0,'leaves'=>0);
			            }else{
			           	  //$attendances_b[] = array(get_object_vars($attendances_a),'total_student'=>$count_student1->total_student);
			              $attendances_b[] = $attendances_a;

			           }

						return response()->json(array('detail'=>$attendance,'attendaces_count'=>$attendances_b),200);
				}
			}

		    public function sectionaten_history($section_id)
			{
                   //$classc = DB::table('Class')->select('*')->where('id','=',$class_id)->first();
				   $attendances_a = DB::table('Attendance')->select(DB::raw('count(id) as absent'))->where('session',get_current_session()->id)->where('section_id',$section_id)->where('status','Absent')->where('date',Carbon::today())->first();
				   $attendances_p = DB::table('Attendance')->select(DB::raw('count(id) as present'))->where('session',get_current_session()->id)->where('section_id',$section_id)->where('status','Present')->where('date',Carbon::today())->first();
			       $data = array('Absent'=>$attendances_a->absent,'Present'=>$attendances_p->present);
			       //return response()->json($data,200);
			    if(empty($data)) {
				  return response()->json(['error'=>'Attendance Not Found'], 404);
				}else{
					 return response()->json($data,200);
			 	}
			} 

			public function attendance_done($section_id)
			{
				$check_attendance = SectionAttendance::where('date',Carbon::today()->toDateString())->first();
				if(empty($check_attendance)){
				$attendance_done = new SectionAttendance;
				$attendance_done->section_id =$section_id;
				$attendance_done->date =Carbon::today()->toDateString();
				$attendance_done->attendance = 'Done';
				$attendance_done->save();

                    if(request()->getHttpHost()=='localhost' || request()->getHttpHost()=='school.ictcore.org'){
						//if(request()->getHttpHost()=='school.ictcore.org'){
                        $attendances =	DB::table('Attendance')->where('section_id',$section_id)->where('date',Carbon::today()->toDateString())->where('status','Absent')->get();
                        if($attendances){
                            foreach($attendances as $attendance){
                                $student =	DB::table('Student')
								->join('Class', 'Student.class', '=', 'Class.code')
								->select( 'Student.regiNo','Student.rollNo','Student.firstName','Student.middleName','Student.lastName','Student.fatherCellNo','Student.fatherName','Class.Name as class')
								->where('Student.regiNo','=',$attendance->regiNo)
								->where('Student.section','=',$section_id)
								->where('Student.session','=',get_current_session()->id)
								->first();
							   $this->sendnotification($student);
							}
						}
					} 
				 return response()->json("Section Attendance Complete today",200);
			    }else{
			     return response()->json("Already Done",200);

			    }
			}
			public function get_attendance_done($section_id)
			{
				$attendance_done = SectionAttendance::where('section_id',$section_id)->where('date',Carbon::today()->toDateString())->first();
				 if(empty($attendance_done)){
				 return response()->json("Section Attendance Not Completed",400);

				 }
				 return response()->json($attendance_done ,200);
			}
			public function get_attendance_student($student_id){
				  // $classc = DB::table('Class')->select('*')->where('id','=',$class_id)->first();
				 $attendance = DB::table('Student')
				->join('Attendance', 'Student.regiNo', '=', 'Attendance.regiNo')
				->join('section', 'Student.section', '=', 'section.id')
				->select( 'Attendance.id','Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName','Student.class','section.name as section_name','Attendance.status','Attendance.date')
                ->where('Student.id',  $student_id)
                ->where('Student.isActive','Yes')
                ->get();
					 /*$attendance->when(request('regiNo', false), function ($q, $regiNo) { 
						return $q->where('Student.regiNo', $regiNo);
					  });
					   $attendance->when(request('class', false), function ($q, $class) { 
						 $classc = DB::table('Class')->select('*')->where('id','=',$class)->first();
						return $q->where('Student.class',  $classc->code);
					  });
					   $attendance->when(request('date', false), function ($q, $date) { 

						return $q->where('Attendance.date',  $date);
					  });

					   $attendance->when(request('session', false), function ($q, $session) { 

						return $q->where('Attendance.session',  $session);
					  });

					   $attendance->when(request('section', false), function ($q, $section) { 
						return $q->where('Student.section', $section);
					  });

					   $attendance->when(request('name', false), function ($q, $name) { 
						return $q->where('Student.firstName', 'like', '%' .$name.'%');
					  });
					/*->where('Student.class','=',Input::get('class'))
					->where('Student.section','=',Input::get('section'))
					->Where('Student.shift','=','Morning')
					->where('Student.session','=',trim(Input::get('session')))
					->where('Student.isActive', '=', 'Yes')
					->where('Attendance.date', '=', $date)*/
					//$attendance=$attendance->paginate(20);
				if($attendance->isEmpty()) {
				  return response()->json(['error'=>'Attendance Not Found'], 404);
				}else{
					 return response()->json($attendance,200);
			 	}
			}

            public function get_attendance_student_today($id)
			{

				$attendance = DB::table('Student')
				->select(DB::raw("Student.id as student_id ,Student.regiNo, Student.rollNo, Student.firstName, Student.middleName, Student.lastName,Student.class,Attendance.id as attendance_id,Attendance.status,Attendance.date,Class.id as class_id" ))
				->join('Class','Student.class','=', 'Class.code')
				->leftJoin('Attendance',function ($join) {
					$join->on('Attendance.regiNo', '=' , 'Student.regiNo') ;
					$join->where('Attendance.date','=',Carbon::today()->toDateString()) ;
				})
				->where('Student.isActive','Yes')
				->where('Student.id',  $id)
				->where('Student.session',2018)
				->first();
				
				if(empty($attendance)) {
					return response()->json(['error'=>'Attendance Not Found'], 404);
				}else{
					return response()->json($attendance,200);
				}
			}



			public function update_attendance($attendance_id){

				$rules = [
				'class_id'=>'required',

				'section_id'=>'required',
				'session'=>'required',
				'regiNo' => 'required',
				'date' => 'required',
				'status' =>'required'
				];
				$validator = \Validator::make(Input::all(), $rules);
				if ($validator->fails()) 
				{
						 return response()->json($validator->errors(), 422);
				}else 
				{
						$absentStudents = array();
						$students = Input::get('regiNo');
						$status = Input::get('status');
					    $class_id = Input::get('class_id');
						$section_id = Input::get('section_id');
						$presentDate = $this->parseAppDate(Input::get('date'));
                         
					  if($status =='Absent' || $status =='absent' ) {
					  
							$attendance = Attendance::find($attendance_id);
							$attendance->class_id = $class_id;
							$attendance->section_id = $section_id;
							$attendance->session=Input::get('session');
							//$attendance->date = $presentDate;
							$attendance->date =Carbon::parse(Input::get('date'))->format('Y-m-d');
							$attendance->regiNo= $students;
							$attendance->status= $status;
							if(Input::get('comments')!=''){
							$attendance->coments = Input::get('comments');
						    }
							//$attendance->created_at= Carbon::now();

							$attendance->save();
							   $student =	DB::table('Student')
								->join('Class', 'Student.class', '=', 'Class.code')
								->select( 'Student.regiNo','Student.rollNo','Student.firstName','Student.middleName','Student.lastName','Student.fatherCellNo','Class.Name as class')
								->where('Student.isActive','Yes')
								->where('Student.regiNo','=',$students)
								->where('Student.section','=',$section_id)
								//->where('class',Input::get('class'))
								->first();
							
								 $data = array(
									   'first_name' => $student->firstName,
										'last_name' => $student->lastName,
										'phone'     => $student->fatherCellNo,
										'email'     => '',
									);

								   $ict  = new ictcoreController();

									$ictcore_attendance= Ictcore_attendance::select("*")->first();
								/*if(!empty($ictcore_attendance) && $ictcore_attendance->ictcore_program_id!=''){
									
								   $contact_id = $ict->ictcore_api('contacts','POST',$data );
									$data = array(
										   'title'       => 'Attendance',
										   'program_id'  => $ictcore_attendance->ictcore_program_id,
											'account_id' => 1,
											'contact_id' => $contact_id,
											'origin'     => 1,
											'direction'  => 'outbound',
										);

									 $transmission_id = $ict->ictcore_api('transmissions','POST',$data );

									 

									 $transmission_send = $ict->ictcore_api('transmissions/'.$transmission_id.'/send','POST',$data=array() );

									 if(!is_array($transmission_send)){


										$status1 = "Completed";
									 }else{
										$status1 ="Pending";
									 }
									$msg =$ictcore_attendance->recording;
									 $smsLog = new SMSLog();
									 $smsLog->type      = "Attendanceapi";
									 $smsLog->sender    = "ictcore";
									 $smsLog->message   = $msg;
									 $smsLog->recipient = $student->fatherCellNo;
									 $smsLog->regiNo    = $students;
									 $smsLog->status    = $status1;
									 $smsLog->save();
							
						return response()->json($attendance,200);
					}else{

					  return response()->json(['Error'=>"Please Add Attendance Message in Setting."]);

					}*/
                  return response()->json($attendance,200);
				}else /*if($status =='Present' || $status =='preaent' || $status =='' || $status =='')*/{
					//}
					
				
					
						$attendance = Attendance::find($attendance_id);
							//$attendance->date = $presentDate;
							$attendance->date = Carbon::parse(Input::get('date'))->format('Y-m-d');
							 //return 'adeelddd';
							$attendance->class_id = $class_id;
							$attendance->section_id = $section_id;
							$attendance->session=Input::get('session');
							$attendance->regiNo= $students;
							$attendance->status= $status;
							//$attendance->created_at= Carbon::now();

							$attendance->save();

							 return response()->json($attendance,200);
				
				/*}
				catch (Exception $e) 
				{
					DB::rollback();
					$errorMessages = new Illuminate\Support\MessageBag;
					 $errorMessages->add('Error', 'Something went wrong!');
					return response()->json(['error'=>withErrors($errorMessages)], 400);

				}*/
			}/*else{
				 return response()->json(['error'=>'Wrong Status'], 400);

			}*/
				
			}

			}

			public function deleted($attendance_id){
	
			$attd = Attendance::find($attendance_id);
			if(!is_null($attd) && $attd->count()>0){

			   DB::table('Attendance')->where('Attendance.id','=',$attendance_id)->delete();
				  return response()->json(['success'=>"Students attendance deleted Succesfully."]);
			}else{
				return response()->json(['error'=>'Attendance Not Found'], 404);

			}
				}
			private function  parseAppDate($datestr)
			{
			  $date = explode('-', $datestr);
			  return $date[2].'-'.$date[1].'-'.$date[0];
			} 

			public function notification($section_id)
			{
				$status     = array('Absent','Late','late');
				$attendance = DB::table('Student')
				->select('Student.id as student_id','Student.firstName', 'Student.middleName', 'Student.lastName','Student.fatherCellNo','Attendance.status','Attendance.regiNo')
				//->join('Class', 'Student.class', '=', 'Class.code')
				->join('Attendance' ,'Student.regiNo', '=' , 'Attendance.regiNo')
				/*->Join('Attendance',function ($join) {
					$join->on('Attendance.regiNo', '=' , 'Student.regiNo') ;
					$join->where('Attendance.date','=',Carbon::today()->toDateString()) ;
				})*/
            ->where('Student.isActive','Yes')
           ->where('Student.section',  $section_id)
           ->where('Student.session',2018)
           ->where('Attendance.date','=',Carbon::today()
           	->toDateString())
           ->whereIn('Attendance.status',$status)
           ->get();
			//return response()->json($attendance, 200);
                if($attendance->count()){
                    //return response()->json('878878787', 200);


                    $ictcore_integration = Ictcore_integration::select("*")->first();
				    if(!empty($ictcore_integration) && $ictcore_integration->ictcore_url && $ictcore_integration->ictcore_user && $ictcore_integration->ictcore_password){ 
				      $ict  = new ictcoreController();
					  $data = array(
						'name' => 'Absent Notification',
						'description' => 'Absent notification',
						);

					 $group_id= $ict->ictcore_api('groups','POST',$data );

			     	}else{

			           // return Redirect::to('/fees/classreport')->withErrors("Please Add ictcore integration in Setting Menu");
	                return response()->json(['error'=>'Please Add ictcore integration in Setting Menu'], 404);

	                    exit();
			     	}
					foreach($attendance as $student)
					{

							$data= array(
					        //'registrationNumber' =>$stdfees->regiNo,
							'first_name'         => $student->firstName,
							'last_name'          =>  $student->lastName,
							'phone'              =>  $student->fatherCellNo,
							'email'              => '',
							);

						   $contact_id = $ict->ictcore_api('contacts','POST',$data );
						    $group = $ict->ictcore_api('contacts/'.$contact_id.'/link/'.$group_id,'PUT',$data=array() );
					}
			}else{
	                return response()->json(['error'=>'Attendance Not Found'], 404);
				//exit();
			}
			    $ictcore_attendance= Ictcore_attendance::select("*")->first();

			    if(!empty($ictcore_attendance) && $ictcore_attendance->ictcore_program_id!=''){
			    	
	                $data = array(
						'program_id' => $ictcore_attendance->ictcore_program_id,
						'group_id' => $group_id,
						'delay' => '',
						'try_allowed' => '',
						'account_id' => 1,
						'status' => '',
					);
					$campaign_id = $ict->ictcore_api('campaigns','POST',$data );
					//$campaign_id = $ict->ictcore_api('campaigns/$campaign_id/start','PUT',$data=array() );
			}
        return response()->json('Campaign Start', 200);


			}
}


			

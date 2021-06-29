<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ictcoreController;

//use App\Api_models\User;

use Illuminate\Support\Facades\Auth;

use Validator;
use App\ClassModel;
use App\Subject;
use App\Message;
use App\Attendance;
use App\Student;
use App\SectionModel;
use App\Ictcore_integration;
use DB;
use Excel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class sectionController extends Controller
{

    public function __construct() 
    {

       $this->middleware('auth:api');

    }
   public $successStatus = 200;

	/**
     * student_classwise api
     *
     * @return \Illuminate\Http\Response
     */
    public function section()
    {
	  $section = DB::table('section');
       $section->when(request('class', false), function ($q, $class) { 
            
              $classc = DB::table('Class')->select('*')->where('id','=',$class)->first();

            return $q->where('class_code',  $classc->code);

            });

           $section = $section->paginate(20);
	  if(count($section)<1)
	  {
	     return response()->json(['error'=>'No Section Found!'], 404);
	  }
	  else {
		  return response()->json($section,200);
	  }
    }

    public function getsection($section_id)
    {
         //$section = SectionModel::find($section_id);
         $now   = Carbon::now();
         $year  =  $now->year;
         $section = DB::table('section')->leftjoin('Student','section.id','=','Student.section')
         ->select('section.id','section.name','section.class_code',DB::raw("count(DISTINCT(Student.id)) as total_student"))->where('section.id',$section_id)->where('Student.session',$year)->where('Student.isActive','Yes')->groupBy('Student.section');
       // ,DB::raw("GROUP_CONCAT(estimation.id SEPARATOR ',') as estimations")
        if(!is_null($section) && $section->count()>0){
           return response()->json($section->first(),200);
        }else{
        return response()->json(['error'=>'Section Not Found'], 404);
       }
    }
    public function putsection($section_id){

        $section = SectionModel::find($section_id);
        if(!is_null($section) && $section->count()>0){

            $section = SectionModel::find($section_id);
            $section->name= Input::get('name');
            $section->description=Input::get('description');
            $section->save();
           return response()->json($section,200);
        }else{
        return response()->json(['error'=>'Section Not Found'], 404);
       }

    }
    public function update_class($class_id)
    {
        $rules=[
		'name' => 'required',
		'description' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
		 return response()->json($validator->errors(), 422);
		}
		else {
			$class = ClassModel::find($class_id);
			$class->name= Input::get('name');
			$class->description=Input::get('description');
			$class->save();
          return response()->json(['success'=>"Class Updated Succesfully."]);

		}
    }
    public function getsectionsubject($section_id){

        $section = SectionModel::find($section_id);
         $subject = DB::table('Subject')->select('code','name','type','class','stdgroup')->where('class',$section->class_code)->get();
//
         /*->join('Class', 'Student.class', '=', 'Class.code')
          ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
          'Class.Name as class','Student.section' ,'Student.presentAddress', 'Student.gender', 'Student.religion')
            ->where('Student.id',$student_id)->first();*/
        if(!is_null($subject) && count($subject)>0){
           return response()->json($subject);
        }else{
        return response()->json(['error'=>'Subject Not Found'], 401);
       }
    }
    public function getsectionstudent($section_id){

        $section = SectionModel::find($section_id);
        // $student = DB::table('Student')->select('*')->where('class',$section->class_code)->where('section',$section_id)->get();
          $student = DB::table('Student')
          ->join('Class', 'Student.class', '=', 'Class.code')
          ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
          'Class.Name as class','Student.section' ,'Student.group' ,'Student.session','Student.presentAddress', 'Student.gender', 'Student.religion')
          ->where('Student.isActive','Yes')
          ->get();

         /*->join('Class', 'Student.class', '=', 'Class.code')
          ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
          'Class.Name as class','Student.section' ,'Student.presentAddress', 'Student.gender', 'Student.religion')
            ->where('Student.id',$student_id)->first();*/
        if(!is_null($student) && count($student)>0){

           return response()->json($student,200);
        }else{
        return response()->json(['error'=>'Student Not Found'], 404);
       }
    }	

    public function getsectionteacher($section_id){

         $section = SectionModel::find($section_id);
         $student = DB::table('Student')->select('*')->where('class',$section->class_code)->where('section',$section_id)->get();

         $teacher = DB::table('teacher')
          ->join('timetable', 'teacher.id', '=', 'timetable.teacher_id')
          ->join('Subject', 'timetable.subject_id', '=', 'Subject.id')
          ->select('teacher.id', 'teacher.firstName', 'teacher.lastName', 'teacher.fatherName', 'teacher.fatherCellNo', 'teacher.fatherCellNo', 'teacher.presentAddress',
          'Subject.name as Subject')->groupby('timetable.teacher_id')
          ->where('timetable.section_id',$section_id)->get();
         /*->join('Class', 'Student.class', '=', 'Class.code')
           ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
          'Class.Name as class','Student.section' ,'Student.presentAddress', 'Student.gender', 'Student.religion')
            ->where('Student.id',$student_id)->first();*/
        if(!is_null($teacher) && count($teacher)>0){
           return response()->json($teacher,200);
        }else{
        return response()->json(['error'=>'Teacher Not Found'], 404);
       }
    }
//
    public function sectionwisenotification($section_id){

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
                /* $drctry = storage_path('app/public/messages/');
                 $mimetype      = mime_content_type($drctry.Input::get('message'));
                if($mimetype =='audio/x-wav' || $mimetype=='audio/wav'){ */
                    $ictcore_integration = Ictcore_integration::select("*")->first();
                 
                if(!empty($ictcore_integration) && $ictcore_integration->ictcore_url !='' && $ictcore_integration->ictcore_user !='' && $ictcore_integration->ictcore_password !=''){ 

                    $ict  = new ictcoreController();
                    $postmethod  = new NotificationController();
                    $data = array(
                    'name' => Input::get('name'),
                    'description' => 'this is section wise group',
                    );
                    $group_id= $ict->ictcore_api('groups','POST',$data );
                    $student=   DB::table('Student')
                    ->select('*')
                    ->where('isActive','Yes')
                    ->where('section', $section_id)
                    ->get();
                    foreach($student as $std){
                        $data = array(
                        'first_name' => $std->firstName,
                        'last_name' => $std->lastName,
                        'phone'     => $std->fatherCellNo,
                        'email'     => '',
                        );
                        $contact_id = $ict->ictcore_api('contacts','POST',$data );
                        $group = $ict->ictcore_api('contacts/'.$contact_id.'/link/'.$group_id,'PUT',$data=array() );
                    }
                        return $postmethod->postnotificationmethod(Input::get('name'),Input::get('type'),Input::get('message'),'group',$group_id);
            /*}else{
                 return response()->json("ERROR:Please Upload Correct file",415 );
             }*/
            }else{

                 return response()->json(['Error'=>"Please Add Intigration  in Setting. Notification not send"],400);
            }
        }
    }

     /*public function sectionwisenotification($section_id){

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

                      $section = SectionModel::find($section_id);

                      if(is_null($section)){
                         return response()->json(['error'=>'Section Not Found'], 404);
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
                                
                         $data = array(
                        'name' => Input::get('name'),
                        'description' => Input::get('description'),
                        );
                    $group_id= $ict->ictcore_api('groups','POST',$data );

                  //  $classes = ClassModel::find($class_id);

                  //  $class_code = $classes->code;

                    $student=   DB::table('Student')
                        ->select('*')
                        ->where('isActive','Yes')
                        ->where('section', $section_id)
                        ->get();
                        foreach($student as $std){

                            $data = array(
                            'first_name' => $std->firstName,
                            'last_name' => $std->lastName,
                            'phone'     => $std->fatherCellNo,
                            'email'     => '',
                            );


                            $contact_id = $ict->ictcore_api('contacts','POST',$data );

                            $group = $ict->ictcore_api('contacts/'.$contact_id.'/link/'.$group_id,'PUT',$data=array() );

                        }

                        $data = array(
                            'program_id' => $program_id,
                            'group_id' => $group_id,
                            'delay' => '',
                            'try_allowed' => '',
                            'account_id' => 1,
                            'status' => '',
                        );
                        $campaign_id = $ict->ictcore_api('campaigns','POST',$data );

                return response()->json(['success'=>"Nofication Sended Succesfully."],200);

            }else{
                    return response()->json("ERROR:Please Upload Correct file",415 );

            }
        }
    } */        
}


	        
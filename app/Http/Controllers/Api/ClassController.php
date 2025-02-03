<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ictcoreController;
//use Illuminate\Support\Facades\Input;
//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
//use App\Api_models\User;
//use Illuminate\Support\Facades\Auth;
//use Input; 
use Validator;
use App\ClassModel;
use App\Message;
use App\Subject;
use App\Attendance;
use App\Ictcore_integration;
use App\Student;
use App\SectionModel;
use DB;
//use Excel;
//use Illuminate\Support\Collection;
//use Carbon\Carbon;
class ClassController extends Controller
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
    public function classes()
    {
      $classes = DB::table('Class')->select('id','code','name','description')->get();
      if(count($classes)<1)
      {
         return response()->json(['error'=>'No Class Found!'], 404);
      }
      else {
          return response()->json($classes,200);
      }
    }
    /**
     * student_classwise api
     *
     * @return \Illuminate\Http\Response
     */
    public function classes_count()
    {
	  $tclass          =  ClassModel::count();
	  if(count($tclass)==0)
	  {
	     return response()->json(['error'=>'No Class Found!'], 404);
	  }
	  else {
		  return response()->json($tclass,200);
	  }
    }

    public function getclass($class_id)
    {
         $class = ClassModel::select('id','code','name','description')->where('id',$class_id)->first();

        if(!is_null($class) && $class->count()>0){
           return response()->json($class);
        }else{
        return response()->json(['error'=>'Class Not Found'], 404);
       }
    }

    public function getclass_section($class_id)
    {
         $classes = ClassModel::find($class_id);
       
       
         $section = DB::table('section')->select('name','description')->where('class_code',$classes->code)->get();



        if(!is_null($classes) && $classes->count()>0){
           return response()->json($section,200);
        }else{
        return response()->json(['error'=>'Class Sections Not Found'], 404);
       }
    }

    public function update_class($class_id)
    {
        $rules=[
        'code' => 'required',
		'name' => 'required',
		'description' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
		 return response()->json($validator->errors(), 422);
		}
		else {
			$class = ClassModel::select('id','code','name','description')->where('id',$class_id)->first();
            $class->code= Input::get('code');
			$class->name= Input::get('name');
			$class->description=Input::get('description');
			$class->save();
          return response()->json($class,200);

		}

    }


    public function classwisenotification($class_id){

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
                    $ict  = new ictcoreController();
                    $postmethod  = new NotificationController();
                    $classes = ClassModel::find($class_id);
                    $class_code = $classes->code;

                    $data = array(
                    'name' => Input::get('name'),
                    'description' => 'this is class '. $class_code.' group',
                    );
                    $group_id= $ict->ictcore_api('groups','POST',$data );
                    
                    $student=   DB::table('Student')
                    ->select('*')
                    ->where('isActive','Yes')
                    ->where('class', $class_code)
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

   /* public function classwisenotification($class_id){

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

                    $classes = ClassModel::find($class_id);

                    $class_code = $classes->code;

                    $student=   DB::table('Student')
                        ->select('*')
                        ->where('isActive','Yes')
                        ->where('class', $class_code)
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
    }*/	    
}


	        
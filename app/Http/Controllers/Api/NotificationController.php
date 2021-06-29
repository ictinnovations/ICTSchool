<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\ictcoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
//use App\Api_models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Exam;
use File;
use App\Message;
use DB;
use Excel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class NotificationController extends Controller
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
	public function getallnotification()
	{
		  $messages = DB::table('message')->select('id','name','description','recording','ictcore_program_id','ictcore_recording_id')->get();
		/*  ->join('Class', 'Student.class', '=', 'Class.code')
		  ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
		  'Class.Name as class','Student.section' ,'Student.group' ,'Student.presentAddress', 'Student.gender', 'Student.religion')
		  ->get();*/
		  if(count($messages)<1)
		  {
		     return response()->json(['error'=>'No message Found!'], 404);
		  }
		  else {
			  return response()->json(['messages' => $messages]);
		  }
	}


    public function getnotification($notification_id)
    {
         $message = Message::find($notification_id);
    	

        if(!is_null($message) && count($message)>0){
           return response()->json(['notification'=>$message]);
        }else{
        return response()->json(['error'=>'Notification Not Found'], 404);
       }
    }

    public function postnotification(Request $request)
    {
         
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
        else {

             return $this->postnotificationmethod(Input::get('name'),Input::get('type'),Input::get('message'),'','');
        }
    }

    public function postnotificationmethod($name,$type,$message,$creatnotication,$id)
    {
          $ict  = new ictcoreController();
        if($type=='voice' || $type=='Voice'){
            $drctry = storage_path('app/public/messages/');
            if(File::exists($drctry.$message)){
                $mimetype      = mime_content_type($drctry.$message);
                 if($mimetype =='audio/x-wav' || $mimetype=='audio/wav'){ 
                     	
                		    
                        $data = array(
                                     'name' => $name,
        				             'description' =>'',
        							 );
                         $recording_id  =  $ict->ictcore_api('messages/recordings','POST',$data );
                         $file_name     =  $drctry.$message;
                         $mimetype      =  mime_content_type($file_name);
                         $cfile         =  curl_file_create($file_name, $mimetype, basename($file_name));
                         $data          =  array( $cfile);
        				 $result        =  $ict->ictcore_api('messages/recordings/'.$recording_id.'/media','PUT',$data );
                         $recording_id  =  $result ;
                        if(!is_array($recording_id )){
                            $data = array(
                                     'name' => $name,
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
            							'name' => $name,
            							'description' =>$message,
            							'recording' =>  basename($file_name),
                                        'type'     => $type,
            							'ictcore_program_id' => $program_id,
            							'ictcore_recording_id' => $recording_id,
            						];
        				$notification_id = Message::insertGetId($notificationData);
        	    }else{
                    return response()->json("ERROR:Please Upload Correct file",415 );
        	    }
            }else{
                return response()->json("ERROR:file not found",404 );
            }
        }elseif($type=='sms' || $type=='Sms' || $type=='SMS'){
            $data = array(
                'name' => $name,
                'data' =>$message,
                'type' => 'plain',
                'description' =>'',
            );
            $text_id  =  $ict->ictcore_api('messages/texts','POST',$data );
            $data = array(
                'name' => $name,
                'text_id' =>$text_id,
            );
            $program_id  =  $ict->ictcore_api('programs/sendsms','POST',$data );
        }
        if($creatnotication=='group'){
            $data = array(
            'program_id' => $program_id,
            'group_id' => $id,
            'delay' => '',
            'try_allowed' => '',
            'account_id' => 1,
            'status' => '',
            );
            $campaign_id = $ict->ictcore_api('campaigns','POST',$data );
            return response()->json(['success'=>"Nofication Sended Succesfully."],200);
        }elseif($creatnotication=='single'){
            $data = array(
            'title' => 'Attendance',
            'program_id' => $program_id,
            'account_id'     => 1,
            'contact_id'     => $id,
            'origin'     => 1,
            'direction'     => 'outbound',
            );
            $transmission_id = $ict->ictcore_api('transmissions','POST',$data );
            $transmission_send = $ict->ictcore_api('transmissions/'.$transmission_id.'/send','POST',$data=array() );
            return response()->json(['success'=>"Nofication Sended Succesfully."],200);
        }
        return response()->json(['success'=>"Nofication save Succesfully.",'id' => $notification_id]);
    }




/* public function postnotificationmethod($name,$type,$message)
    {
         
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
        else {
                 
            if(Input::get('type')=='voice' || Input::get('type')=='Voice'){

                   $drctry = storage_path('app/public/messages/');

                if(File::exists($drctry.Input::get('message'))){

                   
                    $mimetype      = mime_content_type($drctry.Input::get('message'));
                     if($mimetype =='audio/x-wav' || $mimetype=='audio/wav'){ 
                             $ict  = new ictcoreController();
                                // $headers = apache_request_headers();
                                 //dd($headers['Authorization']);
                         // $filename ='notification_'.time();//'recordingn5QzxE.wav';//tempnam(public_path('recording/'), 'recording'). ".wav";

                         // file_put_contents(public_path('recording/').$filename.'.wav', $drctry.Input::get('message'));

                             //      unlink(public_path('recording/'.$filename));

                        
                            sleep(3);
                            $data = array(
                                         'name' => Input::get('name'),
                                         'description' => Input::get('description'),
                                         );

                             $recording_id  =  $ict->ictcore_api('messages/recordings','POST',$data );
                             $name          =  $drctry.Input::get('message');

                             $mimetype      =  mime_content_type($name);

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
                                        'description' =>Input::get('message'),
                                        'recording' =>  basename($name),
                                        'ictcore_program_id' => $program_id,
                                        'ictcore_recording_id' => $recording_id,
                                    ];

                                  $notification_id = Message::insertGetId($notificationData);


                          return response()->json(['success'=>"Nofication save Succesfully.",'id' => $notification_id]);
                    }else{
                        return response()->json("ERROR:Please Upload Correct file". $drctry.Input::get('message'),415 );
                    }
                }
            }

        }
      
    }*/
    public function putnotification($notification_id)
    {
        // return response()->json(Input::all());
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
            $type=Input::get('type');
            $message = Message::find($notification_id);
            if($type=='voice' || $type=='Voice'){
                $drctry = storage_path('app/public/messages/');
                 //return $drctry.Input::get('message');
                if(File::exists($drctry.Input::get('message'))){

                    $mimetype      = mime_content_type($drctry.Input::get('message'));

                    if($mimetype =='audio/x-wav' || $mimetype=='audio/wav'){ 
                        $drctry = storage_path('app/public/messages/');
                       // unlink($drctry.$message->recording);
                        $ict  = new ictcoreController();
                        $filename ='notification_'.time();//'recordingn5QzxE.wav';//tempnam(public_path('recording/'), 'recording'). ".wav";
                        $data = array(
                        'name' => Input::get('name'),
                        'description' =>'',
                        );
                        $recording_id  =  $ict->ictcore_api('messages/recordings','PUT',$data );
                        $name          =  $drctry.Input::get('message');
                        $cfile         =  curl_file_create($name, $mimetype, basename($name));
                        $data          =  array( $cfile);
                        $result        =  $ict->ictcore_api('messages/recordings/'.$message->ictcore_recording_id.'/media','PUT',$data );
                        $recording_id  =  $result ;
                        if(!is_array($recording_id )){
                           /* $data = array(
                            'name' => Input::get('title'),
                            'recording_id' => $recording_id,
                            );
                            $program_id = $ict->ictcore_api('programs/voicemessage','POST',$data );
                            if(!is_array( $program_id )){
                             $program_id = $program_id;
                            }else{
                             return response()->json("ERROR: Program not Created" );
                            }*/
                        }else{
                         return response()->json("ERROR: Recording not Created" );
                        }
                        $message->name         = Input::get('name');
                        $message->description  = Input::get('message');
                        $message->recording    = Input::get('message');
                        $message->type         = Input::get('type');
                        //$message->ictcore_program_id = $program_id;
                        //$message->ictcore_recording_id = $recording_id;
                        if(!is_null($message) && count($message)>0){
                            $message->save();
                            return response()->json(['nofication'=>$message]);
                        }else{
                            return response()->json(['error'=>'Notification Not Found'], 404);
                        }
                    }else{
                         return response()->json("ERROR:Please Upload Correct file",415 );
                    }
                }else{

                     return response()->json("ERROR:file not found",404 );
                }
            }else{
                return response()->json("ERROR:type not found",404 );
            }
        }
    }
     public function deletenotification($notification_id)
    {
          $notification = Message::find($notification_id);
		    if(!is_null($notification) && $notification->count()>0){

               DB::table('message')->where('id','=',$notification_id)->delete();
             $drctry = storage_path('app/public/messages/');
               unlink($drctry.$message->recording);

                  return response()->json(['success'=>"notification deleted Succesfully."],200);
		    }else{
		        return response()->json(['error'=>'notification Not Found'], 404);

		    }
    }

   
}


	        
<?php

namespace App\Http\Controllers\Api;

use DB;
use File;
use Excel;
use Validator;
use Carbon\Carbon;
use App\Models\Exam;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ICTCoreController;

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
        $messages = DB::table('message')->select('id', 'name', 'description', 'recording', 'ictcore_program_id', 'ictcore_recording_id')->get();
        /*  ->join('Class', 'Student.class', '=', 'Class.code')
		  ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
		  'Class.Name as class','Student.section' ,'Student.group' ,'Student.presentAddress', 'Student.gender', 'Student.religion')
		  ->get();*/
        if (count($messages) < 1) {
            return response()->json(['error' => 'No message Found!'], 404);
        } else {
            return response()->json(['messages' => $messages]);
        }
    }


    public function getnotification($notification_id)
    {
        $message = Message::find($notification_id);


        if ($message) {
            return response()->json(['notification' => $message]);
        } else {
            return response()->json(['error' => 'Notification Not Found'], 404);
        }
    }

    public function postnotification(Request $request)
    {

        $rules = [
            'name'    => 'required',
            'type'    => 'required',
            'message' => 'required'
        ];
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        } else {

            return $this->postnotificationmethod($request->input('name'), $request->input('type'), $request->input('message'), '', '');
        }
    }

    public function postnotificationmethod($name, $type, $message, $creatnotication, $id)
    {
        $ict  = new ictcoreController();
        if ($type == 'voice' || $type == 'Voice') {
            $drctry = 'public/messages/';
            if (Storage::exists($drctry . $message)) {
                $mimetype      = mime_content_type($drctry . $message);
                if ($mimetype == 'audio/x-wav' || $mimetype == 'audio/wav') {


                    $data = array(
                        'name' => $name,
                        'description' => '',
                    );
                    $recording_id  =  $ict->ictcore_api('messages/recordings', 'POST', $data);
                    $file_name     =  $drctry . $message;
                    $mimetype      =  mime_content_type($file_name);
                    $cfile         =  curl_file_create($file_name, $mimetype, basename($file_name));
                    $data          =  array($cfile);
                    $result        =  $ict->ictcore_api('messages/recordings/' . $recording_id . '/media', 'PUT', $data);
                    $recording_id  =  $result;
                    if (!is_array($recording_id)) {
                        $data = array(
                            'name' => $name,
                            'recording_id' => $recording_id,
                        );
                        $program_id = $ict->ictcore_api('programs/voicemessage', 'POST', $data);
                        if (!is_array($program_id)) {
                            $program_id = $program_id;
                        } else {
                            return response()->json("ERROR: Program not Created");
                        }
                    } else {
                        return response()->json("ERROR: Recording not Created");
                    }
                    $notificationData = [
                        'name' => $name,
                        'description' => $message,
                        'recording' =>  basename($file_name),
                        'type'     => $type,
                        'ictcore_program_id' => $program_id,
                        'ictcore_recording_id' => $recording_id,
                    ];
                    $notification_id = Message::insertGetId($notificationData);
                } else {
                    return response()->json("ERROR:Please Upload Correct file", 415);
                }
            } else {
                return response()->json("ERROR:file not found", 404);
            }
        } elseif ($type == 'sms' || $type == 'Sms' || $type == 'SMS') {
            $data = array(
                'name' => $name,
                'data' => $message,
                'type' => 'plain',
                'description' => '',
            );
            $text_id  =  $ict->ictcore_api('messages/texts', 'POST', $data);
            $data = array(
                'name' => $name,
                'text_id' => $text_id,
            );
            $program_id  =  $ict->ictcore_api('programs/sendsms', 'POST', $data);
        }
        if ($creatnotication == 'group') {
            $data = array(
                'program_id' => $program_id,
                'group_id' => $id,
                'delay' => '',
                'try_allowed' => '',
                'account_id' => 1,
                'status' => '',
            );
            $campaign_id = $ict->ictcore_api('campaigns', 'POST', $data);
            return response()->json(['success' => "Nofication Sended Succesfully."], 200);
        } elseif ($creatnotication == 'single') {
            $data = array(
                'title' => 'Attendance',
                'program_id' => $program_id,
                'account_id'     => 1,
                'contact_id'     => $id,
                'origin'     => 1,
                'direction'     => 'outbound',
            );
            $transmission_id = $ict->ictcore_api('transmissions', 'POST', $data);
            $transmission_send = $ict->ictcore_api('transmissions/' . $transmission_id . '/send', 'POST', $data = array());
            return response()->json(['success' => "Nofication Sended Succesfully."], 200);
        }
        return response()->json(['success' => "Nofication save Succesfully.", 'id' => $notification_id]);
    }




    /* public function postnotificationmethod($name,$type,$message)
    {
         
     $rules=[
            'name'    =>'required',
            'type'    => 'required',
            'message' =>'required'

            ];
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails())
        {
         return response()->json($validator->errors(), 422);
        }
        else {
                 
            if($request->input('type')=='voice' || $request->input('type')=='Voice'){

                   $drctry = storage_path('app/public/messages/');

                if(File::exists($drctry.$request->input('message'))){

                   
                    $mimetype      = mime_content_type($drctry.$request->input('message'));
                     if($mimetype =='audio/x-wav' || $mimetype=='audio/wav'){ 
                             $ict  = new ictcoreController();
                                // $headers = apache_request_headers();
                                 //dd($headers['Authorization']);
                         // $filename ='notification_'.time();//'recordingn5QzxE.wav';//tempnam(public_path('recording/'), 'recording'). ".wav";

                         // file_put_contents(public_path('recording/').$filename.'.wav', $drctry.$request->input('message'));

                             //      unlink(public_path('recording/'.$filename));

                        
                            sleep(3);
                            $data = array(
                                         'name' => $request->input('name'),
                                         'description' => $request->input('description'),
                                         );

                             $recording_id  =  $ict->ictcore_api('messages/recordings','POST',$data );
                             $name          =  $drctry.$request->input('message');

                             $mimetype      =  mime_content_type($name);

                             $cfile         =  curl_file_create($name, $mimetype, basename($name));
                             $data          =  array( $cfile);
                             $result        =  $ict->ictcore_api('messages/recordings/'.$recording_id.'/media','PUT',$data );
                             $recording_id  =  $result ;
                            if(!is_array($recording_id )){

                              $data = array(
                                         'name' => $request->input('title'),
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
                                        'name' => $request->input('name'),
                                        'description' =>$request->input('message'),
                                        'recording' =>  basename($name),
                                        'ictcore_program_id' => $program_id,
                                        'ictcore_recording_id' => $recording_id,
                                    ];

                                  $notification_id = Message::insertGetId($notificationData);


                          return response()->json(['success'=>"Nofication save Succesfully.",'id' => $notification_id]);
                    }else{
                        return response()->json("ERROR:Please Upload Correct file". $drctry.$request->input('message'),415 );
                    }
                }
            }

        }
      
    }*/
    public function putnotification(Request $request, $notification_id)
    {
        $rules = [
            'name'    => 'required',
            'type'    => 'required',
            'message' => 'required'
        ];

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        } else {
            $type = $request->input('type');
            $message = Message::find($notification_id);
            if ($type == 'voice' || $type == 'Voice') {
                $drctry = 'public/messages/';
                //return $drctry.$request->input('message');
                if (Storage::exists($drctry . $request->input('message'))) {

                    $mimetype      = mime_content_type($drctry . $request->input('message'));

                    if ($mimetype == 'audio/x-wav' || $mimetype == 'audio/wav') {
                        $drctry = storage_path('app/public/messages/');
                        // unlink($drctry.$message->recording);
                        $ict  = new ictcoreController();
                        $filename = 'notification_' . time(); //'recordingn5QzxE.wav';//tempnam(public_path('recording/'), 'recording'). ".wav";
                        $data = array(
                            'name' => $request->input('name'),
                            'description' => '',
                        );
                        $recording_id  =  $ict->ictcore_api('messages/recordings', 'PUT', $data);
                        $name          =  $drctry . $request->input('message');
                        $cfile         =  curl_file_create($name, $mimetype, basename($name));
                        $data          =  array($cfile);
                        $result        =  $ict->ictcore_api('messages/recordings/' . $message->ictcore_recording_id . '/media', 'PUT', $data);
                        $recording_id  =  $result;
                        if (!is_array($recording_id)) {
                            /* $data = array(
                            'name' => $request->input('title'),
                            'recording_id' => $recording_id,
                            );
                            $program_id = $ict->ictcore_api('programs/voicemessage','POST',$data );
                            if(!is_array( $program_id )){
                             $program_id = $program_id;
                            }else{
                             return response()->json("ERROR: Program not Created" );
                            }*/
                        } else {
                            return response()->json("ERROR: Recording not Created");
                        }
                        $message->name         = $request->input('name');
                        $message->description  = $request->input('message');
                        $message->recording    = $request->input('message');
                        $message->type         = $request->input('type');
                        //$message->ictcore_program_id = $program_id;
                        //$message->ictcore_recording_id = $recording_id;
                        if (!is_null($message) && count($message) > 0) {
                            $message->save();
                            return response()->json(['nofication' => $message]);
                        } else {
                            return response()->json(['error' => 'Notification Not Found'], 404);
                        }
                    } else {
                        return response()->json("ERROR:Please Upload Correct file", 415);
                    }
                } else {

                    return response()->json("ERROR:file not found", 404);
                }
            } else {
                return response()->json("ERROR:type not found", 404);
            }
        }
    }
    public function deletenotification($notification_id)
    {
        $notification = Message::find($notification_id);
        if (!is_null($notification) && $notification->count() > 0) {

            DB::table('message')->where('id', '=', $notification_id)->delete();
            $drctry = storage_path('app/public/messages/');
            unlink($drctry . $message->recording);

            return response()->json(['success' => "notification deleted Succesfully."], 200);
        } else {
            return response()->json(['error' => 'notification Not Found'], 404);
        }
    }
}

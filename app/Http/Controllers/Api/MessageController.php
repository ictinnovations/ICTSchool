<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\ictcoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
//use App\Api_models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
//use Storage;
use File;
use App\Exam;
use App\Message;
use DB;
use Excel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class MessageController extends Controller
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
      public function getallmessages()
      {
         $drctry = storage_path('app/public/messages/');

          $files = File::allfiles($drctry);
          $i=0;
          $basename = array();
          foreach ($files as $file)
          {
              $filename[] = pathinfo($file);
              if(!empty($filename) && count($filename)>0 ){
                if(($filename[$i]['extension']=='wav' || $filename[$i]['extension']=='Wav' )){
                  $basename[]=$filename[$i]['basename'];
                }
            }else{
             return response()->json(['error'=>'messages  not found'], 404);
            }
              $i++;
          }
            return response()->json($basename,200);
      }
      public function getmessage($filename)
      {
           $drctry = storage_path('app/public/messages/');//asset(storage_path('app/public/messages/'));//

          if (File::exists($drctry.$filename))
          {
            return response()->download(storage_path('app/public/messages/' . $filename));
          }else{
            return response()->json("ERROR:file not found",404 );

          }

           //$contents = Storage::get($drctry.$filename);

          /* $response = response($drctry.$filename, 200);
            $response->header('Content-Type', ' audio/x-wav');
            $response->header('Content-Disposition', 'attachment; filename='.$filename);
            return $response;
            $headers = array(
             // 'Content-Type'=> 'audio/x-wav',
              //'Content-Disposition'=> 'attachment; filename=' . $filename,
              'Content-type', 'audio/x-wav',
              'Content-Disposition', 'attachment; filename='.$filename
              //'Location:'.$drctry.$filename
            );
             return response()->download($drctry.$filename,$headers);*/


        //->header('Content-type', 'audio/x-wav')
       // ->header('Content-Disposition', 'attachment; filename='.$filename);
              //$headers = array('Content-Type: application/pdf',);
            //return response()->download($drctry.$filename,$headers);


         //  return response()->download($drctry.$filename);

         // return response()->json($contents,200);
      }
    public function postmessage(Request $request)
    {
      $fil = $request->all();
      $filedata =  file_get_contents('php://input');
      $finfo = new \finfo(FILEINFO_MIME_TYPE);
      $mimetype      = $finfo->buffer($filedata);
      if($mimetype =='audio/x-wav' || $mimetype=='audio/wav'){
        $filename ='notification_'.time();
        Storage::disk('public')->put('messages/'.$filename.'.wav', $filedata);
        return $filename.'.wav';
      }else{
       return response()->json("ERROR:Please Upload Correct file",415 );
      }
    }

    public function putmessage($filename)
    {
      $drctry = storage_path('app/public/messages/');
      if (File::exists($drctry.$filename))
      {
        unlink($drctry.$filename);

        $filedata =  file_get_contents('php://input');
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimetype      = $finfo->buffer($filedata);

        if($mimetype =='audio/x-wav' || $mimetype=='audio/wav'){
          $filename ='notification_'.time();
          Storage::disk('public')->put('messages/'.$filename.'.wav', $filedata);
          return $filename.'.wav';
        }else{
          return response()->json("ERROR:Please Upload Correct file",415 );
        }
      }else{
      return response()->json("ERROR:file not found",404 );
      }

    }
    public function deletemessage($filename)
    {
      $drctry = storage_path('app/public/messages/');
      if (File::exists($drctry.$filename))
      {
            //Storage::delete($drctry.$filename);
            unlink($drctry.$filename);
         return response()->json('File Deleted',200);
       }else{
         return response()->json("ERROR:File Already Deleted" );
       }

    }


}



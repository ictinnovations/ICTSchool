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
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ictcoreController;

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
    $i = 0;
    $basename = array();
    foreach ($files as $file) {
      $filename[] = pathinfo($file);
      if (!empty($filename) && count($filename) > 0) {
        if (($filename[$i]['extension'] == 'wav' || $filename[$i]['extension'] == 'Wav')) {
          $basename[] = $filename[$i]['basename'];
        }
      } else {
        return response()->json(['error' => 'messages  not found'], 404);
      }
      $i++;
    }
    return response()->json($basename, 200);
  }
  public function getmessage($filename)
  {
    $drctry = 'public/messages/'; 

    if (Storage::exists($drctry . $filename)) {
        return Storage::download($drctry . $filename);
    } else {
        return response()->json(['ERROR' => 'File not found'], 404);
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
    if ($mimetype == 'audio/x-wav' || $mimetype == 'audio/wav') {
      $filename = 'notification_' . time();
      Storage::disk('public')->put('messages/' . $filename . '.wav', $filedata);
      return $filename . '.wav';
    } else {
      return response()->json(['error' => 'Please upload a valid WAV file'], 415);
    }
  }

  public function putmessage($filename)
  {
    $drctry = 'public/messages/'; 

    if (Storage::exists($drctry . $filename)) {
      unlink($drctry . $filename);

      $filedata =  file_get_contents('php://input');
      $finfo = new \finfo(FILEINFO_MIME_TYPE);
      $mimetype      = $finfo->buffer($filedata);

      if ($mimetype == 'audio/x-wav' || $mimetype == 'audio/wav') {
        $filename = 'notification_' . time();
        Storage::disk('public')->put('messages/' . $filename . '.wav', $filedata);
        return $filename . '.wav';
      } else {
        return response()->json(['error' => 'Please upload a valid WAV file'], 415);
      }
    } else {
      return response()->json(['error' => 'File not found'], 404);
    }
  }
  public function deletemessage($filename)
  {
    $drctry = 'public/messages/'; 
    if (Storage::exists($drctry . $filename)) {
      //Storage::delete($drctry.$filename);
      unlink($drctry . $filename);
      return response()->json(['message' => 'File Deleted'], 200);
    } else {
      return response()->json(['error' => 'File not found or already deleted'], 404);
    }
  }
}

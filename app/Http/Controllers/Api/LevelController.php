<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use App\Http\Controllers\Controller;

//use App\Api_models\User;

use Illuminate\Support\Facades\Auth;

use Validator;
use App\Level;
use DB;


class LevelController extends Controller
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
    public function levels()
    {
	  $level = DB::table('level')->select('id','name','description')->get();
	  if(count($level)<1)
	  {
	     return response()->json(['error'=>'No level Found!'], 404);
	  }
	  else {
		  return response()->json(['levels' => $level],200);
	  }
    }

    public function getlevel($level_id)
    {
         $levels = Level::find($level_id);
        if(!is_null($levels) && $levels->count()>0){
           return response()->json(['level'=>$levels]);
        }else{
        return response()->json(['error'=>'Level Not Found'], 404);
       }
    }    
}


	        
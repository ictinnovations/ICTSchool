<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
//use App\Api_models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Exam;
use DB;
use Excel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ExamController extends Controller
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
	public function getallexam()
	{

           $exams = DB::table('exam')
          ->join('Class', 'exam.class_id', '=', 'Class.id')
          ->join('section', 'exam.section_id', '=', 'section.id')
          ->select('exam.id','exam.type','Class.name as class','section.name as section')
    	  ->get();
		
		  if(count($exams)<1)
		  {
		     return response()->json(['error'=>'No exam Found!'], 404);
		  }
		  else {
			  return response()->json($exams);
		  }
	}
    public function getexam($exam_id)
    {
    	  $exam = DB::table('exam')
          ->join('Class', 'exam.class_id', '=', 'Class.id')
          ->join('section', 'exam.section_id', '=', 'section.id')
          ->select('exam.id','exam.type','Class.name as class','section.name as section')
    	  ->where('exam.id','=',$exam_id)->first();
        if(!is_null($exam) && count($exam)>0){
           return response()->json($exam);
        }else{
        return response()->json(['error'=>'exam Not Found'], 404);
       }
    }  
}


	        
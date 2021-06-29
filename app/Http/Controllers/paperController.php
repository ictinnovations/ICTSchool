<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Exam;
use App\ClassModel;
use App\SectionModel;

use DB;
class paperController extends BaseController {

	public function __construct() {
		/*$this->beforeFilter('csrf', array('on'=>'post'));
		$this->beforeFilter('auth');
		$this->beforeFilter('userAccess',array('only'=> array('delete')));*/
		
	      $this->middleware('auth');
          $this->middleware('auth',array('only'=> array('delete')));
	}
	/**
	* Display a listing of the resource.
	*
	* @return Response
	*/
	public function index()
	{
		 $classes = DB::table('Class')->get();
		 $sections = DB::table('section')->get();

		return View('app.examCreate',compact('classes','sections'));
		//echo "this is section controller";
	}


	/**
	* Show the form for creating a new resource.
	*
	* @return Response
	*/
	public function create()
	{
		$rules=[
			'type' => 'required',
			'class' => 'required',
			'section' => 'required'

		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/exam/create')->withErrors($validator);
		}
		else {
			$type = Input::get('type');

			 $classes = DB::table('Class')->select("*")->where('code','=',Input::get('class'))->first();

			

			$sexists=Exam::select('*')->where('type','=',$type)->where('class_id','=',$classes->id)->where('section_id','=',Input::get('section'))->get();
			if(count($sexists)>0){

				$errorMessages = new \Illuminate\Support\MessageBag;
				$errorMessages->add('deplicate', 'Exam all ready exists!!');
				return Redirect::to('/exam/create')->withErrors($errorMessages);
			}
			else {
				//echo "<pre>";print_r(Input::get('section'));exit;
				foreach(Input::get('section') as $section_id)
				{
					$exam = new Exam;
					$exam->type = Input::get('type');
					$exam->class_id = $classes->id;
					$exam->section_id = $section_id;
					$exam->save();
			    }
				return Redirect::to('/exam/create')->with("success", "Exam Created Succesfully.");
			}

		}

	}


	/**
	* Store a newly created resource in storage.
	*
	* @return Response
	*/
	public function show()
	{
		//$Classes = ClassModel::orderby('code','asc')->get();
		/*$exams = DB::table('exam')
		->select(DB::raw('*'))
		->get();*/

         $exams = DB::table('exam')
          ->join('Class', 'exam.class_id', '=', 'Class.id')
          ->join('section', 'exam.section_id', '=', 'section.id')
          ->select('exam.id','exam.type', 'Class.name as class', 'section.name as section')
          ->get();


       // echo "<pre>";print_r($exams);

        
         //exit;
		//dd($sections);
		//return View::Make('app.classList',compact('Classes'));
		return View('app.examList',compact('exams'));
	}
	/**
	* Show the form for editing the specified resource.
	*
	* @param  int  $id
	* @return Response
	*/
	public function edit($id)
	{
		$exam = Exam::find($id);
		 $classes = DB::table('Class')->get();

		  $getclsss_code = DB::table('Class')->select("*")->where('id','=',$exam->class_id)->first();
		  
		 $sections = DB::table('section')->where('class_code','=',$getclsss_code->code)->get();
		//return View::Make('app.classEdit',compact('class'));
		return View('app.examEdit',compact('exam','classes','sections'));
	}


	/**
	* Update the specified resource in storage.
	*
	* @param  int  $id
	* @return Response
	*/
	public function update()
	{
		$rules=[
			'type' => 'required',
			'class' => 'required',
			'section' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/exam/edit/'.Input::get('id'))->withErrors($validator);
		}
		else {

			$classes = DB::table('Class')->select("*")->where('code','=',Input::get('class'))->first();
			$exam = Exam::find(Input::get('id'));
			$exam->type = Input::get('type');
			$exam->class_id = $classes->id;
			$exam->section_id = Input::get('section');

			$exam->save();
			return Redirect::to('/exam/list')->with("success","Exam Updated Succesfully.");

		}
	}


	/**
	* Remove the specified resource from storage.
	*
	* @param  int  $id
	* @return Response
	*/
	public function delete($id)
	{
		$exam = Exam::find($id);
		$exam->delete();
		return Redirect::to('/exam/list')->with("success","Exam Deleted Succesfully.");
	}

	public function getexams($class)
	{
		 $class_id = DB::table('Class')->select("*")->where('code','=',$class)->first();
                
		 $class_data = Exam::select('id','type')->where('class_id','=',$class_id->id)->get();
	return $class_data;
	}

}

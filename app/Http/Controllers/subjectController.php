<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Subject;
use App\ClassModel;
use App\GPA;
use DB;
class subjectController extends BaseController {

	public function __construct() {
		/*$this->beforeFilter('csrf', array('on'=>'post'));
		$this->beforeFilter('auth');
		$this->beforeFilter('userAccess',array('only'=> array('delete')));*/
	       $this->middleware('auth');
              // $this->middleware('userAccess',array('only'=> array('delete')));
	}
	/**
	* Display a listing of the resource.
	*
	* @return Response
	*/
	public function index()
	{
		$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
		$gpa =GPA::select('for')->distinct()->get();
           // echo "<pre>";print_r($gpa);exit;
		//return View::Make('app.subjectCreate',compact('classes','gpa'));
		return View('app.subjectCreate',compact('classes','gpa'));
	}


	/**
	* Show the form for creating a new resource.
	*
	* @return Response
	*/
	public function create()
	{

//echo "<pre>";print_r(Input::get('class'));
    $classes =  Input::get('class');
		$rules=[
			'name'         => 'required',
			'code'         => 'required',
			'type'         => 'required',
			'subgroup'     => 'required',
			'stdgroup'     => 'required',
			'class'        => 'required',
			'gradeSystem'  => 'required',
			'totalfull'    => 'required',
			'wfull'        => 'required',
			'mfull'        => 'required',
			'sfull'        => 'required',
			'pfull'        => 'required',
			'totalpass'    => 'required',
			'wpass'        => 'required',
			'mpass'        => 'required',
			'spass'        => 'required',
			'ppass'        => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/subject/create')->withErrors($validator);
		}
		else {
			$exsubject = Subject::select('*')->where('class',Input::get('class'))->where('code',Input::get('code'))->get();
			if(count($exsubject)>0)
			{
				$errorMessages = new Illuminate\Support\MessageBag;
				$errorMessages->add('deplicate', 'subject all ready exists for this class!!');
				return Redirect::to('/subject/create')->withErrors($errorMessages);


			}
			else {
				foreach($classes as $class){
				$subject = new Subject;
				$subject->name = Input::get('name');
				$subject->code = Input::get('code');
				$subject->class = $class;
				$subject->gradeSystem = Input::get('gradeSystem');
				$subject->type = Input::get('type');
				$subject->subgroup = Input::get('subgroup');
				$subject->stdgroup = Input::get('stdgroup');
				$subject->totalfull = Input::get('totalfull');
				$subject->totalpass = Input::get('totalpass');
				$subject->wfull = Input::get('wfull');
				$subject->wpass = Input::get('wpass');
				$subject->mfull = Input::get('mfull');
				$subject->mpass = Input::get('mpass');
				$subject->sfull = Input::get('sfull');
				$subject->spass = Input::get('spass');
				$subject->pfull = Input::get('pfull');
				$subject->ppass = Input::get('ppass');

				$subject->save();
			}
				return Redirect::to('/subject/create')->with("success", "Subject Created Succesfully.");
			}

		}
	}


	/**
	* show all resource in strograge.
	*
	* @return Response
	*/
	public function show()
	{

		$Subjects=	DB::table('Subject')
		->join('Class', 'Subject.class', '=', 'Class.code')
		->select('Subject.id', 'Subject.code','Subject.name','Subject.type', 'Subject.subgroup','Subject.stdgroup','Subject.totalfull',
		'Subject.totalpass','Subject.gradeSystem','Subject.wfull', 'Subject.wpass','Subject.mfull','Subject.mpass','Class.Name as class','Subject.sfull','Subject.spass',
		'Subject.pfull','Subject.ppass')
		->get();

		//return View::Make('app.subjectList',compact('Subjects'));
		return View('app.subjectList',compact('Subjects'));
	}




	/**
	* Show the form for editing the specified resource.
	*
	* @param  int  $id
	* @return Response
	*/
	public function edit($id)
	{
		$classes = ClassModel::pluck('name','code');
		$subject = Subject::find($id);
		$gpa =GPA::select('for')->distinct()->get();
		//return View::Make('app.subjectEdit',compact('subject','classes'));
		return View('app.subjectEdit',compact('subject','classes','gpa'));

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
			'name' => 'required',
			'code' => 'required',
			'type' => 'required',
			'subgroup' => 'required',
			'stdgroup' => 'required',
			'class' => 'required',
			'gradeSystem' => 'required',
			'totalfull' => 'required',
			'wfull' => 'required',
			'mfull' => 'required',
			'sfull' => 'required',
			'pfull' => 'required'

		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/subject/edit/'.Input::get('id'))->withErrors($validator);
		}
		else {
			$subject = Subject::find(Input::get('id'));
			$subject->name= Input::get('name');
			$subject->code=Input::get('code');
			$subject->class=Input::get('class');
			$subject->gradeSystem=Input::get('gradeSystem');
			$subject->type=Input::get('type');
			$subject->subgroup=Input::get('subgroup');
			$subject->stdgroup=Input::get('stdgroup');

			$subject->totalfull=Input::get('totalfull');
			$subject->totalpass=Input::get('totalpass');
			$subject->wfull=Input::get('wfull');
			$subject->wpass=Input::get('wpass');
			$subject->mfull=Input::get('mfull');
			$subject->mpass=Input::get('mpass');
			$subject->sfull=Input::get('sfull');
			$subject->spass=Input::get('spass');
			$subject->pfull=Input::get('pfull');
			$subject->ppass=Input::get('ppass');
			$subject->save();
			return Redirect::to('/subject/list')->with("success","Subject Updated Succesfully.");

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
		$subject = Subject::find($id);
		$subject->delete();
		return Redirect::to('/subject/list')->with("success","Subject Deleted Succesfully.");
	}
	public function getmarks($subject,$cls)
	{
		$subject = Subject::select('totalfull','totalpass','wfull','wpass','mfull','mpass','sfull','spass','pfull','ppass')->where('code','=',$subject)->where('class','=',$cls)->get();
		return $subject;
	}
	public function getsubjects($class){

      $subject= Subject::select('id','name')->where('class','=',$class)->get();
	return $subject;
	}


}

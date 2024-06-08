<?php
namespace App\Http\Controllers;
use DB;
use App\Models\GPA;
use App\Models\Subject;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

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
	public function create(Request $request)
	{

//echo "<pre>";print_r($request->input('class'));
    $classes =  $request->input('class');
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
		$validator = \Validator::make($request->all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/subject/create')->withErrors($validator);
		}
		else {
			$exsubject = Subject::select('*')->where('class',$request->input('class'))->where('code',$request->input('code'))->get();
			if(count($exsubject)>0)
			{
				$errorMessages = new Illuminate\Support\MessageBag;
				$errorMessages->add('deplicate', 'subject all ready exists for this class!!');
				return Redirect::to('/subject/create')->withErrors($errorMessages);


			}
			else {
				foreach($classes as $class){
				$subject = new Subject;
				$subject->name = $request->input('name');
				$subject->code = $request->input('code');
				$subject->class = $class;
				$subject->gradeSystem = $request->input('gradeSystem');
				$subject->type = $request->input('type');
				$subject->subgroup = $request->input('subgroup');
				$subject->stdgroup = $request->input('stdgroup');
				$subject->totalfull = $request->input('totalfull');
				$subject->totalpass = $request->input('totalpass');
				$subject->wfull = $request->input('wfull');
				$subject->wpass = $request->input('wpass');
				$subject->mfull = $request->input('mfull');
				$subject->mpass = $request->input('mpass');
				$subject->sfull = $request->input('sfull');
				$subject->spass = $request->input('spass');
				$subject->pfull = $request->input('pfull');
				$subject->ppass = $request->input('ppass');

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
	public function update(Request $request)
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
		$validator = \Validator::make($request->all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/subject/edit/'.$request->input('id'))->withErrors($validator);
		}
		else {
			$subject = Subject::find($request->input('id'));
			$subject->name= $request->input('name');
			$subject->code=$request->input('code');
			$subject->class=$request->input('class');
			$subject->gradeSystem=$request->input('gradeSystem');
			$subject->type=$request->input('type');
			$subject->subgroup=$request->input('subgroup');
			$subject->stdgroup=$request->input('stdgroup');

			$subject->totalfull=$request->input('totalfull');
			$subject->totalpass=$request->input('totalpass');
			$subject->wfull=$request->input('wfull');
			$subject->wpass=$request->input('wpass');
			$subject->mfull=$request->input('mfull');
			$subject->mpass=$request->input('mpass');
			$subject->sfull=$request->input('sfull');
			$subject->spass=$request->input('spass');
			$subject->pfull=$request->input('pfull');
			$subject->ppass=$request->input('ppass');
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

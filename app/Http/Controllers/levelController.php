<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Level;
use DB;
class levelController extends BaseController {

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
		return View('app.levelCreate');
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
			'name' => 'required',
			'description' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/level/create')->withErrors($validator);
		}
		else {
			$sname = Input::get('name');
			$sexists=Level::select('*')->where('name','=',$sname)->get();
			if(count($sexists)>0){

				$errorMessages = new \Illuminate\Support\MessageBag;
				$errorMessages->add('deplicate', 'level all ready exists!!');
				return Redirect::to('/level/create')->withErrors($errorMessages);
			}
			else {
				$class = new Level;
				$class->name = Input::get('name');
				$class->description = Input::get('description');
				$class->save();
				return Redirect::to('/level/create')->with("success", "Level Created Succesfully.");
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
		$levels = DB::table('level')
		->select(DB::raw('level.id,level.name,level.description'))
		->get();
		//dd($sections);
		//return View::Make('app.classList',compact('Classes'));
		return View('app.levelList',compact('levels'));
	}



	/**
	* Show the form for editing the specified resource.
	*
	* @param  int  $id
	* @return Response
	*/
	public function edit($id)
	{
		$level = Level::find($id);
		//return View::Make('app.classEdit',compact('class'));
		return View('app.levelEdit',compact('level'));
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
			'description' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/level/edit/'.Input::get('id'))->withErrors($validator);
		}
		else {
			$section = Level::find(Input::get('id'));
			$section->name= Input::get('name');

			$section->description=Input::get('description');
			$section->save();
			return Redirect::to('/level/list')->with("success","Level Updated Succesfully.");

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
		$class = Level::find($id);
		$class->delete();
		return Redirect::to('/level/list')->with("success","Level Deleted Succesfully.");
	}

}

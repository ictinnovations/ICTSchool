<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\AcadamicYear;
use DB;
class AcadamicYearController extends Controller
{
    public function __construct() {
		
	    $this->middleware('auth');

	}
	/**
	* Display a listing of the resource.
	*
	* @return Response
	*/
	public function index()
	{
		$years=AcadamicYear::all();
		
		//return View::Make('app.GPA',compact('gpaes','gpa'));
		return View('app.acadamicyear.index',compact('years'));
	}

	public function add()
	{
		return View('app.acadamicyear.create');
	}

	/**
	* Show the form for creating a new resource.
	*
	* @return Response
	*/
	public function create()
	{
		$rules=[
			'title' => 'required',
			//'default' => 'required',
			

		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/academicYear/create')->withErrors($validator);
		}
		else {
			$year = new AcadamicYear;
			$year->title= Input::get('title');
			if(Input::get('default')==''){
				$status = 0;
			}
			else{
				$status = 1;
				DB::table('acadamic_year')
            		//->where('id','<>', Input::get('id'))
            		->update(['status' => "0"]);
			}
			$year->status= $status;
			$year->save();
			return Redirect::to('/academicYear/create')->with("success","Acadamic Year Created Succesfully.");

		}
	}

	/**
	* Show the form for editing the specified resource.
	*
	* @param  int  $id
	* @return Response
	*/
	public function edit($id)
	{

		$year = AcadamicYear::find($id);
		//return View::Make('app.GPA',compact('gpaes','gpa'));
		return View('app.acadamicyear.edit',compact('year'));

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
			'title' => 'required',
			//'default' => 'required',
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/academicYear/edit/'.Input::get('id'))->withErrors($validator);
		}
		else {
			$year = AcadamicYear::find(Input::get('id'));
			$year->title= Input::get('title');
			if(Input::get('default')==''){
				$status = 0;
			}
			else{
				$status = 1;
				/*DB::table('post')
            		->where('id','<>', Input::get('id'))
            		->update(['status' => "0"]);*/

			}
			//$year->status= $status;
			$year->save();
			return Redirect::to('/academicYear')->with("success","Acadamic Year updated Succesfully.");
		}
	}

	public function status($id)
	{
		DB::table('acadamic_year')
            ->where('id','<>', Input::get('id'))
            ->update(['status' => "0"]);
	        $year = AcadamicYear::find($id);
	        $year->status= 1;
	        $year->save();
        return Redirect::to('/academicYear')->with("success","Status Active Succesfully.");
	}



}

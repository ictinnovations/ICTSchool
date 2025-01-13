<?php

namespace App\Http\Controllers;

use App\Models\AcadamicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class AcadamicYearController extends Controller
{
	public function __construct()
	{

		$this->middleware('auth');
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$years = AcadamicYear::all();

		//return View::Make('app.GPA',compact('gpaes','gpa'));
		return View('app.acadamicyear.index', compact('years'));
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
	public function create(Request $request)
	{
		$rules = [
			'title' => 'required',
			//'default' => 'required',
		];
		$validator = \Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			return Redirect::to('/academicYear/create')->withErrors($validator);
		} else {
			$year = new AcadamicYear;
			$year->title = $request->input('title');
			if ($request->all('default') == '') {
				$status = 0;
			} else {
				$status = 1;
				DB::table('acadamic_year')
					//->where('id','<>', $request->all('id'))
					->update(['status' => "0"]);
			}
			$year->status = $status;
			$year->save();
			return Redirect::to('/academicYear/create')->with("success", "Acadamic Year Created Succesfully.");
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
		return View('app.acadamicyear.edit', compact('year'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request)
	{
		$rules = [
			'title' => 'required',
			//'default' => 'required',
		];
		$validator = \Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			return Redirect::to('/academicYear/edit/' . $request->all('id'))->withErrors($validator);
		} else {
			$year = AcadamicYear::find($request->input('id'));
			$year->title = $request->input('title');
			if ($request->all('default') == '') {
				$status = 0;
			} else {
				$status = 1;
				/*DB::table('post')
            		->where('id','<>', $request->all('id'))
            		->update(['status' => "0"]);*/
			}
			//$year->status= $status;
			$year->save();
			return Redirect::to('/academicYear')->with("success", "Acadamic Year updated Succesfully.");
		}
	}

	public function status(Request $request, $id)
	{
		DB::table('acadamic_year')
			->where('id', '<>', $request->all('id'))
			->update(['status' => "0"]);
		$year = AcadamicYear::find($id);
		$year->status = 1;
		$year->save();
		return Redirect::to('/academicYear')->with("success", "Status Active Succesfully.");
	}
}

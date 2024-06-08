<?php

namespace App\Http\Controllers;

use App\Models\GPA;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class gpaController extends BaseController
{

	public function __construct()
	{
		/*$this->beforeFilter('csrf', array('on'=>'post'));
		$this->beforeFilter('auth');
		$this->beforeFilter('userAccess',array('only'=> array('index','edit','create','update','delete')));*/
		$this->middleware('auth');
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$gpaes = GPA::all();
		$gpa = array();
		//return View::Make('app.GPA',compact('gpaes','gpa'));
		return View('app.GPA', compact('gpaes', 'gpa'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create(Request $request)
	{
		$rules = [
			'for' => 'required',
			'gpa' => 'required',
			'grade' => 'required|numeric',
			'markfrom' => 'required|integer',
			'markto' => 'required|integer',

		];
		$validator = \Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			return Redirect::to('/gpa')->withErrors($validator);
		} else {
			$gpa = new GPA;
			$gpa->for = $request->input('for');
			$gpa->gpa = $request->input('gpa');
			$gpa->grade = $request->input('grade');
			$gpa->markfrom = $request->input('markfrom');
			$gpa->markto = $request->input('markto');
			$gpa->save();
			return Redirect::to('/gpa')->with("success", "GPA Created Succesfully.");
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

		$gpa = GPA::find($id);
		$gpaes = GPA::all();
		//return View::Make('app.GPA',compact('gpaes','gpa'));
		return View('app.GPA', compact('gpaes', 'gpa'));
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
			'for' => 'required',
			'gpa' => 'required',
			'grade' => 'required|between:0,99.99',
			'markfrom' => 'required|integer',
			'markto' => 'required|integer',

		];
		$validator = \Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			return Redirect::to('/gpa/edit/' . $request->input('id'))->withErrors($validator);
		} else {
			$gpa = GPA::find($request->input('id'));
			$gpa->for = $request->input('for');
			$gpa->gpa = $request->input('gpa');
			$gpa->grade = $request->input('grade');
			$gpa->markfrom = $request->input('markfrom');
			$gpa->markto = $request->input('markto');
			$gpa->save();
			return Redirect::to('/gpa')->with("success", "GPA update Succesfully.");
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
		$gpa = GPA::find($id);
		$gpa->delete();
		return Redirect::to('/gpa')->with("success", "GPA deleted Succesfully.");
	}
}

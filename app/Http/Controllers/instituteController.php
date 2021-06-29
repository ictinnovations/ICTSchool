<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use ValidatesRequests;
use App\Institute;
use App\Branch;
use DB;
use Storage;
class instituteController extends BaseController {

	public function __construct() {
		/*$this->beforeFilter('csrf', array('on'=>'post'));
		$this->beforeFilter('auth');
		$this->beforeFilter('userAccess',array('only'=> array('index','save')));*/
		    $this->middleware('auth', array('only'=>array('show','create','edit','update')));
	}
	/**
	* Display a listing of the resource.
	*
	* @return Response
	*/
	public function index()
	{
		$institute= Institute::select("*")->first();
		if(is_null($institute))
		{
			$institute=new Institute;
			$institute->name = "";
			$institute->establish = "";
			$institute->web = "";
			$institute->email = "";
			$institute->phoneNo = "";
			$institute->address = "";
		}
		if(Storage::disk('local')->exists('/public/grad_system.txt')){
          $contant = Storage::get('/public/grad_system.txt');
          $data = explode('<br>',$contant );

			//echo "<pre>";print_r($data);
			$gradsystem = $data[0]; 
		}else{
	      $gradsystem ='';
		}

		if(Storage::disk('local')->exists('/public/family.txt')){
          $contant_family = Storage::get('/public/family.txt');
          $data_family = explode('<br>',$contant_family );

			//echo "<pre>";print_r($data);
			$family = $data_family[0]; 
		}else{
	      $family ='';
		}

		if(Storage::disk('local')->exists('/public/accounting.txt')){
          $ac = Storage::get('/public/accounting.txt');
          $ac_data = explode('<br>',$ac );

			//echo "<pre>";print_r($data);
			$accounting = $ac_data[0]; 
		}else{
	      $accounting ='';
		}
        //print_r($data);exit;
		//return View::Make('app.institute',compact('institute'));
		return View('app.institute',compact('institute','gradsystem','family','accounting'));
	}
	/**
	* Display a listing of the resource.
	*
	* @return Response
	*/
	public function branches()
	{
		$branches= Branch::select("*")->get();
		$countb= Branch::count();
		/*if(empty($institute))
		{
			$branches=new Branch;
			$branches->branchname = "";
			$branches->url = "";
			$branches->username = "";
			$branches->password = "";
		}*/
       // print_r($branches);exit;
		//return View::Make('app.institute',compact('institute'));
		return View('app.branches',compact('branches','countb'));
	}
	public function createbranch(Request $request)
	{
		$rules=[
			'branchname.*' => 'required',
			'url.*' => 'required',
			'username.*' => 'required',
			'password.*' => 'required',
		];
		//echo "<pre>";print_r(Input::file('logo'));exit;
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('branches')->withinput(Input::all())->withErrors($validator);
		}
		else {
				Branch::truncate();
            for($i=0;$i<count($request->branchname);$i++){
            	$branch = new Branch;
            	$branch->branch_name =$request->branchname[$i];
            	$branch->branch_url =$request->url[$i];
            	$branch->username =$request->username[$i];
            	$branch->password =$request->password[$i];
            	$branch->save();
            }
            return Redirect::to('/branches')->with("success","Branches Created Succesfully.");
		}
	}


	public function index1()
	{
       if(Storage::disk('local')->exists('/public/grad_system.txt')){
          $contant = Storage::get('/public/grad_system.txt');
          $data = explode('<br>',$contant );

			//echo "<pre>";print_r($data);
			$gradsystem = $data[0]; 
		}else{
	      $gradsystem ='';
		}
		return $gradsystem;
	}

	/**
	* Show the form for creating a new resource.
	*
	* @return Response
	*/
	public function save()
	{
		$rules=[
			'name' => 'required',
			'establish' => 'required',
			'web' => 'required',
			'email' => 'required',
			'phoneNo' => 'required',
			'address' => 'required',
			'log' => 'mimes:png',


		];
		//echo "<pre>";print_r(Input::file('logo'));exit;
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('institute')->withinput(Input::all())->withErrors($validator);
		}
		else {
            
            if(Input::get('grade_system')=='' ):
              $gs = 'manual';
            else:
              $gs = 'auto';
            endif;
                Storage::put('/public/grad_system.txt', $gs);
               //echo Input::get('family');exit;
            if(Input::get('family')=='' ):
              $fm = 'off';
            else:
              $fm = 'on';
            endif;
            Storage::put('/public/family.txt', $fm);

             if(Input::get('accounting')=='' ):
              $accounting = 'yes';
            else:
              $accounting = 'no';
            endif;
            
            Storage::put('/public/accounting.txt', $accounting);
			DB::table("institute")->delete();
			$institue=new Institute;
			$institue->name = Input::get('name');
			$institue->establish = Input::get('establish');
			$institue->web = Input::get('web');
			$institue->email = Input::get('email');
			$institue->phoneNo = Input::get('phoneNo');
			$institue->address = Input::get('address');
			$institue->save();

			if(Input::file('logo')!=''){

				$fileName='logo.'.Input::file('logo')->getClientOriginalExtension();
			    //echo base_path() .'/img/',$fileName;exit;
			    $check = Input::file('logo')->move(base_path() .'/img/',$fileName);
			      //print_r($check );
			      //echo base_path() .'/img/',$fileName;exit;
			}else{
				$fileName='';
		}

			return Redirect::to('institute')->with('success', 'Institute  Information saved.');

		}
	}
}

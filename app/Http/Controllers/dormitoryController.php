<?php
namespace App\Http\Controllers;
use DB;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Dormitory;
use App\Models\Institute;
use App\Models\ClassModel;
use App\Models\DormitoryFee;
use Illuminate\Http\Request;
use App\Models\DormitoryStudent;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Database\Eloquent\Collection;

class dormitoryController extends BaseController {

	public function __construct() {
		/*$this->beforeFilter('csrf', array('on'=>'post'));
		$this->beforeFilter('auth');
		$this->beforeFilter('userAccess',array('only'=> array('delete','stddelete')));*/
		 $this->middleware('auth', array('only'=>array('index')));
	}
	/**
	* Display a listing of the resource.
	*
	* @return Response
	*/
	public function index()
	{
		$dormitories=Dormitory::all();
		$dormitory=array();
		//return View::Make('app.dormitory',compact('dormitories','dormitory'));
		return View('app.dormitory',compact('dormitories','dormitory'));
	}


	/**
	* Show the form for creating a new resource.
	*
	* @return Response
	*/
	public function create(Request $request)
	{
		$rules=[
			'name' => 'required',
			'numOfRoom' => 'required|integer',
			'address' => 'required',

		];
		$validator = \Validator::make($request->all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/dormitory')->withErrors($validator);
		}
		else {
			$dormitory = new Dormitory;
			$dormitory->name= $request->input('name');
			$dormitory->numOfRoom=$request->input('numOfRoom');
			$dormitory->address=$request->input('address');
			$dormitory->description=$request->input('description');
			$dormitory->save();
			return Redirect::to('/dormitory')->with("success","Dormitory Created Succesfully.");

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
		$dormitory = Dormitory::find($id);
		$dormitories=Dormitory::all();
		//return View::Make('app.dormitory',compact('dormitories','dormitory'));
		return View('app.dormitory',compact('dormitories','dormitory'));
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
			'numOfRoom' => 'required|integer',
			'address' => 'required',

		];


		$validator = \Validator::make($request->all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/dormitory/edit/'.$request->input('id'))->withErrors($validator);
		}
		else {
			$dormitory = Dormitory::find($request->input('id'));
			$dormitory->name= $request->input('name');
			$dormitory->numOfRoom=$request->input('numOfRoom');
			$dormitory->address=$request->input('address');
			$dormitory->description=$request->input('description');
			$dormitory->save();
			return Redirect::to('/dormitory')->with("success","Dormitory update Succesfully.");

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
		$dormitory = Dormitory::find($id);
		$dormitory->delete();
		return Redirect::to('/dormitory')->with("success","Dormitory deleted Succesfully.");
	}


	//student assign to dormitory part goes Here
	public function stdindex()
	{
		$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
		$dormitories = Dormitory::select('id','name')->orderby('id','asc')->get();
		//return View::Make('app.dormitory_stdadd',compact('classes','dormitories'));
		return View('app.dormitory_stdadd',compact('classes','dormitories'));
	}


	public function stdcreate(Request $request)
	{
		$rules=[
			'regiNo' => 'required',
			'joinDate' => 'required',
			'isActive' => 'required',
			'dormitory' => 'required',
			'roomNo' => 'required',
			'monthlyFee' => 'required|numeric',


		];
		$validator = \Validator::make($request->all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/dormitory/assignstd')->withErrors($validator);
		}
		else {
			$dormStd = new DormitoryStudent;
			$dormStd->regiNo=$request->input('regiNo');
			$dormStd->joinDate=$request->input('joinDate');
			$dormStd->dormitory=$request->input('dormitory');
			$dormStd->roomNo=$request->input('roomNo');
			$dormStd->monthlyFee=$request->input('monthlyFee');
			$dormStd->isActive=$request->input('isActive');
			$dormStd->save();
			return Redirect::to('/dormitory/assignstd')->with("success","Student added to dormitory Succesfully.");

		}
	}

	public function stdShow()
	{

		$dormitories = Dormitory::pluck('name','id');
		$students=array();
		$formdata = new formfoo();
		$formdata->dormitory=1;
		//return View::Make('app.dormitory_stdlist',compact('students','dormitories','formdata'));
		return View('app.dormitory_stdlist',compact('students','dormitories','formdata'));
	}
	public function poststdShow(Request $request)
	{
		$rules = ['dormitory' => 'required',];
		$validator = \Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			return Redirect::to('/dormitory/assignstd/list')->withInput($request->all())->withErrors($validator);
		}
		else {
			$students = DB::table('Student')
			->join('Class', 'Student.class', '=', 'Class.code')
			->join('dormitory_student', 'Student.regiNo', '=', 'dormitory_student.regiNo')
			->select('dormitory_student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
			'Class.Name as class','dormitory_student.roomNo', 'dormitory_student.monthlyFee','dormitory_student.joinDate','dormitory_student.leaveDate','dormitory_student.isActive')
			->where('dormitory_student.dormitory',$request->input('dormitory'))
			->get();
			$dormitories = Dormitory::pluck('name','id');
			$formdata = new formfoo();
			$formdata->dormitory=$request->input('dormitory');
			//return View::Make('app.dormitory_stdlist',compact('students','dormitories','formdata'));
			return View('app.dormitory_stdlist',compact('students','dormitories','formdata'));
		}
	}
	public function stdedit($id)
	{
		$student = DormitoryStudent::find($id);
		$dormitories=Dormitory::pluck('name','id');
		//return View::Make('app.dormitory_stdedit',compact('dormitories','student'));
		return View('app.dormitory_stdedit',compact('dormitories','student'));
	}


	/**
	* Update the specified resource in storage.
	*
	* @param  int  $id
	* @return Response
	*/
	public function stdupdate(Request $request)
	{

		$rules=[
			'isActive' => 'required',
			'dormitory' => 'required',
			'roomNo' => 'required',
			'monthlyFee' => 'required|numeric',

		];


		$validator = \Validator::make($request->all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/dormitory/assignstd/edit/'.$request->input('id'))->withErrors($validator);
		}
		else {
			$dormStd = DormitoryStudent::find($request->input('id'));
			if($request->input('leaveDate')!=""){
				$dormStd->leaveDate=$request->input('leaveDate');
			}

			$dormStd->dormitory=$request->input('dormitory');
			$dormStd->roomNo=$request->input('roomNo');
			$dormStd->monthlyFee=$request->input('monthlyFee');
			$dormStd->isActive=$request->input('isActive');
			$dormStd->save();
			return Redirect::to('/dormitory/assignstd/list')->with("success","Dormitory student info update Succesfully.");

		}
	}


	/**
	* Remove the specified resource from storage.
	*
	* @param  int  $id
	* @return Response
	*/
	public function stddelete($id)
	{
		$dormstd = DormitoryStudent::find($id);
		$dormstd->delete();
		return Redirect::to('/dormitory/assignstd/list')->with("success","Dormitory student deleted Succesfully.");
	}

	public function getstudents($dormid)
	{
		$students = DB::table('Student')
		->join('dormitory_student', 'Student.regiNo', '=', 'dormitory_student.regiNo')
		->select('Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName')
		->where('dormitory_student.dormitory',$dormid)
		->where('dormitory_student.isActive',"Yes")
		->orderby('dormitory_student.regiNo','asc')->get();
		return $students;
	}
	public function feeinfo($regiNo)
	{
		$fee = DormitoryStudent::select('monthlyFee')
		->where('regiNo',$regiNo)
		->get();

		$isPaid= DB::table('dormitory_fee')
		->select('regiNo','feeAmount')
		->where('regiNo',$regiNo)
		->whereRaw('EXTRACT(YEAR_MONTH FROM feeMonth) = EXTRACT(YEAR_MONTH FROM NOW())')
		->get();

		$info=array($fee[0]->monthlyFee);
		if(count($isPaid)>0)
		{
			array_push($info,"true");
		}
		else {
			array_push($info,"false");
		}
		return $info;
	}

	public function feeindex()
	{
		$dormitories=Dormitory::select('name','id')->orderby('id','asc')->get();
		//return View::Make('app.dormitory_fee',compact('dormitories'));
		return View('app.dormitory_fee',compact('dormitories'));
	}
	public function feeadd(Request $request)
	{
		$rules=[
			'regiNo' => 'required',
			'feeMonth' => 'required',
			'feeAmount' => 'required',

		];
		$validator = \Validator::make($request->all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/dormitory/fee')->withErrors($validator);
		}
		else {
			$dormFee = new DormitoryFee;
			$dormFee->regiNo=$request->input('regiNo');
			$dormFee->feeMonth=$request->input('feeMonth');
			$dormFee->feeAmount=$request->input('feeAmount');
			$dormFee->save();
			return Redirect::to('/dormitory/fee')->with("success","Fee added Succesfully.");

		}
	}

	public function reportstd()
	{
		$dormitories=Dormitory::select('name','id')->orderby('id','asc')->get();
		//return View::Make('app.dormitory_rptstd',compact('dormitories'));
		return View('app.dormitory_rptstd',compact('dormitories'));
	}
	public function reportstdprint($dormId)
	{
		$datas = DB::table('Student')
		->join('Class', 'Student.class', '=', 'Class.code')
		->join('dormitory_student', 'Student.regiNo', '=', 'dormitory_student.regiNo')
		->select('dormitory_student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
		'Class.Name as class','dormitory_student.roomNo','Student.section','Student.session' )
		->where('dormitory_student.dormitory',$dormId)
		->where('dormitory_student.isActive',"Yes")
		->get();
		$dormInfo = Dormitory::find($dormId);
		$institute=Institute::select('*')->first();
		$rdata =array('date'=>date('d/m/Y'),'name'=>$dormInfo->name,'totalr'=>$dormInfo->numOfRoom,'totals'=>count($datas));
		$pdf = PDF::loadView('app.dormitory_rptstdprint',compact('datas','rdata','institute'));
		return $pdf->stream('dormitory-students-List.pdf');
	}
	public function reportfee()
	{
		$dormitories=Dormitory::select('name','id')->orderby('id','asc')->get();
		//return View::Make('app.dormitory_rptfee',compact('dormitories'));
		return View('app.dormitory_rptfee',compact('dormitories'));
	}
	public function reportfeeprint($dormId,$month)
	{

		$myquery="SELECT a.regiNo,a.roomNo,CONCAT(b.firstName,' ',b.middleName,' ',b.lastName) as name,c.name as class,'Paid' as isPaid FROM dormitory_student a
		JOIN Student b ON a.regiNo=b.regiNo
		JOIN Class c ON c.code=b.class
		where a.dormitory=".$dormId."
		and EXISTS (select b.feeMonth from dormitory_fee b where b.regiNo=a.regiNo and EXTRACT(YEAR_MONTH FROM b.feeMonth) = EXTRACT(YEAR_MONTH FROM '".$month."'))

		UNION SELECT a.regiNo,a.roomNo,CONCAT(b.firstName,' ',b.middleName,' ',b.lastName) as name,c.name as class,'Due' as isPaid FROM dormitory_student a
		JOIN Student b ON a.regiNo=b.regiNo
		JOIN Class c ON c.code=b.class
		WHERE a.dormitory=".$dormId."
		and NOT EXISTS (select b.feeMonth from dormitory_fee b where b.regiNo=a.regiNo and EXTRACT(YEAR_MONTH FROM b.feeMonth) = EXTRACT(YEAR_MONTH FROM '".$month."'))
		ORDER BY regiNo";

		$datas = DB::select(DB::raw($myquery));



		$dormInfo = Dormitory::find($dormId);
		$institute=Institute::select('*')->first();

		$rdata =array('month'=>date('F-Y', strtotime($month)),'name'=>$dormInfo->name,'total'=>count($datas));
		$pdf = PDF::loadView('app.dormitory_rptfeeprint',compact('datas','rdata','institute'));
		return $pdf->stream('dormitory-free-report.pdf');
	}
}

<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Subject;
use App\ClassModel;
use App\Student;
use App\Attendance;
use App\Accounting;
use App\Marks;
use App\AddBook;
use App\FeeCol;
use App\FeeSetup;
use App\Institute;
use App\FeeHistory;
use DB;
use App\Ictcore_fees;
use App\Ictcore_integration;
use App\Http\Controllers\ictcoreController;
use Carbon\Carbon;
class studentfdata{


}
class feesController extends BaseController {

	public function __construct()
	{
		/*$this->beforeFilter('csrf', array('on'=>'post'));
		$this->beforeFilter('auth');
		$this->beforeFilter('userAccess',array('only'=> array('getDelete','stdfeesdelete')));*/
		$this->middleware('auth');
		 $this->middleware('auth', array('only'=>array('index')));
	}
	public function getsetup()
	{

		$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
		//return View::Make('app.feesSetup',compact('classes'));
		return View('app.feesSetup',compact('classes'));
	}

	/**
	* Store a newly created resource in storage.
	*
	* @return Response
	*/
	public function postSetup()
	{
		$rules=[

			'class' => 'required',
			'type' => 'required',
			'fee' => 'required|numeric',
			'Latefee' => 'required|numeric',
			'title' => 'required'

		];
		$validator = \Validator::make(Input::all(), $rules);

		if ($validator->fails())
		{
			return Redirect::to('/fees/setup')->withErrors($validator);
		}
		else {

			$fee = new FeeSetup();


			$fee->class = Input::get('class');
			$fee->type = Input::get('type');
			$fee->title = Input::get('title');
			$fee->fee = Input::get('fee');
			$fee->Latefee = Input::get('Latefee');
			$fee->description = Input::get('description');
			if(Input::get('description')==''){
				$fee->description ='';
			}
			$fee->save();
			return Redirect::to('/fees/setup')->with("success","Fee Save Succesfully.");


		}
	}




	public function getList()
	{
		$fees=array();
		$classes = ClassModel::pluck('name','code');

		$formdata = new formfoo;
		$formdata->class="";
		//return View::Make('app.feeList',compact('classes','formdata','fees'));
		return View('app.feeList',compact('classes','formdata','fees'));
	}
	/**
	* Display the specified resource.
	*
	* @param  int  $id
	* @return Response
	*/
	public function postList()
	{
		$rules=[

			'class' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);

		if ($validator->fails())
		{
			return Redirect::to('/fees/list')->withErrors($validator);
		}
		else {

			$fees = FeeSetup::select("*")->where('class',Input::get('class'))->get();
			$classes = ClassModel::pluck('name','code');
			$formdata = new formfoo;
			$formdata->class=Input::get('class');
			//return View::Make('app.feeList',compact('classes','formdata','fees'));
			return View('app.feeList',compact('classes','formdata','fees'));



		}
	}


	/**
	* Show the form for editing the specified resource.
	*
	* @param  int  $id
	* @return Response
	*/
	public function getEdit($id)
	{
		$classes = ClassModel::pluck('name','code');
		$fee = FeeSetup::find($id);
		//return View::Make('app.feeEdit',compact('fee','classes'));
		return View('app.feeEdit',compact('fee','classes'));

	}


	/**
	* Update the specified resource in storage.
	*
	* @param  int  $id
	* @return Response
	*/
	public function postEdit()
	{
		$rules=[

			'class' => 'required',
			'type' => 'required',
			'fee' => 'required|numeric',
			'title' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);

		if ($validator->fails())
		{
			return Redirect::to('/fee/edit/'.Input::get('id'))->withErrors($validator);
		}
		else {

			$fee              = FeeSetup::find(Input::get('id'));
			$fee->class       = Input::get('class');
			$fee->type        = Input::get('type');
			$fee->title       = Input::get('title');
			$fee->fee         = Input::get('fee');
			$fee->Latefee     = Input::get('Latefee');
			$fee->description = Input::get('description');
			$fee->save();
			return Redirect::to('/fees/list')->with("success","Fee Updated Succesfully.");


		}
	}


	/**
	* Remove the specified resource from storage.
	*
	* @param  int  $id
	* @return Response
	*/
	public function getDelete($id)
	{
		$fee = FeeSetup::find($id);
		$fee->delete();
		return Redirect::to('/fees/list')->with("success","Fee Deleted Succesfully.");
	}
	public function getvouchar()
	{
		$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
		//return View::Make('app.feeCollection',compact('classes'));
		return View('app.feeVouchar',compact('classes'));
	}
	public function postvouchar()
	{
		$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
		//return View::Make('app.feeCollection',compact('classes'));
		return View('app.feeCollection',compact('classes'));
	}
    
    public function detail()
    {
     $fee_list =DB::table('stdBill')
     ->join('billHistory','stdBill.billNo','=','billHistory.billNo')
     ->select('stdBill.billNo','billHistory.month','billHistory.fee','billHistory.lateFee','billHistory.total')
     ->where('stdBill.regiNo',Input::get('regiNo'))
     ->whereYear('stdBill.created_at', date('Y'))
     ->orderBy('billHistory.month','ASC')
     ->get();
     $month = array('1','2','3','4','5','6','7','8','9','10','11','12');
     echo "<pre>";print_r($fee_list);
    foreach($month as $mnth){
      $fee_list =DB::table('stdBill')
     ->join('billHistory','stdBill.billNo','=','billHistory.billNo')
     ->select('stdBill.billNo','billHistory.month','billHistory.fee','billHistory.lateFee','billHistory.total')
     ->where('stdBill.regiNo',Input::get('regiNo'))
     ->whereYear('stdBill.created_at', date('Y'))
     ->where('billHistory.month',$mnth)
    // ->orderBy('billHistory.month','ASC');
     if($fee_list->count()>0){
        
     }else{

     }
     

    }
    	
    
     exit;
     return View('app.feedetail',compact('students'));
    }

	public function getCollection()
	{
		$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
		if(Input::get('section')!=''){
		$sections = DB::table('section')->select('*')->where('class_code',Input::get('class_id'))->get();
		}else{
			$sections = '';
		}
		if(Input::get('fee_name')!=''){
         $fees= FeeSetup::select('id','title')->where('id','=',Input::get('fee_name'))->get();
		}else{
			$fees=array();
		}
		if(Input::get('regiNo')!=''){
		  $student= Student::select('regiNo','rollNo','firstName','middleName','lastName','discount_id')->where('isActive','=','Yes')->where('regiNo','=',Input::get('regiNo'))->first();
	      //return $students;
		}
		else{
			 $student=array();
		}
		//echo "<pre>";print_r($fees->toArray());exit;
		//return View::Make('app.feeCollection',compact('classes'));
		return View('app.feeCollection',compact('classes','sections','fees','student'));
	}
	public function postCollection()
	{

		$rules=[

			'class'      => 'required',
			'student'    => 'required',
			//'date'     => 'required',
			'paidamount' => 'required',
			'dueamount'  => 'required',
			'ctotal'     => 'required'

		];
		//echo "<pre>";print_r(Input::all());
		//exit;
		$validator = \Validator::make(Input::all(), $rules);

		if ($validator->fails())
		{
			return Redirect::to('/fee/collection?class_id='.Input::get('class').'&section='.Input::get('section').'&session='.Input::get('session').'&type='.Input::get('type').'&month='.Input::get('gridMonth')[0].'&fee_name='.Input::get('fee'))->withInput(Input::all())->withErrors($validator);
		}
		else {

			try {

				/*$chk = DB::table('stdBill')
				->join('billHistory','stdBill.billNo','=','billHistory.billNo')
				->where('stdBill.regiNo',Input::get('student'))
				->where('billHistory.month',);
				*/
				$feeTitles       = Input::get('gridFeeTitle');
				$feeAmounts      = Input::get('gridFeeAmount');
				$feeLateAmounts  = Input::get('gridLateFeeAmount');
				$feeTotalAmounts = Input::get('gridTotal');
				$feeMonths       = Input::get('gridMonth');
				$month = $feeMonths[0]; 
				$counter         = count($feeTitles);

				if($counter>0)
				{
					$rows = FeeCol::count();
					if($rows < 9)
					{
						$billId = 'B00'.($rows+1);
					}
					else if($rows < 100)
					{
						$billId = 'B0'.($rows+1);
					}
					else {
						
						$billId = 'B'.($rows+1);
					}

					DB::transaction(function() use ($billId,$counter,$feeTitles,$feeAmounts,$feeLateAmounts,$feeTotalAmounts,$feeMonths)
					{
                        $j=0;
						for ($i=0;$i<$counter;$i++) {
                               
                               $chk = DB::table('stdBill')
								->join('billHistory','stdBill.billNo','=','billHistory.billNo')
								->where('stdBill.regiNo',Input::get('student'))
								->where('billHistory.month',$feeMonths[$i]);
                              

                        if(  $chk->count()==0){
							$feehistory          = new FeeHistory();
							$feehistory->billNo  = $billId;
							$feehistory->title   = $feeTitles[$i];
							$feehistory->fee     = $feeAmounts[$i];
							$feehistory->lateFee = $feeLateAmounts[$i];
							$feehistory->total   = $feeTotalAmounts[$i];
							$feehistory->month   = $feeMonths[$i];
							$feehistory->save();
						$j++;
						}
                         
						}
						if($j>0){
						$feeCol                = new FeeCol();
						$feeCol->billNo        = $billId;
						$feeCol->class         = Input::get('class');
						$feeCol->regiNo        = Input::get('student');
						$feeCol->payableAmount = Input::get('ctotal');
						$feeCol->paidAmount    = Input::get('paidamount');
						$feeCol->dueAmount     = Input::get('dueamount');
						$feeCol->payDate       = Carbon::now()->format('Y-m-d');
						//echo "<pre>";print_r(Carbon::now()->format('Y-m-d'));exit;
						$feeCol->save();
						\Session::put('not_save', $j);
					}else{
						\Session::put('not_save', 0);
						

					}
                         	
						/*for ($i=0;$i<$counter;$i++) {

							$feehistory          = new FeeHistory();
							$feehistory->billNo  = $billId;
							$feehistory->title   = $feeTitles[$i];
							$feehistory->fee     = $feeAmounts[$i];
							$feehistory->lateFee = $feeLateAmounts[$i];
							$feehistory->total   = $feeTotalAmounts[$i];
							$feehistory->month   = $feeMonths[$i];
							$feehistory->save();

						}*/
					});
                  if(\Session::get('not_save')!=0){
					\Session::forget('not_save');
					return Redirect::to('/fee/collection?class_id='.Input::get('class').'&section='.Input::get('section').'&session='.Input::get('session').'&type='.Input::get('type').'&month='.$month.'&fee_name='.Input::get('fee'))->with("success","Fee collection succesfull.");
				   }else{
				   	\Session::forget('not_save');
				   	$messages = "Student already add fee for this month"; 
				       
				        return Redirect::to('/fee/collection?class_id='.Input::get('class').'&section='.Input::get('section').'&session='.Input::get('session').'&type='.Input::get('type').'&month='.$month.'&fee_name='.Input::get('fee'))->withErrors($messages);
				   }
				}
				else {
					$messages = $validator->errors();
					$messages->add('Validator!', 'Please add atlest one fee!!!');
					
					return Redirect::to('/fee/collection?class_id='.Input::get('class').'&section='.Input::get('section').'&session='.Input::get('session').'&type='.Input::get('type').'&month='.$month.'&fee_name='.Input::get('fee'))->withInput(Input::all())->withErrors($messages);

				}
			}
			catch(\Exception $e)
			{
               //echo $e->getMessage();
				return Redirect::to('/fee/collection?class_id='.Input::get('class').'&section='.Input::get('section').'&session='.Input::get('session').'&type='.Input::get('type').'&month='.Input::get('gridMonth')[0].'&fee_name='.Input::get('fee'))->withErrors( $e->getMessage())->withInput();
			}

		}
	}

	public function getListjson($class,$type)
	{
		$fees= FeeSetup::select('id','title')->where('class','=',$class)->where('type','=',$type)->get();
		return $fees;
	}
	public function getFeeInfo($id)
	{
		$fee= FeeSetup::select('fee','Latefee')->where('id','=',$id)->get();
		return $fee;
	}

	public function getDue($class,$stdId)
	{
		$due = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0) as dueamount'))
		->where('class',$class)
		->where('regiNo',$stdId)
		->first();
		return $due->dueamount;

	}
	public function stdfeeview()
	{
		$classes = ClassModel::pluck('name','code');
		$student = new studentfdata;
		$student->class="";
		$student->section="";
		$student->shift="";
		$student->session="";
		$student->regiNo="";
		$fees=array();
		//return View::Make('app.feeviewstd',compact('classes','student','fees'));
		return View('app.feeviewstd',compact('classes','student','fees'));
	}
	public function stdfeeviewpost()
	{
		$classes = ClassModel::pluck('name','code');
		$student = new studentfdata;
		$student->class=Input::get('class');
		$student->section=Input::get('section');
		$student->shift=Input::get('shift');
		$student->session=Input::get('session');
		$student->regiNo=Input::get('student');
		$fees=DB::Table('stdBill')
		->select(DB::RAW("billNo,payableAmount,paidAmount,dueAmount,DATE_FORMAT(payDate,'%D %M,%Y') AS date"))
		->where('class',Input::get('class'))
		->where('regiNo',Input::get('student'))
		->get();
		 $totals = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
		->where('class',Input::get('class'))
		->where('regiNo',Input::get('student'))
		->first();

		//return View::Make('app.feeviewstd',compact('classes','student','fees','totals'));
		return View('app.feeviewstd',compact('classes','student','fees','totals'));
	}
	public function stdfeesdelete($billNo)
	{
		try {
			DB::transaction(function() use ($billNo)
			{
				FeeCol::where('billNo',$billNo)->delete();
				FeeHistory::where('billNo',$billNo)->delete();

			});
			return Redirect::to('/fees/view')->with("success","Fees deleted succesfull.");
		}
		catch(\Exception $e)
		{

			return Redirect::to('/fees/view')->withErrors( $e->getMessage())->withInput();
		}

	}
	public function reportstd($regiNo)
	{

		$datas=DB::Table('stdBill')
		->select(DB::RAW("payableAmount,paidAmount,dueAmount,DATE_FORMAT(payDate,'%D %M,%Y') AS date"))
		->where('regiNo',$regiNo)
		->get();
		$totals = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
		->where('regiNo',$regiNo)
		->first();
		$stdinfo=DB::table('Student')
		->join('Class', 'Student.class', '=', 'Class.code')
		->select('Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName',
		'Student.section','Student.shift','Student.session','Class.Name as class')
		->where('isActive','Yes')
		->where('Student.regiNo',$regiNo)
		->first();
		$institute=Institute::select('*')->first();
		$rdata =array('payTotal'=>$totals->payTotal,'paiTotal'=>$totals->paiTotal,'dueAmount'=>$totals->dueamount);
		$pdf = \PDF::loadView('app.feestdreportprint',compact('datas','rdata','stdinfo','institute'));
		return $pdf->stream('student-Payments.pdf');

	}
	public function report()
	{
		//return View::Make('app.feesreport');
		return View('app.feesreport');
	}
	public function reportprint($sDate,$eDate)
	{
		$datas= FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
		->whereDate('created_at', '>=', date($sDate))
		->whereDate('created_at', '<=', date($eDate))
		->first();
		$institute=Institute::select('*')->first();
		$rdata =array('sDate'=>$this->getAppdate($sDate),'eDate'=>$this->getAppdate($eDate));
		$pdf = \PDF::loadView('app.feesreportprint',compact('datas','rdata','institute'));
		return $pdf->stream('fee-collection-report.pdf');
	}

	public function billDetails($billNo)
	{
		$billDeatils = FeeHistory::select("*")
		->where('billNo',$billNo)
		->get();
		return $billDeatils;
	}
	private function  parseAppDate($datestr)
	{
		$date = explode('/', $datestr);
		return $date[2].'-'.$date[1].'-'.$date[0];
	}
	private function  getAppdate($datestr)
	{
		$date = explode('-', $datestr);
		return $date[2].'/'.$date[1].'/'.$date[0];
	}


    public function classreportindex(){
        $classes = ClassModel::pluck('name','code');
        $class   = '';
        $section = '';
        $month   = '';
        $session = '';
        $year    = '';
		$student = new studentfdata;
		$student->class = "";
		$student->section = "";
		$student->shift = "";
		$student->session = "";
		$student->regiNo = "";
		$fees = array();
		$paid_student = array();
		$resultArray  = array();
		return View('app.feestdreportclass',compact('classes','student','fees','totals','class','section','month','session','paid_student','year','resultArray'));
	}

    public function classview(){

		$classes = ClassModel::pluck('name','code');
		$student = new studentfdata;
		$student->class=Input::get('class');
		$student->section=Input::get('section');
		$student->shift=Input::get('shift');
		$student->session=Input::get('session');
		$student->regiNo=Input::get('student');
		$feeyear = Input::get('year') ;

		$student_all =	DB::table('Student')->select( '*')->where('isActive','Yes')->where('class','=',Input::get('class'));
		if(Input::get('section')!=''){
		$student_all =$student_all->where('section','=',Input::get('section'));
	    }
		$student_all =$student_all->where('session','=',$student->session)->get();

		if(count($student_all)>0){
			$i=0;
			foreach($student_all as $stdfees){

				$student =	DB::table('billHistory')->leftJoin('stdBill', 'billHistory.billNo', '=', 'stdBill.billNo')
				->select( 'billHistory.billNo','billHistory.month','billHistory.fee','billHistory.lateFee','stdBill.class as class1','stdBill.payableAmount','stdBill.billNo','stdBill.payDate','stdBill.regiNo')
				// ->whereYear('stdBill.payDate', '=', 2017)
				->where('stdBill.regiNo','=',$stdfees->regiNo)->whereYear('stdBill.payDate', '=', Input::get('year'))->where('billHistory.month','=',Input::get('month'))->where('billHistory.month','<>','-1')
				//->orderby('stdBill.payDate')
				->get();

				if(count($student)>0 ){

					foreach($student as $rey){

						$status[] = "paid".'_'.$stdfees->regiNo."_";

						$resultArray[$i] = get_object_vars($stdfees);

						array_push($resultArray[$i],'Paid',$rey->payDate,$rey->billNo,$rey->fee);
						$i++;
					}

				}else{
					$status[$i] = "unpaid".'_'.$stdfees->regiNo."_";
					$resultArray[] = get_object_vars($stdfees);
					array_push($resultArray[$i],'unPaid');
					$i++;
				}

			}
		}
		else{
		$resultArray = array();
		}

		$class   = Input::get('class');
		$month   = Input::get('month');
		$section = Input::get('section');
		$session = Input::get('session');
		$year    = Input::get('year');

		return View('app.feestdreportclass',compact('resultArray','class','month','section','classes','session','year'));
    }

    public function ictcorefees(){


        //echo "<pre>";print_r(Input::get());
		$classes          = ClassModel::pluck('name','code');
		$student          = new studentfdata;
		$student->class   = Input::get('class');
		$student->section = Input::get('section');
		$student->shift   = Input::get('shift');
		$student->session = Input::get('session');
		$student->regiNo  = Input::get('student');
		$feeyear          = Input::get('year') ;

		$student_all =	DB::table('Student')->select( '*')->where('isActive','Yes')->where('class','=',Input::get('class'))->where('section','=',Input::get('section'))->where('session','=',$student->session)->get();
$ictcore_fees = Ictcore_fees::select("*")->first();
						// echo "<pre>";print_r($student_all);
						// exit;
		if(count($student_all)>0){
			$i=0;


					     $ictcore_integration = Ictcore_integration::select("*")->first();
				if(!empty($ictcore_integration) && $ictcore_integration->ictcore_url && $ictcore_integration->ictcore_user && $ictcore_integration->ictcore_password){ 
				      $ict  = new ictcoreController();
					  $data = array(
						'name' => 'Fee Notification',
						'description' => 'this is Fee Notifacation Group',
						);

					 $group_id= $ict->ictcore_api('groups','POST',$data );

		     	}else{

		            return Redirect::to('/fees/classreport')->withErrors("Please Add ictcore integration in Setting Menu");

		     	}
				foreach($student_all as $stdfees){

					$student =	DB::table('billHistory')->leftJoin('stdBill', 'billHistory.billNo', '=', 'stdBill.billNo')
					->select( 'billHistory.billNo','billHistory.month','billHistory.fee','billHistory.lateFee','stdBill.class as class1','stdBill.payableAmount','stdBill.billNo','stdBill.payDate','stdBill.regiNo')
					// ->whereYear('stdBill.payDate', '=', 2017)
					->where('stdBill.regiNo','=',$stdfees->regiNo)->whereYear('stdBill.payDate', '=', Input::get('year'))->where('billHistory.month','=',Input::get('month'))->where('billHistory.month','<>','-1')
					//->orderby('stdBill.payDate')
					->get();

					if(count($student)>0 ){
							//$resultArray = get_object_vars($stdfees)
					}else{
						$data = array(
						'first_name' => $stdfees->firstName,
						'last_name' =>  $stdfees->lastName,
						'phone'     =>  $stdfees->fatherCellNo,
						'email'     => '',
						);

						$contact_id = $ict->ictcore_api('contacts','POST',$data );

					   $group = $ict->ictcore_api('contacts/'.$contact_id.'/link/'.$group_id,'PUT',$data=array() );

						//$resultArray[] = get_object_vars($stdfees);
					}

				}
			}
			else{
			$resultArray = array();
			}
                $data = array(
					'program_id' => $ictcore_fees->ictcore_program_id,
					'group_id' => $group_id,
					'delay' => '',
					'try_allowed' => '',
					'account_id' => 1,
					'status' => '',
				);
				$campaign_id = $ict->ictcore_api('campaigns','POST',$data );
			    $campaign_id = $ict->ictcore_api('campaigns/'.$campaign_id.'/start','PUT',$data=array() );
	          //echo "<pre>";print_r($data);

				return Redirect::to('/fees/classreport')->with("success", "Voice campaign Created Succesfully.");
		//return View('app.feestdreportclass',compact('resultArray','class','month','section','classes','session','year'));
    }

}

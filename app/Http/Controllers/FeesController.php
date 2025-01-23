<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use App\Models\Marks;
use App\Models\FeeCol;
use App\Models\AddBook;
use App\Models\Student;
use App\Models\Subject;
use App\Models\FeeSetup;
use App\Models\Institute;
use App\Models\Accounting;
use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\FeeHistory;
use App\Models\Ictcore_fees;
use Illuminate\Http\Request;
use App\Models\FamilyVouchar;
use App\Models\InvoiceHistory;
use App\Models\Voucherhistory;
use Collective\Html\FormFacade;
use App\Models\Ictcore_integration;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Console\Commands\Invoicegenrated;
use App\Http\Controllers\ICTCoreController;

class studentfdata
{
}
class formfoo0
{
}
class feesController extends BaseController
{

	public function getsetup()
	{

		$classes = ClassModel::select('code', 'name')->orderby('code', 'asc')->get();
		//return View::Make('app.feesSetup',compact('classes'));
		return View('app.feesSetup', compact('classes'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function postSetup(Request $request)
	{
		$rules = [

			'class' => 'required',
			'type' => 'required',
			'fee' => 'required|numeric',
			'Latefee' => 'required|numeric',
			'title' => 'required'

		];
		$validator = \Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return Redirect::to('/fees/setup')->withErrors($validator);
		} else {

			/**
			 * Save feestep in akunting as a item if accounting enable
			 *
			 **/

			$data = array(
				'name' => $request->input('title'),
				'sku' => $request->input('class') . '_' . $request->input('type'),
				'description' => $request->input('description'),
				'sale_price' => $request->input('fee'),
				'purchase_price' => $request->input('Latefee'),
				'quantity' => '20000000',
				'category_id' => '5',
				'tax_id' => '1',
				'enabled' => '1',
				//'name'=>'api2',
			);
			/*if(accounting_check()!='' && accounting_check()=='yes' ){
				$item = php_curl('items','POST',$data);
				//echo "<pre>vv";print_r($item);exit;
				if (is_int($item)){

					$fee              = new FeeSetup();
					$fee->class       = $request->input('class');
					$fee->type        = $request->input('type');
					$fee->title       = $request->input('title');
					$fee->fee         = $request->input('fee');
					$fee->Latefee     = $request->input('Latefee');
					$fee->description = $request->input('description');
					$fee->item_id     = $item;
					if($request->input('description')==''){
						$fee->description ='';
					}
					$fee->save();
				}else{
					return Redirect::to('/fees/setup')->withErrors('somthing wrong please try again to creat new feesetup');
				}
			}else{*/
			$fee              = new FeeSetup();
			$fee->class       = $request->input('class');
			$fee->type        = $request->input('type');
			$fee->title       = $request->input('title');
			$fee->fee         = $request->input('fee');
			$fee->Latefee     = $request->input('Latefee');
			$fee->description = $request->input('description');
			//$fee->item_id     = $item;
			if ($request->input('description') == '') {
				$fee->description = '';
			}
			$fee->save();
			//}
			return Redirect::to('/fees/setup')->with("success", "Fee Save Succesfully.");
		}
	}

	public function getList()
	{
		$fees = array();
		$classes = ClassModel::pluck('name', 'code');

		$formdata = new formfoo0;
		$formdata->class = "";
		//return View::Make('app.feeList',compact('classes','formdata','fees'));
		return View('app.feeList', compact('classes', 'formdata', 'fees'));
	}
	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function postList(Request $request)
	{
		$rules = [

			'class' => 'required'
		];
		$validator = \Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return Redirect::to('/fees/list')->withErrors($validator);
		} else {

			$fees     = FeeSetup::select("*")->where('class', $request->input('class'))->get();
			$classes  = ClassModel::pluck('name', 'code');
			$formdata = new formfoo0;
			$formdata->class = $request->input('class');

			//echo "<pre>";print_r($formdata);exit;
			//return View::Make('app.feeList',compact('classes','formdata','fees'));
			return View('app.feeList', compact('classes', 'formdata', 'fees'));
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
		$classes = ClassModel::pluck('name', 'code');
		$fee = FeeSetup::find($id);
		//return View::Make('app.feeEdit',compact('fee','classes'));
		return View('app.feeEdit', compact('fee', 'classes'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function postEdit(Request $request)
	{
		$rules = [

			'class' => 'required',
			'type' => 'required',
			'fee' => 'required|numeric',
			'title' => 'required'
		];
		$validator = \Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return Redirect::to('/fee/edit/' . $request->input('id'))->withErrors($validator);
		} else {

			$fee              = FeeSetup::find($request->input('id'));
			$fee->class       = $request->input('class');
			$fee->type        = $request->input('type');
			$fee->title       = $request->input('title');
			$fee->fee         = $request->input('fee');
			$fee->Latefee     = $request->input('Latefee');
			$fee->description = $request->input('description');
			$fee->save();
			return Redirect::to('/fees/list')->with("success", "Fee Updated Succesfully.");
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
		return Redirect::to('/fees/list')->with("success", "Fee Deleted Succesfully.");
	}

	/**
	 * Create vouchar
	 **/
	public function vouchar_index(Request $request)
	{
		$classes = ClassModel::select('code', 'name')->orderby('code', 'asc')->get();

		if ($request->input('section') != '') {

			$sections = DB::table('section')->select('*')->where('class_code', $request->input('class'))->get();
		} else {

			$sections = '';
		}
		if ($request->input('fee_name') != '') {

			$fees = FeeSetup::select('id', 'title')->where('id', '=', $request->input('fee_name'))->get();
		} else {

			$fees = array();
		}
		if ($request->input('regiNo') != '') {
			$student = Student::select('regiNo', 'rollNo', 'firstName', 'middleName', 'lastName', 'discount_id')->where('isActive', '=', 'Yes')->where('regiNo', '=', $request->input('regiNo'))->first();
			//return $students;
		} else {

			$student = array();
		}
		$now             =  Carbon::now();
		$year            =  $now->year;
		$month           =  $now->month;

		//echo "<pre>";print_r($fees->toArray());exit;
		//return View::Make('app.feeCollection',compact('classes'));
		return View('app.vouchar.createvoucher', compact('classes', 'sections', 'fees', 'student', 'month', 'year'));

		//return View('app.fee_vouchar',compact('classes','institute','due_date','month','late_fee','discount','total_fee','student','fees'));
	}
	public function getvouchar(Request $request)
	{
		$student = DB::table('Student')
			->select('*')
			->where('regiNo', $request->input('regiNo'))
			->where('section', $request->input('section'))
			->first();
		//&type=Monthly
		//echo "<pre>";print_r($student );
		$fee = DB::table('feesSetup')
			->where('class', $request->input('class'))
			->first();
		//echo "<pre>";print_r($fee );
		$discount = $fee->fee / 100 * $student->discount_id;
		$total_fee  = $discount + $fee->fee;
		$fees        = $fee->fee;
		$late_fee   = $fee->Latefee;
		//return View::Make('app.feeCollection',compact('classes'));
		$institute = DB::table('institute')->first();
		//	$fees= FeeSetup::select('id','title')->where('id','=',$request->input('fee_name'))->get();

		$now             =  Carbon::now();
		$year            =  $now->year;
		$month           =  $now->month;
		$due_date		 =  $now->addDays(10);

		//return View('app.vouchar.fee_vouchar',compact('classes','institute','due_date','month','late_fee','discount','total_fee','student','fees'));
	}
	public function postvouchar(Request $request)
	{
		//echo "<pre>";print_r($request->all());exit;


		$rules = [
			'class'      => 'required',
			'student'    => 'required',
			//'date'     => 'required',
			'paidamount' => 'required',
			'dueamount'  => 'required',
			'ctotal'     => 'required'

		];
		//echo "<pre>";print_r($request->all());
		//exit;
		$validator = \Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return Redirect::to('/fee/vouchar?class=' . $request->input('class') . '&section=' . $request->input('section') . '&session=' . $request->input('session') . '&type=' . $request->input('type') . '&month=' . $request->input('gridMonth')[0] . '&fee_name=' . $request->input('fee') . '&regiNo=' . $request->input('student'))->withInput($request->all())->withErrors($validator);
		} else {

			$student = DB::table('Student')
				->select('*')
				->where('regiNo', $request->input('student'))
				->where('section', $request->input('section'))
				->first();

			$now           =  Carbon::now();
			$now_fromate  =  $now->format('Y-m-d');
			//$year            =  $now->year;
			//$month            =  $now->month;
			$due_date		 =  $now->addDays(10);


			$due_fromate =  $due_date->format('Y-m-d');

			try {



				/*$chk = DB::table('stdBill')
				->join('billHistory','stdBill.billNo','=','billHistory.billNo')
				->where('stdBill.regiNo',$request->input('student'))
				->where('billHistory.month',);
				*/
				$feeTitles       = $request->input('gridFeeTitle');
				$feeAmounts      = $request->input('gridFeeAmount');
				$feeLateAmounts  = $request->input('gridLateFeeAmount');
				$feeTotalAmounts = $request->input('gridTotal');
				$feeMonths       = $request->input('gridMonth');
				$month = $feeMonths[0];
				$counter         = count($feeTitles);

				if ($counter > 0) {
					$rows = FeeCol::count();
					if ($rows < 9) {
						$billId = 'B00' . ($rows + 1);
					} else if ($rows < 100) {
						$billId = 'B0' . ($rows + 1);
					} else {

						$billId = 'B' . ($rows + 1);
					}

					//echo $now_fromate;
					$data = array(
						'invoice_number'     => $billId,
						'order_number'       => $billId,
						'invoice_status_code' => 'draft',
						'invoiced_at'        => $now_fromate,
						'due_at'             => $due_fromate,
						'customer_name'      => $student->firstName . ' ' . $student->lastName,
						'customer_id'        => $student->customer_id ?? $student->id,
						'company_id'         => '1',
						'currency_code'      => 'PKR',
						'currency_rate'      => '1',
						'category_id'        => '5',

					);
					$invoice_Ary = array();
					for ($i = 0; $i < $counter; $i++) {


						$invoice_Ary[$i] =	array(
							"item[$i][name]"      => $feeTitles[$i],
							"item[$i][quantity]"  => '1',
							"item[$i][price]"     => $feeAmounts[$i],
							"item[$i][currency]"  => 'pkr',
							'amount'             => $feeTotalAmounts[$i]
						);
						$test = array_merge($invoice_Ary);

						//'name'=>'api2',



						/*if(accounting_check()!='' && accounting_check()=='yes' ){
								$item = php_curl('invoices','POST',$data);
								echo "<pre>vv";print_r($item);exit;
								if (is_int($item)){
									
								}else{
									//return Redirect::to('/fees/setup')->withErrors('somthing wrong please try again to creat new feesetup');
								}
							}*/
					}
					//print_r($test);
					//exit;

					DB::transaction(function () use ($billId, $counter, $feeTitles, $feeAmounts, $feeLateAmounts, $feeTotalAmounts, $feeMonths, $student, $now_fromate, $due_fromate, $request) {
						$j = 0;
						for ($i = 0; $i < $counter; $i++) {

							$data = array(
								'invoice_number'     => $billId,
								'order_number'       => $billId,
								'invoice_status_code' => 'draft',
								'invoiced_at'        => $now_fromate,
								'due_at'             => $due_fromate,
								'customer_name'      => $student->firstName . ' ' . $student->lastName,
								'customer_id'        => $student->customer_id ?? $student->id,
								'item[$i][name]'     => $feeTitles[$i],
								'item[$i][quantity]' => '1',
								'item[$i][price]'    => $feeAmounts[$i],
								'item[$i][currency]' => 'pkr',
								'company_id'         => '1',
								'currency_code'      => 'PKR',
								'currency_rate'      => '1',
								'category_id'        => '5',
								'amount'             => $feeTotalAmounts[$i]
								//'name'=>'api2',
							);
							$item = 1;
							if (accounting_check() != '' && accounting_check() == 'yes') {
								//$item = php_curl('items','POST',$data);
								//echo "<pre>vv";print_r($item);exit;
								//if (is_int($item)){

								//}else{
								//	return Redirect::to('/fees/setup')->withErrors('somthing wrong please try again to creat new feesetup');
								//}
							}

							$chk = DB::table('stdBill')
								->join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')
								->select('stdBill.*')
								->where('stdBill.regiNo', $request->input('student'))
								->where('billHistory.month', $feeMonths[$i]);


							if ($chk->count() == 0) {
								$feehistory          = new FeeHistory();
								$feehistory->billNo  = $billId;
								$feehistory->title   = $feeTitles[$i];
								$feehistory->fee     = $feeAmounts[$i];
								$feehistory->lateFee = $feeLateAmounts[$i];
								$feehistory->total   = $feeTotalAmounts[$i];
								$feehistory->month   = $feeMonths[$i];
								$feehistory->save();

								if ($feeMonths[$i] == '-1') {
									$type = 'Other';
								} else {
									$type = 'Monthly';
								}


								$voucharhistory           = new Voucherhistory();
								$voucharhistory->bill_id  = $billId;
								$voucharhistory->type     = $type;
								$voucharhistory->ref_id     = $item;
								$voucharhistory->amount   = $feeAmounts[$i];
								//$voucharhistory->due_amount   = $feeAmounts[$i];
								$voucharhistory->rgiNo    = $request->input('student');
								$voucharhistory->status   = 'unpaid';
								$voucharhistory->date    =   Carbon::now();
								$voucharhistory->save();
								$j++;
							} else {
								$chk = $chk->first();

								$feeCol                = FeeCol::find($chk->id);
								//$feeCol->billNo        = $billId;
								//$feeCol->class         = $request->input('class');
								//$feeCol->regiNo        = $request->input('student');
								$feeCol->payableAmount = $request->input('ctotal');
								$feeCol->paidAmount    = 0;
								$feeCol->total_fee     = $request->input('paidamount');
								//$feeCol->dueAmount     = $request->input('dueamount');
								$feeCol->dueAmount     = $request->input('gtotal');
								//$feeCol->payDate       = Carbon::now()->format('Y-m-d');
								//echo "<pre>";print_r(Carbon::now()->format('Y-m-d'));exit;
								$feeCol->save();
							}
						}
						if ($j > 0) {
							$feeCol                = new FeeCol();
							$feeCol->billNo        = $billId;
							$feeCol->class         = $request->input('class');
							$feeCol->regiNo        = $request->input('student');
							$feeCol->payableAmount = $request->input('ctotal');
							$feeCol->paidAmount    = 0;
							$feeCol->total_fee     = $request->input('paidamount');
							$feeCol->dueAmount     = $request->input('dueamount');
							$feeCol->payDate       = Carbon::now()->format('Y-m-d');
							//echo "<pre>";print_r(Carbon::now()->format('Y-m-d'));exit;
							$feeCol->save();
							\Session::put('not_save', $j);
							\Session::put('billid', $billId);
							//\Session::put('billid', $billId);

						} else {
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
					if (\Session::get('not_save') != 0) {
						\Session::forget('not_save');
						return Redirect::to('/fee/get_vouchar?class=' . $request->input('class') . '&section=' . $request->input('section') . '&session=' . $request->input('session') . '&type=' . $request->input('type') . '&month=' . $month . '&fee_name=' . $request->input('fee') . '&regiNo=' . $request->input('student'))->with("success", "Fee collection succesfull.");
					} else {
						\Session::forget('not_save');
						$messages = "Student  vouchar Updated";

						return Redirect::to('/fee/vouchar?class=' . $request->input('class') . '&section=' . $request->input('section') . '&session=' . $request->input('session') . '&type=' . $request->input('type') . '&month=' . $month . '&fee_name=' . $request->input('fee') . '&regiNo=' . $request->input('student'))->withErrors($messages);
					}
				} else {
					$messages = $validator->errors();
					$messages->add('Validator!', 'Please add atlest one fee!!!');

					return Redirect::to('/fee/vouchar?class=' . $request->input('class') . '&section=' . $request->input('section') . '&session=' . $request->input('session') . '&type=' . $request->input('type') . '&month=' . $month . '&fee_name=' . $request->input('fee') . '&regiNo=' . $request->input('student'))->withInput($request->all())->withErrors($messages);
				}
			} catch (\Exception $e) {
				// echo "<pre>";
				// print_r($e);
				// exit;
				return Redirect::to('/fee/vouchar?class=' . $request->input('class') . '&section=' . $request->input('section') . '&session=' . $request->input('session') . '&type=' . $request->input('type') . '&month=' . $request->input('gridMonth')[0] . '&fee_name=' . $request->input('fee') . '&regiNo=' . $request->input('student'))->withErrors($e->getMessage())->withInput();
			}
		}

		$classes = ClassModel::select('code', 'name')->orderby('code', 'asc')->get();
		//return View::Make('app.feeCollection',compact('classes'));
		return View('app.feeCollection', compact('classes'));
	}

	public function createvoucher(Request $request)
	{
		$bill = \Session::get('billid');
		$now             =  Carbon::now();
		$year            =  $now->year;
		if ($request->input('month') == '') {
			$month           =  $now->month;
		} else {
			$month       =   $request->input('month');
		}
		$due_date		 =  $now->addDays(10);
		//echo "<pre>";print_r($request->all());
		$vouchar_details = DB::table('stdBill')
			->join('Student', 'stdBill.regiNo', '=', 'Student.regiNo')
			//->join('voucherhistories','stdBill.billNo','=','voucherhistories.bill_id')
			->join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')
			->join('voucherhistories', 'stdBill.billNo', '=', 'voucherhistories.bill_id')
			->select('billHistory.*', 'stdBill.dueAmount', 'stdBill.payableAmount', 'stdBill.paidAmount', 'stdBill.class', 'stdBill.total_fee', 'stdBill.regiNo', 'voucherhistories.due_date')
			//->where('billHistory.billNo',$bill )
			->where('billHistory.month', '=', $month)
			->whereYear('stdBill.created_at', '=', $year)
			->where('stdBill.regiNo', $request->input('regiNo'))
			->get();
		///echo "<pre>";print_r($vouchar_details);
		//// exit;
		if (empty($vouchar_details->toArray())) {

			return Redirect()->back()->withErrors('NO data Found');
		}
		$student = DB::table('Student')
			->select('*')
			->where('regiNo', $request->input('regiNo'))
			->where('section', $request->input('section'))
			->first();
		//echo "<pre>";print_r($student);
		//exit;
		//&type=Monthly
		//echo "<pre>";print_r($student );
		$fee = DB::table('feesSetup')
			->where('class', $request->input('class'))
			->where('type', 'Monthly')
			->first();
		//echo "<pre>ff";print_r($fee );exit;
		//$discount = $fee->fee /100 * $student->discount_id;
		$discount    =  $student->discount_id;
		// $total_fee  = $discount +$fee->fee;
		$fees        = $fee->fee;
		$late_fee    = $fee->Latefee;
		//return View::Make('app.feeCollection',compact('classes'));
		$institute = DB::table('institute')->first();
		//	$fees= FeeSetup::select('id','title')->where('id','=',$request->input('fee_name'))->get();


		/*$totals          = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
                              ->where('class',$request->input('class'))
						      ->where('regiNo',$request->input('regiNo'))
						      ->where('billNo','<>',$bill)
						      ->first();*/

		//echo "<pre>";print_r($totals);exit;
		return View('app.vouchar.fee_vouchar', compact( 'institute', 'due_date', 'month', 'late_fee', 'discount', 'student', 'fees', 'vouchar_details', 'fees'));
		//http://localhost/apschool/fee/get_vouchar?class=cl1&section=7&session=2018&type=Monthly&month=10&fee_name=2&regiNo=180101                   


	}


	public function getvoucher(Request $request, $id)
	{

		$bill = \Session::get('billid');
		$now             =  Carbon::now();
		$year            =  $now->year;
		$month           =  $now->month;
		$due_date		 =  $now->addDays(10);
		//echo "<pre>";print_r($request->all());
		$vouchar_details = DB::table('stdBill')
			->join('Student', 'stdBill.regiNo', '=', 'Student.regiNo')
			//->join('voucherhistories','stdBill.billNo','=','voucherhistories.bill_id')
			->join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')
			->join('voucherhistories', 'stdBill.billNo', '=', 'voucherhistories.bill_id')
			->select('billHistory.*', 'stdBill.dueAmount', 'stdBill.payableAmount', 'stdBill.paidAmount', 'stdBill.class', 'stdBill.total_fee', 'stdBill.regiNo', 'voucherhistories.due_date')
			//->where('billHistory.billNo',$bill )
			->where('billHistory.month', '=', $month)
			->where('Student.regiNo', $request->input('regiNo'))
			->get();
		// echo "<pre>";print_r($vouchar_details);
		//exit;
		$student = DB::table('Student')
			->select('*')
			->where('regiNo', $request->input('regiNo'))
			->where('section', $request->input('section'))
			->first();
		//echo "<pre>";print_r($student);
		//exit;
		//&type=Monthly
		//echo "<pre>";print_r($student );
		$fee = DB::table('feesSetup')
			->where('class', $request->input('class'))
			->where('type', 'Monthly')
			->first();
		//echo "<pre>ff";print_r($fee );exit;
		//$discount = $fee->fee /100 * $student->discount_id;
		$discount    =  $student->discount_id;
		// $total_fee  = $discount +$fee->fee;
		$fees        = $fee->fee;
		$late_fee    = $fee->Latefee;
		//return View::Make('app.feeCollection',compact('classes'));
		$institute = DB::table('institute')->first();
		//	$fees= FeeSetup::select('id','title')->where('id','=',$request->input('fee_name'))->get();


		/*$totals          = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
                              ->where('class',$request->input('class'))
						      ->where('regiNo',$request->input('regiNo'))
						      ->where('billNo','<>',$bill)
						      ->first();*/

		//echo "<pre>";print_r($totals);exit;
		return View('app.vouchar.fee_vouchar', compact('classes', 'institute', 'due_date', 'month', 'late_fee', 'discount', 'total_fee', 'student', 'fees', 'vouchar_details', 'totals', 'fees'));
		//http://localhost/apschool/fee/get_vouchar?class=cl1&section=7&session=2018&type=Monthly&month=10&fee_name=2&regiNo=180101                   


	}
	public function getfvoucher($id)
	{

		$data = [
			"1" => "January",
			"2" => "February",
			"3" => "March",
			"4" => "April",
			"5" => "May",
			"6" => "June",
			"7" => "July",
			"8" => "August",
			"9" => "September",
			"10" => "October",
			"11" => "November",
			"12" => "December"
		];
		$now 			  =  Carbon::now();
		$month            =  $now->month;
		$html  = '';
		$html .= '
					<div class="modal" id="myModal' . $id . '">
					  <div class="modal-dialog">
					    <div class="modal-content">

					      <!-- Modal Header -->
					      <div class="modal-header">
					        <h4 class="modal-title">Select Months</h4>
					        <button type="button" class="close" data-dismiss="modal">&times;</button>
					      </div>

					      <!-- Modal body -->
					      <div class="modal-body">
					        <form role="form" id="defulter" action="' . url("/family/vouchars/" . $id) . '" name="defulter"  method="get" >

					         <div class="row">
					         <div class="col-md-12">
					         <div class="form-group">
					         <label class="control-label" for="month">Single Month</label>
					         <input type="radio" name="type" value="single" checked id="single" onclick="checkm(this.id)" >
					         <label class="control-label" for="month" >Multi Month</label>
					         <input type="radio"  id="multi"  value="multi" onclick="checkm(this.id)" name="type" >
					         </div>
					         </div>
					         </div>
					          <div class="row" id="multis" style="display:none">
					            <div class="col-md-12">
					                <div class="col-md-4">
					                  <div class="form-group">
					                    <label class="control-label" for="month">Month</label>

					                    <div class="input-group">
					                      <span class="input-group-addon"><i class="glyphicon glyphicon-info-sign blue"></i></span>
					                      
					                      ' . \Form::select("month[]", $data, $month, ["class" => "form-control", "id" => "month", "required" => "true", "multiple" => "true"]) . '
					                    </div>
					                  </div>
					                </div>
					               <div class="col-md-4">
					                <!--<div class="form-group ">
					                  <label for="session">Family ID</label>
					                  <div class="input-group">

					                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i> </span>
					                    <input  type="text" value="" id="family_id"  class="form-control" name="family_id"  >
					                  </div>
					                </div>-->
					              </div>
					              
					            </div>
					          </div>
					          <div class="col-md-2">
					                <div class="form-group">
					                  <label class="control-label" for="">&nbsp;</label>

					                  <div class="input-group">
					                    <button class="btn btn-primary pull-right" id="btnsave" type="submit"><i class="glyphicon glyphicon-th"></i> Get Voucher</button>

					                  </div>
					                </div>
					              </div>
					          </form>
					      </div>
					      <!-- Modal footer -->
					      <div class="modal-footer">
					        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
					      </div>

					    </div>
					  </div>
					</div>';
		echo $html;
	}
	public function get_family_voucher(Request $request, $family_id)
	{
		//$bill = \Session::get('billid');
		//echo 'familyid'.$family_id;
		$now             =  Carbon::now();
		$year            =  $now->year;
		$month           =  $now->month;
		$due_date		 =  $now->addDays(10);
		//echo "<pre>";print_r($request->all());

		$students = DB::table('Student')
			->join('Class', 'Student.class', '=', 'Class.code')
			->join('section', 'Student.section', '=', 'section.id')
			->select(
				'Student.id',
				'Student.regiNo',
				'Student.rollNo',
				'Student.firstName',
				'Student.middleName',
				'Student.lastName',
				'Student.fatherName',
				'Student.motherName',
				'Student.fatherCellNo',
				'Student.motherCellNo',
				'Student.localGuardianCell',
				'Student.discount_id',
				'Student.class as class_code',
				'Class.Name as class',
				'Class.code as class_code',
				'Student.presentAddress',
				'Student.section',
				'Student.gender',
				'Student.religion',
				'section.name'
			)
			->where('Student.isActive', '=', 'Yes')
			//->where('Student.family_id', '=', $family_id)
			//->orwhere('Student.fatherCellNo', '=', $family_id)
			->where(function ($q) use ($family_id) {
				$q->where('Student.family_id', '=', $family_id)
					->orWhere('Student.fatherCellNo', '=', $family_id);
			})
			->get();
		$regiNo = array();
		foreach ($students as $std) {
			$regiNo[] = $std->regiNo;
		}


		//echo "<pre>";print_r($regiNo);
		if ($request->input('type') == 'single' || $request->input('type') == '') {
			$vouchar_details = DB::table('stdBill')
				->join('Student', 'stdBill.regiNo', '=', 'Student.regiNo')
				//->join('voucherhistories','stdBill.billNo','=','voucherhistories.bill_id')
				->join('voucherhistories', 'stdBill.billNo', '=', 'voucherhistories.bill_id')
				->join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')

				->select('billHistory.*', 'stdBill.dueAmount', 'stdBill.payableAmount', 'stdBill.paidAmount', 'stdBill.class', 'stdBill.total_fee', 'stdBill.regiNo', 'voucherhistories.due_date', 'Student.discount_id', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.section')
				//->where('billHistory.billNo',$bill )
				->where('billHistory.month', '=', $month)
				->where('billHistory.title', '=', 'monthly')
				->whereIn('stdBill.regiNo', $regiNo);
			if ($vouchar_details->count() > 0) {
				$vouchar_details = $vouchar_details->get();
				// echo "<pre>";print_r($vouchar_details->toArray());
				//exit;
				$bills = array();
				foreach ($vouchar_details as $vouchar_detail) {
					$bills[] = $vouchar_detail->billNo;
				}

				//echo "<pre>";print_r($vouchar_details->toArray());exit;
			} else {
				//$invoicegenrated = new Invoicegenrated;
				return Redirect()->back()->withErrors('First Create Invoice then you print vouchar');

				$bills = array();
				foreach ($students as $std) {
					$vouchar_generates = $this->createvouchour($std->regiNo, $std->class_code, $std->discount_id, '');
					$bills[] = $std->regiNo;
					$vouchar_generates = '';
					if ($vouchar_generates != '') {
						$bills[] = $vouchar_generates;
					}
				}


				$vouchar_details = DB::table('stdBill')
					->join('Student', 'stdBill.regiNo', '=', 'Student.regiNo')
					//->join('voucherhistories','stdBill.billNo','=','voucherhistories.bill_id')
					->join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')
					->join('voucherhistories', 'stdBill.billNo', '=', 'voucherhistories.bill_id')
					->select('billHistory.*', 'stdBill.dueAmount', 'stdBill.payableAmount', 'stdBill.paidAmount', 'stdBill.class', 'stdBill.total_fee', 'stdBill.regiNo', 'voucherhistories.due_date', 'Student.discount_id', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.section')
					//->where('billHistory.billNo',$bill )
					->where('billHistory.month', '=', $month)
					->whereIn('stdBill.regiNo', $regiNo)
					->get();
			}
			//print_r($bills);exit;


			$bils     = implode(',', $bills);

			$totals    = FeeCol::join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')->select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(total_fee),0) as Totalpay,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(total_fee),0)- IFNULL(sum(paidAmount),0)) as dueAmount,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
				//->where('class',$request->input('class'))
				//->whereMonth('created_at', '=', $month)
				->where('month', '=', $month)
				->whereIn('regiNo', $regiNo)
				//->whereIn('stdBill.billNo',$bills)
				//->where('stdBill.billNo',$bils  )
				->first();
			$check_vouchar  = FamilyVouchar::whereMonth('date', $month)->where('family_id', $family_id)->count();
		} else {

			$bills = array();
			print_r($request->input('month'));
			foreach ($request->input('month') as $month) {
				$vouchar_details = DB::table('stdBill')
					->join('Student', 'stdBill.regiNo', '=', 'Student.regiNo')
					//->join('voucherhistories','stdBill.billNo','=','voucherhistories.bill_id')
					->join('voucherhistories', 'stdBill.billNo', '=', 'voucherhistories.bill_id')
					->join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')
					->select('billHistory.*', 'stdBill.dueAmount', 'stdBill.payableAmount', 'stdBill.paidAmount', 'stdBill.class', 'stdBill.total_fee', 'stdBill.regiNo', 'voucherhistories.due_date', 'Student.discount_id', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.section')
					//->where('billHistory.billNo',$bill )
					->where('billHistory.month', '=', $month)
					->where('billHistory.title', '=', 'monthly')
					->whereIn('stdBill.regiNo', $regiNo);
				if ($vouchar_details->count() > 0) {
					$vouchar_details = $vouchar_details->get();
					// echo "<pre>";print_r($vouchar_details->toArray());
					//exit;
					// $bills = array();
					foreach ($vouchar_details as $vouchar_detail) {
						$bills[] = $vouchar_detail->billNo;
					}

					//echo "<pre>";print_r($vouchar_details->toArray());
				} else {

					//$invoicegenrated = new Invoicegenrated;
					$getmonthname   = \DateTime::createFromFormat('!m', $month)->format('F');
					$errormesssage  = "Please first create invoice of month " . $getmonthname . " then print vouchar";
					return Redirect()->back()->withErrors($errormesssage);

					foreach ($students as $std) {

						$vouchar_generates = $this->createvouchour($std->regiNo, $std->class_code, $std->discount_id, $month);

						//echo "as";
						//$bills[] =$std->regiNo;
						//$vouchar_generates='';
						if ($vouchar_generates != '') {
							$bills[] = $vouchar_generates;
						}
					}


					/*$vouchar_details = DB::table('stdBill')
						               ->join('Student','stdBill.regiNo','=','Student.regiNo')
				                       //->join('voucherhistories','stdBill.billNo','=','voucherhistories.bill_id')
				                       ->join('billHistory','stdBill.billNo','=','billHistory.billNo')
				                       ->join('voucherhistories','stdBill.billNo','=','voucherhistories.bill_id')
		                               ->select('billHistory.*','stdBill.dueAmount','stdBill.payableAmount','stdBill.paidAmount','stdBill.class','stdBill.total_fee','stdBill.regiNo','voucherhistories.due_date','Student.discount_id', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName','Student.section')
		                               //->where('billHistory.billNo',$bill )
		                               ->where('billHistory.month', '=', $month)
		                               ->whereIn('stdBill.regiNo',$regiNo )
		                               ->get();*/
				}

				//print_r($bills);exit;
			}

			$bils     = implode(',', $bills);

			$vouchar_details = DB::table('stdBill')
				->join('Student', 'stdBill.regiNo', '=', 'Student.regiNo')
				//->join('voucherhistories','stdBill.billNo','=','voucherhistories.bill_id')
				->join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')
				->join('voucherhistories', 'stdBill.billNo', '=', 'voucherhistories.bill_id')
				->select('billHistory.*', DB::raw('sum(stdBill.dueAmount) as dueAmount'), DB::raw('sum(stdBill.payableAmount) as payableAmount'), DB::raw('sum(stdBill.paidAmount) as paidAmount'), 'stdBill.class', DB::raw('sum(stdBill.total_fee) as total_fee'), 'stdBill.regiNo', 'voucherhistories.due_date', DB::raw('sum(Student.discount_id) as discount_id'), 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.section')
				->whereIn('stdBill.billNo', $bills)
				->groupBy('stdBill.regiNo')
				//->where('billHistory.month', '=', $month)
				//->whereIn('stdBill.regiNo',$regiNo )
				->get();


			$bils     = implode(',', $bills);

			$totals   = FeeCol::join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')->select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(total_fee),0) as Totalpay,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(total_fee),0)- IFNULL(sum(paidAmount),0)) as dueAmount,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
				//->where('class',$request->input('class'))
				//->whereMonth('created_at', '=', $month)
				//->where('month', '=', $month)
				//->whereIn('regiNo',$regiNo)
				->whereIn('stdBill.billNo', $bills)
				//->where('stdBill.billNo',$bils  )
				->first();

			$check_vouchar  = FamilyVouchar::whereMonth('date', $month)->where('family_id', $family_id)->count();
		}


		// ->get();
		// echo "<pre>";print_r($vouchar_details);exit;
		/* $bils     = implode(',',$bills);

					  $totals   = FeeCol::join('billHistory','stdBill.billNo','=','billHistory.billNo')->select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(total_fee),0) as Totalpay,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(total_fee),0)- IFNULL(sum(paidAmount),0)) as dueAmount,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
											//->where('class',$request->input('class'))
											 //->whereMonth('created_at', '=', $month)
											 //->where('month', '=', $month)
											 //->whereIn('regiNo',$regiNo)
					  							->whereIn('stdBill.billNo',$bills)
					  							//->where('stdBill.billNo',$bils  )
											 ->first();
								$check_vouchar  = FamilyVouchar::whereMonth('date',$month)->where('family_id',$family_id)->count();*/
		//$checkbil = explode(',',$check_vouchar->bills);
		if ($check_vouchar == 0 && count($vouchar_details->toArray()) > 0) {

			$family_vouchar = new FamilyVouchar;
			$family_vouchar->family_id  = $family_id;
			$family_vouchar->bills      = $bils;
			$family_vouchar->date       = Carbon::now();
			$family_vouchar->status     = 'Unpaid';
			$family_vouchar->amount     = $totals->payTotal;
			$family_vouchar->dueamount  = $totals->dueamount;
			$family_vouchar->month      = $month;
			//$family_vouchar->save() ;
		}

		$student = DB::table('Student')
			->select('*')
			->whereIn('regiNo', $regiNo)
			->where('section', $request->input('section'))
			->get();
		//echo "<pre>";print_r($student);
		//exit;
		//&type=Monthly
		//echo "<pre>";print_r($student );
		/*$fee = DB::table('feesSetup')
					       ->where('class',$request->input('class'))
					       ->where('type','Monthly')
					       ->get();*/
		//echo "<pre>ff";print_r($fee );exit;
		//$discount = $fee->fee /100 * $student->discount_id;
		$discount    =  '';
		// $total_fee  = $discount +$fee->fee;
		$fees        = '';
		$late_fee    = '';
		// echo "<pre>";print_r($vouchar_details);exit;
		//return View::Make('app.feeCollection',compact('classes'));
		$institute = DB::table('institute')->first();
		//	$fees= FeeSetup::select('id','title')->where('id','=',$request->input('fee_name'))->get();


		/*$totals          = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
                              ->where('class',$request->input('class'))
						      ->where('regiNo',$request->input('regiNo'))
						      ->where('billNo','<>',$bill)
						      ->first();*/
		//echo "<pre>tyty";print_r($vouchar_details->toArray());
		//echo "<pre>tyty";print_r($totals);
		//exit;
		//echo "<pre>";print_r($totals);exit;
		if ($request->input('month') == '') {
			$month = implode(',', \DateTime::createFromFormat('!m', $month)->format('F'));
		} else {
			foreach ($request->input('month') as $mn) {
				$mnth[] = \DateTime::createFromFormat('!m', $mn)->format('F');
			}
			$month = implode(',', $mnth);
		}
		return View('app.vouchar.fee_vouchar_family', compact('classes', 'institute', 'due_date', 'month', 'late_fee', 'discount', 'total_fee', 'student', 'fees', 'vouchar_details', 'totals', 'fees', 'family_id'));
		//http://localhost/apschool/fee/get_vouchar?class=cl1&section=7&session=2018&type=Monthly&month=10&fee_name=2&regiNo=180101                   


	}

	public function createvouchour($regiNo, $class, $discount, $month)
	{

		//return '32323';
		try {
			$fee_setup       = FeeSetup::select('fee', 'Latefee')
				->where('class', '=', $class)
				->where('type', '=', 'Monthly');
			//->get();
			//
			if ($fee_setup->count() > 0) {

				$fee_setup       =   $fee_setup->first();
				$now             =  Carbon::now();
				$year1           =  $now->year;
				if ($month == '') {
					$month           =  $now->month;
				} else {
					$month           =  $month;
				}
				$date            =  $now->addDays(5);
				//$due_date        =  $now->addDays(10);
				if ($discount == NULL || $discount == '') {
					$discount = 0;
				} else {
					$discount = $discount;
				}

				$totalfee        = $fee_setup->fee - $discount;
				$feeTitles       = 'monthly';
				$feeAmounts      = $totalfee;
				$feeLateAmounts  = 0;
				$feeTotalAmounts = $totalfee;
				$feeMonths       = $month;
				$month           = $month;
				//$counter         = count($feeTitles);

				//if($counter>0)
				//{
				$rows = FeeCol::count();

				if ($rows < 9) {
					$billId = 'B00' . ($rows + 1);
				} else if ($rows < 100) {
					$billId = 'B0' . ($rows + 1);
				} else {

					$billId = 'B' . ($rows + 1);
				}
				//$FeeCol = FeeCol;

				$exception = DB::transaction(function () use ($billId, $feeTitles, $feeAmounts, $feeLateAmounts, $feeTotalAmounts, $feeMonths, $date, $regiNo, $class, $totalfee) {


					$j = 0;
					$chk = DB::table('stdBill')
						->join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')
						->where('stdBill.regiNo', $regiNo)
						->where('stdBill.paidAmount', 0)
						->get();

					$due =  DB::table('stdBill')->select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
						->where('class', $class)
						->where('regiNo', $regiNo)
						->first();

					// echo  $due->paiTotal;
					$chk = DB::table('stdBill')
						->join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')
						->where('stdBill.regiNo', $regiNo)
						->where('billHistory.title', 'monthly')
						->where('billHistory.month', $feeMonths);



					if ($chk->count() == 0 || $chk->count() == '') {

						$chk_rows = DB::table('stdBill')
							->where('stdBill.regiNo', $regiNo);
						//echo '<pre>'.print_r($due->payTotal,true);
						// exit;
						if ($chk_rows->count() == 0) {
							//echo 'ss'.$chk_rows->count();
							// exit;
							$due1  = $totalfee;
						} else {
							$due1 = $due->payTotal + $totalfee;
						}
						//exit;
						//return $chk->count();
						$feehistory          = new FeeHistory();
						$feehistory->billNo  = $billId;
						$feehistory->title   = $feeTitles;
						$feehistory->fee     = $feeAmounts;
						$feehistory->lateFee = $feeLateAmounts;
						$feehistory->total   = $feeTotalAmounts;
						$feehistory->month   = $feeMonths;
						$feehistory->save();

						$voucharhistory           = new Voucherhistory();
						$voucharhistory->bill_id  = $billId;
						$voucharhistory->type     = $feeTitles;
						$voucharhistory->ref_id   = '';
						$voucharhistory->amount   = $feeAmounts;
						$voucharhistory->due_date = $date->format('Y-m-d');
						$voucharhistory->rgiNo    = $regiNo;
						$voucharhistory->status   = 'unpaid';
						$voucharhistory->date     =   Carbon::now();
						$voucharhistory->save();
						$feeCol                = new FeeCol();
						$feeCol->billNo        = $billId;
						$feeCol->class         = $class;
						$feeCol->regiNo        = $regiNo;
						$feeCol->payableAmount = $totalfee;
						$feeCol->total_fee     = $totalfee;
						$feeCol->paidAmount    = 0;
						$feeCol->dueAmount     = $due1;
						$feeCol->payDate       = $date->format('Y-m-d');
						//echo "<pre>";print_r(Carbon::now()->format('Y-m-d'));
						$feeCol->save();
					}
					return $feeCol->billNo;
				});
				return $exception;
			}
		} catch (\Exception $e) {
			//  print_r($e);
			return $e->getMessage();
			//return Redirect::to('/fee/collection?class_id='.$request->input('class').'&section='.$request->input('section').'&session='.$request->input('session').'&type='.$request->input('type').'&month='.$request->input('gridMonth')[0].'&fee_name='.$request->input('fee'))->withErrors( $e->getMessage())->withInput();
		}
	}
	public function vouchar_history(Request $request)
	{
		$classes          = ClassModel::pluck('name', 'code');
		$student          = new studentfdata;
		$student->class   = $request->input('class');
		$student->section = $request->input('section');
		//$student->shift   = $request->input('shift');
		//$student->session = $request->input('session');
		$student->regiNo  = $request->input('regiNo');

		$fees = DB::Table('stdBill')
			->select(DB::RAW("billNo,payableAmount,paidAmount,total_fee,dueAmount,DATE_FORMAT(payDate,'%D %M,%Y') AS date"))
			->where('class', $request->input('class'))
			->where('regiNo', $request->input('regiNo'))
			->get();
		//echo "<pre>";print_r($fees);exit;
		if (empty($fees->toArray())) {

			return Redirect()->back()->withErrors('NO data Found');
		}
		$totals = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(total_fee),0) as Totalpay,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(total_fee),0)- IFNULL(sum(paidAmount),0)) as dueAmount,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
			->where('class', $request->input('class'))
			->where('regiNo', $request->input('regiNo'))
			->first();
		//echo "<pre>";print_r($totals);//exit;
		//return View::Make('app.feeviewstd',compact('classes','student','fees'));
		return View('app.voucharhistory', compact('classes', 'student', 'fees', 'totals'));
	}

	public function family_voucherhistory(Request $request, $family_id)
	{
		//echo $request->input('bill_ids');
		$bills = array();
		$print_vouchar = array();
		$print = $request->input('print');
		$fthername = array();
		$ids = $request->input('bill_ids');
		if ($request->input('bill_ids') !== '') {

			$bills = explode(',', $request->input('bill_ids'));
			$print = $request->input('print');
			$fthername     = DB::table('Student')/*->where('fatherName','<>','')*/->where('family_id', $family_id)->first();
			//echo "<pre>";print_r($fthername->toArray());exit;
			$print_vouchar = FeeCol::join('Student', 'stdBill.regiNo', '=', 'Student.regiNo')
				->select('stdBill.*', 'Student.firstName', 'Student.lastName', 'Student.section', 'Student.fatherName')
				->whereIn('stdBill.billNo', $bills)
				//->where('regiNo',$request->input('regiNo'))
				->get();
		}
		//echo "<pre>";print_r($print_vouchar->toArray());
		//exit;
		$classes          = ClassModel::pluck('name', 'code');
		$student          = new studentfdata;
		$student->class   = $request->input('class');
		$student->section = $request->input('section');
		//$student->shift   = $request->input('shift');
		//$student->session = $request->input('session');
		$student->regiNo  = $request->input('regiNo');

		$fees = DB::Table('stdBill')
			->select(DB::RAW("billNo,payableAmount,paidAmount,total_fee,dueAmount,DATE_FORMAT(payDate,'%D %M,%Y') AS date"))
			->where('class', $request->input('class'))
			->where('regiNo', $request->input('regiNo'))
			->get();
		//echo "<pre>";print_r($fees);exit;

		//echo "<pre>";print_r($totals);//exit;
		//return View::Make('app.feeviewstd',compact('classes','student','fees'));

		$students = DB::table('Student')
			->join('Class', 'Student.class', '=', 'Class.code')
			->join('section', 'Student.section', '=', 'section.id')
			->select(
				'Student.id',
				'Student.regiNo',
				'Student.rollNo',
				'Student.firstName',
				'Student.middleName',
				'Student.lastName',
				'Student.fatherName',
				'Student.motherName',
				'Student.fatherCellNo',
				'Student.motherCellNo',
				'Student.localGuardianCell',
				'Class.Name as class',
				'Class.code as class_code',
				'Student.presentAddress',
				'Student.section',
				'Student.gender',
				'Student.religion',
				'section.name'
			)
			->where('Student.isActive', '=', 'Yes')
			->where('Student.family_id', '=', $family_id)
			->orwhere('Student.fatherCellNo', '=', $family_id)

			->get();
		$regiNo = array();
		foreach ($students as $std) {
			$regiNo[] = $std->regiNo;
		}

		$family_vouchers  = FamilyVouchar::where('family_id', $family_id)->get();
		//echo "<pre>";print_r($family_vouchers);exit;
		$totals   = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(total_fee),0) as Totalpay,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(total_fee),0)- IFNULL(sum(paidAmount),0)) as dueAmount,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
			//->where('class',$request->input('class'))
			->whereIn('regiNo', $regiNo)
			->first();
		//echo "<pre>";print_r($totals );exit;
		return View('app.fvoucharhistory', compact('classes', 'student', 'fees', 'totals', 'family_vouchers', 'family_id', 'print_vouchar', 'print', 'fthername', 'ids'));
	}

	public function family_voucherprint(Request $request, $family_id, $billno)
	{
		$bills = array();
		$print_vouchar = array();
		$print = $request->input('print');
		$fthername = array();
		$ids = $billno;

		if ($billno !== '') {

			$bills = explode(',', $billno);
			$print = $request->input('print');
			$fthername     = DB::table('Student')/*->where('fatherName','<>','')*/->where('family_id', $family_id)->first();
			//echo "<pre>";print_r($fthername->toArray());exit;
			$print_vouchar = FeeCol::join('Student', 'stdBill.regiNo', '=', 'Student.regiNo')
				->select('stdBill.*', 'Student.firstName', 'Student.lastName', 'Student.section', 'Student.fatherName')
				->whereIn('stdBill.billNo', $bills)
				//->where('regiNo',$request->input('regiNo'))
				->get();

			//echo "<pre>";print_r($print_vouchar);exit;
		}
		$family_vouchers  = FamilyVouchar::where('family_id', $family_id)->get();
		//echo "<pre>";print_r($family_vouchers->toArray());exit;
		//$month ='';
		foreach ($family_vouchers as $fee) {
			if ($fee->bills == $billno) {

				$month = \DateTime::createFromFormat('!m', $fee->month)->format('F');
			}
		}
		//echo $month;exit;
		return View('app.print.print', compact('family_id', 'print_vouchar', 'print', 'fthername', 'ids', 'family_vouchers', 'month'));
	}
	public function vouchar_paid(Request $request, $billNo)
	{
		$totals = FeeCol::select('*')
			->where('billNo', $billNo)
			//->where('regiNo',$request->input('regiNo'))
			->first();
		$paid     = FeeCol::find($totals->id);
		$vouchers = Voucherhistory::where('bill_id', $billNo)->first();
		if ($request->input('s') != 'unpaid') {
			$paid->paidAmount   = $totals->total_fee;
			//$paid->dueAmount  = $totals->total_fee;
			$vouchers->status   = 'paid';
		} else {
			$paid->paidAmount = '0.00';
			$vouchers->status = 'unpaid';
		}
		$paid->save();
		$vouchers->save();
		$totals1 = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
			//->where('billNo',$billNo)
			->where('regiNo', $paid->regiNo)
			->first();
		if ($request->input('s') != 'unpaid') {
			$paid->dueAmount  =  $totals1->payTotal - $totals->total_fee;
		} else {

			$paid->dueAmount  = $totals1->payTotal;
		}
		$paid->save();
		//echo "<pre>";print_r($totals);
		return Redirect::back()->with('success', 'voucher paid');

		exit;
	}

	public function family_vouchar_paid(Request $request, $id)
	{

		//echo $id;


		//echo "==".$request->input('bills')."wewe";

		//exit;

		$bills = explode(',', $request->input('bills'));
		$fees = FeeCol::select('*')
			->whereIn('billNo', $bills)
			//->where('regiNo',$request->input('regiNo'))
			->get();
		$regiNos = array();
		//echo "<pre>";print_r($fees);exit;
		foreach ($fees as $totals) {

			$paid       = FeeCol::find($totals->id);
			$regiNos[]  = $paid->regiNo;
			$vouchers   = Voucherhistory::where('bill_id', $totals->billNo)->first();

			if ($request->input('s') != 'unpaid') {
				$paid->paidAmount = $totals->payableAmount;
				//$paid->dueAmount  = $totals->total_fee;
				$vouchers->status = 'paid';
			} else {
				$paid->paidAmount = '0.00';
				$vouchers->status = 'unpaid';
			}
			$paid->save();
			$vouchers->save();

			$totals1 = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
				->where('billNo', $totals->billNo)
				->where('regiNo', $paid->regiNo)
				->first();
			//echo $totals1->dueamount;
			//exit;
			if ($request->input('s') != 'unpaid') {
				//$paid->dueAmount  =  $totals1->payTotal - $totals->total_fee;
				$paid->dueAmount  =  $totals1->dueamount;
				//$paid->dueAmount  = $dueamount;
			} else {

				$paid->dueAmount  = $totals1->payTotal;
			}
			$paid->save();
		}
		$chechdueoveral  = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
			->whereIn('billNo', $bills)
			//->whereIn('regiNo',$regiNos)
			->first();
		//echo "<pre>";print_r($chechdueoveral );
		$family_vouchers  = FamilyVouchar::find($id);
		if ($request->input('s') != 'unpaid') {
			$family_vouchers->status = 'paid';
		} else {
			$family_vouchers->status = 'Unpaid';
		}
		$family_vouchers->dueamount = $chechdueoveral->dueamount;
		$family_vouchers->amount    = $chechdueoveral->payTotal;
		$family_vouchers->save();
		//echo "<pre>".$id;print_r($totals);
		//exit;
		if ($request->input('s') != 'unpaid') {
			return Redirect::to('/family/vouchar_history/' . $family_vouchers->family_id)->with('success', 'voucher paid');
			//return Redirect::to('/family/vouchar_history/'.$family_vouchers->family_id.'?print=yes&bill_ids='. $request->input('bills'))->with('success','voucher paid');
		} else {
			return Redirect::to('/family/vouchar_history/' . $family_vouchers->family_id)->with('success', 'voucher paid');
		}
		exit;
	}

	public function family_vouchar_detail($id)
	{

		$family_vouchers  = FamilyVouchar::find($id);
		echo $getbillsNo 	  = $family_vouchers->bills;
		$bill_ary		  = explode(',', $getbillsNo);

		$fees = DB::Table('stdBill')
			->join('Student', 'stdBill.regiNo', '=', 'Student.regiNo')
			->join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')
			->select(DB::RAW("stdBill.billNo,stdBill.payableAmount,stdBill.paidAmount,stdBill.dueAmount,DATE_FORMAT(stdBill.payDate,'%D %M,%Y') AS date"), 'Student.firstName', 'Student.lastName', 'Student.class', 'Student.section', 'Student.regiNo', 'billHistory.month', 'billHistory.title', 'billHistory.fee', 'billHistory.lateFee')
			//->where('stdBill.class',$request->input('class'))
			//->where('billHistory.month',$month)
			->whereIn('stdBill.billNo', $bill_ary)
			//->where('Student.session',get_current_session()->id)
			//->where('Student.section',$request->input('section'))
			->get();

		$html = '<div class="modal" id="details' . $id . '" style="padding-right: 528px !important; display: block;/*! width: 1146px; */margin-top: 140px;margin-left: -210px;">
	  <div class="modal-dialog">
	    <div class="modal-content" style="width: 1090px;">

	      <!-- Modal Header -->
	      <div class="modal-header">
	        <h4 class="modal-title">Fee Collection Detail</h4>
	        <button type="button" class="close" data-dismiss="modal">&times;</button>
	      </div>

	      <!-- Modal body -->
	      <div class="modal-body">
       		<table id="feeList" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th>Bill No</th>
                  <th>Student</th>
                  <th>Class</th>
                  <th>Section</th>
                  <th>Fee Type</th>
                  <th>Payable Amount</th>
                  <th>Paid Amount</th>
                  <th>Due Amount</th>
                  <th>Month</th>
                  <th>Pay Date</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>';
		foreach ($fees as $fee) {

			$f_idd = gfee_setup($fee->class, $fee->title);
			$section = gsection_name($fee->section);
			$class = gclass_name($fee->class);
			$fction = "onclick=invoicepaidhistory('$fee->billNo')";
			$html .= '<tr>
                    <td><a class="btnbill" href="#" ' . $fction . '>' . $fee->billNo . '</a></td>
                    <td>' . $fee->firstName . $fee->lastName . '</td>
                    <td>' . $class->name . '</td>
                    <td>' . $section->name . '</td>
                    <td>' . $fee->title . '</td>
                    <td>' . $fee->payableAmount . '</td>
                    <td>' . $fee->paidAmount . '</td>
                    <td>' . $fee->dueAmount . '</td>
                    <td> ';
			if ($fee->title == "monthly") {
				$month_name =  \DateTime::createFromFormat('!m', $fee->month)->format('F');
			} else {
				$month_name = '';
			}

			$html .= $month_name . '</td>
                    
                    <td>' . $fee->date . '</td>';

			if ($fee->payableAmount === $fee->paidAmount || $fee->paidAmount >= $fee->payableAmount) {
				$status = 'paid';
			} elseif ($fee->paidAmount == '0.00' || $fee->paidAmount == '') {

				$status = 'unpaid';
			} else {
				$status = 'partially paid';
			}

			$html .= '<td>';
			if ($status == 'paid') {
				$html .= '<button  class="btn btn-success" >' . $status . '</button>';
			} elseif ($status == 'partially paid') {
				$html .= '<button  class="btn btn-warning" >' . $status . '</button>';
			} else {
				$html .= '<button  class="btn btn-danger" >' . $status . '</button> </td>';
			}
			if ($fee->payableAmount === $fee->paidAmount || $fee->paidAmount >= $fee->payableAmount) {
				$fun  = "onclick =alert('Invoice Fully Paid')";
			} else {
				$fun  = "onclick =feecol('$fee->billNo')";
			}
			if ($fee->paidAmount < $fee->payableAmount) {
				$class = "btninvoice";
			} else {
				$class = "";
			}
			$html .= '<td>
                    	<button value="' . $fee->billNo . '" title="Collect Invoice" class="btn btn-primary ' . $class . '" ' . $fun . '> <i class="fas fa-dollar-sign icon-white"></i></button>
						</td>';
			$html .= '</tr>';
		}
		$html .= '</tbody>';
		$html .= '</table>';
		$html .= ' </div>

	      <!-- Modal footer -->
	      <div class="modal-footer">
	        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
	      </div>

	    </div>
	  </div>
	</div>';
		$html .= ' ';
		echo $html;
	}

	/*public function getvouchar()
	{
		$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
		//return View::Make('app.feeCollection',compact('classes'));
		$institute= Institute::select("*")->first();
		return View('app.fee_vouchar',compact('classes','institute'));
	}
	public function postvouchar()
	{
		$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
		//return View::Make('app.feeCollection',compact('classes'));
		return View('app.feeCollection',compact('classes'));
	}*/

	public function detail(Request $request)
	{

		$month = array('1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
		//echo "<pre>";print_r($fee_list);
		foreach ($month as $key => $mnth) :
			$fee_list = DB::table('stdBill')
				->join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')
				->select('stdBill.billNo', 'billHistory.month', 'billHistory.fee', 'billHistory.lateFee', 'billHistory.total')
				->where('stdBill.regiNo', $request->input('regiNo'))
				->whereYear('stdBill.created_at', date('Y'))
				->where('billHistory.month', $key);
			// ->orderBy('billHistory.month','ASC');
			if ($fee_list->count() > 0) :
				$fee_list = $fee_list->first();
				$fee_data[] = array('month' => $mnth, 'fee' => $fee_list->fee, 'lateFee' => $fee_list->lateFee, 'total' => $fee_list->total, 'status' => 'paid');
			else :
				$fee_data[] = array('month' => $mnth, 'fee' => '', 'lateFee' => '', 'total' => '', 'status' => 'unpaid');
			endif;
		endforeach;
		return View('app.feedetail', compact('fee_data'));
	}

	public function getCollection(Request $request)
	{
		$classes = ClassModel::select('code', 'name')->orderby('code', 'asc')->get();
		if ($request->input('section') != '') {
			$sections = DB::table('section')->select('*')->where('class_code', $request->input('class_id'))->get();
		} else {
			$sections = '';
		}
		if ($request->input('fee_name') != '') {
			$fees = FeeSetup::select('id', 'title')->where('id', '=', $request->input('fee_name'))->get();
		} else {
			$fees = array();
		}
		if ($request->input('regiNo') != '') {
			$student = Student::select('regiNo', 'rollNo', 'firstName', 'middleName', 'lastName', 'discount_id')->where('isActive', '=', 'Yes')->where('regiNo', '=', $request->input('regiNo'))->first();
			//return $students;
		} else {
			$student = array();
		}
		$now             =  Carbon::now();
		$year            =  $now->year;
		$month      =  $now->month;

		//echo "<pre>";print_r($fees->toArray());exit;
		//return View::Make('app.feeCollection',compact('classes'));
		return View('app.feeCollection', compact('classes', 'sections', 'fees', 'student', 'month'));
	}
	public function postCollection(Request $request)
	{

		$rules = [

			'class'      => 'required',
			'student'    => 'required',
			//'date'     => 'required',
			'paidamount' => 'required',
			'dueamount'  => 'required',
			'ctotal'     => 'required'

		];
		//echo "<pre>";print_r($request->all());
		//exit;
		$validator = \Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return Redirect::to('/fee/collection?class_id=' . $request->input('class') . '&section=' . $request->input('section') . '&session=' . $request->input('session') . '&type=' . $request->input('type') . '&month=' . $request->input('gridMonth')[0] . '&fee_name=' . $request->input('fee'))->withInput($request->all())->withErrors($validator);
		} else {

			try {
				$student = DB::table('Student')
					->select('*')
					->where('regiNo', $request->input('student'))
					->where('section', $request->input('section'))
					->first();
				$now            =  Carbon::now();
				$due_date		 =  $now->addDays(10);
				$due_fromate 	 =  $due_date->format('Y-m-d');

				/*$chk = DB::table('stdBill')
				->join('billHistory','stdBill.billNo','=','billHistory.billNo')
				->where('stdBill.regiNo',$request->input('student'))
				->where('billHistory.month',);
				*/
				$feeTitles       = $request->input('gridFeeTitle');
				$feeAmounts      = $request->input('gridFeeAmount');
				$feeLateAmounts  = $request->input('gridLateFeeAmount');
				$feeTotalAmounts = $request->input('gridTotal');
				$feeMonths       = $request->input('gridMonth');
				$feetype         = $request->input('feetype');
				$type            = $request->input('type1');
				$month           = $feeMonths[0];
				$counter         = count($feeTitles);

				if ($counter > 0) {
					$rows = FeeCol::count();
					if ($rows < 9) {
						$billId = 'B00' . ($rows + 1);
					} else if ($rows < 100) {
						$billId = 'B0' . ($rows + 1);
					} else {

						$billId = 'B' . ($rows + 1);
					}

					DB::transaction(function () use ($billId, $counter, $feeTitles, $feeAmounts, $feeLateAmounts, $feeTotalAmounts, $feeMonths, $feetype, $type) {
						$j    = 0;
						$item = '';
						for ($i = 0; $i < $counter; $i++) {
							if ($feeMonths[$i] == '-1' || $type[$i] == 'Other') {
								$type = 'Other';
							} else {
								$type = 'Monthly';
							}
							$feetype = DB::table('feesSetup')
								->where('id', $feetype[$i])
								->first();
							$chk = DB::table('stdBill')
								->join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')
								->where('stdBill.regiNo', $request->input('student'))
								->where('billHistory.month', $feeMonths[$i]);
							if ($feetype->type == 'Monthly') {
								$chk = $chk->where('billHistory.title', $feetype->title);
								//$chk =$chk->where()
							} else {
								$chk = $chk->where('billHistory.title', $feetype->title);
							}
							//->where('billHistory.month',$feeMonths[$i]);



							if ($chk->count() == 0) {
								$feehistory          = new FeeHistory();
								$feehistory->billNo  = $billId;
								$feehistory->title   = $feeTitles[$i];
								$feehistory->fee     = $feeAmounts[$i];
								$feehistory->lateFee = $feeLateAmounts[$i];
								$feehistory->total   = $feeTotalAmounts[$i];
								$feehistory->month   = $feeMonths[$i];
								$feehistory->save();




								$voucharhistory           = new Voucherhistory();
								$voucharhistory->bill_id  = $billId;
								$voucharhistory->type     = $type;
								$voucharhistory->ref_id     = $item;
								$voucharhistory->amount   = $feeAmounts[$i];
								//$voucharhistory->due_amount   = $feeAmounts[$i];
								$voucharhistory->rgiNo    = $request->input('student');
								$voucharhistory->status   = 'unpaid';
								$voucharhistory->date    =   Carbon::now();
								$voucharhistory->save();
								$j++;
							} else {
								/*$chk =$chk->first();

							    $feeCol                = FeeCol::find($chk->id);
								//$feeCol->billNo        = $billId;
								//$feeCol->class         = $request->input('class');
								//$feeCol->regiNo        = $request->input('student');
								$feeCol->payableAmount = $request->input('ctotal');
								$feeCol->paidAmount    = 0;
								$feeCol->total_fee     = $request->input('paidamount');
								//$feeCol->dueAmount     = $request->input('dueamount');
								$feeCol->dueAmount     = $request->input('gtotal');
								//$feeCol->payDate       = Carbon::now()->format('Y-m-d');
							//echo "<pre>";print_r(Carbon::now()->format('Y-m-d'));exit;
								$feeCol->save();*/
							}


							/*if(  $chk->count()==0){
								$feehistory          = new FeeHistory();
								$feehistory->billNo  = $billId;
								$feehistory->title   = $feeTitles[$i];
								$feehistory->fee     = $feeAmounts[$i];
								$feehistory->lateFee = $feeLateAmounts[$i];
								$feehistory->total   = $feeTotalAmounts[$i];
								$feehistory->month   = $feeMonths[$i];
								$feehistory->save();
								$j++;

							    $voucharhistory           = new Voucherhistory();
                                $voucharhistory->bill_id  = $billId;
                                $voucharhistory->type     = $feeTitles[$i];
                                $voucharhistory->ref_id   = '';
                                $voucharhistory->amount   = $feeTotalAmounts[$i];
                                $voucharhistory->due_date = Carbon::now()->addDays(5)->format('Y-m-d');
                                $voucharhistory->rgiNo    = $request->input('student');
                                $voucharhistory->status   = 'unpaid';
                                $voucharhistory->date     =   Carbon::now();
                                $voucharhistory->save();
							}*/
						}
						if ($j > 0) {
							$feeCol                = new FeeCol();
							$feeCol->billNo        = $billId;
							$feeCol->class         = $request->input('class');
							$feeCol->regiNo        = $request->input('student');
							$feeCol->payableAmount = $request->input('ctotal');
							//$feeCol->paidAmount    = $request->input('paidamount');
							$feeCol->total_fee     = $request->input('total_fee');
							$feeCol->paidAmount    = '0.00';
							//$feeCol->dueAmount     = $request->input('dueamount');
							$feeCol->dueAmount     = $request->input('ctotal');
							$feeCol->payDate       = Carbon::now()->format('Y-m-d');
							//echo "<pre>";print_r(Carbon::now()->format('Y-m-d'));exit;
							$feeCol->save();


							\Session::put('not_save', $j);
						} else {
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
					if (\Session::get('not_save') != 0) {
						\Session::forget('not_save');
						$mesg = '';
						if ($request->input('save_sms') == 'save_sms') {

							$send_sms = $this->send_sms($request->input('student'), $request->input('class'), $request->input('paidamount'));
							if ($send_sms == 200) {
								$mesg = "and Send sms";
							} else {
								$mesg = "and sms not send some thing wrong";
							}
						}
						return Redirect::to('/fee/collection?class_id=' . $request->input('class') . '&section=' . $request->input('section') . '&session=' . $request->input('session') . '&type=' . $request->input('type') . '&month=' . $month . '&fee_name=' . $request->input('fee'))->with("success", "Fee collection succesfull " . $mesg);
					} else {
						\Session::forget('not_save');
						$messages = "Student already add fee for this month";

						return Redirect::to('/fee/collection?class_id=' . $request->input('class') . '&section=' . $request->input('section') . '&session=' . $request->input('session') . '&type=' . $request->input('type') . '&month=' . $month . '&fee_name=' . $request->input('fee'))->withErrors($messages);
					}
				} else {
					$messages = $validator->errors();
					$messages->add('Validator!', 'Please add atlest one fee!!!');

					return Redirect::to('/fee/collection?class_id=' . $request->input('class') . '&section=' . $request->input('section') . '&session=' . $request->input('session') . '&type=' . $request->input('type') . '&month=' . $month . '&fee_name=' . $request->input('fee'))->withInput($request->all())->withErrors($messages);
				}
			} catch (\Exception $e) {
				//echo $e->getMessage();
				return Redirect::to('/fee/collection?class_id=' . $request->input('class') . '&section=' . $request->input('section') . '&session=' . $request->input('session') . '&type=' . $request->input('type') . '&month=' . $request->input('gridMonth')[0] . '&fee_name=' . $request->input('fee'))->withErrors($e->getMessage())->withInput();
			}
		}
	}

	public function send_sms($regiNo, $class, $amount)
	{
		$student_all = DB::table('Student')->where('regiNo', $regiNo)->where('class', $class)->where('isActive', 'Yes')->first();
		if (!empty($student_all)) {
			$ict     = new ictcoreController();
			$i       = 0;
			$attendance_noti     = DB::table('notification_type')->where('notification', 'fess')->first();
			$ictcore_fees        = Ictcore_fees::select("*")->first();
			$ictcore_integration = Ictcore_integration::select("*")->where('type', 'sms');
			//echo $ictcore_integration->method;
			//exit;
			if ($ictcore_integration->count() > 0) {
				$ictcore_integration = $ictcore_integration->first();
			} else {
				//return Redirect::to('fee_detail?action=unpaid')->withErrors("Sms credential not found");
				return 404;
			}
			//$group_id = $ict->telenor_apis('group','','','','','');
			$contacts = array();
			$contacts1 = array();
			$i = 0;


			if (preg_match("~^0\d+$~", $student_all->fatherCellNo)) {
				$to = preg_replace('/0/', '92', $student_all->fatherCellNo, 1);
			} else {
				$to = $student_all->fatherCellNo;
			}
			//$contacts1[] = $to;
			if (strlen(trim($to)) == 12) {
				$contacts = $to;
				//$i++;
			}
			//$comseprated= implode(',',$contacts);
			//$group_contact_id = $ict->telenor_apis('add_contact',$group_id,$contacts,'','','');
		} else {
			return 403;
		}
		$col_msg = DB::table('message')->first();
		if (empty($col_msg)) {
			$msg = 'Fee Paid paid amount is ' . $amount;
		} else {
			$msg = $col_msg->description;
			$msg1 = str_replace("[name]", $student_all->firstName . '' . $student_all->lastName, $msg);
			//$msg2 = str_replace("[month]",$month,$msg1);
			$msg = str_replace("[amount]", $amount, $msg1);
		}
		/*if($fee_msg->count()>0 && $fee_msg->first()->description!=''){
				$msg = $fee_msg->first()->description;
			}else{
				$msg = "please submit your child  fee for this month";
			}*/
		if ($ictcore_integration->method != 'ictcore') {
			$snd_msg  = $ict->verification_number_telenor_sms($to, $msg, 'SidraSchool', $ictcore_integration->ictcore_user, $ictcore_integration->ictcore_password, 'sms');
		} else {
			$send_msg_ictcore = sendmesssageictcore($student_all->firstName, $student_all->lastName, $to, $msg, 'fee_paid');
		}
		//$campaign      = $ict->telenor_apis('campaign_create',$group_id,'',$msg,'','sms');
		//$send_campaign = $ict->telenor_apis('send_msg','','','','',$campaign);
		//session()->forget('upaid');
		return 200;
	}

	public function getListjson($class, $type)
	{
		$fees = FeeSetup::select('id', 'title')->where('class', '=', $class)->where('type', '=', $type)->get();
		return $fees;
	}
	public function getFeeInfo($id)
	{
		$fee = FeeSetup::select('fee', 'Latefee')->where('id', '=', $id)->get();
		return $fee;
	}

	public function getDue($class, $stdId)
	{
		$now             =  Carbon::now();
		$year1           =  $now->year;
		$month           =  $now->month;
		$selmonth		  = $request->input('month');
		$due = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0) as dueamount'))
			->where('class', $class)
			->where('regiNo', $stdId);
		if ($selmonth == $month) {
			$due = $due->whereMonth('created_at', '<>', $month);
		}
		$due = $due->first();
		return $due->dueamount;
	}
	public function stdfeeview()
	{
		$classes = ClassModel::pluck('name', 'code');
		$student = new studentfdata;
		$student->class = "";
		$student->section = "";
		$student->shift = "";
		$student->session = "";
		$student->regiNo = "";
		$fees = array();
		//return View::Make('app.feeviewstd',compact('classes','student','fees'));
		return View('app.feeviewstd', compact('classes', 'student', 'fees'));
	}
	public function stdfeeviewpost(Request $request)
	{
		$classes          = ClassModel::pluck('name', 'code');
		$student          = new studentfdata;
		$student->class   = $request->input('class');
		$student->section = $request->input('section');
		$student->shift   = $request->input('shift');
		$student->session = $request->input('session');
		$student->regiNo  = $request->input('student');

		$fees = DB::Table('stdBill')
			->select(DB::RAW("billNo,payableAmount,paidAmount,dueAmount,DATE_FORMAT(payDate,'%D %M,%Y') AS date"))
			->where('class', $request->input('class'))
			->where('regiNo', $request->input('student'))
			->get();
		$totals = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
			->where('class', $request->input('class'))
			->where('regiNo', $request->input('student'))
			->first();

		//return View::Make('app.feeviewstd',compact('classes','student','fees','totals'));
		return View('app.feeviewstd', compact('classes', 'student', 'fees', 'totals'));
	}
	public function stdfeeinvoices()
	{

		$now              =  Carbon::now();
		$year             =  $now->year;
		$month            =  $now->month;
		$classes = ClassModel::pluck('name', 'code');
		$section = array('A' => 'A');
		$student = new studentfdata;
		$student->class = "";
		$student->section = "";
		$student->shift = "";
		$student->session = "";
		$student->regiNo = "";
		$fees = array();

		$fees = DB::Table('stdBill')
			->join('Student', 'stdBill.regiNo', '=', 'Student.regiNo')
			->join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')
			->select(DB::RAW("stdBill.billNo,stdBill.payableAmount,stdBill.paidAmount,stdBill.dueAmount,DATE_FORMAT(stdBill.payDate,'%D %M,%Y') AS date"), 'Student.firstName', 'Student.lastName', 'Student.class', 'Student.section', 'Student.regiNo', 'billHistory.month', 'billHistory.title', 'billHistory.fee', 'billHistory.lateFee')
			//->where('stdBill.class',$request->input('class'))
			->where('billHistory.month', $month)
			->whereYear('stdBill.created_at', $year)
			//->where('Student.section',$request->input('section'))
			->get();
		//echo "<pre>";print_r($fees);exit;
		//return View::Make('app.feeviewstd',compact('classes','student','fees'));
		return View('app.invoice', compact('classes', 'student', 'fees', 'section', 'month', 'year'));
	}
	public function stdfeeinvoicespost(Request $request)
	{
		$now              =  Carbon::now();
		$year             =  $now->year;
		$month            =  $now->month;

		$classes          = ClassModel::pluck('name', 'code');
		$section          = DB::table('section')->where('class_code', $request->input('class'))->pluck('name', 'id');
		$student          = new studentfdata;
		$student->class   = $request->input('class');
		$student->section = $request->input('section');
		$student->shift   = $request->input('shift');
		$student->session = $request->input('session');
		$student->regiNo  = $request->input('student');

		$fees = DB::Table('stdBill')
			->join('Student', 'stdBill.regiNo', '=', 'Student.regiNo')
			->join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')
			->select(DB::RAW("stdBill.billNo,stdBill.payableAmount,stdBill.paidAmount,stdBill.dueAmount,DATE_FORMAT(stdBill.payDate,'%D %M,%Y') AS date"), 'Student.firstName', 'Student.lastName', 'Student.class', 'Student.section', 'Student.regiNo', 'billHistory.month', 'billHistory.title', 'billHistory.fee', 'billHistory.lateFee')
			->where('stdBill.class', $request->input('class'));
		if ($request->input('fee_type') == 'monthly') {
			$fees = $fees->where('billHistory.month', $request->input('month'));
		} else {
			$month = '-1';
			$fees = $fees->where('billHistory.month', '-1');
		}
		$fees = $fees->whereYear('stdBill.created_at', $request->input('year'))
			->where('Student.section', $request->input('section'))
			->get();

		$totals = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
			->where('class', $request->input('class'))
			//->where('regiNo',$request->input('student'))
			->first();
		$fee_setup  = FeeSetup::select('id', 'title')->where('class', '=', $student->class)->where('type', '=', 'Monthly')->first();

		//return View::Make('app.feeviewstd',compact('classes','student','fees','totals'));
		return View('app.invoice', compact('classes', 'student', 'fees', 'totals', 'section', 'fee_setup', 'month', 'year'));
	}
	public function stdfeesdelete($billNo)
	{
		try {
			DB::transaction(function () use ($billNo) {
				FeeCol::where('billNo', $billNo)->delete();
				FeeHistory::where('billNo', $billNo)->delete();
			});
			return Redirect::to('/fees/view')->with("success", "Fees deleted succesfull.");
		} catch (\Exception $e) {

			return Redirect::to('/fees/view')->withErrors($e->getMessage())->withInput();
		}
	}
	public function reportstd($regiNo)
	{

		$datas = DB::Table('stdBill')
			->select(DB::RAW("payableAmount,paidAmount,dueAmount,DATE_FORMAT(payDate,'%D %M,%Y') AS date"))
			->where('regiNo', $regiNo)
			->get();
		$totals = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
			->where('regiNo', $regiNo)
			->first();
		$stdinfo = DB::table('Student')
			->join('Class', 'Student.class', '=', 'Class.code')
			->select(
				'Student.regiNo',
				'Student.rollNo',
				'Student.firstName',
				'Student.middleName',
				'Student.lastName',
				'Student.section',
				'Student.shift',
				'Student.session',
				'Class.Name as class'
			)
			->where('isActive', 'Yes')
			->where('Student.regiNo', $regiNo)
			->first();
		$institute = Institute::select('*')->first();
		$rdata = array('payTotal' => $totals->payTotal, 'paiTotal' => $totals->paiTotal, 'dueAmount' => $totals->dueamount);
		$pdf = \PDF::loadView('app.feestdreportprint', compact('datas', 'rdata', 'stdinfo', 'institute'));
		return $pdf->stream('student-Payments.pdf');
	}
	public function report()
	{
		//return View::Make('app.feesreport');
		return View('app.feesreport');
	}
	public function reportprint($sDate, $eDate)
	{
		$datas = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
			->whereDate('created_at', '>=', date($sDate))
			->whereDate('created_at', '<=', date($eDate))
			->first();
		$institute = Institute::select('*')->first();
		$rdata = array('sDate' => $this->getAppdate($sDate), 'eDate' => $this->getAppdate($eDate));
		$pdf = \PDF::loadView('app.feesreportprint', compact('datas', 'rdata', 'institute'));
		return $pdf->stream('fee-collection-report.pdf');
	}

	public function billDetails($billNo)
	{
		$billDeatils = FeeHistory::select("*")
			->where('billNo', $billNo)
			->get();
		return $billDeatils;
	}
	public function invoicehist($billNo)
	{
		$billDeatils = InvoiceHistory::select("*")
			->where('billNo', $billNo)
			->get();
		return $billDeatils;
	}
	public function invoiceDetails($billNo)
	{

		$fees = DB::Table('stdBill')
			->join('Student', 'stdBill.regiNo', '=', 'Student.regiNo')
			->join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')
			->select(DB::RAW("stdBill.billNo,stdBill.payableAmount,stdBill.paidAmount,stdBill.dueAmount,DATE_FORMAT(stdBill.payDate,'%D %M,%Y') AS date"), 'Student.firstName', 'Student.lastName', 'Student.class', 'Student.section', 'billHistory.month', 'billHistory.title', 'billHistory.fee', 'billHistory.lateFee')
			->where('stdBill.billNo', $billNo)
			->get();

		$html = '';
		foreach ($fees as $fee) {
			$html .= '
				 <div id="myModald' . $fee->billNo . '" class="modal preview-modal"  data-backdrop="" role="dialog"  aria-labelledby="preview-modal" aria-hidden="true">
                            <div class="modal-dialog">
                        <div class="modal-content"><div class="modal-header"><h4 class="modal-title">Collect Invoice</h4><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button></div><div class="modal-body" >';
			// <form class="form-horizontal" method="post" action="'.url("/fees/invoice/collect/").'/'.$fee->billNo.'" name="CollectIncoiveForm" role="form"  >
			$html .=
				'<input type="hidden" name="_token" value="' . csrf_token() . '">
                               <input type="hidden" name="billNo" id="billNo" value="' . $fee->billNo . '">
                               <input type="hidden" name="payableAmount" id="payableAmount" value="' . $fee->payableAmount . '">
                              <div class="form-group row" >
                                  <label class="col-sm-3 text-right control-label col-form-label ">Invoice ID * </label>
                                  <div class="col-sm-9  control-label col-form-label ">
                                      ' . $fee->billNo . '
                                  </div>
                              </div>
                              <div class="form-group row">
                                  <label class="col-sm-3 text-right control-label col-form-label ">Total</label>
                                  <div class="col-sm-3  control-label col-form-label ">
                                       ' . $fee->payableAmount . ' Rs
                                  </div>
                                  <label class="col-sm-3 text-right control-label col-form-label ">Pending Amount</label>
                                  <div class="col-sm-3  control-label col-form-label ">
                                      ' . $fee->dueAmount . ' Rs
                                  </div>
                              </div>
                              <div class="form-group row has-error" >
                                  <label class="col-sm-3 text-right control-label col-form-label">Collection Amount *</label>
                                  <div class="col-sm-9">
                                      <input name="collectionAmount" id="collectionAmount" min="10" required="" class="form-control  type="text">
                                  </div>
                              </div>
                              
                              
                              <div class="form-group m-b-0">
                                  <div class="offset-sm-3 col-sm-9">
                                      <button type="button" onclick="feecollection();" class="btn btn-info waves-effect waves-light " >Collect Invoice</button>
                                  </div>
                              </div>';
			//</form>
			$html .= ' </div>
                      </div>
                      </div>
                      </div>
			';
		}

		return $html;
	}

	public function invoiceCollect(Request $request, $billNo)
	{
		$now              =  Carbon::now();
		$year             =  $now->year;
		$month            =  $now->month;

		$paidamount = $request->input('collectionAmount') . '.00';
		$totals = FeeCol::select('*')
			->where('billNo', $billNo)
			//->where('regiNo',$request->input('regiNo'))
			->first();

		$paid      = FeeCol::find($totals->id);
		$vouchers  = Voucherhistory::where('bill_id', $billNo)->first();
		$getmonth  = DB::table('billHistory')->where('billNo', $billNo)->where('month', '<>', '-1')->first();

		$totalpaid = $paid->paidAmount + $paidamount;
		/*if((int)$totalpaid > (int)$request->input('payableAmount') ){
			echo  $totalpaid ."ww======ww".$request->input('payableAmount');
		}
		echo  $totalpaid ."======".$request->input('payableAmount');exit;*/
		if ($totalpaid > $request->input('payableAmount')) {
			return 419;
		}
		if ($paidamount < 0) {
			return 404;
		}
		if ($paidamount == '' || $paidamount == '0.00' /*||  $paidamount>0*/) {
			return 404;
		}
		if ($request->input('s') != 'unpaid') {
			$paid->paidAmount = $totalpaid;
			//$paid->dueAmount  = $totals->total_fee;
			if ($paidamount >= $totalpaid) {
				$status = 'paid';
			} elseif ($paidamount === 0) {

				$status = 'unpaid';
			} else {
				$status = 'partially paid';
			}
			$vouchers->status = $status;
		} else {

			$paid->paidAmount = '0.00';
			$vouchers->status = 'unpaid';
		}
		$paid->save();
		$vouchers->save();
		$totals1 = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
			->where('billNo', $billNo)
			//->where('regiNo',$paid->regiNo)
			->first();
		if ($request->input('s') != 'unpaid') {
			$paid->dueAmount  =  $totals1->dueamount;
		} else {

			$paid->dueAmount  = $totals1->payTotal;
		}
		$paid->save();

		$invoicehistory = new InvoiceHistory;

		$invoicehistory->billNo = $billNo;
		$invoicehistory->amount = $paidamount;
		$invoicehistory->date   = Carbon::now();
		$invoicehistory->status = $status;
		$invoicehistory->save();

		$student = DB::table('Student')->where('regiNo', $paid->regiNo)->first();
		$fees = DB::Table('stdBill')
			->join('Student', 'stdBill.regiNo', '=', 'Student.regiNo')
			->join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')
			->select(DB::RAW("stdBill.billNo,stdBill.payableAmount,stdBill.paidAmount,stdBill.dueAmount,DATE_FORMAT(stdBill.payDate,'%D %M,%Y') AS date"), 'Student.firstName', 'Student.lastName', 'Student.class', 'Student.section', 'billHistory.month', 'billHistory.title', 'billHistory.fee', 'billHistory.lateFee')
			->where('stdBill.class', $paid->class)
			->where('Student.section', $student->section)
			->where('billHistory.month', $getmonth->month)
			->whereYear('stdBill.created_at', $year)
			->get();

		$html = '<table id="feeList1" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th>Bill No</th>
                  <th>Student</th>
                  <th>Fee Type</th>
                  <th>Payable Amount</th>
                  <th>Paid Amount</th>
                  <th>Due Amount</th>
                  <th>Month</th>
                  <th>Pay Date</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="ajax_data">';
		foreach ($fees as $fee) {
			$bilid = "'" . $fee->billNo . "'";
			$html .= ' <tr>
                    <td><a class="btnbill" href="#" onclick="invoicepaidhistory(' . $bilid . ');">' . $fee->billNo . '</a></td>
                    <td>' . $fee->firstName . $fee->lastName . '</td>
                    <td>' . $fee->title . '</td>
                    <td>' . $fee->payableAmount . '</td>
                    <td>' . $fee->paidAmount . '</td>
                    <td>' . $fee->dueAmount . '</td>';
			if ($fee->title == "monthly") {
				$month = \DateTime::createFromFormat('!m', $fee->month)->format('F');
			} else {
				$month = "other";
			}
			/* <td>'. \DateTime::createFromFormat('m', $fee->month)->format('F').' </td>*/
			$html .= ' <td>' . $month . ' </td>
                    <td>' . $fee->date . '</td>';

			if ($fee->payableAmount === $fee->paidAmount || $fee->paidAmount >= $fee->payableAmount) {
				$status = 'paid';
			} elseif ($fee->paidAmount == '0.00' || $fee->paidAmount == '') {

				$status = 'unpaid';
			} else {
				$status = 'partially paid';
			}

			$html .= '<td>';
			if ($status == 'paid') {
				$html .= '<button  class="btn btn-success" >';
				$html .=  $status;
				$html .= '</button>';
			} elseif ($status == 'partially paid') {
				$html .= '<button  class="btn btn-warning" >';
				$html .= $status;
				$html .= '</button>';
			} else {
				$html .= '<button class="btn btn-danger" >';
				$html .= $status;
				$html .= '</button>';
			}
			$html .= '</td>
                    <td>';

			if ($fee->paidAmount < $fee->payableAmount) {

				$fun    = "btninvoice";
			} else {

				$fun = '';
			}
			if ($fee->payableAmount === $fee->paidAmount || $fee->paidAmount >= $fee->payableAmount) {
				$alrt = "alert('Invoice Fully Paid')";
			} else {
				$alrt = "";
			}


			//<a title='Delete' class='btn btn-danger' href='{{url("/fees/delete")}}/{{$fee->billNo}}'> <i class="glyphicon glyphicon-trash icon-red"></i></a>--}}

			//<button value="{{$fee->billNo}}" title='Collect Invoice' class='btn btn-primary @if( $fee->paidAmount<$fee->payableAmount) btninvoice @endif' @if($fee->payableAmount===$fee->paidAmount || $fee->paidAmount>=$fee->payableAmount ) onclick = "alert('Invoice Fully Paid')" @endif> <i class="fas fa-dollar-sign icon-white"></i></button>
			$html .= '<button value="' . $fee->billNo . '" title="Collect Invoice" class="btn btn-primary  ' . $fun . '" onclick="' . $alrt . '"> <i class="fas fa-dollar-sign icon-white"></i></button>
                      <a title="Edit" class="btn btn-warning" href="' . url('/fees/invoice/update/' . $fee->billNo) . '"> <i class="glyphicon glyphicon-pencil icon-white"></i></a>
                    </td>
                  </tr>';
		}
		$html .= '</tbody>
            </table>';
		//echo "<pre>";print_r($totals);
		//return Redirect::back()->with('success','Invoice paid');
		return $html;

		exit;
	}
	private function  parseAppDate($datestr)
	{
		$date = explode('/', $datestr);
		return $date[2] . '-' . $date[1] . '-' . $date[0];
	}
	private function  getAppdate($datestr)
	{
		$date = explode('-', $datestr);
		return $date[2] . '/' . $date[1] . '/' . $date[0];
	}


	public function classreportindex()
	{
		$classes 			= ClassModel::pluck('name', 'code');
		$class   			= '';
		$section 			= '';
		$month   			= '';
		$session 			= '';
		$year    			= '';
		$student 			= new studentfdata;
		$student->class 	= "";
		$student->section 	= "";
		$student->shift 	= "";
		$student->session 	= "";
		$student->regiNo 	= "";
		$fees         		= array();
		$paid_student 		= array();
		$resultArray  		= array();

		$now  				=  Carbon::now();
		$year  			=  $now->year;
		$month 			=  $now->month;

		$student_all =	DB::table('Student')
			->join('Class', 'Student.class', '=', 'Class.code')
			->join('section', 'Student.section', '=', 'section.id')
			->select('Student.*', 'Class.name as class', 'section.name as section')
			->where('Student.isActive', 'Yes')
			->where('Student.session', '=', get_current_session()->id)
			->orderBy('Student.regiNo', 'ASC')
			->get();
		//  }
		//echo '<pre>'.get_current_session();print_r($student_all);
		//exit;
		if (count($student_all) > 0) {
			$i = 0;
			foreach ($student_all as $stdfees) {

				$student =	DB::table('billHistory')
					->Join('stdBill', 'billHistory.billNo', '=', 'stdBill.billNo')
					->select('billHistory.billNo', 'billHistory.month', 'billHistory.fee', 'billHistory.lateFee', 'stdBill.class as class1', 'stdBill.payableAmount', 'stdBill.billNo', 'stdBill.payDate', 'stdBill.regiNo', 'stdBill.paidAmount')
					// ->whereYear('stdBill.payDate', '=', 2017)
					->where('stdBill.paidAmount', '<>', '0.00')
					->where('stdBill.regiNo', '=', $stdfees->regiNo)
					->whereYear('stdBill.payDate', '=', $year)
					->where('billHistory.month', '=', $month)
					->where('billHistory.month', '<>', '-1')
					//->orderby('stdBill.payDate')
					->get();
				//echo "<pre>";print_r($student);exit;
				if (count($student) > 0) {

					foreach ($student as $rey) {

						$status[] = "paid" . '_' . $stdfees->regiNo . "_";

						$resultArray[$i] = get_object_vars($stdfees);

						array_push($resultArray[$i], 'Paid', $rey->payDate, $rey->billNo, $rey->fee);
						$i++;
					}
				} else {
					$status[$i] = "unpaid" . '_' . $stdfees->regiNo . "_";
					$resultArray[] = get_object_vars($stdfees);
					array_push($resultArray[$i], 'unPaid');
					$i++;
				}
			}
		} else {
			$resultArray = array();
		}

		return View('app.feestdreportclass', compact('classes', 'student', 'fees', 'class', 'section', 'month', 'session', 'paid_student', 'year', 'resultArray'));
	}

	public function classview(Request $request)
	{

		$classes = ClassModel::pluck('name', 'code');
		$student = new studentfdata;
		$student->class = $request->input('class');
		$student->section = $request->input('section');
		$student->shift = $request->input('shift');
		$student->session = $request->input('session');
		$student->regiNo = $request->input('student');
		$feeyear = $request->input('year');

		$student_all =	DB::table('Student')->select('*')->where('isActive', 'Yes')->where('class', '=', $request->input('class'));
		if ($request->input('section') != '' && $request->input('direct') != 'yes') {
			$student_all = $student_all->where('section', '=', $request->input('section'));
		}
		//  if($request->input('direct')=='yes'){
		$student_all = $student_all->where('session', '=', $student->session)->get();
		//  }
		//echo '<pre>';print_r($student_all);
		//exit;
		if (count($student_all) > 0) {
			$i = 0;
			foreach ($student_all as $stdfees) {

				$student =	DB::table('billHistory')->Join('stdBill', 'billHistory.billNo', '=', 'stdBill.billNo')
					->select('billHistory.billNo', 'billHistory.month', 'billHistory.fee', 'billHistory.lateFee', 'stdBill.class as class1', 'stdBill.payableAmount', 'stdBill.billNo', 'stdBill.payDate', 'stdBill.regiNo', 'stdBill.paidAmount')
					// ->whereYear('stdBill.payDate', '=', 2017)
					->where('stdBill.paidAmount', '<>', '0.00')
					->where('stdBill.regiNo', '=', $stdfees->regiNo)
					->whereYear('stdBill.payDate', '=', $request->input('year'))
					->where('billHistory.month', '=', $request->input('month'))
					->where('billHistory.month', '<>', '-1')
					//->orderby('stdBill.payDate')
					->get();

				if (count($student) > 0) {

					foreach ($student as $rey) {

						$status[] = "paid" . '_' . $stdfees->regiNo . "_";

						$resultArray[$i] = get_object_vars($stdfees);

						array_push($resultArray[$i], 'Paid', $rey->payDate, $rey->billNo, $rey->fee);
						$i++;
					}
				} else {
					$status[$i] = "unpaid" . '_' . $stdfees->regiNo . "_";
					$resultArray[] = get_object_vars($stdfees);
					array_push($resultArray[$i], 'unPaid');
					$i++;
				}
			}
		} else {
			$resultArray = array();
		}

		$class   = $request->input('class');
		$month   = $request->input('month');
		$section = $request->input('section');
		$session = $request->input('session');
		$year    = $request->input('year');
		//echo "<pre>".$request->input('month');print_r($resultArray);
		// exit;
		return View('app.feestdreportclass', compact('resultArray', 'class', 'month', 'section', 'classes', 'session', 'year'));
	}

	public function ictcorefees(Request $request)
	{


		//echo "<pre>";print_r($request->input());
		$classes          = ClassModel::pluck('name', 'code');
		$student          = new studentfdata;
		$student->class   = $request->input('class');
		$student->section = $request->input('section');
		$student->shift   = $request->input('shift');
		$student->session = $request->input('session');
		$student->regiNo  = $request->input('student');
		$feeyear          = $request->input('year');
		if ($request->input('all') == 'yes') {
			//$student_all =unserialize($request->input('result'));

		} else {

			if ($request->input('session') == '') {
				$student->session = get_current_session()->id;
			}



			$student_all =	DB::table('Student')
				->select('*')
				->where('isActive', 'Yes');
			if ($request->input('class') != '') {
				$student_all->where('class', '=', $request->input('class'));
			}
			if ($request->input('class') != '') {
				$student_all->where('section', '=', $request->input('section'));
			}

			$student_all->where('session', '=', $student->session);

			//$student_all = $student_all->get();
		}
		//$data = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $request->input('result'));

		$ictcore_fees    = Ictcore_fees::select("*")->first();
		//echo "<pre>";print_r($student_all);
		//exit;
		if ($student_all->count() > 0) {
			$student_all = $student_all->get();
			$i = 0;


			$ictcore_integration = Ictcore_integration::select("*")->first();
			if (!empty($ictcore_integration) && $ictcore_integration->ictcore_url && $ictcore_integration->ictcore_user && $ictcore_integration->ictcore_password) {
				$ict  = new ictcoreController();
				$data = array(
					'name' => 'Fee Notification',
					'description' => 'this is Fee Notifacation Group',
				);

				$group_id = $ict->ictcore_api('groups', 'POST', $data);
				if (is_object($group_id) &&  $group_id->error != '') {
					//$group_id->error->message;
					return Redirect::to('/fees/classreport')->withErrors($group_id->error->message);
				}
			} else {

				return Redirect::to('/fees/classreport')->withErrors("Please Add ictcore integration in Setting Menu");
			}
			$i = 0;
			//$contactdata = array();
			foreach ($student_all as $stdfees) {

				$student =	DB::table('billHistory')
					->Join('stdBill', 'billHistory.billNo', '=', 'stdBill.billNo')
					->select('billHistory.billNo', 'billHistory.month', 'billHistory.fee', 'billHistory.lateFee', 'stdBill.class as class1', 'stdBill.payableAmount', 'stdBill.billNo', 'stdBill.payDate', 'stdBill.regiNo', 'stdBill.paidAmount')
					// ->whereYear('stdBill.payDate', '=', 2017)
					->where('stdBill.paidAmount', '=', '0.00')
					->where('stdBill.regiNo', '=', $stdfees->regiNo)
					->whereYear('stdBill.payDate', '=', $request->input('year'))
					->where('billHistory.month', '=', $request->input('month'))
					->where('billHistory.month', '<>', '-1');
				//->orderby('stdBill.payDate')
				//->first();
				//echo "<pre>";print_r($student);
				if ($student->count() > 0) {
					$student = $student->first();
					//$resultArray = get_object_vars($stdfees)
					$i++;
				} else {
					$contactdata = array(
						'first_name' => $stdfees->firstName,
						'last_name' =>  $stdfees->lastName,
						'phone'     =>  $stdfees->fatherCellNo,
						'email'     => '',
					);
					//echo "<pre>";print_r($data);
					$contact_id  = $ict->ictcore_api('contacts', 'POST', $contactdata);
					if (is_object($contact_id) && $contact_id->error != '') {
						//echo $contact_id->error->message;
						return Redirect::to('/fees/classreport')->withErrors($contact_id->error->message);
					}
					$group  = $ict->ictcore_api('contacts/' . $contact_id . '/link/' . $group_id, 'PUT', $data = array());

					//echo "<pre>gghg";print_r($group);exit;
					/*if(!empty($group) && is_object($group) && $group->error!=''){
							echo $group->error->message;
							return Redirect::to('/fees/classreport')->withErrors($group->error->message);

						}*/
					//$resultArray[] = get_object_vars($stdfees);
				}
			}
		} else {
			$resultArray = array();
		}
		//echo "<pre>";print_r($contactdata);exit;
		if (!empty($contactdata)) {
			$data = array(
				'program_id'  => $ictcore_fees->ictcore_program_id,
				'group_id'    => $group_id,
				'delay'       => '',
				'try_allowed' => '',
				'account_id'  => 1,
				'status'      => '',
			);

			//echo "<pre>";print_r($data);
			$campaign_id = $ict->ictcore_api('campaigns', 'POST', $data);
			if (is_object($campaign_id) && $campaign_id->error != '') {
				//echo $campaign_id->error->message;
				return Redirect::to('/fees/classreport')->withErrors($campaign_id->error->message);
			}
			$campaign_id = $ict->ictcore_api('campaigns/' . $campaign_id . '/start', 'PUT', $data = array());
			if (is_object($campaign_id) &&  $campaign_id->error != '') {
				//echo $campaign_id->error->message;
				return Redirect::to('/fees/classreport')->withErrors($campaign_id->error->message);
			}
			//echo "<pre>";print_r($data);
		} else {
			return Redirect::to('/fees/classreport')->withErrors("Unpaid Student Not Found");
			exit;
		}

		return Redirect::to('/fees/classreport')->with("success", "Voice campaign Created Succesfully.");
		//return View('app.feestdreportclass',compact('resultArray','class','month','section','classes','session','year'));
	}

	public function fee_detail(Request $request)
	{
		$action = $request->input('action');
		if ($action != '') :
			$now   = Carbon::now();
			$year1  =  $now->year;
			$year   =  get_current_session()->id;
			$month  =  $now->month;
			// $all_section =	DB::table('section')->select( '*')->get();
			$all_section =	DB::table('Class')->select('*')->get();
			//$student_all =	DB::table('Student')->select( '*')->where('class','=',$request->input('class'))->where('section','=',$request->input('section'))->where('session','=',$student->session)->get();
			$ourallpaid = 0;
			$ourallunpaid = 0;
			$unpaidArray = array();
			$resultArray = array();
			if (count($all_section) > 0) {
				$i = 0;
				$paid = 0;
				$unpaid = 0;
				$total_s = 0;
				foreach ($all_section as $section) {

					$student_all =	DB::table('Student')
						->join('section', 'Student.section', '=', 'section.id')
						//->left('section','Student.section','=','section.id')
						->select('Student.*', 'section.name as section_name')->where('class', '=', $section->code)/*->where('section','=',$section->id)/**/
						->where('session', '=', $year)
						//->where('Student.session','=',$year)
						->where('Student.isActive', '=', 'Yes')
						->get();

					// $unpaidArray[$section->code.'_'.$section->name."_".'unpaid']=0;
					//$resultArray[$section->code.'_'.$section->name."_".'paid'] =  0;

					if (count($student_all) > 0) {
						foreach ($student_all as $stdfees) {
							$student =	DB::table('billHistory')
								->Join('stdBill', 'billHistory.billNo', '=', 'stdBill.billNo')
								->select('billHistory.billNo', 'billHistory.month', 'billHistory.fee', 'billHistory.lateFee', 'stdBill.class as class1', 'stdBill.payableAmount', 'stdBill.billNo', 'stdBill.payDate', 'stdBill.regiNo', 'stdBill.paidAmount')
								// ->whereYear('stdBill.payDate', '=', 2017)
								->where('stdBill.paidAmount', '<>', '0.00')
								->where('stdBill.regiNo', '=', $stdfees->regiNo)->whereYear('stdBill.payDate', '=', $year1)->where('billHistory.month', '=', $month)->where('billHistory.month', '<>', '-1')
								//->orderby('stdBill.payDate')
								->get();
							if (count($student) > 0) {
								foreach ($student as $rey) {

									//$status[] = "paid".'_'.$stdfees->regiNo."_";
									//$resultArray[$i] = get_object_vars($stdfees);
									//array_push($resultArray[$i],'Paid',$rey->payDate,$rey->billNo,$rey->fee);
									//$resultArray[$section->code.'_'.$section->name."_".'paid'] =  ++$paid;
									$resultArray[$section->code . '_' . $section->name . "_" . 'paid_' . $stdfees->regiNo] = (object) array_merge((array) $stdfees, (array) $rey);
								}
							} else {

								$unpaidArray[$section->code . '_' . $section->name . "_" . 'unpaid_' . $stdfees->regiNo]  = $stdfees;
								//$ourallunpaid =++$ourallunpaid;
							}
							//$resultArray[$section->code.'_'.$section->name."_".'total']=++$total_s;
						}
					} else {
						//$resultArray[$section->code.'_'.$section->name."_".'total']=0;
						//$resultArray[$section->code.'_'.$section->name."_".'unpaid']=array();
						// $unpaidArray[$section->code.'_'.$section->name."_".'paid'] =  array();

					}
					//$resultArray[] = get_object_vars($section);
					//array_push($resultArray[$i],$total,$paid,$unpaid);
					//$scetionarray[] = array('section'=>$section->name,'class'=>$section->code);
					//$resultArray1[] = array('total'=> $resultArray[$section->code.'_'.$section->name."_".'total'],'unpaid'=>$resultArray[$section->code.'_'.$section->name."_".'unpaid'],'paid'=>$resultArray[$section->code.'_'.$section->name."_".'paid']);

				}
			} else {
				//$resultArray = array();
			}
			if ($action == 'unpaid') {
				$fee_detail = $unpaidArray;
				$status = 'Unpaid';
				session()->forget('upaid');
				session(['upaid' => $fee_detail]);

				$defulters = array();
				/*$i=0;
			foreach($fee_detail as $defulter){
            $defulters[$i]=$defulter->fatherCellNo;
            $i++;
			}*/
				//echo "<pre>";print_r($defulters);
				//exit;

			} else {
				$fee_detail = $resultArray;
				$status = 'Paid';
			}

			$month_n = $now->format('F');
			$statusn =  $status;
			//echo "<pre>";print_r( $attendances_detail);
			return View('app.fee_detail', compact('fee_detail', 'status', 'month_n', 'year', 'statusn'));

		endif;
	}
	/**
	 * Send notification for all unpaid student
	 ***/
	public function sendnotification(Request $request)
	{

		$notification = '';
		$student_all =	\Session::get('upaid');
		echo $request->input('action');
		switch ($request->input('action')) {
			case 'sms':
				//
				$notification  = 'sms';
				$send_sms_notification = $this->send_sms_notification($student_all);
				return $send_sms_notification;
				exit;
				break;

			case 'voice':
				// 
				$notification = 'voice';
				$send_voice_notification = $this->send_voice_notification($student_all);
				return $send_voice_notification;
				exit;
				break;
		}

		/*if(!empty($student_all)){
			$ict  = new ictcoreController();
			$i=0;
			$attendance_noti     = DB::table('notification_type')->where('notification','fess')->first();
			$ictcore_fees        = Ictcore_fees::select("*")->first();
			$ictcore_integration = Ictcore_integration::select("*")->where('type',$attendance_noti->type)->first();
			if($ictcore_integration->method=="telenor"){
				$group_id = $ict->telenor_apis('group','','','','','');
			}else{
				if(!empty($ictcore_integration) && $ictcore_integration->ictcore_url && $ictcore_integration->ictcore_user && $ictcore_integration->ictcore_password){ 

					$data = array(
						'name' => 'Fee Notification',
						'description' => 'fee notification',
						);

					echo  $group_id= $ict->ictcore_api('groups','POST',$data );
				}else{
					return Redirect::to('fee_detail?action=unpaid')->withErrors("Please Add ictcore integration in Setting Menu");
					exit();
				}
			}
			$contacts = array();
			$contacts1 = array();
			foreach($student_all as $stdfees)
			{
				if (preg_match("~^0\d+$~", $stdfees->fatherCellNo)) {
					$to = preg_replace('/0/', '92', $stdfees->fatherCellNo, 1);
				}else {
					$to =$stdfees->fatherCellNo;  
				}

				$data = array(
					//'registrationNumber' =>$stdfees->regiNo,
					'first_name'         => $stdfees->firstName,
					'last_name'          =>  $stdfees->lastName,
					'phone'              =>  NULL,
					'email'              => '',
					);
				if($ictcore_integration->method=="telenor"){
					$contacts1[] = $to;
					if(strlen(trim($to))==12){
						$contacts[] = $to;
					}
				}else{
					$contact_id = $ict->ictcore_api('contacts','POST',$data );

					$group = $ict->ictcore_api('contacts/'.$contact_id.'/link/'.$group_id,'PUT',$data=array() );
				}
			}
			if($ictcore_integration->method=="telenor" && !empty($contacts)){
				$comseprated= implode(',',$contacts);
                     
				$group_contact_id = $ict->telenor_apis('add_contact',$group_id,$comseprated,'','','');
			}
		}else{
			return Redirect::to('fee_detail?action=unpaid')->withErrors("Student not found");
		}

		if($ictcore_integration->method=="telenor"){
			$fee_msg = DB::table('ictcore_fees');
			if($fee_msg->count()>0 && $fee_msg->first()->description!=''){
				$msg = $fee_msg->first()->description;
			}else{
				$msg = "please submit your child  fee for this month";
			}
			
			//$group_id='410598';
			$campaign      = $ict->telenor_apis('campaign_create',$group_id,'',$msg,$fee_msg->first()->telenor_file_id,$attendance_noti->type);
			// echo $campaign;
			// print_r($campaign);
			 //exit;
			// $this->info('Notification sended successfully'.$campaign);

			$send_campaign = $ict->telenor_apis('send_msg','','','','',$campaign);
	           //	echo $send_campaign;
			 //print_r($send_campaign);	exit;
			session()->forget('upaid');
			return Redirect::to('fee_detail?action=unpaid')->with('success',"Notification sended");

		}else{
			//echo 'sded';
			if(!empty($ictcore_fees) && $ictcore_fees->ictcore_program_id!=''){
				$data = array(
					'program_id' => $ictcore_fees->ictcore_program_id,
					'group_id' => $group_id,
					'delay' => '',
					'try_allowed' => '',
					'account_id' => 1,
					);
				//$campaign_id = $ict->ictcore_api('campaigns','POST',$data );
				//$campaign_id = $ict->ictcore_api('campaigns/'.$campaign_id.'/start','PUT',$data=array() );
				//session()->forget('upaid');
				return Redirect::to('fee_detail?action=unpaid')->with('success',"Notification sended");

			}
			//echo 'testing';
		}*/
	}

	public function send_sms_notification($student_all)
	{
		if (!empty($student_all)) {
			$ict  = new ictcoreController();
			$i = 0;
			$attendance_noti     = DB::table('notification_type')->where('notification', 'fess')->first();
			$ictcore_fees        = Ictcore_fees::select("*")->first();
			$ictcore_integration = Ictcore_integration::select("*")->where('type', 'sms');
			//echo $ictcore_integration->method;
			//exit;
			if ($ictcore_integration->count() > 0) {
				$ictcore_integration = $ictcore_integration->first();
			} else {
				return Redirect::to('fee_detail?action=unpaid')->withErrors("Sms credential not found");
			}
			if ($ictcore_integration->method == 'telenor') {
				$group_id  = $ict->telenor_apis('group', '', '', '', '', '');
			} else {
				$data = array(
					'name'        => 'Fee Notification',
					'description' => 'fee notification',
				);

				$group_id = $ict->ictcore_api('groups', 'POST', $data);
			}
			$contacts  = array();
			$contacts1 = array();
			$i = 0;
			foreach ($student_all as $stdfees) {
				if (preg_match("~^0\d+$~", $stdfees->fatherCellNo)) {
					$to = preg_replace('/0/', '92', $stdfees->fatherCellNo, 1);
				} else {
					$to = $stdfees->fatherCellNo;
				}
				$contacts1[] = $to;
				if (strlen(trim($to)) == 12) {
					$contacts[] = $to;
					if ($ictcore_integration->method != 'telenor') {
						$data = array(
							//'registrationNumber' =>$stdfees->regiNo,
							'first_name'         => $stdfees->firstName,
							'last_name'          =>  $stdfees->lastName,
							'phone'              =>  $to,
							'email'              => '',
						);


						$contact_id = $ict->ictcore_api('contacts', 'POST', $data);
						$group = $ict->ictcore_api('contacts/' . $contact_id . '/link/' . $group_id, 'PUT', $data = array());
					}

					$i++;
				}

				/*if($i==3){
					break;
				}*/
			}
			if ($ictcore_integration->method == 'telenor') {
				$comseprated = implode(',', $contacts);
				$group_contact_id = $ict->telenor_apis('add_contact', $group_id, $comseprated, '', '', '');
			}
		} else {
			return Redirect::to('fee_detail?action=unpaid')->withErrors("Student not found");
		}
		$fee_msg = DB::table('ictcore_fees');
		if ($fee_msg->count() > 0 && $fee_msg->first()->description != '') {
			$msg = $fee_msg->first()->description;
		} else {
			$msg = "please submit your child  fee for this month";
		}
		if ($ictcore_integration->method == 'telenor') {
			$campaign = $ict->telenor_apis('campaign_create', $group_id, '', $msg, $fee_msg->first()->telenor_file_id, 'sms');
			$send_campaign = $ict->telenor_apis('send_msg', '', '', '', '', $campaign);
		} else {

			$data = array(
				'name' => 'fee_noti',
				'data' => $msg,
				'type' => 'utf-8',
				'description' => '',
			);
			$text_id  =  $ict->ictcore_api('messages/texts', 'POST', $data);
			$data     = array(
				'name' => 'fee_noti',
				'text_id' => $text_id,
			);
			$program_id  =  $ict->ictcore_api('programs/sendsms', 'POST', $data);


			$program_id = $program_id;

			$data = array(
				'program_id' => $program_id,
				'group_id'   => $group_id,
				'delay'      => '',
				'try_allowed' => '',
				'account_id' => 1,
			);
			//echo ""
			$campaign_id = $ict->ictcore_api('campaigns', 'POST', $data);
			//$campaign_id = $ict->ictcore_api('campaigns/$campaign_id/start'

		}
		session()->forget('upaid');
		return Redirect::to('fee_detail?action=unpaid')->with('success', "Notification sended");
	}
	public function send_voice_notification($student_all)
	{
		if (!empty($student_all)) {
			$ict  = new ictcoreController();
			$i = 0;
			$attendance_noti     = DB::table('notification_type')->where('notification', 'fess')->first();
			$ictcore_fees        = Ictcore_fees::select("*")->first();
			$ictcore_integration = Ictcore_integration::select("*")->where('type', 'voice')->first();
			if (!empty($ictcore_integration) && $ictcore_integration->ictcore_url && $ictcore_integration->ictcore_user && $ictcore_integration->ictcore_password) {
				$data = array(
					'name' => 'Fee Notification',
					'description' => 'fee notification',
				);

				$group_id = $ict->ictcore_api('groups', 'POST', $data);
			} else {
				return Redirect::to('fee_detail?action=unpaid')->withErrors("Please Add ictcore integration in Setting Menu");
				exit();
			}

			//$i=0;
			$contacts = array();
			$contacts1 = array();
			$i = 0;
			foreach ($student_all as $stdfees) {
				if (preg_match("~^0\d+$~", $stdfees->fatherCellNo)) {
					$to = preg_replace('/0/', '92', $stdfees->fatherCellNo, 1);
				} else {
					$to = $stdfees->fatherCellNo;
				}
				$data = array(
					//'registrationNumber' =>$stdfees->regiNo,
					'first_name'         => $stdfees->firstName,
					'last_name'          =>  $stdfees->lastName,
					'phone'              =>  $to,
					'email'              => '',
				);

				if (strlen(trim($to)) == 12) {
					$contact_id = $ict->ictcore_api('contacts', 'POST', $data);
					$group = $ict->ictcore_api('contacts/' . $contact_id . '/link/' . $group_id, 'PUT', $data = array());
					$i++;
				}
				/*if($i==3){
						break;
					}*/
			}
		} else {
			return Redirect::to('fee_detail?action=unpaid')->withErrors("Student not found");
		}
		if (!empty($ictcore_fees) && $ictcore_fees->ictcore_program_id != '') {
			$data = array(
				'program_id' => $ictcore_fees->ictcore_program_id,
				'group_id' => $group_id,
				'delay' => '',
				'try_allowed' => '',
				'account_id' => 1,
			);
			$campaign_id = $ict->ictcore_api('campaigns', 'POST', $data);
			$campaign_id = $ict->ictcore_api('campaigns/' . $campaign_id . '/start', 'PUT', $data = array());
			return Redirect::to('fee_detail?action=unpaid')->with('success', "Notification sended");
		}
	}
}

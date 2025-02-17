<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Carbon\Carbon;
use App\Models\Marks;
use App\Models\Branch;
use App\Models\FeeCol;
use App\Models\AddBook;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Accounting;
use App\Models\Attendance;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class DashboardController extends BaseController
{

	public function __construct()
	{
		/*$this->beforeFilter('csrf', array('on'=>'post'));
			$this->beforeFilter('auth', array('only'=>array('index')));*/
		$this->middleware('auth', array('only' => array('index')));
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{

		/*activity()
		   //->performedOn($anEloquentModel)
		   ->causedBy(Auth()->user())
		   ->withProperties(['customProperty' => 'customValue'])
		   ->log('Look, I logged something');*/

		$now                  =  Carbon::now();
		if ($request->input('year') == "") {

			$year1            =  $now->year;
		} else {
			$year1            =  trim(urlencode($request->input('year')));
		}
		$year             =  get_current_session()->id;
		// $year             =  get_current_session();
		// echo "<pre>";
		// print_r($year);
		// exit;
		if ($request->input('month') == "") {
			$month            =  $now->month;
		} else {
			$month            = trim($request->input('month'));
		}
		//echo "mm - -".$month ."-- year -- ".$year1 ;
		$error            = \Session::get('error');
		$success          = \Session::get('success');
		$tclass           =  ClassModel::count();
		$tsubject         =  Subject::count();
		$tstudent         =  Student::where('isActive', 'Yes')->where('session', $year)->count();

		// echo get_current_session()->id. $tstudent;exit;
		// dd($year);


		$teacher         =  Teacher::count();
		$totalAttendance = Attendance::groupBy('date')
			->select('date')
			->get();
		//echo Carbon::now()->format('Y-m-d');
		$totalabsent     = Attendance::where('date', Carbon::now()->format('Y-m-d'))->where('status', 'Absent')->count();
		$totallate       = Attendance::where('date', Carbon::now()->format('Y-m-d'))->where('status', 'Late')->count();

		//echo "<pre>";print_r($totalabsent );exit;
		$totalExam = Marks::groupBy('exam', 'subject')->get();
		$book      = AddBook::count();
		$total     = [
			'class'       => $tclass,
			'student'     => $tstudent,
			'subject'     => $tsubject,
			'attendance'  => count($totalAttendance),
			'exam'        => count($totalExam),
			'book'        => $book,
			'totalabsent' => $totalabsent,
			'teacher'     => $teacher,
			'totallate'   => $totallate
		];
		// 	//graph data
		//   dd($tstudent);
		$monthlyIncome = Accounting::selectRaw('month(date) as month, sum(amount) as amount, year(date) as year')
			->where('type', 'Income')
			->groupBy('month')
			->get();
		//,DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount')
		$tutionfees = FeeCol::join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')->select(DB::RAW('billHistory.month, year(stdBill.created_at) as year,sum(stdBill.payableAmount) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
			//->where('class',$request->input('class'))
			->groupBy('month')
			//->where('regiNo',$request->input('student'))
			->get();
		$comabine_array = array();
		$i = 0;
		foreach ($tutionfees as $fees) {

			//echo 'sas'.$fees->month.array_search($fees->month,$monthlyIncome[0]->toArray());

			//print_r($monthlyIncome->toArray());
			//echo $search_path = $this->searchForId($fees->month,$monthlyIncome->toArray(), array()); 
			//if($fees->month == array_search($fees->month,$monthlyIncome->toArray()))
			$key = array_search($fees->month, array_column($monthlyIncome->toArray(), 'month'));
			//echo "<br>d" .$key ."d<br>";
			if ($key !== false and $monthlyIncome[$key]->month == $fees->month) {

				//echo  ++$i;
				$comabine_array[] = array('month' => $monthlyIncome[$key]->month, 'amount' => $monthlyIncome[$key]->amount + $fees->paiTotal, 'year' => $monthlyIncome[$key]->year);
				//array_merge($monthlyIncome[$key]->toArray(),$tutionfees->toArray()) ;//$monthlyIncome[$id]->month;

			} else {
				$comabine_array[] = array('month' => $fees->month, 'amount' => $fees->paiTotal, 'year' => $fees->year);
			}
			/*//echo $fees->month;
			if($id >= 0){
				$fdd = true;
			}else{
				$fdd = false;
			}
			echo "<br>" .$fdd ."<br>";
			if($fdd){
				echo '232'. $i++;
			if($monthlyIncome[$id]->month ==$fees->month){
				echo 'sas';
				$comabine_array[] =array_merge($monthlyIncome[$id]->toArray(),$tutionfees->toArray()) ;//$monthlyIncome[$id]->month;
			}
			}*/
		}
		//$explod = "asas,sassa,asaa,sasas,saasa,asassa,asas,asa";
		//$explod = explode(',',$search_path );


		//echo "<pre>xcx";print_r($comabine_array);
		//echo "<pre>xcx";print_r($monthlyIncome);
		//echo "<pre>";print_r($tutionfees->toArray() );
		//exit;

		$monthlyExpences = Accounting::selectRaw('month(date) as month, sum(amount) as amount, year(date) as year')
			->where('type', 'Expence')
			->groupBy('month')
			->get();

		//echo "<pre>";print_r($monthlyExpences->toArray() );exit;
		$incomeTotal  = Accounting::where('type', 'Income')
			->sum('amount');
		$expenceTotal = Accounting::where('type', 'Expence')
			->sum('amount');
		$incomes  = $this->datahelper1($comabine_array);
		$expences = $this->datahelper($monthlyExpences);

		//echo "<pre>";print_r($expences);exit;

		$tutionfeesum = FeeCol::select(DB::RAW('sum(payableAmount) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
			->whereYear('created_at', $year1)
			//->where('regiNo',$request->input('student'))
			->first();
		$incomeTotals = $incomeTotal + $tutionfeesum->paiTotal;
		//echo "<pre>";print_r($incomes);exit;
		$balance      = $incomeTotals - $expenceTotal;

		$monthlyexp   = Accounting::where('type', 'Expence')
			->whereMonth('date', $month)
			->whereYear('date', $year1)
			->sum('amount');
		//echo "<pre>";print_r($monthlyexp);exit;


		$fee_check_status   = FeeCol::join('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')
			->select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,
			IFNULL(sum(total_fee),0) as Totalpay,
			IFNULL(sum(paidAmount),0) as paiTotal,
			IFNULL(sum(adjusted),0) as adjustTotal,
			(IFNULL(sum(total_fee),0)- IFNULL(sum(paidAmount),0)) as dueAmount,
			(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
			->where('month', '=', $month)
			->first();
		// echo "<pre>";
		// print_r($fee_check_status);
		// exit;
		//return View::Make('dashboard',compact('error','success','total','incomes','expences','balance'));

		//paid or unpaid fee list

		$attendances_b = array();
		$scetionarray  = array();
		$resultArray1  = array();
		$student_all12 =	DB::table('section')
			/*->where('session','=',$student->session)*/
			->leftjoin('Student', 'section.id', '=', 'Student.section')
			->leftjoin('stdBill', 'Student.regiNo', '=', 'stdBill.regiNo')
			->leftJoin('billHistory', 'stdBill.billNo', '=', 'billHistory.billNo')
			->select('stdBill.billNo', 'stdBill.payDate', 'stdBill.regiNo')
			->where('Student.isActive', 'Yes')
			->get();

		// echo "<pre>";print_r($student_all);exit;



		//$all_section =	DB::table('section')->select( '*')->get();
		$all_section =	DB::table('Class')->select('*')->get();
		//$student_all =	DB::table('Student')->select( '*')->where('class','=',$request->input('class'))->where('section','=',$request->input('section'))->where('session','=',$student->session)->get();
		$ourallpaid   = 0;
		$ourallunpaid = 0;
		if (count($all_section) > 0) {
			$i = 0;

			foreach ($all_section as $section) {

				$paid    = 0;
				$unpaid  = 0;
				$total_s = 0;
				$student_all =	DB::table('Student')->select('*')->where('class', '=', $section->code)/*->where('section','=',$section->id)/**/->where('session', '=', $year)
					//->where('Student.session','=',$year)
					->where('Student.isActive', '=', 'Yes')
					->get();
				$resultArray[$section->code . '_' . $section->name . "_" . 'total'] = 0;
				$resultArray[$section->code . '_' . $section->name . "_" . 'unpaid'] = 0;
				$resultArray[$section->code . '_' . $section->name . "_" . 'paid'] =  0;
				if (count($student_all) > 0) {
					foreach ($student_all as $stdfees) {
						$student =	DB::table('billHistory')->Join('stdBill', 'billHistory.billNo', '=', 'stdBill.billNo')
							->select('billHistory.billNo', 'billHistory.month', 'billHistory.fee', 'billHistory.lateFee', 'stdBill.class as class1', 'stdBill.payableAmount', 'stdBill.billNo', 'stdBill.payDate', 'stdBill.regiNo', 'stdBill.paidAmount')
							// ->whereYear('stdBill.payDate', '=', 2017)
							->where('stdBill.regiNo', '=', $stdfees->regiNo)
							->where('stdBill.regiNo', '=', $stdfees->regiNo)
							->whereYear('stdBill.payDate', '=', $year1)
							->where('billHistory.month', '=', $month)
							->where('billHistory.month', '<>', '-1')
							// ->where('stdBill.paidAmount', '<>', '0.00')
							->where(function ($query) {
								$query->where('stdBill.paidAmount', '<>', '0.00')
									->orWhere('stdBill.adjusted', '>', 0);
							})
							//->orderby('stdBill.payDate')
							->get();
						if (count($student) > 0) {
							foreach ($student as $rey) {
								//$status[] = "paid".'_'.$stdfees->regiNo."_";
								//$resultArray[$i] = get_object_vars($stdfees);
								//array_push($resultArray[$i],'Paid',$rey->payDate,$rey->billNo,$rey->fee);
								$resultArray[$section->code . '_' . $section->name . "_" . 'paid'] =  ++$paid;
								//$yes ='yes';
								$ourallpaid = ++$ourallpaid;
							}
						} else {
							//$status[$i] = "unpaid".'_'.$stdfees->regiNo."_";
							//$resultArray[] = get_object_vars($stdfees);
							//array_push($resultArray[$i],'unPaid');

							//$resultArray[$section->class_code.'_'.$section->name."_".'paid'] =  0;
							$resultArray[$section->code . '_' . $section->name . "_" . 'unpaid'] = ++$unpaid;
							$ourallunpaid = ++$ourallunpaid;
						}
						$resultArray[$section->code . '_' . $section->name . "_" . 'total'] = ++$total_s;
					}
				} else {
					$resultArray[$section->code . '_' . $section->name . "_" . 'total']  = 0;
					$resultArray[$section->code . '_' . $section->name . "_" . 'unpaid'] = 0;
					$resultArray[$section->code . '_' . $section->name . "_" . 'paid']   =  0;
				}
				//$resultArray[] = get_object_vars($section);
				//array_push($resultArray[$i],$total,$paid,$unpaid);
				$scetionarray[] = array('section' => $section->name, 'class' => $section->code);
				$resultArray1[] = array('total' => $resultArray[$section->code . '_' . $section->name . "_" . 'total'], 'unpaid' => $resultArray[$section->code . '_' . $section->name . "_" . 'unpaid'], 'paid' => $resultArray[$section->code . '_' . $section->name . "_" . 'paid']);
			}
		} else {
			$resultArray = array();
		}

		foreach ($all_section as $teacher) {
			$sections[]     = $teacher->id;
			$count_student1 = array();
			$count_student1 =  DB::table('Student')->select(DB::raw('COUNT(*) as total_student'))->where('class', $teacher->code)->first();
			// $count_student =  $count_student1->total_attendance;
			//$count_student[] =$count_student1->toArray();
			$attendances_a = DB::table('Attendance')
				->join('Class', 'Attendance.class_id', '=', 'Class.id')
				//->join('section', 'Attendance.section_id', '=', 'section.id')
				->select(DB::raw('COUNT(*) as total_attendance,
                           SUM(Attendance.status="Absent") as absent,
                           SUM(Attendance.status="Present" ) as present ,
                           SUM(Attendance.coments="sick_leave" OR Attendance.coments="leave") as leaves'), 'Class.id as class_id', 'Class.name as class')->where('Attendance.session', $year)->where('Attendance.class_id', $teacher->id)->where('date', Carbon::today()->toDateString())->first();
			//$tst[] = $attendances_a[$i]->total_attendance;
			//$attendances_a = $attendances_a + $count_student; 

			if ($attendances_a->total_attendance == 0) {
				$attendances_b[] = array('total_attendance' => 0, 'absent' => 0, 'present' => 0, 'leaves' => 0, 'class_id' => $teacher->id, 'class' => $teacher->name, 'total_student' => $count_student1->total_student);
			} else {
				//$attendances_b[] = array(get_object_vars($attendances_a),'total_student'=>$count_student1->total_student);
				$attendances_b[] = array('total_attendance' => $attendances_a->total_attendance, 'absent' => $attendances_a->absent, 'present' => $attendances_a->present, 'leaves' => $attendances_a->leaves, 'class_id' => $attendances_a->class_id, 'class' => $teacher->name, 'total_student' => $count_student1->total_student);


				//	$attendances_b[] =;
			}

			// $attendances_b['total_student'.'_'.$teacher->section] =$count_student1->total_student; 
			// $attendances_b['76']=65;
			//$merged = $attendances_b->merge($count_student);
			//echo "<pre>";print_r($attendances_b);exit;
			//array_push($attendances_b,$count_student1);//($attendances_b,$count_student1);
			// $resultArray[$i] = $attendances_b;
			//$result[] = $attendances_b + $count_student1;
			// array_push($attendances_b,'rer');
			// $a = array_merge($attendances_b, $count_student1);

			$i++;
		}
		// echo "<pre>";print_r($attendances_b);
		// exit;

		// $test = $resultArray1 + $scetionarray;
		// $result = array_merge_recursive($scetionarray , $resultArray1);
		//echo "<pre>".$ourallpaid;print_r($resultArray1);
		//exit;

		//echo "<pre>";print_r($resultArray1);
		//echo "<pre>";print_r($total);
		$month_n = $now->format('F');
		if ($request->input('month') != "") {
			$month_n = \DateTime::createFromFormat('!m', $request->input('month'))->format('F');
		}

		$class   = array();
		$present = array();
		$absent  = array();
		foreach ($attendances_b as $attendance) :
			$class[]   = $attendance['class'];
			$present[] = $attendance['present'];
			$absent[]  = $attendance['absent'];

		endforeach;
		/// echo "<pre>class";print_r($class);
		//// echo "<pre>p";print_r($present);
		// echo "<pre>a";print_r($absent);whereYear('created_at', '=', date('Y')
		// exit;
		$holidays  = DB::table('Holidays')/*->whereMonth('holiDate',$month)*/->get();
		$class_off = DB::table('ClassOffDay')/*->whereMonth('offDate',$month)*/->get();

		foreach ($holidays as $holiday) {
			$calender_event[] = array('title' => $holiday->description, 'start' => $holiday->holiDate);
		}
		foreach ($class_off as $class_of) {
			$calender_event[] = array('title' => $class_of->description, 'start' => $class_of->offDate);
		}
		//$test = array_merge($class_off->toArray(),$holidays->toArray());
		//echo "<pre>a";print_r($class_off);
		if (!empty($calender_event)) {
			$json_event_data = json_encode($calender_event);
		} else {
			$json_event_data = '';
		}
		//echo $json_event_data;
		//echo "<pre>a";print_r($calender_event);

		//exit;
		$branches = Branch::select("*")->get();
		$cbranches = Branch::count();
		return View('dashboard', compact('error', 'success', 'total', 'incomes', 'expences', 'balance', 'scetionarray', 'resultArray1', 'year', 'month_n', 'attendances_b', 'month', 'class', 'present', 'absent', 'ourallunpaid', 'ourallpaid', 'json_event_data', 'branches', 'cbranches', 'year1', 'fee_check_status', 'monthlyexp'));
	}
	private function datahelper($data)
	{
		$DataKey = [];
		$DataVlaue = [];
		foreach ($data as $d) {
			array_push($DataKey, date("F", mktime(0, 0, 0, $d->month, 10)) . ',' . $d->year);
			array_push($DataVlaue, $d->amount);
		}
		return ["key" => $DataKey, "value" => $DataVlaue];
	}
	private function datahelper1($data)
	{
		$DataKey = [];
		$DataVlaue = [];
		foreach ($data as $d) {
			array_push($DataKey, date("F", mktime(0, 0, 0, $d['month'], 10)) . ',' . $d['year']);
			array_push($DataVlaue, $d['amount']);
		}
		return ["key" => $DataKey, "value" => $DataVlaue];
	}


	function searchForId($search_value, $array, $id_path)
	{

		// Iterating over main array 
		foreach ($array as $key1 => $val1) {

			$temp_path = $id_path;

			// Adding current key to search path 
			array_push($temp_path, $key1);

			// Check if this value is an array 
			// with atleast one element 
			if (is_array($val1) and count($val1)) {

				// Iterating over the nested array 
				foreach ($val1 as $key2 => $val2) {

					if ($val2 == $search_value) {

						// Adding current key to search path 
						array_push($temp_path, $key2);

						return join(",", $temp_path);
					}
				}
			} elseif ($val1 == $search_value) {
				return join(",", $temp_path);
			}
		}

		return null;
	}
}

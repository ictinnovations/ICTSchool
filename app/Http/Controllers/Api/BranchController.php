<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ictcoreController;

//use App\Api_models\User;

use Illuminate\Support\Facades\Auth;

use Validator;
use App\ClassModel;
use App\Message;
use App\Subject;
use App\Attendance;
use App\Student;
use App\Ictcore_integration;
use App\SectionModel;
use DB;
use Excel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class BranchController extends Controller
{

    public function __construct() 
    {

     //  $this->middleware('auth:api');

    }
   public $successStatus = 200;

    /**
    * Count student
    **/
    public function branches_data()
    {
       $tstudent['overall']      =  Student::where('isActive','Yes')->count();
       $tstudent['current']      =  Student::where('isActive','Yes')->where('session',get_current_session()->id)->count();
       $tstudent['fess']         = $this->count_student_fee();
       $tstudent['teachers']     = $this->count_teachers();
       $tstudent['classes']      = ClassModel::count();
       $tstudent['present']      = Attendance::where('date',Carbon::now()->format('Y-m-d'))->where('status','Present')->count();
       $tstudent['absent']       = Attendance::where('date',Carbon::now()->format('Y-m-d'))->where('status','Absent')->count();
       $tstudent['late']         = Attendance::where('date',Carbon::now()->format('Y-m-d'))->where('status','Late')->count();
       $admin_info               = DB::table('users')->where('group','admin')->orderBy('id','Asc')->first();
       $tstudent['admin_id']     = $admin_info->id;
        
        
          return response()->json($tstudent,200);
    }
    public function count_student_fee()
    {
        $now             =  Carbon::now();
        $year            =  get_current_session()->id;
        $year1            =  $now->year;
         $month           =  $now->month;
        $all_section =  DB::table('Class')->select( '*')->get();
        //$student_all =    DB::table('Student')->select( '*')->where('class','=',Input::get('class'))->where('section','=',Input::get('section'))->where('session','=',$student->session)->get();
        $ourallpaid =0;
        $ourallunpaid=0;
        if(count($all_section)>0){
            $i=0;
            
            
          
            foreach($all_section as $section){
                 $paid =0;
                 $unpaid=0;
                 $total_s=0;
             $student_all = DB::table('Student')->select( '*')->where('class','=',$section->code)/*->where('section','=',$section->id)/**/->where('session','=',$year)
              //->where('Student.session','=',$year)
             ->where('Student.isActive','=','Yes')
             ->get();
               $resultArray[$section->code.'_'.$section->name."_".'total']=0;
                $resultArray[$section->code.'_'.$section->name."_".'unpaid']=0;
                $resultArray[$section->code.'_'.$section->name."_".'paid'] =  0;
                if(count($student_all) >0){
                    foreach($student_all as $stdfees){
                        $student =  DB::table('billHistory')->Join('stdBill', 'billHistory.billNo', '=', 'stdBill.billNo')
                        ->select( 'billHistory.billNo','billHistory.month','billHistory.fee','billHistory.lateFee','stdBill.class as class1','stdBill.payableAmount','stdBill.billNo','stdBill.payDate','stdBill.regiNo')
                        // ->whereYear('stdBill.payDate', '=', 2017)
                        ->where('stdBill.regiNo','=',$stdfees->regiNo)->whereYear('stdBill.payDate', '=', $year1)->where('billHistory.month','=',$month)->where('billHistory.month','<>','-1')
                        //->orderby('stdBill.payDate')
                        ->get();
                        if(count($student)>0 ){
                            foreach($student as $rey){
                                //$status[] = "paid".'_'.$stdfees->regiNo."_";
                                //$resultArray[$i] = get_object_vars($stdfees);
                                //array_push($resultArray[$i],'Paid',$rey->payDate,$rey->billNo,$rey->fee);
                                $resultArray[$section->code.'_'.$section->name."_".'paid'] =  ++$paid;
                                //$yes ='yes';
                               $ourallpaid = ++$ourallpaid;
                            }
                        }else{
                            //$status[$i] = "unpaid".'_'.$stdfees->regiNo."_";
                            //$resultArray[] = get_object_vars($stdfees);
                            //array_push($resultArray[$i],'unPaid');
                            
                            //$resultArray[$section->class_code.'_'.$section->name."_".'paid'] =  0;
                            $resultArray[$section->code.'_'.$section->name."_".'unpaid']=++$unpaid;
                            $ourallunpaid =++$ourallunpaid;
                        }
                        $resultArray[$section->code.'_'.$section->name."_".'total']=++$total_s;
                    }
                }else{
                  $resultArray[$section->code.'_'.$section->name."_".'total']=0;
                  $resultArray[$section->code.'_'.$section->name."_".'unpaid']=0;
                  $resultArray[$section->code.'_'.$section->name."_".'paid'] =  0;

                }
            //$resultArray[] = get_object_vars($section);
            //array_push($resultArray[$i],$total,$paid,$unpaid);
            $scetionarray[] = array('section'=>$section->name,'class'=>$section->code);
            $resultArray1[] = array('total'=> $resultArray[$section->code.'_'.$section->name."_".'total'],'unpaid'=>$resultArray[$section->code.'_'.$section->name."_".'unpaid'],'paid'=>$resultArray[$section->code.'_'.$section->name."_".'paid']);

            }
            
        }
        else{
            $resultArray = array();
        }

        return array(['ourallunpaid'=>$ourallunpaid,'ourallpaid'=>$ourallpaid]);
    }

    /**
     * count teachers api
     *
     * @return \Illuminate\Http\Response
     */
    public function count_teachers()
    {
        $teachers = DB::table('teacher')->count();
        return $teachers;
    }
}


	        
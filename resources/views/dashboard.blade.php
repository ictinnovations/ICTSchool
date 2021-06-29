@extends('layouts.master')
@section("style")
<link href="{{ URL::asset('/css/custom.min.css')}}" rel='stylesheet'>
<link href="{{ URL::asset('/font-awesome/css/font-awesome.min.css')}}" rel='stylesheet'>
<link href="{{ URL::asset('/css/theme1.css')}}" rel="stylesheet" media="all">
<link href="{{url('/css/bootstrap-datepicker.css')}}" rel="stylesheet">
<style>
.fc-today{
  background-color: #2AA2E6;
  color:#fff;


}
.fc-button-today
{
  display: none;
}
.green{
  color: #1ABB9C;
}
.homepage-box {
    height: auto !important;
}

</style>
@stop
@section('content')
@if (Session::get('accessdined'))
<div class="alert alert-danger">
  <button data-dismiss="alert" class="close" type="button">×</button>
  <strong>Process Faild.</strong> {{ Session::get('accessdined')}}

</div>
@endif
@if (Session::get('success'))
<div class="alert alert-success">
  <button data-dismiss="alert" class="close" type="button">×</button>
  <strong>Process Success.</strong> {{ Session::get('success')}}<br>

</div>
@endif

<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <!-- /top tiles -->
   @if(Auth::user()->group=='Director') 
    <?php //request()->session()->put('surl', request()->root());?>
@if($cbranches>0)
@foreach($branches as $branch)

<?php 
$get_data = branchesapi($branch->username,$branch->password,$branch->branch_url,'login');
//$get_students = branchesapi($branch->username,$branch->password,$branch->branch_url,'students/count');
//$get_classes = branchesapi($branch->username,$branch->password,$branch->branch_url,'classes/count');

//echo "<pre>";print_r($get_data);exit;
 ?>
 @if(is_object($get_data) && $get_data!='404')
<div class="box col-md-4">
        <div class="box-inner homepage-box">
            <div class="box-header well" data-original-title="">
                <h2><i class="glyphicon glyphicon-list-alt"></i> {{ucwords($branch->branch_name)}}</h2>

                <div class="box-icon">
                    <a href="#" class="btn btn-minimize btn-round btn-default"><i
                            class="glyphicon glyphicon-chevron-up"></i></a>
                    <a href="#" class="btn btn-close btn-round btn-default"><i
                            class="glyphicon glyphicon-remove"></i></a>
                </div>
            </div>
            <div class="box-content row">
                <!-- Begin MailChimp Signup Form -->
                <div class="col-md-6 col-sm-6 col-xs-6">
                  <a data-toggle="tooltip" title="6 new members." class="well top-block" href="#">
                  <i class="glyphicon glyphicon-user blue"></i>

                  <div>Total Student</div>
                  {{--<div>Current Session: @if(is_object($get_data)){{$get_data->current}} @endif </div>
                  <div>OverAll:  @if(is_object($get_data)) {{$get_data->overall}} @endif</div>
                 --}}
                 <div>@if(is_object($get_data)) {{$get_data->overall}} @endif</div>
                  {{--<span class="notification"></span>--}}
                  </a>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-6">
                  <a data-toggle="tooltip" title="6 new members." class="well top-block" href="#">
                  <i class="glyphicon glyphicon-user blue"></i>

                  <div>Total Classes</div>
                  <div>@if(is_object($get_data)) {{$get_data->classes}} @endif</div>
                  
                  </a>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-6">
                  <a data-toggle="tooltip" title="6 new members." class="well top-block" href="#">
                  <i class="glyphicon glyphicon-user blue"></i>

                  <div>Total Teachers</div>
                  <div>@if(is_object($get_data)) {{$get_data->teachers}} @endif</div>
                 
                  </a>
                </div>
                <br/>
                <div class="col-md-6 col-sm-6 col-xs-6">
                  <a data-toggle="tooltip" title="6 new members." class="well top-block" href="#">
                  <i class="glyphicon glyphicon-user blue"></i>

                  <div>Today Attendance</div>
                  <div>Present: @if(is_object($get_data)) {{$get_data->present}} @endif</div>
                  <div>Absent: @if(is_object($get_data)) {{$get_data->absent}} @endif</div>
                 
                  </a>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-6">
                  <a data-toggle="tooltip" title="6 new members." class="well top-block" href="#">
                  <i class="glyphicon glyphicon-user blue"></i>

                  <div>Total Unpaid</div>
                  <div>@if(is_object($get_data)) {{$get_data->fess[0]->ourallunpaid}} @endif</div>
                  
                  </a>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-6">
                  <a data-toggle="tooltip" title="6 new members." class="well top-block" href="#">
                  <i class="glyphicon glyphicon-user blue"></i>
                  <div>Total Paid</div>
                  <div>@if(is_object($get_data)) {{$get_data->fess[0]->ourallpaid}} @endif</div>
                  </a>
                </div>

                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="form-group">
                      <a href="{{$branch->branch_url}}/login/{{$get_data->admin_id}}/{{Auth::user()->id}}" class="btn btn-primary btn-block btn-sm ml-1" target="_blank">More Detail</a>
                  </div>
                </div>
            </div>

        </div>
         
    </div>
    @else






    <div class="box col-md-4">
        <div class="box-inner homepage-box">
            <div class="box-header well" data-original-title="">
                <h2><i class="glyphicon glyphicon-list-alt"></i> {{ucwords($branch->branch_name)}}</h2>

                <div class="box-icon">
                    <a href="#" class="btn btn-minimize btn-round btn-default"><i
                            class="glyphicon glyphicon-chevron-up"></i></a>
                    <a href="#" class="btn btn-close btn-round btn-default"><i
                            class="glyphicon glyphicon-remove"></i></a>
                </div>
            </div>
             <div class="box-content row">
                <!-- Begin MailChimp Signup Form -->
                <div class="col-md-12 col-sm-12 col-xs-12">
                  
                  <h1>Some thing Wrong please contact ictvision</h1>
                  </div>
                  </div>
            </div>
            </div>
    @endif
    @endforeach
    @endif
    </div>

    </div>
    </div>
@endif












 @if(Auth::user()->group!='Director')

<!----------------------- MOnth OR year wise filter --------------------- -->

        <form role="form" id="defulter" name="defulter"  method="get" >

          <div class="row">
            <div class="col-md-12">
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="control-label" for="month">Month</label>

                    <div class="input-group">
                      <span class="input-group-addon"><i class="glyphicon glyphicon-info-sign blue"></i></span>
                      <?php  $data=[
                      '1'=>'January',
                      '2'=>'February',
                      '3'=>'March',
                      '4'=>'April',
                      '5'=>'May',
                      '6'=>'June',
                      '7'=>'July',
                      '8'=>'August',
                      '9'=>'September',
                      '10'=>'October',
                      '11'=>'November',
                      '12'=>'December'
                      ];?>
                      {{ Form::select('month',$data,$month,['class'=>'form-control','id'=>'month','required'=>'true'])}}
                    </div>
                  </div>
                </div>
              {{--<div class="col-md-4">
                <div class="form-group ">
                  <label for="session">session</label>
                  <div class="input-group">

                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i> </span>
                    <input  value="{{date('Y')}}" type="text" id="session" required="true" class="form-control datepicker2" name="session"   data-date-format="yyyy" value="{{$session}}">
                  </div>
                </div>
              </div>--}}
               <div class="col-md-4">
                <div class="form-group ">
                  <label for="session">Year</label>
                  <div class="input-group">

                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i> </span>
                    <input  type="text" value="" id="yeard" required="true" class="form-control datepicker2" name="year"   data-date-format="yyyy" >
                  </div>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label class="control-label" for="">&nbsp;</label>

                  <div class="input-group">
                    <button class="btn btn-primary pull-right" id="btnsave" type="submit"><i class="glyphicon glyphicon-th"></i> Get List</button>

                  </div>
                </div>
              </div>



            </div>
          </div>
          </form>

<!----------------------- End ------------------------------------------- -->


<div class="row m-t-25">
                            <div class="col-sm-6 col-lg-4">
                                <a href="{{url('/class/list')}}">
                                <div class="overview-item overview-item--c3">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-account-o"></i>
                                            </div>
                                            <div class="text">
                                                <h2>{{$total['class']}}</h2>
                                                <span>Classes</span>
                                            </div>
                                        </div>
                                        <div class="overview-chart">
                                            <canvas id="widgetChart1"></canvas>
                                        </div>
                                    </div>
                                </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <a href="{{url('/student/list')}}">
                                <div class="overview-item overview-item--c2">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-shopping-cart"></i>
                                            </div>
                                            <div class="text">
                                                <h2>{{$total['student']}}</h2>
                                                <span>Students</span>
                                            </div>
                                        </div>
                                        <div class="overview-chart">
                                            <canvas id="widgetChart2"></canvas>
                                        </div>
                                    </div>
                                </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                               <a href="{{url('/attendance_detail?action=absent')}}">
                                <div class="overview-item overview-item--c3">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-calendar-note"></i>
                                            </div>
                                            <div class="text">
                                                <h2>{{$total['totalabsent']}}</h2>
                                                <span>Absent Students</span>
                                            </div>
                                        </div>
                                        <div class="overview-chart">
                                            <canvas id="widgetChart3"></canvas>
                                        </div>
                                    </div>
                                </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <a href="{{url('/attendance_detail?action=late')}}">
                                <div class="overview-item overview-item--c4">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-money"></i>
                                            </div>
                                            <div class="text">
                                                <h2>{{$total['totallate']}}</h2>
                                                <span>Late Students</span>
                                            </div>
                                        </div>
                                        <div class="overview-chart">
                                            <canvas id="widgetChart4"></canvas>
                                        </div>
                                    </div>
                                </div>
                                </a>
                            </div>
                             @if(Auth::user()->group=='Admin')
                             <div class="col-sm-6 col-lg-4">
                                <a href="{{url('/fee_detail?action=paid')}}">
                                <div class="overview-item overview-item--c1">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-account-o"></i>
                                            </div>
                                            <div class="text">
                                                <h2>{{$ourallpaid}}</h2>
                                                <span>Fee Paid</span>
                                            </div>
                                        </div>
                                        <div class="overview-chart">
                                            <canvas id="widgetChart50"></canvas>
                                        </div>
                                    </div>
                                </div>
                                </a>
                            </div>

                             <div class="col-sm-6 col-lg-4">
                               <a href="{{url('/fee_detail?action=unpaid')}}">
                                <div class="overview-item overview-item--c2">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-account-o"></i>
                                            </div>
                                            <div class="text">
                                                <h2>{{$ourallunpaid}}</h2>
                                                <span>Fee Unpaid</span>
                                            </div>
                                        </div>
                                        <div class="overview-chart">
                                            <canvas id="widgetChart60"></canvas>
                                        </div>
                                    </div>
                                </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <a href="#">
                                <div class="overview-item overview-item--c3">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-account-o"></i>
                                            </div>
                                            <div class="text">
                                             {{--<h1>{{Voucharcheck()}}dsds</h1>--}}
                                                <h2>{{$fee_check_status->payTotal - $fee_check_status->paiTotal}}</h2>
                                                <span>Pending Amount <small>[{{$month_n}}]</small></span>
                                            </div>
                                        </div>
                                        <div class="overview-chart">
                                            <canvas id="widgetChart1"></canvas>
                                        </div>
                                    </div>
                                </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <a href="#">
                                <div class="overview-item overview-item--c2">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-shopping-cart"></i>
                                            </div>
                                            <div class="text">
                                                <h2>{{$fee_check_status->paiTotal}}</h2>
                                                <span>Paid Amount <small>[{{$month_n}}]</small></span>
                                            </div>
                                        </div>
                                        <div class="overview-chart">
                                            <canvas id="widgetChart2"></canvas>
                                        </div>
                                    </div>
                                </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                               <a href="#">
                                <div class="overview-item overview-item--c3">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-calendar-note"></i>
                                            </div>
                                            <div class="text">
                                                <h2>{{$monthlyexp}}</h2>
                                                <span>Total Expenses <small>[{{$month_n}}]</small></span>
                                            </div>
                                        </div>
                                        <div class="overview-chart">
                                            <canvas id="widgetChart3"></canvas>
                                        </div>
                                    </div>
                                </div>
                                </a>
                            </div>

                            @endif
                        </div>

    {{--<div class="row tile_count text-center">
      <div class="col-md-6 col-sm-6 col-xs-6 tile_stats_count">
         <a href="{{url('/class/list')}}">
        <span class="count_top"><i class="fa fa-2x fa-home green"></i>Class</span>
        <div class="count red">{{$total['class']}}</div>
      </a>
      </div>
         <div class="col-md-6 col-sm-6 col-xs-6 tile_stats_count">
         <a href="{{url('/student/list')}}">
        <span class="count_top"><i class="fa fa-2x fa-users green"></i> Students</span>
        <div class="count blue">{{$total['student']}}</div>
      </a>
      </div>

      <div class="col-md-6 col-sm-6 col-xs-6  tile_stats_countw">
        <a href="{{url('/attendance_detail?action=absent')}}">
        <span class="count_top"><i class="fa fa-2x fa-calendar green"></i> Absent Student <small>(today)</small> </span>
        <div class="count yellow" style="font-size: 40px;">{{$total['totalabsent']}}</div>
      </a>
      </div>

         
      @if(Auth::user()->group=='Admin')
      <div class="col-md-6 col-sm-6 col-xs-6 tile_stats_count">
       <a href="{{url('/fee_detail?action=paid')}}">
        <span class="count_top"><i class="fa fa-2x fa-check-circle green"></i> Fee Paid <small>({{$month_n}})</small></span>
        <div class="count yellow">{{$ourallpaid}}</div>
      </a>
      </div>
      @endif

      <div class="col-md-6 col-sm-6 col-xs-6 tile_stats_countw">
         <a href="{{url('/attendance_detail?action=late')}}">
        <span class="count_top"><i class="fa fa-2x fa-bell-o green"></i>Late Student <small>(today)</small> </span>
        <div class="count blue" style="font-size: 40px;">{{$total['totallate']}}</div>
      </a>
      </div>

       @if(Auth::user()->group=='Admin')
      <div class="col-md-6 col-sm-6 col-xs-6 tile_stats_count">
        <a href="{{url('/fee_detail?action=unpaid')}}">
        <span class="count_top"><i class="fa fa-2x fa-bullhorn green"></i> Fee UnPaid <small>({{$month_n}})</small></span>
        <div class="count blue">{{$ourallunpaid}}</div>
     </a>
      </div>
      @endif

      




    </div>--}}

  
      
    </div>

    </div>

   
     <div class="row">
            <div class="col-md-6">
                <div class="au-card recent-report">
                <div class="box box-primary">
                    <div class="box-body">
                        <!-- THE CALENDAR -->
                        <div id="calendar"></div>
                    </div>
                    <!-- /.box-body -->
                </div>
                </div>
            </div>
            <div class="col-md-6">
            <div class="au-card recent-report">
            @if(request()->getHttpHost()=='localhost' || request()->getHttpHost()=='school.ictcore.org')
            <a href='{{url("attendance/today_delete")}}' class="btn btn-danger">Clear today attendance</a>
            @endif
                <div class="box box-info">
                    <div class="box-body" style="max-height: 342px;">
                        <canvas id="attendanceChart" style="width: 400px; height: 150px;"></canvas>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            </div>
        </div>
       
       
 @if(Auth::user()->group=='Admin')
    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
         <div class="au-card recent-report">
         <div class="au-card recent-report">
         <a href='{{url('/cron/run')}}' class="btn btn-danger">Generate Vouchars <small> Current Month</small></a>
        
         <a href='#' class="btn btn-primary" data-toggle="modal" data-target="#myModal">Generate Vouchars more than One Month <small> Current Month</small></a>
        <h2>Fee Detail <small> {{$month_n}}</small></h2>
         <table id="feeList" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th>Class</th>
                  <th>Number of paid</th>
                  <th>Number of Upaid</th>
                  <th>Number of Student</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
              <?php $i=0; 
              //echo "<pre>".$i;print_r($scetionarray);
              //exit;
              ?>
              @foreach($scetionarray as $section)
               
                <tr>
                  <td>{{$section['section']}}</td>
              
                  <td>{{$resultArray1[$i]['paid']}}</td>
                  <td>{{$resultArray1[$i]['unpaid']}}</td>
                  <td>{{$resultArray1[$i]['total']}}</td>
                  <td><a href="{{url('/fees/classreport?class_id='.$section['class'].'&month='.$month.'&year='.$year1.'&direct=yes')}}">veiw detail</a></td>
                 
                </tbody>
                <?php $i++; ?>
                @endforeach
              </table>
      </div>
      </div>
      @endif
     <?php /* <div class="col-md-6 col-sm-6 col-xs-6">
         <h2>Attendance Detail  <small> today</small></h2>
         <table id="feeList" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th>Class</th>
                  <th>Number of Student</th>
                  <th>Total Attendance</th>
                  <th>Number of Paresnt</th>
                  <th>Number of Absent</th>
                  <th>Number of Leaves</th>
                  <th>Action</th>
                
                </tr>
              </thead>
              <tbody>
              <?php $i=0; 
              //echo "<pre>".$i;print_r($scetionarray);
              //exit;
              ?>
              @foreach($attendances_b as $attendance)
               
                <tr>
                  <td>{{$attendance['class']}}</td>
                  <td>{{$attendance['total_student']}}</td>
                  <td>{{$attendance['total_attendance']}}</td>
                  <td>{{$attendance['present']}}</td>
                  <td>{{$attendance['absent']}}</td>
                  <td> @if($attendance['leaves']==''){{  0 }} @else {{ $attendance['leaves'] }} @endif </td>
                  <td></td>
                 
                </tbody>
                <?php $i++; ?>
                @endforeach
              </table>
      </div> */ ?>
      </div>
      @endif
 



    <!-- /top tiles -->
    <!-- Graph start -->
    @if(Auth::user()->group=='Admin')
   <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Accounting Report<small>(Monthly)</small></h2>
            <label class="total_bal">
              Balance: {{$balance}}
            </label>
            <div class="clearfix"></div>
          </div>
          <div class="x_content"><iframe class="chartjs-hidden-iframe" style="width: 100%; display: block; border: 0px; height: 0px; margin: 0px; position: absolute; left: 0px; right: 0px; top: 0px; bottom: 0px;"></iframe>
            <canvas height="136" id="lineCharttest" width="821" style="width: 821px; height: 136px;"></canvas>
          </div>
        </div>
      </div>

    </div>

    <!-- <h2>
<?php //echo join($incomes['key'], '","')?>
<?php //echo join($incomes['value'], ',')?>

<?php //echo join($expences['value'], ',')?>
</h2> -->
    @endif
@stop
@section("model")
<!-- The Modal -->
<div class="modal" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Select Months</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        <form role="form" id="defulter" action="{{url('/cron/invoices/months')}}" name="defulter"  method="get" >

          <div class="row">
            <div class="col-md-12">
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="control-label" for="month">Month</label>

                    <div class="input-group">
                      <span class="input-group-addon"><i class="glyphicon glyphicon-info-sign blue"></i></span>
                      <?php  $data=[
                      '1'=>'January',
                      '2'=>'February',
                      '3'=>'March',
                      '4'=>'April',
                      '5'=>'May',
                      '6'=>'June',
                      '7'=>'July',
                      '8'=>'August',
                      '9'=>'September',
                      '10'=>'October',
                      '11'=>'November',
                      '12'=>'December'
                      ];?>
                      {{ Form::select('month[]',$data,$month,['class'=>'form-control','id'=>'month','required'=>'true','multiple'=>'true'])}}
                    </div>
                  </div>
                </div>
              {{--<div class="col-md-4">
                <div class="form-group ">
                  <label for="session">Family ID</label>
                  <div class="input-group">

                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i> </span>
                    <input  value="" type="text" id="session" required="true" class="form-control" name="family_id"    value="">
                  </div>
                </div>
              </div>--}}
               <div class="col-md-4">
                <div class="form-group ">
                  <label for="session">Family ID</label>
                  <div class="input-group">

                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i> </span>
                    <input  type="text" value="" id="family_id"  class="form-control" name="family_id"  >
                  </div>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group">
                  <label class="control-label" for="">&nbsp;</label>

                  <div class="input-group">
                    <button class="btn btn-primary pull-right" id="btnsave" type="submit"><i class="glyphicon glyphicon-th"></i>Create Vouchers</button>

                  </div>
                </div>
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
</div>

@stop
@section("script")
<script src="{{url('/js/Chart.min.js')}}"></script>
<script src="{{url('/js/bootstrap-datepicker.js')}}"></script>
<script script type="text/javascript">
 
  $(document).ready(function () {

        $(".datepicker2").datepicker( {
              format: " yyyy", // Notice the Extra space at the beginning
              viewMode: "years",
              minViewMode: "years",
              autoclose:true

            }).on('changeDate', function (ev) {

              //getstudents();

            });
        $('#calendar').fullCalendar({
        header: {
            left: 'prev,next',
            center: 'title',
        },
        today: 'true',
        height: 300,
   <?php if($json_event_data!=''){ ?>
    events: /*[
    {
      title  : 'event1',
      start  : '2018-10-01'
    },
    {
      title  : 'event2',
      start  : '2018-10-05',
      end    : '2018-10-07'
    },
    {
      title  : 'event3',
      start  : '2018-10-09T12:30:00',
      allDay : false // will make the time show
    }
  ]*/
  <?php echo $json_event_data;
   }
   ?>
 
    });

           var ctx = document.getElementById('attendanceChart').getContext('2d');
            //var attendanceChart = new Chart(ctx, config);
            var myChart = new Chart(ctx, {
    type: 'line',
    data: {
          labels: ["<?php echo join($class, '","')?>"],
        datasets: [{
                    label: 'Present',
                    data: ["<?php echo join($present, '","')?>"],
                    backgroundColor:  "rgb(54, 162, 235)",
                    borderColor:  "rgb(54, 162, 235)",
                    fill: false,
                    pointRadius: 6,
                    pointHoverRadius: 20,
                }, {
                    label: 'Absent',
                    data: ["<?php echo join($absent, '","')?>"],
                    backgroundColor: "rgb(255, 99, 132)",
                    borderColor: "rgb(255, 99, 132)",
                    fill: false,
                    pointRadius: 6,
                    pointHoverRadius: 20,

                }
                ]
            },
    options: {
      responsive: true,
       hover: {
                    mode: 'index'
                },
        scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Class'
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Attendace'
                        }
                    }]
                },
                title: {
                    display: true,
                    text: 'Students Today\'s Attendance'
                }
    }
});
        });
  



Chart.defaults.global.legend = {
  enabled: false
};
// Line chart
   var ctx = document.getElementById("lineCharttest");
   var lineCharttest = new Chart(ctx, {
     type: 'line',
     data: {
       labels: ["<?php echo join($incomes['key'], '","')?>"],
       datasets: [{
         label: "Income",
         backgroundColor: "rgba(38, 185, 154, 0.31)",
         borderColor: "rgba(38, 185, 154, 0.7)",
         pointBorderColor: "rgba(38, 185, 154, 0.7)",
         pointBackgroundColor: "rgba(38, 185, 154, 0.7)",
         pointHoverBackgroundColor: "#fff",
         pointHoverBorderColor: "rgba(220,220,220,1)",
         pointBorderWidth: 1,
         data: [<?php echo join($incomes['value'], ',')?>]
       }, {
         label: "Expence",
         backgroundColor: "rgba(3, 88, 106, 0.3)",
         borderColor: "rgba(3, 88, 106, 0.70)",
         pointBorderColor: "rgba(3, 88, 106, 0.70)",
         pointBackgroundColor: "rgba(3, 88, 106, 0.70)",
         pointHoverBackgroundColor: "#fff",
         pointHoverBorderColor: "rgba(151,187,205,1)",
         pointBorderWidth: 1,
         data: [<?php echo join($expences['value'], ',')?>]
       }]
     },
     options: {
          responsive: true
          
          }
          

        
   });


  
</script>
@stop

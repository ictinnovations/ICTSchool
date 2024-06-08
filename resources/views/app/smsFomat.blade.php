@extends('layouts.master')
@section('style')

@stop
@section('content')
    @if (Session::get('success'))
        <div class="alert alert-success">
            <button data-dismiss="alert" class="close" type="button">×</button>
            <strong>Process Success.</strong> {{ Session::get('success')}}

        </div>
    @endif
    <div class="row">
        <div class="box col-md-12">
            <div class="box-inner">
                <div data-original-title="" class="box-header well">
                    <h2><i class="glyphicon glyphicon-user"></i>Bulk SMS Send</h2>

                </div>
                <div class="box-content">
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <strong>Whoops!</strong> There were some problems with your input.<br><br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if($sms)
                        <form role="form" action="{{url('/sms/update')}}" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="{{$sms->id}}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="type">Type</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="glyphicon glyphicon-info-sign blue"></i></span>

                                                {{ Form::select('type',['Attendance'=>'Attendance','Result Publish'=>'Result Publish','Holy Day'=>'Holy Day'],$sms->type,['class'=>'form-control','required'=>'true'])}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="sender">Sender</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="glyphicon glyphicon-info-sign blue"></i></span>
                                                <input type="text" class="form-control" value="{{$sms->sender}}" required name="sender" placeholder="school name,supersoft">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="message">Message</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="glyphicon glyphicon-info-sign blue"></i></span>
                                                <textarea type="text" class="form-control" maxlength="160" required name="message" placeholder="Message 160 letters">{{$sms->message}}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <button class="btn btn-primary pull-right" type="submit"><i class="glyphicon glyphicon-plus"></i>Update</button>
                        </form>
                    @else
                        <form role="form" action="{{url('/sms/create')}}" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="type">Type</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="glyphicon glyphicon-info-sign blue"></i></span>
                                                <select name="type" class="form-control" required>
                                                    <option value="Attendance">Attendance</option>
                                                    <option value="Result Publish">Result Publish</option>
                                                    <option value="Holy Day">Holy Day</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="sender">Sender</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="glyphicon glyphicon-info-sign blue"></i></span>
                                                <input type="text" class="form-control" required name="sender" placeholder="school name,supersoft">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="message">Message</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="glyphicon glyphicon-info-sign blue"></i></span>
                                                <textarea type="text" class="form-control" maxlength="160" required name="message" placeholder="Message 160 letters"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>


                            <button class="btn btn-primary pull-right" type="submit"><i class="glyphicon glyphicon-plus"></i>Add</button>
                            <br>
                        </form>
                    @endif
                    <br>
                </div>


                @if(count($smses)>0)
                    <div class="row">
                        <div class="col-md-12">
                            <table id="smsList" class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Sender</th>
                                    <th>Message</th>

                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($smses as $sms)

                                    <tr>
                                        <td>{{$sms->type}}</td>
                                        <td>{{$sms->sender}}</td>
                                        <td>{{$sms->message}}</td>


                                        <td>
                                            <a title='Edit' class='btn btn-info' href='{{url("/sms/edit")}}/{{$sms->id}}'> <i class="glyphicon glyphicon-edit icon-white"></i></a>&nbsp&nbsp<a title='Delete' class='btn btn-danger' href='{{url("/sms/delete")}}/{{$sms->id}}'> <i class="glyphicon glyphicon-trash icon-white"></i></a>
                                        </td>
                                @endforeach
                                </tbody>
                            </table>

                        </div>
                    </div>
                @endif






            </div>
        </div>
    </div>
    </div>
@stop
@section('script')
    <script type="text/javascript">
        $( document ).ready(function() {
            $('#smsList').dataTable();
        });
    </script>

@stop

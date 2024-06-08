@extends('layouts.master')
@section('content')
@if (Session::get('success'))

<div class="alert alert-success">
  <button data-dismiss="alert" class="close" type="button">×</button>
    <strong>Process Success.</strong> {{ Session::get('success')}}<br><a href="#">View List</a><br>

</div>
@endif
<div class="row">
<div class="box col-md-12">
        <div class="box-inner">
            <div data-original-title="" class="box-header well">
                <h2><i class="glyphicon glyphicon-home"></i> Diary Create</h2>

            </div>
            <div class="box-content">
              <form role="form" action="{{url('/teacher/diary')}}" method="post">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="id" value="{{ $teacher_id }}">

                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group">
                            <label for="name">Class</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-info-sign blue"></i></span>
                                <select name="class" id="class" required class="form-control">
                                    @if($teachers_class)
                                    @foreach($teachers_class as $class)
                                      <option value="{{$class->code}}">{{$class->name}}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                            <label for="name">Sections</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-info-sign blue"></i></span>
                                {{--<input type="text" class="form-control" autofocus required name="name" placeholder="Class Name">--}}
                                  <select name="section[]" id="section" required class="form-control selectpicker" multiple data-hide-disabled="true" data-size="5">
                                    <option value="">---Select Section---</option>
                                </select>
                            </div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                            <label for="name">Subjects</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-info-sign blue"></i></span>
                                {{--<input type="text" class="form-control" autofocus required name="name" placeholder="Class Name">--}}
                                <select name="subject" id="subject" required class="form-control">
                                    <option value="">---Select Subject---</option>
                                </select>
                            </div>
                        </div>
                      </div>
                    </div>
                  
                    <div class="form-group">
                        <label for="name">Diary</label>
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <textarea type="text" class="form-control" name="description" id="summary-ckeditor" placeholder="Diary"></textarea>
                        </div>
                    </div>
                    <div class="clearfix"></div>
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
                    <div class="form-group">
                      <button class="btn btn-primary pull-right" type="submit"><i class="glyphicon glyphicon-plus"></i>Add</button>
                      <br>
                  </div>
                </form>






        </div>
    </div>
</div>
</div>
<script src="{{ asset('vendor/unisharp/laravel-ckeditor/ckeditor.js') }}"></script>
<script>
    //CKEDITOR.replace( 'summary-ckeditorc' );
    //$('.selectpicker').selectpicker();
      getsections();
      getSubjects();
    //var getSubjects = function () 
    function getSubjects()
    {
      var val        = $('#class').val();
      var teacher_id = {{$teacher_id}};

       // alert(val);
      $.ajax({
        url:"{{url('/teacher/getsubjects')}}"+'/'+val+'/'+teacher_id,
        type:'get',
        dataType: 'json',
        success: function( json ) {


          $('#subject').empty();
          $('#subject').append($('<option>').text("--Select Subject--").attr('value',""));
          $.each(json, function(i, subject) {
             console.log(subject);

            $('#subject').append($('<option>').text(subject.name).attr('value', subject.code));
          });
        }
      });
    };

function getsections()
{
    var aclass     = $('#class').val();
    var teacher_id = {{$teacher_id}};
   // alert(aclass);
    $.ajax({
      url: "{{url('/teacher/getsections')}}"+'/'+aclass+'/'+teacher_id,
      data: {
        format: 'json'
      },
      error: function(error) {
        //alert("Please fill all inputs correctly!");
      },
      dataType: 'json',
      success: function(data) {
        $('#section').empty();
      // $('#section').append($('<option>').text("--Select Section--").attr('value',""));
      var options = [];
        $.each(data, function(i, section) {
          //console.log(section);
            var opt="<option value='"+section.id+"'>"+section.name + " </option>"
          //console.log(opt);
          //$('#section').append(opt);
          options.push(opt);

        });
        $("#section").html(options).selectpicker('refresh');
        //console.log(data);

      },
      type: 'GET'
    });
};
</script>
@stop

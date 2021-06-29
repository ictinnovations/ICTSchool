<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\ClassModel;
use App\Question;
use App\QuestionTemp;
use App\Subject;
use Carbon\Carbon;
use DB;
Class formfoo1{

}
class QuestionController extends Controller
{
    /**
    * Show the form for creating a new quiz event.
    *
    * @return \Illuminate\Http\Response
    */
    public function create(){
        $classes = ClassModel::all();

        /*$query =DB::table('Student1')
        ->join('Class','Student.class','=','Class.name')
        ->join('section','Student.section','=','section.name')
        ->join('feesSetup','Student.class','=','feesSetup.class')
        ->select('Student.*','Class.name as class','section.name as section','feesSetup.fee')
        ->where('Student.family_id','23232')
        ->get();*/
        return view('app.question', compact('classes'));
    }

    /**
    * Store a newly created quiz event in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {

        $rules=[

            'q_name' => 'required',
            'class_id' => 'required',
            'question.*' => 'required',
            'session' => 'required',
            'chapter' => 'required',
            'level' => 'required',
        ];

       /*$messsages = array(
        'lname.required'=>'The Last Name field is required',
        'fname.required'=>'The First Name field is required',
        
        );*/

    

        $validator = \Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            return Redirect::to('/question/create')->withErrors($validator)->withInput();
        }
        else {
            $quiz_name  = $request->input('q_name');
            $class_code = $request->input('class_id');

            $questions  = $request->input('question'); //Question
            $types      = $request->input('qt'); //Question types

            $i    = $request->input('i'); //Correct answer for identification
            $mc   = $request->input('mc'); //Choices for multiple choice
            $c_mc = $request->input('c-mc'); //Correct choice
            $tf   = $request->input('tf'); //Correct answer for true or false
            $p    = $request->input('points'); //Question point

           /* Questionnaire::create([
                'questionnaire_name' => $quiz_name,
            ]);*/

            //$q_id = Questionnaire::count(); //Questionnaire id.

            for($x = 0; $x < count($questions); $x++){
                $question = $questions[$x];
                $choices = ""; //For multiple choice use.
                $answer = null; //Obviously.
                $points = $p[$x];

                if($types[$x] == 0){
                    //ERROR
                }else if ($types[$x] == 1){//Identification
                    $answer = $i[$x];
                }else if($types[$x] == 2){//Multiple choice
                    $choices = $mc[$x][0] . ";" . $mc[$x][1] . ";" . $mc[$x][2] . ";" . $mc[$x][3];
                    $answer = $c_mc[$x];
                }else if($types[$x] == 3){//True or False
                    $answer = $tf[$x];
                }

                if(trim($question) == "" || is_null($question))
                    continue;
                     //echo $question;
                         //print_r(Question::all());exit;
                Question::create([
                   // 'questionnaire_id'  => $q_id,
                    'quize_name'     => $quiz_name,
                    'question_name'     => $question,
                    'session'           => $request->input('session'),
                    'class_code'        => $request->input('class_id'),
                    'subject_id'        => $request->input('subject'),
                    'chapter'           => $request->input('chapter'),
                    'level'             => $request->input('level'),
                    'question_type'     => $types[$x],
                    'choices'           => $choices,
                    'answer'            => $answer,
                    'points'            => $points
                ]);
               // exit;
            }

            /*QuizEvent::create([
                'quiz_event_name' => $quiz_name,
                'questionnaire_id' => $q_id,
                'class_id' => $class_code,
                'quiz_event_status' => 0,
            ]);*/

            return Redirect::to('/question/create')->with("success","Paper Created Succesfully.");
        }
    }


    public function generate()
    {
    	$formdata = new formfoo1;
		$formdata->class="";
		$formdata->section="00";
		$formdata->shift="";
		$formdata->exam="";
		$formdata->session="";
		$formdata->type="";
    	$classes = ClassModel::all();
    	$students =array();
    	return view('app.generatepaper',compact('classes','formdata','students'));
    }

    public function post_generate(Request $request)
    {
    	$getmcqs = Question::where('class_code',$request->class)
    						->where('subject_id',$request->subject) 
    						->whereIn('chapter',$request->chapter) 
    						->where('session',$request->session) 
    						->where('question_type',2)
    						->whereIn('level',$request->level) 
    						->orderByRaw('RAND()')
    						->take($request->mcqs)
    						->get();
    	$getshorts = Question::where('class_code',$request->class)
    						->where('subject_id',$request->subject) 
    						->whereIn('chapter',$request->chapter) 
    						->where('session',$request->session) 
    						->where('question_type',3)
    						->whereIn('level',$request->level) 
    						->orderByRaw('RAND()')
    						->take($request->short)
    						->get();
		$getlongs = Question::where('class_code',$request->class)
					->where('subject_id',$request->subject) 
					->whereIn('chapter',$request->chapter) 
					->where('session',$request->session) 
					->where('question_type',1)
					->whereIn('level',$request->level) 
					->orderByRaw('RAND()')
					->take($request->long)
					//->inRandomOrder()
					->get();
        //INSERT INTO connection2.table (SELECT * from connection1.table);
    	QuestionTemp::truncate();
    	//echo "<pre>";print_r($getmcqs->toArray());
    	if($getmcqs){
	    	foreach ($getmcqs->toArray() as $item) 
	        {
	           unset($item['id']); 
	            $item['created_at'] = Carbon::now();
	            $item['updated_at'] = Carbon::now();
	            //echo "<pre>";print_r($item);
	            QuestionTemp::insert($item);
	        }
    	}
    	if($getshorts){
	        foreach ($getshorts->toArray() as $item) 
	        {
	           unset($item['id']); 
	            $item['created_at'] = Carbon::now();
	            $item['updated_at'] = Carbon::now();
	            //echo "<pre>";print_r($item);
	            QuestionTemp::insert($item);
	        }
    	}
    	if($getlongs){
	        foreach ($getlongs->toArray() as $item) 
	        {
	           unset($item['id']); 
	            $item['created_at'] = Carbon::now();
	            $item['updated_at'] = Carbon::now();
	            //echo "<pre>";print_r($item);
	            QuestionTemp::insert($item);
	        }
    	}
        $gmcqs = array();
        //echo $request->print;
        for($i=0;$i<$request->print;$i++){

        	$tempararymcq   = QuestionTemp::where('question_type',2)->orderByRaw('RAND()')->get();
        	$tempararylong  = QuestionTemp::where('question_type',1)->get();
        	$tempararyshort = QuestionTemp::where('question_type',3)->get();
    		//echo $i;
    		//echo "===============================================================================================";
    		//$gmcqs[$i];
    		foreach ($tempararymcq as $items){

    			$gmcqs[$i][] = $items;
    		}
    		foreach ($tempararylong as $items){

    			$gmcqs[$i][] = $items;
    		}
    		foreach ($tempararyshort as $items){

    			$gmcqs[$i][] = $items;
    		}
    		//echo "===============================================================================================";
    	}
    	if(empty($gmcqs)){
    		return Redirect::back()->withInput()->with("error", "No questions found");
    	}
    	//echo "<pre>".count($gmcqs);exit;
    	return view('app.printpaper',compact('gmcqs'));
    	//echo "<pre>";print_r(array_rand($getmcqs->toArray()));
    	//foreach()
    }

    public function list()
    {
    	$formdata = new formfoo1;
		$formdata->class="";
		$formdata->section="00";
		$formdata->shift="";
		$formdata->exam="";
		$formdata->session="";
		$formdata->type="";
    	$classes = ClassModel::all();
    	$questions =array();
    	return view('app.paperlist',compact('formdata','classes','questions'));
    }
    public function getlist(Request $request)
    {
    	$formdata = new formfoo1;
		$formdata->class="";
		$formdata->section="00";
		$formdata->shift="";
		$formdata->exam="";
		$formdata->session="";
		$formdata->type="";
    	$classes = ClassModel::all();
    	//$students =array();

    	$questions = Question::join('Subject','questions.subject_id','=','Subject.id')
    						->select('questions.*','Subject.name')
    						->where('class_code',$request->class)
    						->where('subject_id',$request->subject) 
    						->whereIn('chapter',$request->chapter) 
    						->where('session',$request->session) 
    						//->where('question_type',2)
    						->whereIn('level',$request->level) 
    						->orderBy('question_type','ASC')
    						//->take($request->mcqs)
    						->get();
    	return view('app.paperlist',compact('formdata','classes','questions'));
    }

    public function edit($id)
    {
    	$formdata = new formfoo1;
		$formdata->class="";
		$formdata->section="00";
		$formdata->shift="";
		$formdata->exam="";
		$formdata->session="";
		$formdata->type="";
    	$classes = ClassModel::all();
    	
    	$questions = Question::where('id',$id)
    						->first();
    	$subjects = Subject::where('class',$questions->class_code)->get();
    	
    	return view('app.questionedit',compact('formdata','classes','questions','subjects'));
    }

    public function update(Request $request)
    {
    	/*$rules=[

            'q_name' => 'required',
            'class_id' => 'required',
            'question.*' => 'required',
            'session' => 'required',
            'chapter' => 'required',
            'level' => 'required',
        ];
        $validator = \Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            return Redirect::to('/question/create')->withErrors($validator)->withInput();
        }
        else {*/


        $quiz_name = $request->input('q_name');
        $class_code = $request->input('class_id');

        $questions = $request->input('question'); //Question
        $types = $request->input('qt'); //Question types

        $i = $request->input('i'); //Correct answer for identification
        $mc = $request->input('mc'); //Choices for multiple choice
        $c_mc = $request->input('c-mc'); //Correct choice
        $tf = $request->input('tf'); //Correct answer for true or false

        $p = $request->input('points'); //Question point

       /* Questionnaire::create([
            'questionnaire_name' => $quiz_name,
        ]);*/

        //$q_id = Questionnaire::count(); //Questionnaire id.

        for($x = 0; $x < count($questions); $x++){
            $question = $questions[$x];
            $choices = ""; //For multiple choice use.
            $answer = null; //Obviously.
            $points = $p[$x];

            if($types[$x] == 0){
                //ERROR
            }else if ($types[$x] == 1){//Identification
                $answer = $i[$x];
            }else if($types[$x] == 2){//Multiple choice
                $choices = $mc[$x][0] . ";" . $mc[$x][1] . ";" . $mc[$x][2] . ";" . $mc[$x][3];
                $answer = $c_mc[$x];
            }else if($types[$x] == 3){//True or False
                $answer = $tf[$x];
            }

            if(trim($question) == "" || is_null($question))
                continue;
//echo $question;
//print_r(Question::all());exit;
            Question::where('id', $request->id)->update([
               // 'questionnaire_id'  => $q_id,
                'quize_name'     => $quiz_name,
                'question_name'     => $question,
                'session'           => $request->input('session'),
                'class_code'        => $request->input('class_id'),
                'subject_id'        => $request->input('subject'),
                'chapter'           => $request->input('chapter'),
                'level'             => $request->input('level'),
                'question_type'     => $types[$x],
                'choices'           => $choices,
                'answer'            => $answer,
                'points'            => $points
            ]);
            //$question = Question::find($request->id);
           // exit;
        }

        /*QuizEvent::create([
            'quiz_event_name' => $quiz_name,
            'questionnaire_id' => $q_id,
            'class_id' => $class_code,
            'quiz_event_status' => 0,
        ]);*/

        return Redirect::to('/question/list')->with("success","Question Updated Succesfully.");
    }

    public function delete($id)
    {
    	$del =  Question::where('id', $id)->delete();
    	return Redirect::to('/question/list')->with("success","Question Deleted Succesfully.");

    }

    public function chapters(Request $request,$class){
    	          $getmcqs = Question::where('class_code',$request->class)
    						->where('subject_id',$request->subject) 
    						->groupBy('chapter')
    						->get();
    	return $getmcqs;
    }

}

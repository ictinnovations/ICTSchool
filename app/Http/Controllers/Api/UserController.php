<?php

namespace App\Http\Controllers\Api;

use DB;
use Hash;
use Excel;
use Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\SectionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    public function __construct()
    {

        //  $this->middleware('auth:api');
    }
    public $successStatus = 200;
    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if ($request->input('email') != '') {
            $user = $request->input('email');
            $parameter = 'email';
        }
        if ($request->input('username') != '') {
            $user = $request->input('username');
            $parameter = 'login';
        }
        if (Auth::attempt([$parameter => $user, 'password' => $request->input('password')])) {
            
            $user = Auth::user();
            // $success['token'] =  $user->createToken('MyApp')->accessToken;
            $success['token'] =  $user->createToken('DefaultToken')->plainTextToken;
            if ($user->group == 'Teacher') {
                // echo"<pre>";print_r($user);exit; 
                $success['teacher_id'] =  $user->group_id;
            }
            return response()->json(['success' => $success], $this->successStatus);
        } else {
            return response()->json(['error' => 'Unauthorised'], 400);
        }
    }
    /**
     * profile api
     *
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }
    /**
     * get_user api
     *@param  int  $user_id
     * @return \Illuminate\Http\Response
     */
    public function get_user($user_id)
    {
        //dd($user_id);
        // $user = Auth::user();
        $user = DB::table('users')->select('id', 'firstname', 'lastname', 'desc', 'login', 'email', 'group')->where('id', '=', $user_id)->first();
        if (!is_null($user)) {
            return response()->json($user, $this->successStatus);
        } else {
            return response()->json(['error' => 'Student not found'], 404);
        }
    }

    /**
     * get_user api
     *@param  int  $user_id
     * @return \Illuminate\Http\Response
     */
    public function get_alluser()
    {
        //dd($user_id);
        // $user = Auth::user();
        $user = DB::table('users')->select('id', 'firstname', 'lastname', 'phone', 'desc', 'login', 'group');

        $user->when(request('group', false), function ($q, $group) {
            return $q->where('group', $group);
        });

        $user = $user->paginate(20);
        if (count($user) > 0) {
            return response()->json($user, $this->successStatus);
        } else {

            return response()->json(['error' => 'Student not found'], 404);
        }
    }
    public function create_user(Request $request)
    {
        $rules = [
            'firstname' => 'required',
            'fatherphone' => 'required',
            'username' => 'required|unique:users,login',
            'password' =>  'required',
            'regiNo' =>  'required|unique:users',
        ];
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        } else {

            $student = Student::where('regiNo', $request->input('regiNo'))->first();
            if (!empty($student) && $student->regiNo != '') {
                /* $chk_studnet  = User::where('regiNo',$request->input('regiNo'))->first();
                if(!empty($chk_studnet)){
                return response()->json(['error'=>422,'message'=>'Already Register'], 422);

                }*/
                //$user = User::select('id','firstname','lastname','phone','desc','login','group')->where('id',$user_id)->first();
                $user = new User;
                $user->firstname = $request->input('firstname');
                $user->lastname = $request->input('lastname');
                if ($request->input('lastname') == '') {
                    $user->lastname = '';
                }
                $user->login = $request->input('username');
                $user->phone = $request->input('fatherphone');
                $user->email = $request->input('email');
                if ($request->input('email') == '') {
                    $user->email = NULL;
                }
                $user->password = Hash::make($request->input('password'));
                $user->group = "Student";
                $user->group_id = $student->id;
                $user->regiNo = $request->input('regiNo');
                $user->access = 1;
                $user->save();
                return response()->json($student->id, $this->successStatus);
            } else {
                return response()->json(['error' => 404, 'message' => 'Student Not Found with This Registration n0'], 404);
            }
        }
    }
    public function put_user(Request $request, $user_id)
    {
        //dd($user_id);
        $rules = [
            'firstname' => 'required',
            'lastname' => 'required',
            'phone' => 'required',
            'login' => 'required',
            //'password'=>  'required',
            'group'   => 'required'
        ];
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        } else {
            //$user = User::find($user_id);
            $user = User::select('id', 'firstname', 'lastname', 'phone', 'desc', 'login', 'group')->where('id', $user_id)->first();
            $user->firstname = $request->input('firstname');
            $user->lastname = $request->input('lastname');
            $user->login = $request->input('login');
            //  $user->email = $request->input('email');
            $user->phone = $request->input('phone');
            if ($request->input('password') != '') {
                $user->password = Hash::make($request->input('password'));
            }
            $user->group = $request->input('group');
            $user->save();
            return response()->json($user, $this->successStatus);
        }
    }

    /**
     * logout api
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        $this->guard()->logout();

        $request->session()->flush();

        $request->session()->regenerate();

        $json = [
            'success' => true,
            'code' => 401,
            'message' => 'You are Logged out.',
        ];
        return response()->json($json);
    }


    /**
     * attendance api
     *
     * @return \Illuminate\Http\Response
     */
    protected function guard()
    {
        return Auth::guard('api');
    }
}

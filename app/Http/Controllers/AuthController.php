<?php

namespace App\Http\Controllers;

use App\Models\Oj;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(){  // constructor
        $this->middleware('auth:api', ['except' => ['login', 'register', 'checkEmail', 'checkUsername', 'addCf', 'addUva']]);
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'password'  =>  'required|string|min:6'
        ]);

        if($validator -> fails()){
            return response()->json($validator->errors(), 400);
        }

        $token_validity = 24 * 60;

        $this->guard()->factory()->setTTL($token_validity);

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = $this->guard()->user();



        return response()->json([
            'name'  => $user->name,
            'username'  => $user->username,
            'timezone'  => $user->timezone,
            'token' => $token
        ], 200);
    }

    public function checkEmail(Request $request){
        $email = $request->email;
        $res = User::where('email', $email)->first();
        if($res){
            return response()->json(["status" => "FOUND"], 409);
        }
        return response()->json(["status" => "NOT FOUND"], 200);
    }

    public function checkUsername(Request $request){
        $username = $request->username;
        $res = User::where('username', $username)->first();
        if($res){
            return response()->json(["status" => "FOUND"], 409);
        }
        return response()->json(["status" => "NOT FOUND"], 200);
    }


    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name'      =>  'required|string|between:2,100',
            'email'     =>  'required|email|unique:users',
            'username'  =>  'required|string|unique:users|min:5', 
            'password'  =>  'required|string|min:6'
        ]);
        if($validator -> fails()){
            return response()->json(['status' => 'FAILED'], 422);
        }
        $user = User::create(array_merge($validator->validated(), 
        ['password'  =>  bcrypt($request->password)]));

        if($request->has('codeforces')){
            $this->addCf($request);
        }
        if($request->has('uva')){
            $this->addUva($request);
        }

        return response()->json(['status' => 'CREATED SUCCESSFULLY'], 201);
    }

    private function addCf(Request $request){
        $oj = new Oj;
        $oj->username = $request->username;
        $oj->ojName = "CF";
        $oj->ojid = $request->codeforces;
        $oj->save();
    }

    private function addUva(Request $request){
        $oj = new Oj;
        $oj->username = $request->username;
        $oj->ojName = "UVA";
        $oj->ojid = $request->uva;
        $oj->save();
    }

    public function logout(){
        
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh(){
        return $this->respondWithToken($this->guard()->refresh());
    }


    protected function respondWithToken($token){
        $token_validity = 24 * 60;

        $this->guard()->factory()->setTTL($token_validity);
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'token_validity' => $this->guard()->factory()->getTTL()*60
        ]);
    }

    protected function guard(){
        return Auth::guard();
    }
}

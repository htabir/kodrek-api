<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(){  // constructor
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
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

        return $this->respondWithToken($token);
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name'      =>  'required|string|between:2,100',
            'email'     =>  'required|email|unique:users',
            'username'  =>  'required|string|unique:users|min:5', 
            'password'  =>  'required|string|min:6'
        ]);
        if($validator -> fails()){
            return response()->json($validator->errors(), 422);
        }
        $user = User::create(array_merge($validator->validated(), 
        ['password'  =>  bcrypt($request->password)]));

        return response()->json(['message' => 'User Created Successfully', 'user' => $user]);
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

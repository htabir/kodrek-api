<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UvaController extends Controller
{
    protected $user;

    public function __construct(){
        $this->middleware('auth:api', ['except' => ['check']]);
        $this->user = $this->guard()->user();
    }

    public function check($id){
        $response = Http::get('https://uhunt.onlinejudge.org/api/uname2uid/'.$id);
        if($response == "0"){
            return response()->json([
                'oj'    =>  "UVA",
                'id'    =>  0,
                'status' => "FAILED"
            ]);
        }else{
            return response()->json([
                'oj'    =>  "UVA",
                'id'    =>  $response,
                'status' => "OK"
            ]);
        }
    }

    public function total($time, $id){
        
    }

    protected function guard(){
        return Auth::guard();
    }
}

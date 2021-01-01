<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $response = Http::get('https://uhunt.onlinejudge.org/api/subs-user/'.Http::get('https://uhunt.onlinejudge.org/api/uname2uid/'.$id));
        $date =  strtotime(date("Y-m-d").' '.'0:6:0');      // setting date for today
        $totalSub = 0;  $ac = 0; $wa = 0; $others = 0;      // counter
        $stats=array();     // list of accepted problems
        $unStats=array();   // list of unsolved
        //$num = Http::get('https://uhunt.onlinejudge.org/api/p/id/1415');
        
        foreach($response['subs'] as $subs){
            if($time == "today" && $subs[4] < $date){   // condition for today
                continue;
            }
            $totalSub++;
            $id = 'uva'.$subs[1];

            if(!(array_key_exists($id,$stats))){
                if($subs['2'] == 90){
                    $stats[$id] = "OK";
                }
            }

            if($subs[2] == 90){
                $ac++;
                if(array_key_exists($id,$stats) && array_key_exists($id,$unStats)){
                    unset($unStats[$id]);
                }
            }else if($subs[2] == 70){
                $wa++;
                if(!(array_key_exists($id,$stats))){
                    $unStats[$id] = "WRONG_ANSWER";
                }
            }else{
                $others++;
                if(!(array_key_exists($id,$stats))){
                    $unStats[$id] = "OTHERS";
                }
            }

        }

        return response()->json([
            'ac_' => count($stats),
            'totalSubmission'   =>  $totalSub,
            'accepted'  =>  $ac,
            'wrong_answer'  =>  $wa,
            'others'  =>  $others,
            'solvedSet'   => $stats,
            'unsolvedSet'   => $unStats
        ]);
        
    }

    protected function guard(){
        return Auth::guard();
    }
}

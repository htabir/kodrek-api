<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class CodeforcesController extends Controller
{
    protected $user;

    public function __construct(){
        $this->middleware('auth:api', ['except' => ['check']]);
        $this->user = $this->guard()->user();
    }

    public function check($id){
        $response = Http::get('https://codeforces.com/api/user.info?handles='.$id);
        return $response;
    }

    public function total($time, $id){
        $date =  strtotime(date("Y-m-d").' '.'0:6:0');
        $response = Http::get('https://codeforces.com/api/user.status?handle='.$id);
        $totalSub = 0;  $ac = 0; $wa = 0; $others = 0;
        $stats=array();

        foreach($response['result'] as $subs){
            if($time == "today" && $subs['creationTimeSeconds'] < $date){
                break;
            }
            $totalSub++;
            if(isset($subs['contestId'])){
                $id = $subs['contestId'].$subs['problem']['index'];
            }else{
                $id = $subs['problem']['problemsetName'].$subs['problem']['index'];
            }

            if($subs['verdict'] == "OK"){
                $ac++;
            }else if($subs['verdict'] == "WRONG_ANSWER"){
                $wa++;
            }else{
                $others++;
            }

            if(array_key_exists($id,$stats)){
                continue;
            }else{
                if($subs['verdict'] == "OK"){
                    $stats[$id] = $subs['verdict'];
                }                
            }            
        }
        
        return response()->json([
            'ac_' => count($stats),
            'totalSubmission'   =>  $totalSub,
            'accepted'  =>  $ac,
            'wrong_answer'  =>  $wa,
            'others'  =>  $others,
            'set'   => $stats
        ]);
    }


    protected function guard(){
        return Auth::guard();
    }

}

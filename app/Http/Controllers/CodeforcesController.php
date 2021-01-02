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
        
        
    }

    public function total($time, $id){
        $date =  strtotime(date("Y-m-d").' '.'0:6:0');      // setting date for today
        $response = Http::get('https://codeforces.com/api/user.status?handle='.$id); // response for a user id
        
        $totalSub = 0;  $ac = 0; $wa = 0; $others = 0;      // counter
        $stats=array();     // list of accepted problems
        $unStats=array();   // list of unsolved


        foreach($response['result'] as $subs){

            if($time == "today" && $subs['creationTimeSeconds'] < $date){   // condition for today
                break;
            }

            $totalSub++;// submission counter
            if(isset($subs['contestId'])){
                $id = $subs['contestId'].$subs['problem']['index'];
            }else{
                $id = $subs['problem']['problemsetName'].$subs['problem']['index'];
            }

            if(!(array_key_exists($id,$stats))){
                if($subs['verdict'] == "OK"){
                    $stats[$id] = $subs['verdict'];
                }
            } 
            
            if($subs['verdict'] == "OK"){
                $ac++;
                $stats[$id] = $subs['verdict'];
                unset($unStats[$id]);
            }else if($subs['verdict'] == "WRONG_ANSWER"){
                $wa++;
                $unStats[$id] = $subs['verdict'];
            }else{
                $others++;
                $unStats[$id] = "OTHERS";
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

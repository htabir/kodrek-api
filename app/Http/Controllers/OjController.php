<?php

namespace App\Http\Controllers;

use App\Models\Oj;
use App\Models\User;
use App\Models\UvaProblem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class OjController extends Controller
{
    protected $user;

    public function __construct(){
        $this->middleware('auth:api', ['except' => ['refreshUva', 'checkCf', 'checkUva']]);
        $this->user = $this->guard()->user();
    }

    public function checkCf(Request $request){
        $username = $request->codeforces;
        $response = Http::get('https://codeforces.com/api/user.info?handles='.$username);
        if($response['status'] == "OK"){
            return response()->json(["status" => "OK"], 200);
        }
        return response()->json(["status" => "FAILED"], 404);
    }

    public function checkUva(Request $request){
        $username = $request->uva;
        $response = Http::get('https://uhunt.onlinejudge.org/api/uname2uid/'.$username);
        if(json_decode($response)){
            return response()->json(["status" => "OK"], 200);
        }
        return response()->json(["status" => "FAILED"], 404);
    }

    public function addCf($id){
        $oj = new Oj;
        $oj->username = $this->user->username;
        $oj->ojName = "CF";
        $oj->ojid = $id;
        $oj->save();
        return response()->json(['status' => 'ok', 'OJ' => $oj]);
    }

    public function addUva($id){
        $oj = new Oj;
        $oj->username = $this->user->username;
        $oj->ojName = "UVA";
        $oj->ojid = $id;
        $oj->save();
        return response()->json(['status' => 'ok', 'OJ' => $oj]);
    }

    public function cfStats($id){

        $stats = Oj::where('username', $id)
                    ->where('ojname', 'CF')->first();

        $id = $stats['ojid'];
        
        $newCheckpoint = 0;
        $oldCheckpoint = $stats['checkpoint'];
        $totalSub = $stats['totalSub'];   $totalAc = $stats['totalAc']; 
        $totalWa = $stats['totalWa'];     $totalOt = $stats['totalOt'];

        $disAc = ($stats['solvedSet'] == null) ? array() : $stats['solvedSet'];
        $disUn = ($stats['unsolvedSet'] == null) ? array() : $stats['unsolvedSet'];

        $count = 1; $flag = 1; $gap = $oldCheckpoint == 0 ? 5000 : 25;
        
        while($flag){
            $response = Http::get('https://codeforces.com/api/user.status?handle='.$id.'&from='.$count.'&count='.($count+$gap));

            if($response['result'] == null){
                break;
            }

            foreach($response['result'] as $subs){

                if($subs['creationTimeSeconds'] <= $oldCheckpoint){
                    $flag = 0;
                    break;
                }

                if($newCheckpoint == 0 ){
                    $newCheckpoint = $subs['creationTimeSeconds'];
                }
                   
                $totalSub++;
                $probId = "";
                if(isset($subs['contestId'])){
                    $probId = $subs['contestId'].$subs['problem']['index'];
                }else{
                    $probId = $subs['problem']['problemsetName'].$subs['problem']['index'];
                }


                if($subs['verdict'] == "OK"){
                    $totalAc++;
                    if(!isset($disAc[$probId])){
                        $disAc[$probId] = $subs['creationTimeSeconds'];
                    }else{
                        if($disAc[$probId] < $subs['creationTimeSeconds']){
                            $disAc[$probId] = $subs['creationTimeSeconds'];
                        }
                    }
                    unset($disUn[$probId]);
                }else if($subs['verdict'] == "WRONG_ANSWER"){
                    $totalWa++;
                    if(!array_key_exists($probId, $disAc)){
                        if(!isset($disUn[$probId])){
                            $disUn[$probId] = $subs['creationTimeSeconds'];
                        }else{
                            if($disUn[$probId] < $subs['creationTimeSeconds']){
                                $disUn[$probId] = $subs['creationTimeSeconds'];
                            }
                        }
                    }
                }else{
                    $totalOt++;
                    if(!array_key_exists($probId, $disAc)){
                        if(!isset($disUn[$probId])){
                            $disUn[$probId] = $subs['creationTimeSeconds'];
                        }else{
                            if($disUn[$probId] < $subs['creationTimeSeconds']){
                                $disUn[$probId] = $subs['creationTimeSeconds'];
                            }
                        }
                    }
                }

            }
            $count += $gap;
        } 

        Oj::where('ojname', 'CF')
            ->where('ojid', $id)
            ->update([
                'checkpoint' => ($newCheckpoint==0 ? $oldCheckpoint : $newCheckpoint), 
                'totalSub'  => $totalSub, 
                'disAc'     => count($disAc),
                'totalAc'   =>  $totalAc,
                'totalWa'   =>  $totalWa,
                'totalOt'   =>  $totalOt,
                'solvedSet' => $disAc,
                'unsolvedSet'   => $disUn
            ]);

        return response()->json([
            'totalSub'  =>  $totalSub,
            'disAc'     => count($disAc),            
            'totalAc'   =>  $totalAc,
            'totalWa'   =>  $totalWa,
            'totalOt'   =>  $totalOt,
            'solvedSet' => $disAc,
            'unsolvedSet'   => $disUn
        ]); 
    }

    // public function cfSolved($id){
    //     $stats = Oj::where('username', $id)
    //                 ->where('ojname', 'CF')->first();

    //     $disAc = ($stats['solvedSet'] == null) ? array() : $stats['solvedSet'];

    //     return response()->json($disAc, 200);
    // }

    
    // public function cfUnsolved($id){
    //     $stats = Oj::where('username', $id)
    //                 ->where('ojname', 'CF')->first();

    //     $disAc = ($stats['unsolvedSet'] == null) ? array() : $stats['unsolvedSet'];

    //     return response()->json($disAc, 200);
    // }

    public function uvaStats($id){
        // if($id == 'me'){
        //     $id = $this->user['username'];
        // }

        $stats = Oj::where('username', $id)
                    ->where('ojname', 'UVA')->first();

        $id = $stats['ojid'];        

        $newCheckpoint = 0;
        $oldCheckpoint = $stats['checkpoint'];
        $totalSub = $stats['totalSub'];   $totalAc = $stats['totalAc']; 
        $totalWa = $stats['totalWa'];     $totalOt = $stats['totalOt'];

        $disAc = ($stats['solvedSet'] == null) ? array() : $stats['solvedSet'];
        $disUn = ($stats['unsolvedSet'] == null) ? array() : $stats['unsolvedSet'];

        $response = Http::get('https://uhunt.onlinejudge.org/api/subs-user/'.Http::get('https://uhunt.onlinejudge.org/api/uname2uid/'.$id).'/'.$oldCheckpoint);
        $response =  $response['subs']; 
        usort($response, function($a, $b){
            return $a[4] > $b[4] ? -1 : 1;
        });


        if($response != null){
            foreach($response as $subs){

                if($newCheckpoint == 0){
                    $newCheckpoint = $subs['0'];
                }
                   
                $totalSub++;
                $probId = UvaProblem::where('uvaid', $subs[1])->first()->uvaNum;

                if($subs[2] == "90"){
                    $totalAc++;
                    if(!isset($disAc[$probId])){
                        $disAc[$probId] = $subs[4];
                    }
                    unset($disUn[$probId]);
                }else if($subs[2] == "70"){
                    $totalWa++;
                    if(!array_key_exists($probId, $disAc)){
                        if(!isset($disUn[$probId])){
                            $disUn[$probId] = $subs[4];
                        }
                    }
                }else{
                    $totalOt++;
                    if(!array_key_exists($probId, $disAc)){
                        if(!isset($disUn[$probId])){
                            $disUn[$probId] = $subs[4];
                        }
                    }
                }

            }
        }

        Oj::where('ojname', 'UVA')
            ->where('ojid', $id)
            ->update([
                'checkpoint' => ($newCheckpoint==0 ? $oldCheckpoint : $newCheckpoint), 
                'totalSub'  => $totalSub, 
                'disAc'     => count($disAc),
                'totalAc'   =>  $totalAc,
                'totalWa'   =>  $totalWa,
                'totalOt'   =>  $totalOt,
                'solvedSet' => $disAc,
                'unsolvedSet'   => $disUn
            ]);

        return response()->json([
            'totalSub'  =>  $totalSub,
            'disAc'     => count($disAc),            
            'totalAc'   =>  $totalAc,
            'totalWa'   =>  $totalWa,
            'totalOt'   =>  $totalOt,
            'solvedSet' => $disAc,
            'unsolvedSet'   => $disUn
        ]); 

    }

    // public function uvaSolved($id){
    //     $stats = Oj::where('username', $id)
    //                 ->where('ojname', 'UVA')->first();

    //     $disAc = ($stats['solvedSet'] == null) ? array() : $stats['solvedSet'];

    //     return response()->json($disAc, 200);
    // }

    
    // public function uvaUnsolved($id){
    //     $stats = Oj::where('username', $id)
    //                 ->where('ojname', 'UVA')->first();

    //     $disAc = ($stats['unsolvedSet'] == null) ? array() : $stats['unsolvedSet'];

    //     return response()->json($disAc, 200);
    // }

    public function refreshUva(){

        $response = Http::get('https://uhunt.onlinejudge.org/api/p');
        $response =  json_decode($response);

        foreach($response as $probs){
            if((UvaProblem::where('uvaId', '=', $probs[0])->first()) == null){
                $uva = UvaProblem::create(['uvaId' => $probs[0], 'uvaNum' => $probs[1]]);
            }
        }
        return 'done';
    }

    protected function guard(){
        return Auth::guard();
    }
}

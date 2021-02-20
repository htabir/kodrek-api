<?php

namespace App\Http\Controllers;

use App\Models\Preset;
use App\Models\PresetProblem;
use App\Models\PresetUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresetController extends Controller
{
    protected $user;

    public function __construct(){
        $this->middleware('auth:api');
        $this->user = $this->guard()->user();
    }

    public function create(Request $request){
        $cf = json_encode($request['set']['cf']);
        $uva = json_encode($request['set']['uva']);

        $preset = Preset::create(['name' => $request['name'], 'ownerName' => $request['owner']]);

        $prob = PresetProblem::create([
            'presetId' => $preset['id'], 
            'ojName' => 'cf', 
            'problemId' => $cf]);

            $prob = PresetProblem::create([
                'presetId' => $preset['id'], 
                'ojName' => 'uva', 
                'problemId' => $uva]);

        return response()->json(["status"=>"Created Successfully"], 200);
    }

    public function setPreset($presetId){
        $found = Preset::where('id', $presetId)->first();
        if($found == null){
            return response()->json(["status" => "Preset Not Found"], 404);
        }

        if(count($this->user->preset_users) == 0){
            $presetUser = PresetUser::create([
                'username' => $this->user['username'], 
                'presetId' => $presetId, 
                'status' =>    true,
                ]);
            return response()->json($this->presetDetails($presetId), 200);
        }else{
            $presetUser = $this->user->preset_users->where('username', $this->user['username'])
                                     ->where('status', 1)->first();
            $diff = time()-strtotime($presetUser['updated_at']);
            
            $presetUser->update([
                'status'    =>  0,
                'days'      =>  $presetUser['days'] + $diff,
            ]);

            $presetUser = $this->user->preset_users->where('presetId', $presetId);

            if(count($presetUser) == 0){
                $presetUser = PresetUser::create([
                    'username' => $this->user['username'], 
                    'presetId' => $presetId, 
                    'status' =>    1,
                    ]);
                return response()->json($this->presetDetails($presetId), 200);
            }

            $presetUser->first()->update(['status' => 1]);

            return response()->json($this->presetDetails($presetId), 200);
        }
                 
    }

    public function stats($username){
        $found = PresetUser::where("username", $username)->where("status", 1)->first();
        if($found == null){
            return response()->json(["status" => "No Registered Preset"], 404);
        }else{
            return response()->json($this->presetDetails($found['presetId']), 200);
        }
    }

    public function presetDetails($presetId){
        $problemSet = PresetProblem::where('presetId', $presetId)->get();
        $presetInfo = Preset::where('id', $presetId)->first();
        $preset = array();
        $preset['presetId'] = $presetId;
        $preset['name'] = $presetInfo["name"];
        $preset['owner'] = $presetInfo["ownerName"];
        foreach($problemSet as $set){
            $preset[$set['ojName']] = json_decode($set['problemId']);
        }
        $presetUser = $this->user->preset_users->where('presetId', $presetId)->first();
        if($presetUser == null){
            $preset['days'] = 0;
            $preset['checkpoint'] = time();
            $preset['like'] = 0;
        }else{
            $preset['days'] = $presetUser['days'];
            $preset['checkpoint'] = strtotime($presetUser['updated_at']);
            $preset['like'] = $presetUser['like'];
        }
        
        return $preset;
    }

    public function like(){
        $found = PresetUser::where("username", Auth::user()->username)->where("status", 1)->first();
        $like = ($found->like == 1) ? 0 : 1;
        PresetUser::where("username", Auth::user()->username)->where("status", 1)
        ->update(["like"=>$like]);

        return response()->json(["status"=>"like updated successfully"], 200);
    }

    public function presetList(){
        $list = Preset::where('viewer', 1)->orWhere('ownerName', $this->user->username)->get();
        $id = array();
        $name = array();
        $total = array();
        $touch = array();
        $like = array();
        $days = array();
        foreach($list as $l){
            array_push($id, $l["id"]);
            array_push($name, $l["name"]);
            array_push($touch, count(PresetUser::where("presetId", $l["id"])->get()));
            array_push($like, count(PresetUser::where("presetId", $l["id"])->where("like", 1)->get()));
            $presetProbs = PresetProblem::where("presetId", $l["id"])->get();
            $cc = 0;
            foreach($presetProbs as $pp){
                $cc+= count(json_decode($pp["problemId"]));
            }
            array_push($total, $cc);
            $presetUser = PresetUser::where("presetId", $l["id"])->first();
            if($presetUser == null){
                array_push($days, 0);
            }else if($presetUser["status"] == 1){
                array_push($days, (time() - strtotime($presetUser["updated_at"]) + $presetUser["days"]));
            }else{
                array_push($days, ($presetUser["days"]));
            }
        }
        return response()->json([
            "id"    =>  $id,
            "name"  =>  $name,
            "total" =>  $total,
            "touch" =>  $touch,
            "like"  =>  $like,
            "days"  =>  $days
        ], 200);
    }
    
    protected function guard(){
        return Auth::guard();
    }
}

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
            return response()->json([
                'status'    =>  'ok',
            ]);
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
                return response()->json([
                    'status'    =>  'ok',
                ]);
            }

            $presetUser->first()->update(['status' => 1]);

            return response()->json([
                'status'    =>  'ok',
            ]);
        }
                 
    }

    public function stats($id){
        if(count($this->user->preset_users) == 0){
            return response()->json([
                'status'    => 'failed',
                'deatails'  =>  null
            ]);
        }else{
            $activePreset = $this->user->preset_users->where('status', 1)->first();
            $problemSet = PresetProblem::where('presetId', $activePreset['presetId'])->get();
            $preset = array();
            $preset['id'] = $activePreset['id'];
            foreach($problemSet as $set){
                $preset[$set['ojName']] = $set['problemId'];
            }
            return $preset;
        }
    }

    
    protected function guard(){
        return Auth::guard();
    }
}

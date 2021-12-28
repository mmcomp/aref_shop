<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\TeamUserCreateRequest;
use App\Http\Requests\User\TeamUserEditRequest;
use App\Http\Resources\User\TeamUserResource;
use App\Models\TeamUser;

class TeamUserController extends Controller
{   
  
    public function index()
    {
        $data=TeamUserResource::collection(TeamUser::all());
        //$data=TeamUserResource::
        return ($data);
        // $data=Fault::all();
        // return response()->json($data,200);
    }
    public function show($id)
    {
        $data=TeamUser::find($id)->first();
        return response()->json($data,200);
        // $data=Fault::find($id);
        // return response()->json($data,201);
    }   
    public function store(TeamUserCreateRequest $request)
    { 
        $request["is_full"]=false;        
         //$data=Fault::Create($request->all());
         $data= TeamUser::create($request->all());
              return response()->json($data,200);
        
    }
    public function update(TeamUserCreateRequest $request,TeamUser $teamUser)
    {
        $data=TeamUser::find($teamUser);
        if($data !== null)
        {

        }
        return response()->json($data,200);
       // return response()->json($id,209);
      //  $validation=self::Validation($id);    
    }

}

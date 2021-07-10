<?php

namespace App\Http\Controllers;

use App\Http\Requests\InsertOrderForUserRequest;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    
    /**
     * Insert factor for a user
     *
     * @param  \App\Http\Requests\InsertOrderForUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(InsertOrderForUserRequest $request)
    {

        $users_id = $request->input('users_id');
        Order::create([
           'users_id' => $users_id,
           'status' => 'waiting', 
           'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
           'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        
    }
}

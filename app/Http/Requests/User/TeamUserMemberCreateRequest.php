<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\TeamUserMember;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Validator;

class TeamUserMemberCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
    //    $count= TeamUserMember::where("team_user_id",$this->team_user_id)->count();
    //    dd($this->mobile);
    //    if($count >= 2)
    //    {
    //        return [ 
    //            "team_user_id" => [
    //                Rule::in("greater than 2")
    //            ]
    //        ];
    //    }
        return [
        // "team_user_id" => [
        //     "required",
        //     "int"
        // //    Validator::extend("gsg",function($query){
        // //        $count= $query->where("team_user_id",$this->team_user_id)->count();
        // //        return ($count<2);
        // //     })
        //  /*,"unique:team_user_members,team_user_id"*/
        // ],
            "mobile" => [
                "required", 
                "size:11",
               // "unique:team_user_members,mobile,NULL,id,team_user_id,".$request->id
            //    Rule::unique('team_user_members')->where(function ($query)  {
            //     return $query->where('mobile', $this->mobile)
            //     ->where('team_user_id', $this->team_user_id);
            //    })
            ]//,
            //"is_verified" => ["required", "bool"],
        ];
    }
    public function withValidator($validator)
   {
       if ($validator->fails()) {
           $errors = (new ValidationException($validator))->errors();

           throw new HttpResponseException(
               response()->json(['errors' => $errors], 422)
           );
       }
   }
}

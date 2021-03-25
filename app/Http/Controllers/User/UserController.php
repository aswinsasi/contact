<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\User;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $rules = array(
            'email' => 'required|email',
            'password' => 'required',
          
        );

        $validator = Validator::make($request->all() , $rules);
        
        if ($validator->fails()) {
            $res = array(
                'errorcode' => '3',
                'message' => $validator->messages()
            );

        } else {
        }
        return response()->json($res);
    }


    public function register(Request $request)
    {

        $rules = array(
            'name'            => 'required',
            'email'           => 'required|email|unique:users,email',
            'mobile'          => 'required|digits:10|unique:users,mobile',
            'password'        => 'required|min:4'
        );

        $validator = Validator::make($request->all() , $rules);
        
        if ($validator->fails()) {
            $res = array(
                'errorcode' => '3',
                'message' => $validator->messages()
            );

        } else {

           $user = User::create([
                    'name'       => $request->name,
                    'email'      => $request->email,
                    'mobile'     => $request->mobile,
                    'password'   => Hash::make($request->password),
                    'status'     => 1,
                ]);

                $data['user_id'] = $user->id ;

                $res = array(
                    'errorcode' => 0,
                    'data' => $data,
                    'message' => "Successfully registered!"
                );
        }
      
        return response()->json($res);
    }
}

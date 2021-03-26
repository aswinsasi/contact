<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\User;
use App\UserContact;
use Auth;

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
           
            if(Auth::attempt(['email' => $request->email, 'password' => $request->password]))
    		{
                $user = Auth::user();
                Auth::login($user);
                $data['user_id'] = Auth::user()->id;

                $res = array(
                    'errorcode' => 0,
                    'data' => $data,
                    'message' => "Successfully Login!"
                );
            }else{
                $res = array(
                    'errorcode' => 1,
                    'data' => (object)[],
                    'message' => "Enter valid credentials and try again!"
                );
            }
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

    public function logout($user_id)
    {
        if(!is_null($user_id))
        {
            $user = User::find($user_id);
            if($user)
            {
                Auth::logout($user);
                $res = array(
                    'errorcode' => 0,
                    'data' => (object)[],
                    'message' => "Successfully logout!"
                );
            }else{
                $res = array(
                    'errorcode' => 1,
                    'data' => (object)[],
                    'message' => "User not found oops!"
                );
            }
        }else{
            $res = array(
                'errorcode' => 2,
                'data' => (object)[],
                'message' => "Please provide user id!"
            );
        }
        return response()->json($res);
    }

    public function profileDetails($user_id =null)
    {
        if($user_id)
        {
            $user = User::find($user_id);
            $data = array();

            if($user)
            {
                
                $data = [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $user->password,
                    'mobile'  => $user->mobile,
                    'status' => $user->status   
                ];

                $res = array(
                    'errorcode' => 0,
                    'data' => $data,
                    'message' => "Success!"
                );

            }else{
                $res = array(
                    'errorcode' => 1,
                    'data' => (object)[],
                    'message' => "User not found oops!"
                );
            }

        }else{
            $res = array(
                'errorcode' => 2,
                'data' => (object)[],
                'message' => "Please provide user id!"
            );
        }

        return response()->json($res);
    }

    public function addContact(Request $request)
    {
        $rules = array(
            'user_id'         => 'required',
            'name'            => 'required',
            'mobile'          => 'required|numeric',
        );

        $validator = Validator::make($request->all() , $rules);
        
        if ($validator->fails()) {
            $res = array(
                'errorcode' => '3',
                'message' => $validator->messages()
            );

        } else {
                $user = User::where('id',$request->user_id)->first();
                
                if($user)
                {

                   $contact = UserContact::updateOrCreate(['user_id' => $user->id , 'mobile' => $request->mobile],[
                            'name'      => $request->name,
                            'nick_name' => $request->nick_name,
                            'realtion'  => $request->relation,
                            'address'   => $request->address
                            ]);

                            $data['user_id'] = $user->id ;
                            $data['contact_id'] = $contact->id ;

                            $res = array(
                                'errorcode' => 0,
                                'data' => $data,
                                'message' => "Contact Saved Successfully!"
                            );

                }else{

                    $res = array(
                        'errorcode' => 1,
                        'data' => (object)[],
                        'message' => "User not found oops!"
                    );
                }

        }

        return response()->json($res);
    }

    public function viewContact($contact_id =null)
    {
        if($contact_id)
        {
            $contact = UserContact::find($contact_id);

            $data = array();
           
            
            if($contact)
            {
                $data = [
                    'user_id' => $contact->user_id,
                    'contact_id' => $contact->id,
                    'name' => $contact->name,
                    'nick_name' => $contact->nick_name,
                    'relation' => $contact->relation,
                    'address' => $contact->address,
                    'mobile'  => $contact->mobile,
                
                ];

                $res = array(
                    'errorcode' => 0,
                    'data' => $data,
                    'message' => "Success!"
                );
            }else{

                $res = array(
                    'errorcode' => 1,
                    'data' => (object)[],
                    'message' => "Contact not found oops!"
                );
            }

        }else{
            $res = array(
                'errorcode' => 2,
                'data' => (object)[],
                'message' => "Please provide contact id!"
            );
        }

        return response()->json($res);
    }


    public function allContact($user_id =null)
    {
        if($user_id)
        {
            $user = User::with('contacts')->where('id',$user_id)->first();

            $data = array();
          
            
            if($user)
            {
                if(count($user->contacts)>0)
                {
                    foreach($user->contacts as $contact)
                    {
                        $data[] = [
                            'user_id' => $contact->user_id,
                            'contact_id' => $contact->id,
                            'name' => $contact->name,
                            'nick_name' => $contact->nick_name,
                            'relation' => $contact->relation,
                            'address' => $contact->address,
                            'mobile'  => $contact->mobile,
                        
                        ];
                    }
                }
               

                $res = array(
                    'errorcode' => 0,
                    'data' => $data,
                    'message' => "Success!"
                );
            }else{

                $res = array(
                    'errorcode' => 1,
                    'data' => (object)[],
                    'message' => "User not found oops!"
                );
            }

        }else{
            $res = array(
                'errorcode' => 2,
                'data' => (object)[],
                'message' => "Please provide user id!"
            );
        }

        return response()->json($res);
    }

    public function editContact(Request $request)
    {
        $rules = array(
            'contact_id'      => 'required',
            'name'            => 'required',
            'mobile'          => 'required|numeric',
        );

        $validator = Validator::make($request->all() , $rules);
        
        if ($validator->fails()) {
            $res = array(
                'errorcode' => '3',
                'message' => $validator->messages()
            );

        } else {
              $contact = UserContact::where('id',$request->contact_id)->first();

              if($contact)
              {
                  $contact->update([
                      'name' => $request->name,
                      'nick_name' => $request->nick_name,
                      'mobile'  => $request->mobile,
                      'relation' => $request->relation,
                      'address' => $request->address
                  ]);

                  $data['contact_id'] = $contact->id;

                  $res = array(
                    'errorcode' => 0,
                    'data' => $data,
                    'message' => "Contact Edited Successfully!"
                );

              }else{
                $res = array(
                    'errorcode' => 1,
                    'data' => (object)[],
                    'message' => "Contact not found oops!"
                );
              }
        }

        return response()->json($res);
    }


    public function deleteContact($contact_id =null)
    {
        if($contact_id)
        {
            $contact = UserContact::find($contact_id);

            //$data = array();
           
            
            if($contact)
            {
                $contact->delete();
                
                $res = array(
                    'errorcode' => 0,
                    'data' => (object)[],
                    'message' => "Deleted Successfully!"
                );
            }else{
                $res = array(
                    'errorcode' => 1,
                    'data' => (object)[],
                    'message' => "Contact not found oops!"
                );
            }
        }else{
            $res = array(
                'errorcode' => 2,
                'data' => (object)[],
                'message' => "Please provide contact id!"
            );
        }
        return response()->json($res);
    }
}

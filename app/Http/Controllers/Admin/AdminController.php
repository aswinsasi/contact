<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\AdminUser;
use App\User;
use App\UserContact;
use Auth;

class AdminController extends Controller
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

            if($request->route()->getPrefix() == 'api/admin')
            {
                $credentials = $request->only('email', 'password');

                if (Auth::guard('admin')->attempt($credentials)) {
                    $user = Auth::guard('admin')->user();
                    $data['admin_details'] = $user->toArray();
                    $res = array(
                        'errorcode' => 0,
                        'data' => $data,
                        'message' => "Successfully login!"
                    );

                }
                else {
                    
                    $res = array(
                        'errorcode' => 1,
                        'data' => (object)[],
                        'message' => "Enter valid credentials and try again!"
                    );
    
                }
            }
        }

        return response()->json($res);
    }

    public function logout($admin_id)
    { 
        if(!is_null($admin_id))
        {
            $admin = AdminUser::find($admin_id);
            if($admin)
            {
                Auth::logout($admin);
                $res = array(
                    'errorcode' => 0,
                    'data' => (object)[],
                    'message' => "Successfully logout!"
                );
            }else{
                $res = array(
                    'errorcode' => 1,
                    'data' => (object)[],
                    'message' => "Admin not found oops!"
                );
            }
        }else{
            $res = array(
                'errorcode' => 2,
                'data' => (object)[],
                'message' => "Please provide admin id!"
            );
        }
        return response()->json($res);
    }


    public function addUser(Request $request)
    {
        $rules = array(
            'admin_id'        => 'required',
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
                $adminuser = AdminUser::where('id',$request->admin_id)->first();
                
                if($adminuser)
                {

                   $user = User::updateOrCreate(['email' => $request->email],[
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
                                'message' => "User Added Successfully!"
                            );

                }else{

                    $res = array(
                        'errorcode' => 1,
                        'data' => (object)[],
                        'message' => "Admin not found oops!"
                    );
                }

        }

        return response()->json($res);
    }

    public function viewUser($admin_id =null,$user_id =null)
    {
        $admin = AdminUser::find($admin_id);
            if($admin)
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
                            'mobile' => $user->mobile,
                            'status' => $user->status==1 ? 'active' : 'inactive',
                        
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
            }else{
                $res = array(
                    'errorcode' => 2,
                    'data' => (object)[],
                    'message' => "Please provide admin id!"
                );
            }

        return response()->json($res);
    }

    public function allUsers($admin_id =null)
    {
        if($admin_id)
        {
            $admin = AdminUser::where('id',$admin_id)->first();

            

            $data = array();
          
            
            if($admin)
            {
                $users = User::all();
                if(count($users)>0)
                {
                    foreach($users as $user)
                    {
                        $data[] = [
                            'user_id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'mobile' => $user->mobile,
                            'status' => $user->status==1 ? 'active' : 'inactive',
                        
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
                    'message' => "Admin not found oops!"
                );
            }

        }else{
            $res = array(
                'errorcode' => 2,
                'data' => (object)[],
                'message' => "Please provide admin id!"
            );
        }

        return response()->json($res);
    }


    public function editUser(Request $request)
    {

        $user = User::where('id',$request->user_id)->first();

        $rules = array(
            'admin_id'        => 'required',
            'user_id'         => 'required',
            'name'            => 'required',
            'email'           => ['required','email',Rule::unique('users','email')->ignore($user)],
            'mobile'          => ['required','numeric',Rule::unique('users','mobile')->ignore($user)],
            'password'        => 'min:4',
            'status'          => 'required',

        );

        $validator = Validator::make($request->all() , $rules);
        
        if ($validator->fails()) {
            $res = array(
                'errorcode' => '3',
                'message' => $validator->messages()
            );

        } else {

            $admin = AdminUser::where('id',$request->admin_id)->first();
            $data = array();

            if($admin)
            {
                    $user = User::where('id',$request->user_id)->first();

                    if($user)
                    {

                        if($request->has('password'))
                        {
                            $password = Hash::make($request->password);
                        }else{
                            $password = $user->password;
                        }
                        $user->update([
                            'name' => $request->name,
                            'email' => $request->email,
                            'mobile'  => $request->mobile,
                            'password' => $password,
                            'status' => $request->status
                        ]);

                        $data['user_id'] = $user->id;

                        $res = array(
                            'errorcode' => 0,
                            'data' => $data,
                            'message' => "User Edited Successfully!"
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
                    'errorcode' => 1,
                    'data' => (object)[],
                    'message' => "Admin not found oops!"
                );
              }
        }

        return response()->json($res);
    }


    public function deleteUser($admin_id=null,$user_id =null)
    {
        $admin = AdminUser::find($admin_id);
        if($admin)
        {
            if($user_id)
            {
                $user = User::find($user_id);

                //$data = array();
            
                
                if($user)
                {
                    $user->delete();
                    
                    $res = array(
                        'errorcode' => 0,
                        'data' => (object)[],
                        'message' => "Deleted Successfully!"
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
        }else{
            $res = array(
                'errorcode' => 2,
                'data' => (object)[],
                'message' => "Please provide admin id!"
            );
        }
        return response()->json($res);
    }


    public function addUserContact(Request $request)
    {
        $rules = array(
            'admin_id'        => 'required',
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

            $admin = AdminUser::find($request->admin_id);
            if($admin)
            {
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
            }else{

                $res = array(
                    'errorcode' => 1,
                    'data' => (object)[],
                    'message' => "Admin not found oops!"
                );
            }

        }

        return response()->json($res);
    }

    public function viewUserAllContact($admin_id=null,$user_id=null)
    {
          $admin = AdminUser::find($admin_id);
            if($admin)
            {
                if($user_id)
                {
                    $user = User::find($user_id);

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
            }else{
                $res = array(
                    'errorcode' => 2,
                    'data' => (object)[],
                    'message' => "Please provide admin id!"
                );
            }

        return response()->json($res);
    }


    public function viewUserContact($admin_id=null,$contact_id =null)
    {

        $admin = AdminUser::find($admin_id);
            if($admin)
            {
                    if($contact_id)
                    {
                        $contact = UserContact::with('user')->where('id',$contact_id)->first();

                        $data = array();
                    
                        
                        if($contact)
                        {
                            $data = [
                                'user_id' => $contact->user->id,
                                'user_name' => $contact->user->name,
                                'contact_id' => $contact->id,
                                'contact_name' => $contact->name,
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
            }else{
                $res = array(
                    'errorcode' => 2,
                    'data' => (object)[],
                    'message' => "Please provide admin id!"
                );
            }

         return response()->json($res);
    }


    public function editUserContact(Request $request)
    {

        $user = User::where('id',$request->user_id)->first();

        $rules = array(
            'admin_id'        => 'required',
            'contact_id'      => 'required',
            'name'            => 'required',
            'mobile'          => ['required','numeric'],

        );

        $validator = Validator::make($request->all() , $rules);
        
        if ($validator->fails()) {
            $res = array(
                'errorcode' => '3',
                'message' => $validator->messages()
            );

        } else {

            $admin = AdminUser::where('id',$request->admin_id)->first();
            $data = array();

            if($admin)
            {
                    $contact = UserContact::with('user')->where('id',$request->contact_id)->first();

                    if($contact)
                    {

                        $contact->update([
                            'name' => $request->name,
                            'nick_name' => $request->nick_name,
                            'mobile'  => $request->mobile,
                            'relation' => $request->relation,
                            'address' => $request->address
                        ]);

                        $data['user_id'] = $contact->user->id;
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
              }else{
                $res = array(
                    'errorcode' => 1,
                    'data' => (object)[],
                    'message' => "Admin not found oops!"
                );
              }
        }

        return response()->json($res);
    }



    public function deleteUserContact($admin_id=null,$contact_id =null)
    {

        $admin = AdminUser::find($admin_id);
            if($admin)
            {
                    if($contact_id)
                    {
                        $contact = UserContact::where('id',$contact_id)->first();

                        $data = array();
                    
                        
                        if($contact)
                        {
                            $contact->delete();

                            $res = array(
                                'errorcode' => 0,
                                'data' => $data,
                                'message' => "Contact Deleted Successfully!"
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
            }else{
                $res = array(
                    'errorcode' => 2,
                    'data' => (object)[],
                    'message' => "Please provide admin id!"
                );
            }

         return response()->json($res);
    }



    public function dashboard($admin_id=null)
    {
        $admin = AdminUser::find($admin_id);

        $data = array();
        
        if($admin)
        {
            $user_count = User::count();

            $user_active = User::where('status',1)->count();

            $user_inactive = User::where('status',0)->count();

            $total_contacts = UserContact::count();

            $today_contacts = UserContact::whereDate('created_at', Carbon::today())->count();

            $week_contacts = UserContact::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();

            $month_contacts = UserContact::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->count();

            $data['total_users'] = $user_count;
            $data['active_users'] = $user_active;
            $data['inactive_users'] = $user_inactive;
            $data['contacts_current_day_count'] = $today_contacts ==null ? 0 : $today_contacts;
            $data['contacts_current_weeek_count'] = $week_contacts ==null ? 0 : $week_contacts;
            $data['contacts_current_month_count'] = $month_contacts == null ? 0 : $month_contacts;

            $res = array(
                'errorcode' => 0,
                'data' => $data,
                'message' => "Success!"
            );
            
        }else{
            $res = array(
                'errorcode' => 2,
                'data' => (object)[],
                'message' => "Please provide admin id!"
            );
        }

        return response()->json($res);
    }



}

<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Input;
use App\Models\Guard;
use Hash;




class LoginController extends Controller
{

    public function __construct()
    {
        
    }

   
  
	public function verify_user(Request $request){
		$data = array();
		$rules = array(
		  'email'       => 'required',
		  'password'   => 'required',
		);
		
		$validator = Validator::make($request->all(), $rules);
		if(!$validator->fails()){
			$email = $request->input('email'); 
			//$user_details = User::check_user_exits($email);
			$user_details = User::login_check_user_exits($email);
			
			if(!empty($user_details)){   
				
				if (Hash::check($request->input('password'), $user_details->password)){
			
					if($user_details->is_active == '1'){
						$data['message'] = "success";
						$data['type'] = $user_details->type;
						if($user_details->type=='promoter'){
							$user_details->events_count = $user_details->events()->count();
							$user_details->guards_count = $user_details->guards()->count();
						}
						$data['detail'] = $user_details;
						return response( array('data' =>$data ,'response' => 1));
					}else{
						$data['message'] = "Wrong email or password";
						return response( array('data' =>$data ,'response' => 0));
					}
				
				}else{
					$data['message'] = "Wrong email or password";
					return response( array('data' =>$data ,'response' => 0));
				}
					 
			}else{
			  $data['message'] = "User does not exist";
			  return response( array('data' =>$data ,'response' => 0));
			}
		  
		}else{
		  $data['message'] = "Please fill all required fields";
		  return response( array('data' =>$data ,'response' => 0));
		}

	}
	
	public function guard_login(Request $request){
		$data = array();
		$rules = array(
		  'username'       => 'required',
		  'password'   => 'required',
		);
		
		$validator = Validator::make($request->all(), $rules);
		if(!$validator->fails()){
			$username = $request->input('username'); 
			$guard = Guard::check_user_exits($username);
			
			if(!empty($guard)){
				if (Hash::check($request->input('password'), $guard->password)){
			
					if($guard->is_active == '1'){
						$data = $guard;
						$data['type'] = 'guard';
						return response( array('data' =>$data ,'response' => 1));
					}else{
						$data['message'] = "Guard is not active";
						return response( array('data' =>$data ,'response' => 0));
					}
				
				}else{
					$data['message'] = "Wrong username or password";
					return response( array('data' =>$data ,'response' => 0));
				}		
			}else{
			  $data['message'] = "Guard does not exist";
			  return response( array('data' =>$data ,'response' => 0));
			}
		}else{
		  $data['message'] = "Please fill all required fields";
		  return response( array('data' =>$data ,'response' => 0));
		}
	}
	
	public function profile(Request $request){
		$user = User::with('state','country')->where('email','=',$request->input('email'))->first();	
		if(count($user)>0){
			$thumbnail = public_path('uploads/user_profile/'.$user->id.'/thumbnail_'.$user->profile_photo);
			if(file_exists($thumbnail)){
				$user->profile_photo = 'thumbnail_'.$user->profile_photo;
			}
			if($user->type=='promoter'){
				$user->events_count = $user->events()->count();
				$user->guards_count = $user->guards()->count();
			}
		
			return response( array('data' =>$user ,'response' => 1));
		}else{
			return response( array('data' =>'' ,'response' => 0));
		}
	}		
	 
	public function all_users(){
		$users = User::orderBy('id','Asc')->get();	
		if(count($users)>0){
			foreach($users as $user){
				$thumbnail = public_path('uploads/user_profile/'.$user->id.'/thumbnail_'.$user->profile_photo);
				if(file_exists($thumbnail)){
					$user->profile_photo = 'thumbnail_'.$user->profile_photo;
				}
			}
			return response( array('data' =>$users ,'response' => 1));
		}
	}
	
	public function other_profile(Request $request){
		$user = User::findOrFail($request->input('id'));	
	 
		if(count($user)>0){
			$thumbnail = public_path('uploads/user_profile/'.$user->id.'/thumbnail_'.$user->profile_photo);
			if(file_exists($thumbnail)){
				$user->profile_photo = 'thumbnail_'.$user->profile_photo;
			}
			return response( array('data' =>$user ,'response' => 1));
		}
	}		
	
	public function get_user_id(Request $request){		
		$user = User::where('email','=',$request->input('user_email'))->first();			
		if(count($user)>0){			
			return response( array('data' =>$user->id ,'response' => 1));	  
		}	
	}
	
}

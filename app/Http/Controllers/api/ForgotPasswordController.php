<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Input;
use Carbon\Carbon;
use Mail;
use Config;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ForgotPasswordController extends Controller
{
    public function __construct()
    {    
    }  
	public function forgot_password(Request $request){
		$data  = array();
		$rules = array(
		  'email'       => 'required',
		);		
		$validator = Validator::make($request->all(), $rules);
		if(!$validator->fails()){
			$email        = $request->input('email'); 
			$user_details = User::check_user_exits($email);			
			if(!empty($user_details)){  			
				//generate token
				do
				{
					$code = str_random(15);
					$passport_token = User::where('passport_token', $code)->get();
				}
				while(!$passport_token->isEmpty());			
				$tomorrow 						= Carbon::tomorrow();
				$user_details->passport_token   = $code;
				$user_details->valid_token_date = $tomorrow;
				$user_details->save();				
				if(!empty($user_details->passport_token) && !empty($user_details->valid_token_date)){
					$token      = $user_details->passport_token;
					$valid_date = $user_details->valid_token_date;
					$base_url 	= Config::get('constants.base_url');
					$url = $base_url.'reset-password/'.$token;
					Mail::send('admin.emails.forgot-password',
									['url' => $url ],
									function($message) use($email)
									{
									   $message->to($email)->subject('Reset Password');
									}
								);
					$data['message']	=	"Reset link has been sent to your email address";
					return response( array('data' =>$data ,'response' => 1));
					} else {
						$data['message'] = " Token expired";
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
	
	public function forgot_password_test(Request $request){
		$data  = array();
		$rules = array(
		  'email'       => 'required',
		);		
		$validator = Validator::make($request->all(), $rules);
		if(!$validator->fails()){
			$email        = $request->input('email'); 
			$user_details = User::check_user_exits($email);			
			if(!empty($user_details)){  			
				//generate token
				do
				{
					$code = str_random(5);
					$passport_token = User::where('passport_token', $code)->get();
				}
				while(!$passport_token->isEmpty());			
				$tomorrow 						= Carbon::tomorrow();
				$user_details->passport_token   = $code;
				$user_details->valid_token_date = $tomorrow;
				$user_details->save();				
				if(!empty($user_details->passport_token) && !empty($user_details->valid_token_date)){
					$token      = $user_details->passport_token;
					$valid_date = $user_details->valid_token_date;
					$base_url 	= Config::get('constants.base_url');
					$url = $base_url.'reset-password/'.$token;
					Mail::send('admin.emails.forgot-password',
									['url' => $code ],
									function($message) use($email)
									{
									   $message->to($email)->subject('Reset Password');
									}
								);
					$data['message']	=	"Reset OTP has been sent to your email address";
					return response( array('data' =>$data ,'response' => 1));
					} else {
						$data['message'] = " OTP expired";
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
	
	public function check_otp(Request $request){
		$code = $request->input('code');
		if($code){
			$user = User::where('passport_token', $code)->first();
			if($user){
				
			
				$passport_token = $user->passport_token;
				$valid_date = $user->valid_token_date;
				
				if(Carbon::now() > $valid_date){
					return response( array('data' =>"OTP does not exist" ,'response' => 0));
				} else{
					return response( array('data' =>$user ,'response' => 1));
				}
			} else {
				 return response( array('data' =>"OTP does not exist" ,'response' => 0));
			}
			
		} else {
			 return response( array('data' =>"OTP does not exist" ,'response' => 0));
		}
	}
	
	public function resetPassowrd(Request $request)
    {
		$data  = array();
		$rules = $this->rules();
		$validator = Validator::make($request->all(), $rules);
        if(!$validator->fails()){
			/* $response = $this->broker()->reset(
				$this->credentials($request), function ($user, $password) {
					$this->resetPassword($user, $password);
				}
			); */
			if($request->token=='undefined'){
				return response( array('data' =>"Token not exist" ,'response' => 0));
			} else{
				$user = User::where('email', $request->email)->first();
				if($user){
					$after_update = $this->resetPassword($user, $request->password);
					return response( array('data' =>$after_update ,'response' => 1));
				} else {
					return response( array('data' =>"User not found" ,'response' => 0));
				}
			}
			/* 
			
			return $response == Password::PASSWORD_RESET
                    ? array('data' =>"Passowrd reset succesfully" ,'response' => 1)
                    : array('data' =>"error" ,'response' => 0); */
		} else {
			return response( array('data' =>"Please fill required fileds" ,'response' => 0));
		}

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        
    }
	
	protected function rules()
    {
        return [
            'token' => 'required|min:5',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:3',
        ];
    }

    /**
     * Get the password reset validation error messages.
     *
     * @return array
     */
    protected function validationErrorMessages()
    {
        return [];
    }

    /**
     * Get the password reset credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
		
		/*  return array('email'=>$request->email,'password'=>$request->password,'password_confirmation'=>$request->password_confirmation,'token'=>$request->token);*/
		return $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );
	}
    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
		$user->forceFill([
            'password' => bcrypt($password),
            'remember_token' => Str::random(60),
        ])->save();

        return $user;
    }
	
	public function broker()
    {
        return Password::broker();
    }
}

<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\User;
use Redirect;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\Input;

class ResetPasswordController extends Controller
{
    

    /**
     * Reset Password
     */
    public function resetPassword(Request $request, $token)
    {
        $user_detail = User::where('passport_token','=',$token)->first();
		$tomorrow  = Carbon::tomorrow();
		$tomorrow  = $tomorrow->format('Y-m-d');
		$now  = Carbon::tomorrow();
		$now  = $now->format('Y-m-d');
		if($tomorrow == $user_detail->valid_token_date || $now == $user_detail->valid_token_date ){
		   return view('auth.reset_password')->with(['requested_user' => $user_detail->id]); 
		}else{
			echo "This link has been expired";
		}	   
    }
	
	/**
     * Change Password
     */
    public function changePassword(Request $request)
    {	$rules = array(
		  'email'    => 'required',
		  'confirm_password'  => 'required',
		);
		
		$validator = Validator::make($request->all(), $rules);
		if(!$validator->fails()){	
			$id = $request->input('user_id');
			if( $id > 0 && !empty($id)){
				$user = User::findOrFail($id);
				$user->password = Hash::make($request->input('password'));
				$user->save();
				echo "Your password has been Reset";
			}
		}else{
			//echo "This link has been expired";
		}		
    }

}

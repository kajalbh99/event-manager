<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\Admin;
use Illuminate\Support\Facades\Input;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;
use Redirect;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/register';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin', ['except' => 'logout']);
    }

   
    public function index()
    {       
       return Redirect::route('admin-home');	
    }
    
    /* 
    *  user login get request  
    */
    public function getLogin()
    {       
       if (auth()->user()) return Redirect::route('admin-home');	
        return view('auth.login');
    }

    /* 
    *  user login post request  
    */
    public function postLogin(Request $request)
    {   
		if($request->isMethod('post'))
		{
		    $rules = [
				'password' =>'required',
				'email'    =>'required',
			]; 
			$validator = Validator::make(Input::all(), $rules);
			if ($validator->fails())
			{ 
                return Redirect::back()->with('message','Email and Password Required');
            }

            $email = Input::get('email');
            $password = Input::get('password');

            if ( Auth::attempt(array('email' => $email, 'password' => $password, 'type' => 'admin')) ){
                return Redirect::route('admin-home');	
            }
            else {        
                return Redirect::back()->with('error','Wrong Email ID or  wrong Password');
            }			
        }       			
    }
    
    /* 
    *  user logout request  
    */
    public function logout() {
	    Auth::logout();
	    return Redirect::route('admin');	
    }
}



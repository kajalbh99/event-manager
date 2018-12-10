<?php

namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;
use App\User;
use App\Models\UserFriend;
use Illuminate\Http\Request;
use Nahid\Talk\Facades\Talk;
use Auth;
use View;
use DB;
use Cartalyst\Stripe\Laravel\Facades\Stripe;
class CommonController extends Controller
{
    public function __construct(Request $req)
    {
		
        
    }
	
	public function getAppdata(){
		/* $envFile = app()->environmentFilePath();
		$str = file_get_contents($envFile);
		$str = preg_split('/\s+/', $str);
		$data = array();
		foreach($str as $env_key => $env_value){

			$entry = explode("=", $env_value, 2);
			$data[$entry[0]] = $entry[1];
			
		}
		 */
		$data['STRIPE_KEY'] = env('STRIPE_KEY');
		$data['STRIPE_SECRET'] = env('STRIPE_SECRET');
		$data['STRIPE_CLIENT_ID'] = env('STRIPE_CLIENT_ID');
		return response( array('data' =>$data ,'response' =>1));
		
	}
	
}

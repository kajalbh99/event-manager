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
class StripeController extends Controller
{
    public function __construct(Request $req)
    {
		
        
    }
	public function charge(Request $request)
    {
		$stripe = Stripe::make(env('STRIPE_SECRET'));
		try {
			
			
			if (!isset($request->token_id)) {
				return response( array('data' =>'Please send token' ,'response' =>0));
			}else{
				$charge = $stripe->charges()->create([
				'card' => $request->token_id,
				'currency' => 'USD',
				'amount'   => $request->amount,
				'description' => 'Add in wallet',
				]);
				if($charge['status'] == 'succeeded') {
					return response( array('data' =>$charge ,'response' =>1));
				} else {
					return response( array('data' =>'error' ,'response' =>0));
				} 
			}
		}  catch (Exception $e) {
			
			return response( array('data' =>$e->getMessage() ,'response' =>0));
		} catch(\Cartalyst\Stripe\Exception\CardErrorException $e) {
			return response( array('data' =>$e->getMessage() ,'response' =>0));
		} catch(\Cartalyst\Stripe\Exception\MissingParameterException $e) {
			return response( array('data' =>$e->getMessage() ,'response' =>0));
		} 
    }
	
}

<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Country;
use App\Models\State;
use App\Models\Event;
use DB;
use Redirect;
use Auth;
use File;
use Validator;
use Illuminate\Support\Facades\Input;
//use Yajra\Datatables\Datatables;
use Yajra\Datatables\Facades\Datatables;
use League\ISO3166\ISO3166;
use Cartalyst\Stripe\Laravel\Facades\Stripe;
use App\Models\UserAccount;
use Image;
use App\Library\ImageManipulator;

class PromotersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     */
    public function index()
    {
         return view('admin.promoters.list_promoters');
    }

    
    public function addUser(REquest $request)
    {   
        $country_details = Country::all();
        $state_details   = State::all();
        $user_details    = User::all();
        if($request->isMethod('post')){
            //echo "<pre>"; print_r($request->all()); die;

            //<!-- validation rules
			$country = Country::find($request->input('country_id'))->country_code;
			$country_data = (new ISO3166)->alpha3($country);
			$stripe = Stripe::make(env('STRIPE_SECRET'));
			
		    $rules = [
				'user_name' =>'required|unique:users,user_name',
                'email' =>'required|Email|unique:users,email',
                'state_id' => 'required',
			]; 
			$validator = Validator::make(Input::all(), $rules);
			if ($validator->fails())
			{ 
                $messages = $validator->messages();
				if (!empty($messages)) {
                    if ($messages->has('email')) {
                        return Redirect::route('user-add')->with('error','Entered Email Already Exists.');
                    }
                    if ($messages->has('user_name')) {
                        return Redirect::route('user-add')->with('error','Entered User_name Already Exists.');
                    }
                    if ($messages->has('state_id')) {
                        return Redirect::route('user-add')->with('error','Select United State Country and state');
                    }			         						   
				}
			}
            //validation rules ended-->
			if($request->input('account_number')!="" || $request->input('routing_number')!=""){
				$acct = $stripe->account()->create(array(
				  "type" => "custom",
				  "country" => $country_data['alpha2'],
				  "email" =>$request->input('email'),
				  "external_account" => array(
					"object" => "bank_account",
					"country" => $country_data['alpha2'],
					"currency" => $country_data['currency'][0],
					"routing_number" => $request->input('routing_number'),
					"account_number" =>$request->input('account_number'),
				  ),
				  "legal_entity" => array(

					"first_name" => $request->input('name'),
					'business_name' =>$request->input('email'),
					),

				  "tos_acceptance" => array(
					"date" => strtotime('now'),
					"ip" => $_SERVER['REMOTE_ADDR']
				  )
				));
				if($acct){
					$user_obj = new User();
					$user_obj->name       = $request->input('name');
					$user_obj->user_name       = $request->input('user_name');
					$user_obj->email      = $request->input('email');
					$user_obj->country_id = $request->input('country_id');
					$user_obj->state_id   = $request->input('state_id');
					$user_obj->mobile     = $request->input('mobile');
					$user_obj->password   = Hash::make($request->input('password'));
					$user_obj->dob        = date('Y-m-d', strtotime( $request->input('dob') ));
					$user_obj->gender     = $request->input('gender');
					$user_obj->type       = $request->input('type');
					$user_obj->is_active  = ($request->input('is_active') == 1)? '1':'0';
					$user_obj->save();
					
					$UserAccount = new  UserAccount();
					$UserAccount->user_id = $user_obj->id;
					$UserAccount->routing_number = $request->input('routing_number');
					$UserAccount->account_number = $request->input('account_number');
					$UserAccount->stripe_account_id = $acct['id'];
					$UserAccount->save();
					
					if( !empty( $request->file('profile_photo') ) )
					{  
					   $photo = $request->file('profile_photo');
					   $input['imagename'] = $user_obj->id.'.'.$photo->getClientOriginalExtension();
					   $file_name = time().rand(0,99)."_".$input['imagename'];
					   $destinationPath = public_path('uploads/user_profile/'.$user_obj->id); 
					   if(!File::exists($destinationPath)) {
						   File::makeDirectory($destinationPath, $mode = 0777, true, true);
					   }
					   $photo->move($destinationPath, $file_name);
					   $user_profile = User::findOrFail($user_obj->id);
					   $user_profile->profile_photo = $file_name; 
					   $user_profile->save();                      
				   } 
				}
								
			}else {
				$user_obj = new User();
				$user_obj->name       = $request->input('name');
				$user_obj->user_name       = $request->input('user_name');
				$user_obj->email      = $request->input('email');
				$user_obj->country_id = $request->input('country_id');
				$user_obj->state_id   = $request->input('state_id');
				$user_obj->mobile     = $request->input('mobile');
				$user_obj->password   = Hash::make($request->input('password'));
				$user_obj->dob        = date('Y-m-d', strtotime( $request->input('dob') ));
				$user_obj->gender     = $request->input('gender');
				$user_obj->type       = $request->input('type');
				$user_obj->is_active  = ($request->input('is_active') == 1)? '1':'0';
				$user_obj->save();
				if( !empty( $request->file('profile_photo') ) )
				{  
				   $photo = $request->file('profile_photo');
				   $input['imagename'] = $user_obj->id.'.'.$photo->getClientOriginalExtension();
				   $file_name = time().rand(0,99)."_".$input['imagename'];
				   $destinationPath = public_path('uploads/user_profile/'.$user_obj->id); 
				   if(!File::exists($destinationPath)) {
					   File::makeDirectory($destinationPath, $mode = 0777, true, true);
				   }
				   $photo->move($destinationPath, $file_name);
				   $user_profile = User::findOrFail($user_obj->id);
				   $user_profile->profile_photo = $file_name; 
				   $user_profile->save();                      
				} 
			}
            
            return Redirect::route('promoters-list')->with('message','User Successfully Added.');	
        }
        return view('admin.promoters.add_promoters')->with(['country_details' => $country_details,'state_details' => $state_details ]);
    }
    /**
     * State list
     */
    public function getStateList($country_id)
    {   
        $states =  DB::table("states")->where("country_id",$country_id)->pluck("state_name","id");            
        return response()->json($states);
    }

    /**
     * delete user
     */
    public function deleteUser($id)
    {
        $delete_user = User::findOrFail($id);
		$delete_user->delete();
        return Redirect::route('user-list')->with('message','User Successfully Deleted.');	
	   
    }

    public function editUser(Request $request,$id)
    {   
        $acct    = '';
        $user_obj    = User::findOrFail($id);
		$user_account = $user_obj->account;
        $country_details = Country::all();
        $state_details   = State::where('country_id','=',$user_obj->country_id)->get();
        if($request->isMethod('post')){
			$country = Country::find($request->input('country_id'))->country_code;
			$country_data = (new ISO3166)->alpha3($country);
			$stripe = Stripe::make(env('STRIPE_SECRET'));
			
            //<!-- validation rules
		    $rules = [
				'user_name' =>'required|unique:users,user_name,'.$id,
				'email' =>'required|Email|unique:users,email,'.$id,
                'user_image' =>'image|mimes:jpeg,jpg|max:20480',
                'state_id' => 'required',
                
			]; 
			$validator = Validator::make(Input::all(), $rules);
			if ($validator->fails())
			{ 
                $messages = $validator->messages();
				if (!empty($messages)) {
                    if ($messages->has('email')) {
                        return Redirect::route('user-edit',$id)->with('error','Entered Email Already Exists.');
                    }
                    if ($messages->has('user_name')) {
                        return Redirect::route('user-edit',$id)->with('error','Entered User_name Already Exists.');
                    }
                    if ($messages->has('state_id')) {
                        return Redirect::route('user-edit',$id)->with('error','Select United State Country and state');
                    }			         						   
				}
			}
			
			if($request->input('account_number')!="" || $request->input('routing_number')!=""){
				
				
				if($user_account){
					$account_number = $user_account->account_number;
					$routing_number = $user_account->routing_number;
					
					if($account_number==$request->input('account_number') && $routing_number == $request->input('routing_number')){
						
					} else {
						$acct = $stripe->account()->create(array(
						  "type" => "custom",
						  "country" => $country_data['alpha2'],
						  "email" =>$request->input('email'),
						  "external_account" => array(
							"object" => "bank_account",
							"country" => $country_data['alpha2'],
							"currency" => $country_data['currency'][0],
							"routing_number" => $request->input('routing_number'),
							"account_number" =>$request->input('account_number'),
						  ),
						  "legal_entity" => array(

							"first_name" => $request->input('name'),
							'business_name' =>$request->input('email'),
							),

						  "tos_acceptance" => array(
							"date" => strtotime('now'),
							"ip" => $_SERVER['REMOTE_ADDR']
						  )
						));
					}
				} else {
					$acct = $stripe->account()->create(array(
					  "type" => "custom",
					  "country" => $country_data['alpha2'],
					  "email" =>$request->input('email'),
					  "external_account" => array(
						"object" => "bank_account",
						"country" => $country_data['alpha2'],
						"currency" => $country_data['currency'][0],
						"routing_number" => $request->input('routing_number'),
						"account_number" =>$request->input('account_number'),
					  ),
					  "legal_entity" => array(

						"first_name" => $request->input('name'),
						'business_name' =>$request->input('email'),
						),

					  "tos_acceptance" => array(
						"date" => strtotime('now'),
						"ip" => $_SERVER['REMOTE_ADDR']
					  )
					));
					
				}				
			}
			if($acct){
				$user_obj->name       = $request->input('name');
				$user_obj->user_name  = $request->input('user_name');
				$user_obj->email      = $request->input('email');
				$user_obj->country_id = $request->input('country_id');
				$user_obj->state_id   = $request->input('state_id');
				$user_obj->mobile     = $request->input('mobile');

				if(!empty($request->input('password')))
				$user_obj->password  = Hash::make($request->input('password'));

				$user_obj->dob        = date('Y-m-d', strtotime( $request->input('dob') ));
				$user_obj->gender     = $request->input('gender');
				$user_obj->type       = $request->input('type');
				$user_obj->is_active  = ($request->input('is_active') == 1)? '1':'0';
				$user_obj->save();
				if( !empty( $request->file('profile_photo') ) )
				{  
					$photo = $request->file('profile_photo');
					$input['imagename'] = $user_obj->id.'.'.$photo->getClientOriginalExtension();
					$file_name = time().rand(0,99)."_".$input['imagename'];
					$destinationPath = public_path('uploads/user_profile/'.$user_obj->id); 
					if(!File::exists($destinationPath)) {
					   File::makeDirectory($destinationPath, $mode = 0777, true, true);
					}
					// delete the old profile
					if(!empty($user_obj->profile_photo)){
					$old_profile = public_path('uploads/user_profile/'.$user_obj->id.'/'.$user_obj->profile_photo);
					if(file_exists($old_profile)){
						unlink($old_profile);
					} 
					}
					$photo->move($destinationPath, $file_name);
					$user_obj->photo = $file_name;
					$user_profile = User::findOrFail($user_obj->id);
					$user_profile->profile_photo = $file_name; 
					$user_profile->save();                      
				}
			   
				if($user_account){
					$user_account->user_id = $user_obj->id;
					$user_account->routing_number = $request->input('routing_number');
					$user_account->account_number = $request->input('account_number');
					$user_account->stripe_account_id = $acct['id'];
					$user_account->save();
				} else {
					$UserAccount = new  UserAccount();
					$UserAccount->user_id = $user_obj->id;
					$UserAccount->routing_number = $request->input('routing_number');
					$UserAccount->account_number = $request->input('account_number');
					$UserAccount->stripe_account_id = $acct['id'];
					$UserAccount->save();
			   }
				
			} else{
				$user_obj->name       = $request->input('name');
				$user_obj->user_name  = $request->input('user_name');
				$user_obj->email      = $request->input('email');
				$user_obj->country_id = $request->input('country_id');
				$user_obj->state_id   = $request->input('state_id');
				$user_obj->mobile     = $request->input('mobile');
				
				if(!empty($request->input('password')))
					$user_obj->password  = Hash::make($request->input('password'));
				
				$user_obj->dob        = date('Y-m-d', strtotime( $request->input('dob') ));
				$user_obj->gender     = $request->input('gender');
				$user_obj->type       = $request->input('type');
				$user_obj->is_active  = ($request->input('is_active') == 1)? '1':'0';
				$user_obj->save();
				if( !empty( $request->file('profile_photo') ) )
				{  
				   $photo = $request->file('profile_photo');
				   $input['imagename'] = $user_obj->id.'.'.$photo->getClientOriginalExtension();
				   $file_name = time().rand(0,99)."_".$input['imagename'];
				   $destinationPath = public_path('uploads/user_profile/'.$user_obj->id); 
				   if(!File::exists($destinationPath)) {
					   File::makeDirectory($destinationPath, $mode = 0777, true, true);
				   }
				   // delete the old profile
				   if(!empty($user_obj->profile_photo)){
					$old_profile = public_path('uploads/user_profile/'.$user_obj->id.'/'.$user_obj->profile_photo);
					if(file_exists($old_profile)){
						unlink($old_profile);
					} 
					}
				   $photo->move($destinationPath, $file_name);
				   $user_obj->photo = $file_name;
				   $user_profile = User::findOrFail($user_obj->id);
				   $user_profile->profile_photo = $file_name; 
				   $user_profile->save();                      
			   }
			}
            
           return Redirect::route('promoters-list')->with('message','User Successfully Edited.');   
        }

        return view('admin.promoters.edit-promoters')->with(['user_details'=>$user_obj,'country_details' => $country_details,'state_details' => $state_details ]);

    }
	
	public function getAjaxUserFriends(Request $request,$id)
	{
		$user  = User::find($id);
		if(count($user)>0){
			$data = [];
			$data = User::ajax_user_friends($id);
			if(count($data)>0){
				return Datatables::of($data)->make(true);
				
			}else{
				return Datatables::of($data)->make();
			}
			
		}
		
	}
	/***** get promoter events *******/
	public function getAjaxPromoterEvents(Request $request,$id)
	{
		$user  = User::find($id);
		if(count($user)>0){
			$data = [];
			$data = Event::where('user_id',$id)->latest('id');
			if(count($data)>0){
				return Datatables::of($data)->make(true);
				
			}else{
				return Datatables::of($data)->make();
			}
			
		}
		
	}
	/*********************/
	/**
     * Promoters listing
     */
    public function getAjaxPromoters()
    {   
        $user_details    = User::orderBy('id', 'desc')->where('type','promoter')->with(['country','state'])->latest('id');
		
		return Datatables::of($user_details)
        ->add_column('country', function($user) {
            return $user->country ? $user->country->country_name : '';
        })
		/* ->add_column('user_status', function($user) {
            return $user->status==1 ? 'Yes' : 'No';
        }) */
		->add_column('state', function($user) {
            return $user->state ? $user->state->state_name : '';
        })
        ->make(true);
	
    }
}

<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Country;
use App\Models\State;
use App\Models\UserPhotoGallery;
use App\Models\UserFriend;
use Input;
use File;
use Illuminate\Support\Facades\Hash;
use Mail;
use Config;
use Image;
use App\Library\ImageManipulator;

class RegisterController extends Controller
{

    public function __construct()
    {
        
    }

   
  
	public function register_user_test(Request $request){
		$admin_email = Config::get('constants.admin_email');
		$data = array();
		$rules = array(
		  'name'     => 'required', 
		  'email'    => 'required',
		  'password' => 'required',
		  'country'  => 'required',
		  'username'  => 'required',
		);
		
		$validator = Validator::make($request->all(), $rules);
		if(!$validator->fails()){
			$email = $request->input('email'); 
			$username = $request->input('username'); 
			$user_details = User::check_user_exits($email);
			$user_details_by_username = User::check_user_exits_by_username($username);
			
			if(empty($user_details) && empty($user_details_by_username)){   
				//echo $request->input('is_promoter'); die;
				$user = new User();
				$user->name       = $request->input('name');
				$user->email      = $request->input('email');
				$user->user_name  = $request->input('username');
				$user->password   = Hash::make($request->input('password'));
				$user->country_id = $request->input('country');
				$user->gender     = $request->input('gender');
				if($request->input('is_promoter') === 'true'){
					$user->type   = 'promoter';
				}
				if($request->input('state') != '' && $request->input('state') != 'undefined'){
					$user->state_id   = $request->input('state');
				}
				if($request->input('dob') != '' && $request->input('dob') != 'undefined'){
				$user->dob		  = date('Y-m-d', strtotime( $request->input('dob') ));
				}
				if($request->input('is_promoter') === 'true'){
					$user->is_active  = '0';
				}else{
					$user->is_active  = '1';
				}
				
				$user->save();
				if(!empty($user->id)){
					if($user->type=='promoter'){
						Mail::send('admin.emails.registration_promoter',
							[],
							function($message) use($email)
							{
							   $message->to($email)->subject('Carnivalist Sign up.');
							}
						);

					}else{
						Mail::send('admin.emails.registration',
							[],
							function($message) use($email)
							{
							   $message->to($email)->subject('Carnivalist Sign up.');
							}
						);
					}
					Mail::send('admin.emails.new_user',
						[],
						function($message) use($admin_email)
						{
						   $message->to($admin_email)->subject('Carnivalist Sign up.');
						}
					);
					
					$data['message'] = "User registered";
					return response( array('data' =>$data ,'response' => 1,'user'=> $user));
				}else{
					$data['message'] = "User registration failed";
					return response( array('data' =>$data ,'response' => 0));
				}
													 
			}elseif($user_details_by_username && $user_details){
			  $data['message'] = "Email and user name already exist";
			  return response( array('data' =>$data ,'response' => 0));
			}elseif($user_details_by_username && empty($user_details)){
			  $data['message'] = "User name already exist";
			  return response( array('data' =>$data ,'response' => 0));
			}elseif(empty($user_details_by_username) && $user_details){
			  $data['message'] = "Email already exist";
			  return response( array('data' =>$data ,'response' => 0));
			}
		  
		}else{
		  $data['message'] = "Please fill all required fields";
		  return response( array('data' =>$data ,'response' => 0));
		}

	}
	
	public function register_user(Request $request){
		$admin_email = Config::get('constants.admin_email');
		$data = array();
		$rules = array(
		  'name'     => 'required', 
		  'email'    => 'required',
		  'password' => 'required',
		  'country'  => 'required',
		);
		$type = '';
		$validator = Validator::make($request->all(), $rules);
		if(!$validator->fails()){
			$email = $request->input('email'); 
			$user_details = User::check_user_exits($email);
			
			if(empty($user_details)){   
				//echo $request->input('is_promoter'); die;
				$user = new User();
				$user->name       = $request->input('name');
				$user->email      = $request->input('email');
				$user->user_name  = $request->input('name');
				$user->password   = Hash::make($request->input('password'));
				$user->country_id = $request->input('country');
				$user->gender     = $request->input('gender');
				if($request->input('is_promoter') === 'true'){
					$user->type   = 'promoter';
					$type = 'promoter';
				}else{
					$type = 'user';
				}
				if($request->input('state') != '' && $request->input('state') != 'undefined'){
					$user->state_id   = $request->input('state');
				}
				if($request->input('dob') != '' && $request->input('dob') != 'undefined'){
				$user->dob		  = date('Y-m-d', strtotime( $request->input('dob') ));
				}
				if($request->input('is_promoter') === 'true'){
					$user->is_active  = '0';
				}else{
					$user->is_active  = '1';
				}
				
				$user->save();
				if(!empty($user->id)){
					if($user->type=='promoter'){
						Mail::send('admin.emails.registration_promoter',
							[],
							function($message) use($email)
							{
							   $message->to($email)->subject('Carnivalist Sign up.');
							}
						);

					}else{
						Mail::send('admin.emails.registration',
							[],
							function($message) use($email)
							{
							   $message->to($email)->subject('Carnivalist Sign up.');
							}
						);
					}
					Mail::send('admin.emails.new_user',
						[],
						function($message) use($admin_email)
						{
						   $message->to($admin_email)->subject('Carnivalist Sign up.');
						}
					);
					
					$data['message'] = "User registered";
					
					return response( array('data' =>$data ,'response' => 1,'type'=> $type));
				}else{
					$data['message'] = "User registration failed";
					return response( array('data' =>$data ,'response' => 0));
				}
													 
			}else{
			  $data['message'] = "Email already exist";
			  return response( array('data' =>$data ,'response' => 0));
			}
		  
		}else{
		  $data['message'] = "Please fill all required fields";
		  return response( array('data' =>$data ,'response' => 0));
		}

	}
	
	public function country_list(){
		$country_detail = Country::orderBy('id', 'asc')->get();
		$country_list = [];
		if(count($country_detail)>0){
			foreach($country_detail as $country){
				$country_list[] = ['id'=>$country->id,'name'=>$country->country_name];	
			}
		}
		if(count($country_list)>0){
			$data['list'] = $country_list;
			return response( array('data' =>$data ,'response' => 1));
		}else{
			return response( array('data' =>'error' ,'response' => 0));
		}
		
	}
		
	public function state_list(){
		$state_details = State::all()->groupBy('country_id');
		$state_list = [];
		if(count($state_details)>0){
			foreach($state_details as $country_id => $states){
				$list = [];
				if(count($states)>0){
					foreach($states as $state ){
						$list[] = ['id'=>$state->id,'name'=>$state->state_name];
					}				
				}
				$state_list[$country_id] = $list;
			}
		}
		if(count($state_list)>0){
			$data['list'] = $state_list;
			return response( array('data' =>$data ,'response' => 1));
		}else{
			return response( array('data' =>'error' ,'response' => 0));
		}
		
	}
	
	public function edit_profile(Request $request){
		$id = $request->input('id');
		$data = array();
		$rules = array(
		  'id' => 'required',
		  'email' =>'required|Email|unique:users,email,'.$id,
		  'username' =>'required|unique:users,user_name,'.$id,
		);
		$validator = Validator::make($request->all(), $rules);
		if(!$validator->fails()){
			$email = $request->input('email'); 
			$messages = $validator->messages();
						
			$user = User::findOrFail($request->input('id'));
			if(count($user)>0){					
				$user->name       = $request->input('name');
				$user->email      = $request->input('email');
				$user->user_name      = $request->input('username');
				if(!empty($request->input('password'))){
				$user->password   = Hash::make($request->input('password'));
				}
				$user->mobile = $request->input('mobile');
				$user->country_id = $request->input('country');
				if($request->input('state') != 'undefined'){
					$user->state_id   = $request->input('state');
				}
				$user->gender     = $request->input('gender');
				$user->dob		  = date('Y-m-d', strtotime( $request->input('dob') ));
				$user->save();
				$data_to_send ="You profile have been successfully updated on Carnivalist."; 
				$heading = "Thank You";
				Mail::send('emails.profile_updated',
					['data'=>$data_to_send,'heading'=>$heading],
					function($message) use($email)
					{
					   $message->to($email)->subject('Carnivalist Profile Updated.');
					}
				);
				$data['message']  = "Profile saved";
				$data['user'] = $user;
				return response( array('data' =>$data ,'response' => 1));
			}else{
				$data['message'] = "Profile not saved";
				return response( array('data' =>$data ,'response' => 0));			
			}
						
		}else{
		  $data['message'] = "Email already exist";
		  return response( array('data' =>$data ,'response' => 0));
		}
	}

	public function uploadImage(Request $request){
		$test="";
		$path="";
		$email = $request->input('email');
		$user_obj= User::where("email","=",$email)->first(); 
		$data =  $request->file('file');
		
		if( (!empty( $request->file('file'))) && (count($user_obj) > 0 ) )
		{ 
			
		   $photo = $request->file('file');
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
				$old_thumbnail = public_path('uploads/user_profile/'.$user_obj->id.'/thumbnail_'.$user_obj->profile_photo);
				if(file_exists($old_thumbnail)){
					unlink($old_thumbnail);
				}				
			}
		   $test = $photo->move($destinationPath, $file_name);
		   $user_obj->profile_photo = $file_name; 
		   $user_obj->save(); 
		   $data_to_send ="You profile image have been successfully updated on Carnivalist."; 
			$heading = "Thank You";
			Mail::send('emails.profile_updated',
				['data'=>$data_to_send,'heading'=>$heading],
				function($message) use($email)
				{
				   $message->to($email)->subject('Carnivalist Profile Image Updated.');
				}
			);
		   
           if($test){
			   $path = public_path('uploads/user_profile/'.$user_obj->id).'/'.$user_obj->profile_photo;
			   $thumbnail = public_path('uploads/user_profile/'.$user_obj->id).'/'.'thumbnail_'.$user_obj->profile_photo;
			   
			   if(file_exists($path))
				{
					$this->create_jpeg_thumbnail($path,"",150, 150,$thumbnail);
					//$this->create_thumbnail($path,"",150, 150,$thumbnail);
				}
		   }
		}
        return response($path);
	}
	
	public function create_thumbnail($imgSrc,$thumbDirectory,$w,$h,$image){
		/*$thumbnail1 = Image::make($imgSrc);
		$thumbnail1->resize($w, $h);
		$thumbnail1->save($image); */
		
		 $manipulator = new ImageManipulator($imgSrc);
		$width 	= $manipulator->getWidth();
		$height = $manipulator->getHeight();
		if($width > $w && $height > $h){
			self::crop_image($imgSrc, $image, $w, $h);
			

			
		
		}
		else{
			
			$transparent_image = $w.'x'.$h.'.png';
			
			
			$img = Image::make(public_path('/images/'.$transparent_image));
			
			$original = Image::make($imgSrc);
			// create a new Image instance for inserting
			
			$img->insert($original,'center');
			
			$original->resize($w, $h, function ($constraint) {
				$constraint->aspectRatio();
			});
			$img->save($image);
		}
	}
	
	public static function crop_image($imgSrc, $image, $w, $h) {
		$manipulator = new ImageManipulator($imgSrc);
		$width 	= $manipulator->getWidth();
		$height = $manipulator->getHeight();
		$centreX = round($width / 2);
		$centreY = round($height / 2);
		// our dimensions will be 200x130
		$x1 = $centreX - ($w/2); // 200 / 2
		$y1 = $centreY - ($h/2); // 130 / 2

		$x2 = $centreX + ($w/2); // 200 / 2
		$y2 = $centreY + ($h/2); // 130 / 2

		// center cropping to 200x130
		$newImage = $manipulator->crop($x1, $y1, $x2, $y2);
		// saving file to uploads folder
		$manipulator->save($image);
    }
	
	public static function resize_image($path, $size, $w, $h) {
        
		
		$original_file_path = $upload_dir . '/' . $file_name;
		$manipulator = new ImageManipulator($original_file_path);
		$width 	= $manipulator->getWidth();
		$height = $manipulator->getHeight();
		
		
		if($width > $w && $height > $h){
		
			//crop function caall
			
			self::crop_image($file_name, $upload_dir, $size, $w, $h);
		
		}
        else{
			
			$new_dir = $upload_dir . '/' . $size;
			self::createDirPath($new_dir);
			
			$new_file_path = $new_dir . '/' . $file_name;
			
			$transparent_image = $w.'x'.$h.'.png';
			
			
			$img = Image::make(public_path('/images/'.$transparent_image));
			
			$original = Image::make($original_file_path);
			// create a new Image instance for inserting
			
			$img->insert($original,'center');
			
			$original->resize($w, $h, function ($constraint) {
				$constraint->aspectRatio();
			});
			$img->save($new_file_path);
		}
		
		
    }
	public function get_user_gallery_image(Request $request){
		$email = $request->input('email');
		$user  = User::where('email','=',$email)->first();
		if(count($user)>0){
			$gallery = UserPhotoGallery::where('user_id','=',$user->id)->get();
			return response(array('data' => $gallery, 'response' => 1));			
		}else{
			return response(array('data' => 'error', 'response' => 0));
		}						
	}
	
	public function upload_user_gallery_image(Request $request){
		$email     = $request->input('email');
		$user_obj  = User::where('email','=',$email)->first();
		if((!empty( $request->file('file'))) && (count($user_obj) > 0 )){
			   $photo = $request->file('file');
			   $input['imagename'] = $user_obj->id.'.'.$photo->getClientOriginalExtension();
			   $file_name = time().rand(0,99)."_".$input['imagename'];
			   $destinationPath = public_path('uploads/user_photo_gallery/'.$user_obj->id); 
			   if(!File::exists($destinationPath)) {
				   File::makeDirectory($destinationPath, $mode = 0777, true, true);
			   }
			  				
			   $photo->move($destinationPath, $file_name);
			   $gallery = new UserPhotoGallery();
			   $gallery->user_id            =  $user_obj->id;
			   $gallery->user_gallery_image = $file_name; 
			   $gallery->save(); 
			   
			   $data_to_send ="You gallery images have been successfully uploaded on Carnivalist."; 
				$heading = "Thank You";
				Mail::send('emails.profile_updated',
					['data'=>$data_to_send,'heading'=>$heading],
					function($message) use($email)
					{
					   $message->to($email)->subject('Carnivalist Profile Updated.');
					}
				);
			return response('success');			
		}else{
			return response('error');
		}
		
	}
	
	public function sent_friend_request(Request $request){
		$user_email = $request->input('user_email');
		$friend_id  = $request->input('friend_id');
		$requester  = User::where('email','=',$user_email)->first();
		$friend  = User::where('id','=',$friend_id)->first();
		if(count($requester)>0){
			$add_friend = new UserFriend();
			$add_friend->user_id    = $requester->id;
			$add_friend->friend_id  = $friend_id;
			$add_friend->request_on = date('Y-m-d H:i:s');
			$add_friend->save();
			
			Mail::send('emails.friend_request',
				['data'=>'You friend request have been successfully sent to '.$friend->name.'','heading'=>'Thank You'],
				function($message) use($requester)
				{
				   $message->to($requester->email)->subject('Friend request successfully sent.');
				}
			);
			
			Mail::send('emails.friend_request',
				['data'=>'You have pending friend request from '.$requester->name.'','heading'=>'Thank You'],
				function($message) use($friend)
				{
				   $message->to($friend->email)->subject('Friend request pending.');
				}
			);
			
			return response( array('data' =>"success" ,'response' => 1));			
		}else{
			return response( array('data' =>"error" ,'response' => 0));
		}
	}
	
	public function check_friend_request(Request $request){
		$user_email = $request->input('user_email');
		$friend_id  = $request->input('friend_id');
		$requester  = User::where('email','=',$user_email)->first();
		if(count($requester)>0){
			$check = UserFriend::where('user_id','=',$requester->id)->where('friend_id','=',$friend_id)->first();
			if(count($check)>0){
				return response( array('data' =>"success" ,'response' => 1));
			}else{
				return response( array('data' =>"error" ,'response' => 0));
			}
			
		}
	}
	
	public function confirm_friend_request(Request $request){
		$user_email = $request->input('user_email');
		$friend_id  = $request->input('friend_id');
		$friend = User::where('id','=',$friend_id)->first();
		$user       = User::where('email','=',$user_email)->first();
		if(count($user)>0){
			$confrim = UserFriend::where('friend_id','=',$user->id)->where('user_id','=',$friend_id)->first();
			if(count($confrim)>0){
				$confrim->approved_on = date('Y-m-d H:i:s');
				$confrim->is_friend = 1;
				$confrim->save();
				Mail::send('emails.friend_request',
					['data'=>'You friend request has been approved by '.$user->name.'','heading'=>'Thank You'],
					function($message) use($friend)
					{
					   $message->to($friend->email)->subject('Friend request accepted.');
					}
				);
				return response( array('data' =>$friend_id ,'response' => 1));
			}else{
				return response( array('data' =>"error" ,'response' => 0));
			}
			
		}
	}
	
	public function check_friend(Request $request){
		$user_id       = $request->input('user');
		$friend        = $request->input('friend_id');
		if(count($friend)>0){
			$sent_friend = UserFriend::where('friend_id','=',$user_id)->where('user_id','=',$friend)
								->where('is_friend','=',1)
								->first();
			$added_friend = UserFriend::where('friend_id','=',$friend)->where('user_id','=',$user_id)
								->where('is_friend','=',1)
								->first();
			if( count($sent_friend)>0 || count($added_friend)>0 ){
				return response( array('data' =>"success" ,'response' => 1));
			}else{
				return response( array('data' =>"error" ,'response' => 0));
			}
		}else{
			return response( array('data' =>"error" ,'response' => 0));	
		}
	}
	
	public function check_received_friend_request(Request $request){
		$user_email = $request->input('user_email');
		$friend_id  = $request->input('friend_id');
		$user       = User::where('email','=',$user_email)->first();
		if(count($user)>0){
			$check = UserFriend::where([['friend_id','=',$user->id],['user_id','=',$friend_id],['approved_on','=',null]])
								->first();
			if(count($check)>0){
				return response( array('data' =>"success" ,'response' => 1));
			}else{
				return response( array('data' =>"error" ,'response' => 0));
			}
			
		}
	}
	
	public function cancel_friend_request(Request $request){
		$user_email = $request->input('user_email');
		$friend_id  = $request->input('friend_id');
		$requester  = User::where('email','=',$user_email)->first();
		$friend  = User::where('id','=',$friend_id)->first();
		if(count($requester)>0){
			$check = UserFriend::where([['user_id','=',$requester->id],['friend_id','=',$friend_id]])->first();
			if(count($check)>0){
				$check->delete();
				Mail::send('emails.friend_request',
					['data'=>'You have successfully cancelled friend request','heading'=>'Thank You'],
					function($message) use($user_email)
					{
					   $message->to($user_email)->subject('Friend request cancelled.');
					}
				);
				return response( array('data' =>"success" ,'response' => 1));
			}else{
				return response( array('data' =>"error" ,'response' => 0));
			}
			
		}
	}
	
	public function delete_friend_request(Request $request){
		$user_email = $request->input('user_email');
		$friend_id  = $request->input('friend_id');
		$requester  = User::where('email','=',$user_email)->first();
		$friend  = User::where('id','=',$friend_id)->first();
		if(count($requester)>0){
			$check = UserFriend::where([['friend_id','=',$requester->id],['user_id','=',$friend_id]])->first();
			if(count($check)>0){
				$check->delete();
				Mail::send('emails.friend_request',
					['data'=>'Your friend request cancelled by '.$user_email.'','heading'=>'Sorry'],
					function($message) use($friend)
					{
					   $message->to($friend->email)->subject('Friend request deleted.');
					}
				);
				return response( array('data' =>"success" ,'response' => 1));
			}else{
				return response( array('data' =>"error" ,'response' => 0));
			}
			
		}
	}
	
	public function pending_friend_request(Request $request){
		$user_email = $request->input('user_email');
		$user  = User::where('email','=',$user_email)->first();
		if(count($user)>0){
			
			$check = UserFriend::where([['friend_id','=',$user->id],['approved_on','=',null]])
								->with('user')
								->get();
			
			$data = [];
			if(count($check)>0){
				
				foreach($check as $image){
					$thumbnail = public_path('uploads/user_profile/'.$image->user->id.'/thumbnail_'.$image->user->profile_photo);
					if(file_exists($thumbnail)){
						
						$image->user->profile_photo = 'thumbnail_'.$image->user->profile_photo;
					}
				}
				$data = $check;
				return response( array('data' =>$data ,'response' => 1));
			}else{
				return response( array('data' =>$data ,'response' => 1));
			}
			
		}
	}
	
	
	/*public function find_other_user_friend(Request $request){
		$user = $request->input('friend_id');
		if($user > 0){
			$check = UserFriend::where([['is_friend','=',1],['friend_id','=',$user]])
								->orWhere([['is_friend','=',1],['user_id','=',$user]])
								->with('user')
								->get();
			$data = [];
			if(count($check)>0){
				$data = $check;
				return response( array('data' =>$data ,'response' => 1));
			}else{
				return response( array('data' =>$data ,'response' => 1));
			}
		}
	}*/
	
	public function other_user_gallery(Request $request){
		$user = $request->input('friend_id');
		if($user > 0){
			$gallery = UserPhotoGallery::where('user_id','=',$user)->get();
			return response(array('data' => $gallery, 'response' => 1));			
		}else{
			return response(array('data' => 'error', 'response' => 0));
		}
	}
	
	public function remove_friend(Request $request){
		$user_email = $request->input('user_email');
		$user  = User::where('email','=',$user_email)->first();
		$friend_id = $request->input('friend_id');
		if(count($user)>0){
			$remove = UserFriend::where([['user_id','=',$user->id],['friend_id','=',$friend_id],['is_friend','=',1]])
								->orWhere([['user_id','=',$friend_id],['friend_id','=',$user->id],['is_friend','=',1]])
								->first();
			if(count($remove)>0){
				$remove->delete();
				return response(array('data' => $friend_id, 'response' => 1));	
				
			}else{
				return response(array('data' => 'error', 'response' => 0));
			}			
		}else{
			return response(array('data' => 'error', 'response' => 0));
		}
		
		
	}
	
	public function find_other_user_friend(Request $request){
		$user  = User::find($request->friend_id);
		if(count($user)>0){
			$data = [];
			$data = User::user_friends($request->friend_id);
			if(count($data)>0){
				foreach($data as $image){
					$thumbnail = public_path('uploads/user_profile/'.$image->id.'/thumbnail_'.$image->profile_photo);
					if(file_exists($thumbnail)){
						
						$image->profile_photo = 'thumbnail_'.$image->profile_photo;
				
					}
				}
				return response( array('data' =>$data ,'response' => 1));
				//return response()->json(['data'=>$data], 200);
				
			}else{
				return response( array('data' =>$data ,'response' => 1));
				//return response()->json(['data'=>$data], 401);
			}
			
		}
		else{
			return response( array('status'=>'failure','message'=>'Please provide user details','response' => 0));
		}
	}
	
	public function find_friend(Request $request){
		$user_email = $request->input('user_email');
		$user  = User::where('email','=',$user_email)->first();
		if(count($user)>0){
			$data = [];
			$data = User::user_friends($user->id);
			if(count($data)>0){
				foreach($data as $image){
					$thumbnail = public_path('uploads/user_profile/'.$image->id.'/thumbnail_'.$image->profile_photo);
					if(file_exists($thumbnail)){
						
						$image->profile_photo = 'thumbnail_'.$image->profile_photo;
				
					}
				}
				return response( array('data' =>$data ,'response' => 1));
				
			}else{
				return response( array('data' =>$data ,'response' => 1));
			}
			
		}
		else{
			return response( array('status'=>'failure','message'=>'Please provide user details','response' => 0));
		}
	}
	
	public function facebook_login(Request $request){
		$data = array();
		$rules = array(
		  'name'     => 'required', 
		  'email'    => 'required',
		);
		
		$validator = Validator::make($request->all(), $rules);
		if(!$validator->fails()){
			$email = $request->input('email'); 
			$user_details = User::check_user_exits($email);
			
			if(empty($user_details)){   
				
				$user = new User();
				$user->name       = $request->input('name');
				$user->email      = $request->input('email');
				$user->user_name  = $request->input('name');
				$user->password   = '';
				$user->is_active  = '1';
				$user->login_type  = '2';
				$user->save();
				if(!empty($user->id)){
					Mail::send('admin.emails.registration',
						[],
						function($message) use($email)
						{
						   $message->to($email)->subject('Carnivalist Sign up.');
						}
					);
					$data['message'] = "User registered";
					$data['type'] = 'user';
					$data['email'] = $request->input('email');
					return response( array('data' =>$data ,'response' => 1));
				}else{
					$data['message'] = "User registration failed";
					return response( array('data' =>$data ,'response' => 0));
				}
													 
			}else{
			  $data['message'] = "Email already exist";
			  $data['type'] = $user_details->type;
			  $data['email'] = $user_details->email;
			  return response( array('data' =>$data ,'response' => 1));
			}
		  
		}else{
		  $data['message'] = "Please fill all required fields";
		  return response( array('data' =>$data ,'response' => 0));
		}

	}
	
	
	
	public function create_jpeg_thumbnail($imgSrc,$thumbDirectory,$thumbnail_width,$thumbnail_height,$image) {
		
         //$imgSrc is a FILE - Returns an image resource.
        $thumbDirectory = trim($thumbDirectory);
	 	$imageSourceExploded = explode('/', $imgSrc);
	  	$imageName = $imageSourceExploded[count($imageSourceExploded)-1];
	  	$imageDirectory = str_replace($imageName, '', $imgSrc);
	  	$filetype = explode('.',$imageName);
	  	$filetype = strtolower($filetype[count($filetype)-1]);
	  
	  //getting the image dimensions 
	     list($width_orig, $height_orig) = getimagesize($imgSrc);  
	     
	     
	     //$myImage = imagecreatefromjpeg($imgSrc);
		  if ($filetype == 'jpg'  or $filetype == 'JPG' ) {
		      $myImage = imagecreatefromjpeg("$imageDirectory/$imageName");
		  } else
		  if ($filetype == 'jpeg'  or $filetype == 'JPEG' ) {
		      $myImage = imagecreatefromjpeg("$imageDirectory/$imageName");
		  } else
		  if ($filetype == 'png'  or $filetype == 'PNG' ) {
		      $myImage = imagecreatefrompng("$imageDirectory/$imageName");
		  } else
		  if ($filetype == 'gif' or $filetype == 'GIF') {
		      $myImage = imagecreatefromgif("$imageDirectory/$imageName");
		  }
	     
	     	$ratio_orig = $width_orig/$height_orig;
	    
		     if ($thumbnail_width/$thumbnail_height > $ratio_orig) {
		        $new_height = $thumbnail_width/$ratio_orig;
		        $new_width = $thumbnail_width;
		     }  else{
		        $new_width = $thumbnail_height*$ratio_orig;
		        $new_height = $thumbnail_height;
		     }
	    
		     $x_mid = $new_width/2;  //horizontal middle
		     $y_mid = $new_height/2; //vertical middle
	    
	     	$process = imagecreatetruecolor(round($new_width), round($new_height));    
	     
		     if($ratio_orig>=1){
			      imagecopyresampled($process, $myImage, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
			     
			      $thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
			      
			      imagecopyresampled($thumb, $process, 0, 0, ($x_mid-($thumbnail_width/2)), ($y_mid-($thumbnail_height/2)), $thumbnail_width, $thumbnail_height, $thumbnail_width, $thumbnail_height);
		     }else{
			      	$ratio_desc = ceil(($thumbnail_height/$height_orig)*100);
				   $new_height = round(($ratio_desc/100)*$height_orig);
				   $new_width = round(($ratio_desc/100)*$width_orig);
			   
				   $thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height) ;
				   // fill rest color with grey background
				   $grey = imagecolorallocate($thumb, 62, 62, 62);
				   imagefill($thumb, 0, 0, $grey);
				   
				   imagecopyresampled($thumb, $myImage, round(($thumbnail_width-$new_width)/2), 0, 0, 0, $new_width, $thumbnail_height, $width_orig, $height_orig);
		     }
	     
	     $thumbImageName = $image;
	     $destination = $thumbDirectory=='' ? $thumbImageName : $thumbDirectory."/".$thumbImageName;
	     imagejpeg($thumb, $destination, 100);
	     return $thumbImageName; 
	 }
	 
	public function delete_user_gallery_image(Request $request){
		$user_id = $request->input('user_id');
		$user  = User::find($user_id);
		if($user){
			$image = $request->input('image');
			$destinationPath = public_path('uploads/user_photo_gallery/'.$user->id.'/'.$image); 
			
			$delte_gallery = UserPhotoGallery::where(['user_id'=>$user->id,'user_gallery_image'=>$image])->delete();
			if($delte_gallery){
				$gallery = UserPhotoGallery::where(['user_id'=>$user->id])->get();
				if(File::exists($destinationPath)) {
					unlink($destinationPath);
				}
				return response(array('data' => $gallery, 'response' => 1));
			}else{
				return response(array('data' => 'Error in deleting file', 'response' => 0));
			}
						
		}else{
			return response(array('data' => 'User not found', 'response' => 0));
		}						
	}
	
}

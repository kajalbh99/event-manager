<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Country;
use App\Models\State;
use App\Models\BandReview;
use App\Models\EventReview;
use App\Models\Ticket;
use App\Models\Event;
use App\Models\Band;
use DB;
use Redirect;
use Auth;
use File;
use Validator;
use Illuminate\Support\Facades\Input;
use Carbon\Carbon;
//use Yajra\Datatables\Datatables;
use Yajra\Datatables\Facades\Datatables;

use Image;
use App\Library\ImageManipulator;

class UserController extends Controller
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
		$band_reviews =BandReview::where('is_approved',0)->count();
		$event_reviews =EventReview::where('is_approved',0)->count();
		$user_requests =User::where(['is_active'=>0,'type'=>'user'])->count();
		$promoter_requests =User::where(['is_active'=>0,'type'=>'promoter'])->count();
		$tickets_requests =Ticket::with('events','requsted_user','member')->with('type')->has('events')->where(['status'=>0])->count();
		$upcoming_events =Event::whereDate('event_date','>=',Carbon::today()->toDateString())->count();
		$total_bands =Band::count();
		return view('admin.dashboard.home')->with(['total_reviews'=>(int)$band_reviews+(int)$event_reviews,'user_requests'=>$user_requests,'promoter_requests'=>$promoter_requests,'tickets_requests'=>$tickets_requests,'upcoming_events'=>$upcoming_events,'total_bands'=>$total_bands]);
    }

    /**
     * User listing
     */
    public function listUsers()
    {   
         $user_details    = User::orderBy('id', 'desc')->where('type','!=','promoter')->with(['country','state'])->get();
		  return view('admin.users.list_users')->with(['user_details' => $user_details ]);
    }

    /**
     * New User
     */
    public function addUser(REquest $request)
    {   
        $country_details = Country::all();
        $state_details   = State::all();
        $user_details    = User::all();
        if($request->isMethod('post')){
            //echo "<pre>"; print_r($request->all()); die;

            //<!-- validation rules
		    $rules = [
				'user_name' =>'required|unique:users,user_name',
                'email' =>'required|Email|unique:users,email',
                //'state_id' => 'required',
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
                    /* if ($messages->has('state_id')) {
                        return Redirect::route('user-add')->with('error','Select United State Country and state');
                    } */			         						   
				}
			}
            //validation rules ended-->

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

			   $path = public_path('uploads/user_profile/'.$user_obj->id).'/'.$file_name;
			   $thumbnail = public_path('uploads/user_profile/'.$user_obj->id).'/'.'thumbnail_'.$file_name;
			   
			    if(file_exists($path))
				{
					$this->create_jpeg_thumbnail($path,"",150, 150,$thumbnail);
					//$this->create_thumbnail_with_crop($path,"",150, 150,$thumbnail);
				}
           }
            
            return Redirect::route('user-list')->with('message','User Successfully Added.');	
        }
        return view('admin.users.add_users')->with(['country_details' => $country_details,'state_details' => $state_details ]);
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
		$user_details    = User::findOrFail($id);
        $country_details = Country::all();
        $state_details   = State::where('country_id','=',$user_details->country_id)->get();
        if($request->isMethod('post')){
            //<!-- validation rules
		    $rules = [
				'user_name' =>'required|unique:users,user_name,'.$id,
				'email' =>'required|Email|unique:users,email,'.$id,
                'user_image' =>'image|mimes:jpeg,jpg|max:20480',
                //'state_id' => 'required',
                
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
                    /* if ($messages->has('state_id')) {
                        return Redirect::route('user-edit',$id)->with('error','Select United State Country and state');
                    } */			         						   
				}
			}
            //validation rules ended-->
            $user_obj = User::findOrFail($id);
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
				$thumbnail = public_path('uploads/user_profile/'.$user_obj->id.'/thumbnail_'.$user_obj->profile_photo);
                if(file_exists($thumbnail)){
                    unlink($thumbnail);
                } 
                }
               $photo->move($destinationPath, $file_name);
               $user_obj->photo = $file_name;
               $user_profile = User::findOrFail($user_obj->id);
               $user_profile->profile_photo = $file_name; 
               $user_profile->save();
			   
			   $path = public_path('uploads/user_profile/'.$user_obj->id).'/'.$file_name;
			   $thumbnail = public_path('uploads/user_profile/'.$user_obj->id).'/'.'thumbnail_'.$file_name;
				if(file_exists($path))
				{
					$this->create_jpeg_thumbnail($path,"",150, 150,$thumbnail);
					//$this->create_thumbnail_with_crop($path,"",150, 150,$thumbnail);
					
				}
           }
            
           return Redirect::route('user-list')->with('message','User Successfully Edited.');   
        }

        return view('admin.users.edit-users')->with(['user_details'=>$user_details,'country_details' => $country_details,'state_details' => $state_details ]);

    }
	
	public function create_thumbnail_with_crop($imgSrc,$thumbDirectory,$w,$h,$image){
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
	/**
     * Promoters listing
     */
    public function getAjaxPromoters()
    {   
        $user_details    = User::orderBy('id', 'desc')->where('type','promoter')->with(['country','state'])->latest('id');
		
		return Datatables::of($user_details)
        ->add_column('country', function($user) {
            return $user->country->country_name ? $user->country->country_name : '';
        })
		->add_column('state', function($user) {
            return $user->state->state_name ? $user->state->state_name : '';
        })
        ->make(true);
	
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
	 
	public function getAjaxUsers()
    {   
        $user_details    = User::orderBy('id', 'desc')->where('type','!=','promoter')->where('type','!=','admin')->with(['country','state'])->latest('id');
		
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
	
	public function editUserApproveStatus($id,$status){
		 $user = User::findOrFail($id);
		 $user->is_active = $status;
		 $user->save();
		 return redirect()->back()->with('message','User updated.');
	}
	
	public function postUpdateStatus(Request $request){
		$id = $request->id;
		$val = $request->value;
		$user  = User::find($id);
		if($user){
			$user->is_active = $val;
			$user->save();
			return response( array('data' =>'Updated successfuly' ,'response' => 1));
		} else {
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
}

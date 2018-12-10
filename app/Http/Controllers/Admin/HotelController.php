<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Carnival;
use App\Models\Country;
use App\Models\State;
use App\Models\HotelGallery;
use App\Models\HotelReview;
use App\Models\HotelReviewMedias;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\Input;
use Redirect;
use Auth;
use File;
//use Yajra\Datatables\Datatables;
use Yajra\Datatables\Facades\Datatables;
use Mail;
use Config;
use Image;
use App\Library\ImageManipulator;

class HotelController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin', ['except' => 'logout']);
    }

    public function index()
    {   
         return view('admin.hotels.list_hotels');
    }
	public function getAjaxHotels()
	{
		$hotels = Hotel::orderBy('id', 'desc')->latest('id');
		return Datatables::of($hotels)->make(true);
	}
    /**
     * Create unique slug name
     */
    public function createSlug($slug){
        $next = 1;
        while(Hotel::where('hotel_slug', '=', $slug)->first()) {          
            $pos = strrpos($slug, "_");
            $digit = substr($slug, -1);
            $digit = intval($digit);
            if($digit == 0){
               $i = 1;
               $slug = $slug.'-'.$i;
                
            }else{
                $slug = substr($slug, 0, -2);
                $slug = $slug.'-'.$next;
                $next++;
            }          
        }
        return $slug;
    }

    /**
     * Edit slug name
     */
    public function editSlug($slug,$requested_slug,$id){
        $last_slug = Hotel::select('hotel_slug')->where('id', '=', $id)->first();
       // echo $last_slug->carnival_slug."<br>".$requested_slug; die;
        if($last_slug->event_slug == $requested_slug){
           $slug = $requested_slug;
        }else{
          $slug = $this->createSlug($requested_slug);  
        }

        return $slug;
        
    }

    /**
     * Add new Event
     */
    public function addHotel(Request $request){
      
     $country_details = Country::all();
	 $carnival_details = Carnival::all();
	 $state_details   = State::all();
     if($request->isMethod('post')){
         
         $hotel_obj = new Hotel();
         //echo "<pre>"; print_r($request->all()); die;
         $slug_input =  str_slug($request->input('hotel_name'), '-');      
         $slug = $this->createSlug($slug_input);
         $hotel_obj->hotel_slug = $slug;
         $hotel_obj->user_id = Auth::user()->id;
         $hotel_obj->hotel_name = $request->input('hotel_name');
         $hotel_obj->hotel_location = $request->input('hotel_location');
         $hotel_obj->country_id = $request->input('country_id');
         $hotel_obj->state_id = $request->input('state_id');
         $hotel_obj->carnival_id = $request->input('carnival_id');
         $hotel_obj->hotel_description = $request->input('hotel_description');
         
         $hotel_obj->hotel_privacy = $request->input('hotel_privacy');
         $hotel_obj->is_active = ($request->input('is_active') == 1) ? '1':'0';
		 $hotel_obj->save();
         if( !empty( $request->file('hotel_banner') ) )
         {  
            $banner = $request->file('hotel_banner');
            $input['imagename'] = Auth::user()->id.'.'.$banner->getClientOriginalExtension();
            $file_name = time().rand(0,99)."_".$input['imagename'];
            $destinationPath = public_path('uploads/hotel_banners/'.$hotel_obj->id); 
            if(!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, $mode = 0777, true, true);
            }
            $banner->move($destinationPath, $file_name);
			$hotel = Hotel::findOrFail($hotel_obj->id);
            $hotel->hotel_banner = $file_name;  
			$hotel->save();
			
			$path       = public_path('uploads/hotel_banners/'.$hotel_obj->id).'/'.$hotel->hotel_banner;
			$thumbnail1 = public_path('uploads/hotel_banners/'.$hotel_obj->id).'/'.'small_'.$hotel->hotel_banner;
			$thumbnail2 = public_path('uploads/hotel_banners/'.$hotel_obj->id).'/'.'medium_'.$hotel->hotel_banner;
			$thumbnail3 = public_path('uploads/hotel_banners/'.$hotel_obj->id).'/'.'large_'.$hotel->hotel_banner;
			$this->create_jpeg_thumbnail($path,"",160, 120,$thumbnail1);
			$this->create_jpeg_thumbnail($path,"",375, 250,$thumbnail2);
			$this->create_jpeg_thumbnail($path,"",600,250,$thumbnail3);
			/* if(file_exists($path))
			{
				//$this->create_jpeg_thumbnail($path,"",150, 150,$thumbnail);
				$this->create_thumbnail_with_crop($path,"",160, 120,$thumbnail1);
				$this->create_thumbnail_with_crop($path,"",375, 250,$thumbnail2);
				$this->create_thumbnail_with_crop($path,"",600, 250,$thumbnail3);
			} */
				
        }
       

         //removed images uploaded on file selector
         $removed_image_ids = array_map('intval', explode(',', $request->input('removed_img')));
         $images =  $request->file('hotel_gallery');
         foreach($removed_image_ids  as $id){
            if($id>2){
                unset($images[$id-2]);
            }else{
                unset($images[$id-1]);
            }           
        }
        
         if(count($images)>0){
            foreach($images as $image){
                $hotel_gallery = new HotelGallery();
                if( !empty($image) )
                {  
                $input['imagename'] = Auth::user()->id.'.'.$image->getClientOriginalExtension();
                $file_name = time().rand(0,99)."_".$input['imagename'];
                $destinationPath = public_path('uploads/hotel_gallery/'.$hotel_obj->id); 
                if(!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, $mode = 0777, true, true);
                }
                $image->move($destinationPath, $file_name);                      
            }
                $hotel_gallery->hotel_id = $hotel_obj->id;
                $hotel_gallery->hotel_gallery_image = $file_name; 
                $hotel_gallery->save();
				
				$path       = public_path('uploads/hotel_gallery/'.$hotel_obj->id).'/'.$hotel_gallery->hotel_gallery_image;
				$thumbnail1 = public_path('uploads/hotel_gallery/'.$hotel_obj->id).'/'.'small_'.$hotel_gallery->hotel_gallery_image;
				$thumbnail2 = public_path('uploads/hotel_gallery/'.$hotel_obj->id).'/'.'medium_'.$hotel_gallery->hotel_gallery_image;
				$thumbnail3 = public_path('uploads/hotel_gallery/'.$hotel_obj->id).'/'.'large_'.$hotel_gallery->hotel_gallery_image;
				
				/* if(file_exists($path))
				{
					//$this->create_jpeg_thumbnail($path,"",150, 150,$thumbnail);
					$this->create_thumbnail_with_crop($path,"",160, 120,$thumbnail1);
					$this->create_thumbnail_with_crop($path,"",375, 250,$thumbnail2);
					$this->create_thumbnail_with_crop($path,"",600, 250,$thumbnail3);
				} */
				$this->create_jpeg_thumbnail($path,"",160, 120,$thumbnail1);
				$this->create_jpeg_thumbnail($path,"",375, 250,$thumbnail2);
				$this->create_jpeg_thumbnail($path,"",600,250,$thumbnail3);

            }
        }
         return Redirect::route('hotel-list')->with('message','Hotel Successfully Added.');	
      }else
        return view('admin.hotels.add_hotel')->with(['country_details'=>$country_details,'state_details'=>$state_details,'carnival_details'=>$carnival_details]);
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
	
    /**
     * Delete event
     */
    public function deleteHotel($id)
    {
        $delete_hotel = Hotel::findOrFail($id);
		$delete_hotel->delete();
        return Redirect::route('hotel-list')->with('message','Hotel Successfully Deleted.');	
	   
    }
    
    /**
     * Edit Event
     */
    public function editHotel(Request $request,$id)
    {   
        $hotel_gallery_obj = HotelGallery::select('hotel_gallery_image')->where('hotel_id','=',$id)->get();
        $hotel_gallery_obj = $hotel_gallery_obj->toArray();
       
        $country_details = Country::all();
		$carnival_details = Carnival::all();
        $state_details = State::all();
        $hotel_obj = Hotel::findOrFail($id);
		$reviews   = HotelReview::where('hotel_id','=',$id)->with('user')->get();
        if(count($hotel_obj)>0){
             if($request->isMethod('post')){

                //<!-- validation rules
                $rules = [				
                    'hotel_slug' =>'required|unique:hotels,hotel_slug,'.$id,			
                ]; 
                $validator = Validator::make(Input::all(), $rules);
                if ($validator->fails())
                { 
                    $messages = $validator->messages();
                    if (!empty($messages)) {
                        if ($messages->has('hotel_slug')) {
                            return Redirect::route('hotel-edit',$id)->with('error','Entered Slug Already Exists.');
                        }			         						   
                    }
                }
                //validation rules ended-->

                $slug_input =  str_slug($request->input('hotel_slug'), '-');      
                $slug = $this->editSlug($slug_input,$request->input('hotel_slug'),$id);
                $hotel_obj->hotel_slug = $slug;
                $hotel_obj->user_id = Auth::user()->id;
                $hotel_obj->hotel_name = $request->input('hotel_name');
                $hotel_obj->hotel_location = $request->input('hotel_location');
                $hotel_obj->country_id = $request->input('country_id');
				$hotel_obj->state_id = $request->input('state_id');
				$hotel_obj->carnival_id = $request->input('carnival_id');
                $hotel_obj->hotel_description = $request->input('hotel_description');
                $hotel_obj->is_active = ($request->input('is_active') == 1) ? '1':'0';
       
                if( !empty( $request->file('hotel_banner') ) )
                {  
                   $banner = $request->file('hotel_banner');
                   $input['imagename'] = Auth::user()->id.'.'.$banner->getClientOriginalExtension();
                   $file_name = time().rand(0,99)."_".$input['imagename'];
                   $destinationPath = public_path('uploads/hotel_banners/'.$hotel_obj->id); 
                   if(!File::exists($destinationPath)) {
                       File::makeDirectory($destinationPath, $mode = 0777, true, true);
                   }
                   // delete the old banner
                   if(!empty($hotel_obj->hotel_banner)){
                        $old_banner = public_path('uploads/hotel_banners/'.$hotel_obj->id.'/'.$hotel_obj->hotel_banner);
                        if(file_exists($old_banner)){
                            unlink($old_banner);
                        }
						$thumbnail1 = public_path('uploads/hotel_banners/'.$hotel_obj->id.'/'.'small_'.$hotel_obj->hotel_banner);
						if(file_exists($thumbnail1)){
							unlink($thumbnail1);
						}
						$thumbnail2 = public_path('uploads/hotel_banners/'.$hotel_obj->id.'/'.'medium_'.$hotel_obj->hotel_banner);
						if(file_exists($thumbnail2)){
							unlink($thumbnail2);
						}$thumbnail3 = public_path('uploads/hotel_banners/'.$hotel_obj->id.'/'.'large_'.$hotel_obj->hotel_banner);
						if(file_exists($thumbnail3)){
							unlink($thumbnail3);
						}
                    }
                   $banner->move($destinationPath, $file_name);
                   $hotel_obj->hotel_banner = $file_name;                      
               }
                $hotel_obj->save();
				
				$path       = public_path('uploads/hotel_banners/'.$hotel_obj->id).'/'.$hotel_obj->hotel_banner;
				$thumbnail1 = public_path('uploads/hotel_banners/'.$hotel_obj->id).'/'.'small_'.$hotel_obj->hotel_banner;
				$thumbnail2 = public_path('uploads/hotel_banners/'.$hotel_obj->id).'/'.'medium_'.$hotel_obj->hotel_banner;
				$thumbnail3 = public_path('uploads/hotel_banners/'.$hotel_obj->id).'/'.'large_'.$hotel_obj->hotel_banner;
				/* if(file_exists($path))
				{
					//$this->create_jpeg_thumbnail($path,"",150, 150,$thumbnail);
					$this->create_thumbnail_with_crop($path,"",160, 120,$thumbnail1);
					$this->create_thumbnail_with_crop($path,"",375, 250,$thumbnail2);
					$this->create_thumbnail_with_crop($path,"",600, 250,$thumbnail3);
				} */
				$this->create_jpeg_thumbnail($path,"",160, 120,$thumbnail1);
				$this->create_jpeg_thumbnail($path,"",375, 250,$thumbnail2);
				$this->create_jpeg_thumbnail($path,"",600,250,$thumbnail3);
			

                //removed saved images
                $remove_saved_imgs = $request->input('removed_saved_img');
                $remove_saved_imgs = ( explode( ',', $remove_saved_imgs ) );
                //var_dump($remove_saved_imgs); die;
                if(count($remove_saved_imgs)>0){
                    foreach($remove_saved_imgs  as $img){
                        if(!empty($img)){
                            $old_image = public_path('uploads/hotel_gallery/'.$hotel_obj->id.'/'.$img);
                            if(file_exists($old_image)){
                                unlink($old_image);
                            }
							$thumbnail1 = public_path('uploads/hotel_gallery/'.$hotel_obj->id.'/'.'small_'.$img);
							if(file_exists($thumbnail1)){
								unlink($thumbnail1);
							}
							$thumbnail2 = public_path('uploads/hotel_gallery/'.$hotel_obj->id.'/'.'medium_'.$img);
							if(file_exists($thumbnail2)){
								unlink($thumbnail2);
							}$thumbnail3 = public_path('uploads/hotel_gallery/'.$hotel_obj->id.'/'.'large_'.$img);
							if(file_exists($thumbnail3)){
								unlink($thumbnail3);
							}
                            $delete_image = HotelGallery::where('hotel_gallery_image', $img)->delete();
                        }
                    }

                }

                //removed uploaded on file selector but not to upload 
                $removed_image_ids = array_map('intval', explode(',', $request->input('removed_img')));
                $images =  $request->file('hotel_gallery');
                foreach($removed_image_ids  as $id){
                    if($id>2){
                        unset($images[$id-2]);
                    }else{
                        unset($images[$id-1]);
                    }
                  }
               
                // upload new images
                if(count($images)>0){
                   foreach($images as $image){
                    $hotel_gallery = new HotelGallery();
                       if( !empty($image) )
                       {  
                       $input['imagename'] = Auth::user()->id.'.'.$image->getClientOriginalExtension();
                       $file_name = time().rand(0,99)."_".$input['imagename'];
                       $destinationPath = public_path('uploads/hotel_gallery/'.$hotel_obj->id); 
                       if(!File::exists($destinationPath)) {
                           File::makeDirectory($destinationPath, $mode = 0777, true, true);
                       }
                       $image->move($destinationPath, $file_name);                      
                   }
                       $hotel_gallery->hotel_id = $hotel_obj->id;
                       $hotel_gallery->hotel_gallery_image = $file_name; 
                       $hotel_gallery->save();
					   
						$path       = public_path('uploads/hotel_gallery/'.$hotel_obj->id).'/'.$hotel_gallery->hotel_gallery_image;
						$thumbnail1 = public_path('uploads/hotel_gallery/'.$hotel_obj->id).'/'.'small_'.$hotel_gallery->hotel_gallery_image;
						$thumbnail2 = public_path('uploads/hotel_gallery/'.$hotel_obj->id).'/'.'medium_'.$hotel_gallery->hotel_gallery_image;
						$thumbnail3 = public_path('uploads/hotel_gallery/'.$hotel_obj->id).'/'.'large_'.$hotel_gallery->hotel_gallery_image;
						
						/* if(file_exists($path))
						{
							//$this->create_jpeg_thumbnail($path,"",150, 150,$thumbnail);
							$this->create_thumbnail_with_crop($path,"",160, 120,$thumbnail1);
							$this->create_thumbnail_with_crop($path,"",375, 250,$thumbnail2);
							$this->create_thumbnail_with_crop($path,"",600, 250,$thumbnail3);
						} */
						$this->create_jpeg_thumbnail($path,"",160, 120,$thumbnail1);
						$this->create_jpeg_thumbnail($path,"",375, 250,$thumbnail2);
						$this->create_jpeg_thumbnail($path,"",600,250,$thumbnail3);
					   
       
                   }
               }
                return Redirect::route('hotel-list')->with('message','Hotel Successfully Edited.');	
            }
            else{
                return view('admin.hotels.edit_hotel')->with(['hotel_details' => $hotel_obj,'country_details'=>$country_details,'hotel_gallery'=>$hotel_gallery_obj,'reviews'=>$reviews,'state_details'=>$state_details,'carnival_details'=>$carnival_details]);
            }
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
	     
		     if($ratio_orig>=1 or $thumbnail_height!=PROPERTY_THUMB_HEIGHT_LARGE){
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
	 
	
	public function approveHotelReview(Request $request){
		$id = $request->input('id');
		$approve = HotelReview::findOrFail($id);
		if(count($approve)>0){
			$approve->is_approved = 1;
			$approve->save();
			Mail::send('emails.comments_on_hotel',
				['review'=>$approve,'heading'=>'Congratulations.','data'=>'Your review on hotel has been approved'],
				function($message) use($approve)
				{
				   $message->to($approve->user->email)->subject('Carnivalist Hotel Comment Aprroved.');
				}
			);
			
		}
	}
	
	public function deleteHotelReview(Request $request){
		$id = $request->input('id');
		$review = HotelReview::findOrFail($id);
		if(count($review)>0){
			HotelReviewMedias::where('hotel_reviews_id',$review->id)->delete();
			$review->delete();
			return response( array('data' =>'Deleted successfuly' ,'response' => 1));
		} else {
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
	
	public function disapproveHotelReview(Request $request){
		$id = $request->input('id');
		$approve = HotelReview::findOrFail($id);
		if(count($approve)>0){
			$approve->is_approved = 0;
			$approve->save();
			Mail::send('emails.comments_on_hotel',
				['review'=>$approve,'heading'=>'Sorry.','data'=>'Your review on hotel has been disapproved'],
				function($message) use($approve)
				{
				   $message->to($approve->user->email)->subject('Carnivalist Hotel Comment Disaprroved.');
				}
			);
		}
	}
	
	public function editHotelApproveStatus($id,$status){
		 $hotel = Hotel::findOrFail($id);
		 $hotel->is_active = $status;
		 $hotel->save();
		 return redirect()->back()->with('message','Hotel updated.');
	}
	
	public function postUpdateStatus(Request $request){
		$id = $request->id;
		$val = $request->value;
		$hotel  = Hotel::find($id);
		if($hotel){
			$hotel->is_active = $val;
			$hotel->save();
			return response( array('data' =>'Updated successfuly' ,'response' => 1));
		} else {
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
}

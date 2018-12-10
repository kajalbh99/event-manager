<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transportation;
use App\Models\Carnival;
use App\Models\Country;
use App\Models\State;
use App\Models\TransportationGallery;
use App\Models\TransportationReview;
use App\Models\TransportationReviewMedias;
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

class TransportationController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin', ['except' => 'logout']);
    }

    public function index()
    {   
         return view('admin.transportations.list_transportation');
    }
	public function getAjaxTransportation()
	{
		$transportation = Transportation::latest('id');
		return Datatables::of($transportation)->make(true);
	}
    /**
     * Create unique slug name
     */
    public function createSlug($slug){
        $next = 1;
        while(Transportation::where('transportation_slug', '=', $slug)->first()) {          
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
        $last_slug = Transportation::select('transportation_slug')->where('id', '=', $id)->first();
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
    public function addtransportation(Request $request){
      
     $country_details = Country::all();
	 $carnival_details = Carnival::all();
	 $state_details   = State::all();
     if($request->isMethod('post')){
         
         $transportation_obj = new Transportation();
         //echo "<pre>"; print_r($request->all()); die;
         $slug_input =  str_slug($request->input('transportation_name'), '-');      
         $slug = $this->createSlug($slug_input);
         $transportation_obj->transportation_slug = $slug;
         $transportation_obj->user_id = Auth::user()->id;
         $transportation_obj->transportation_name = $request->input('transportation_name');
         $transportation_obj->transportation_location = $request->input('transportation_location');
         $transportation_obj->country_id = $request->input('country_id');
         $transportation_obj->state_id = $request->input('state_id');
         $transportation_obj->carnival_id = $request->input('carnival_id');
         $transportation_obj->transportation_description = $request->input('transportation_description');
         
         $transportation_obj->transportation_privacy = $request->input('transportation_privacy');
          $transportation_obj->is_active = ($request->input('is_active') == 1) ? '1':'0';
		$transportation_obj->save();
         if( !empty( $request->file('transportation_banner') ) )
         {  
            $banner = $request->file('transportation_banner');
            $input['imagename'] = Auth::user()->id.'.'.$banner->getClientOriginalExtension();
            $file_name = time().rand(0,99)."_".$input['imagename'];
            $destinationPath = public_path('uploads/transportation_banners/'.$transportation_obj->id); 
            if(!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, $mode = 0777, true, true);
            }
            $banner->move($destinationPath, $file_name);
			$transportation = Transportation::findOrFail($transportation_obj->id);
			
            $transportation->transportation_banner = $file_name;  
			$transportation->save();
			
			$path       = public_path('uploads/transportation_banners/'.$transportation_obj->id).'/'.$transportation->transportation_banner;
			$thumbnail1 = public_path('uploads/transportation_banners/'.$transportation_obj->id).'/'.'small_'.$transportation->transportation_banner;
			$thumbnail2 = public_path('uploads/transportation_banners/'.$transportation_obj->id).'/'.'medium_'.$transportation->transportation_banner;
			$thumbnail3 = public_path('uploads/transportation_banners/'.$transportation_obj->id).'/'.'large_'.$transportation->transportation_banner;
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
         $images =  $request->file('transportation_gallery');
         foreach($removed_image_ids  as $id){
            if($id>2){
                unset($images[$id-2]);
            }else{
                unset($images[$id-1]);
            }           
        }
        
         if(count($images)>0){
            foreach($images as $image){
                $transportation_gallery = new TransportationGallery();
                if( !empty($image) )
                {  
                $input['imagename'] = Auth::user()->id.'.'.$image->getClientOriginalExtension();
                $file_name = time().rand(0,99)."_".$input['imagename'];
                $destinationPath = public_path('uploads/transportation_gallery/'.$transportation_obj->id); 
                if(!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, $mode = 0777, true, true);
                }
                $image->move($destinationPath, $file_name);                      
            }
                $transportation_gallery->transportation_id = $transportation_obj->id;
                $transportation_gallery->transportation_gallery_image = $file_name; 
                $transportation_gallery->save();
				
				$path       = public_path('uploads/transportation_gallery/'.$transportation_obj->id).'/'.$transportation_gallery->transportation_gallery_image;
				$thumbnail1 = public_path('uploads/transportation_gallery/'.$transportation_obj->id).'/'.'small_'.$transportation_gallery->transportation_gallery_image;
				$thumbnail2 = public_path('uploads/transportation_gallery/'.$transportation_obj->id).'/'.'medium_'.$transportation_gallery->transportation_gallery_image;
				$thumbnail3 = public_path('uploads/transportation_gallery/'.$transportation_obj->id).'/'.'large_'.$transportation_gallery->transportation_gallery_image;
				
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
        }
         return Redirect::route('transportation-list')->with('message','transportation Successfully Added.');	
      }else
        return view('admin.transportations.add_transportation')->with(['country_details'=>$country_details,'state_details'=>$state_details,'carnival_details'=>$carnival_details]);
    }
    
    
    /**
     * Delete event
     */
    public function deletetransportation($id)
    {
        $delete_transportation = Transportation::findOrFail($id);
		$delete_transportation->delete();
        return Redirect::route('transportation-list')->with('message','transportation Successfully Deleted.');	
	   
    }
    
    /**
     * Edit Event
     */
    public function edittransportation(Request $request,$id)
    {   
        $transportation_gallery_obj = TransportationGallery::select('transportation_gallery_image')->where('transportation_id','=',$id)->get();
        $transportation_gallery_obj = $transportation_gallery_obj->toArray();
       
        $country_details = Country::all();
		$carnival_details = Carnival::all();
        $state_details = State::all();
        $transportation_obj = Transportation::findOrFail($id);
		$reviews   = TransportationReview::where('transportation_id','=',$id)->with('user')->get();
        if(count($transportation_obj)>0){
             if($request->isMethod('post')){

                //<!-- validation rules
                $rules = [				
                    'transportation_slug' =>'required|unique:transportation,transportation_slug,'.$id,			
                ]; 
                $validator = Validator::make(Input::all(), $rules);
                if ($validator->fails())
                { 
                    $messages = $validator->messages();
                    if (!empty($messages)) {
                        if ($messages->has('transportation_slug')) {
                            return Redirect::route('transportation-edit',$id)->with('error','Entered Slug Already Exists.');
                        }			         						   
                    }
                }
                //validation rules ended-->

                $slug_input =  str_slug($request->input('transportation_slug'), '-');      
                $slug = $this->editSlug($slug_input,$request->input('transportation_slug'),$id);
                $transportation_obj->transportation_slug = $slug;
                $transportation_obj->user_id = Auth::user()->id;
                $transportation_obj->transportation_name = $request->input('transportation_name');
                $transportation_obj->transportation_location = $request->input('transportation_location');
                $transportation_obj->country_id = $request->input('country_id');
				$transportation_obj->state_id = $request->input('state_id');
				$transportation_obj->carnival_id = $request->input('carnival_id');
                $transportation_obj->transportation_description = $request->input('transportation_description');
                $transportation_obj->is_active = ($request->input('is_active') == 1) ? '1':'0';
       
                if( !empty( $request->file('transportation_banner') ) )
                {  
                   $banner = $request->file('transportation_banner');
                   $input['imagename'] = Auth::user()->id.'.'.$banner->getClientOriginalExtension();
                   $file_name = time().rand(0,99)."_".$input['imagename'];
                   $destinationPath = public_path('uploads/transportation_banners/'.$transportation_obj->id); 
                   if(!File::exists($destinationPath)) {
                       File::makeDirectory($destinationPath, $mode = 0777, true, true);
                   }
                   // delete the old banner
                   if(!empty($transportation_obj->transportation_banner)){
                        $old_banner = public_path('uploads/transportation_banners/'.$transportation_obj->id.'/'.$transportation_obj->transportation_banner);
                        if(file_exists($old_banner)){
                            unlink($old_banner);
                        }
						$thumbnail1 = public_path('uploads/transportation_banners/'.$transportation_obj->id.'/'.'small_'.$transportation_obj->transportation_banner);
						if(file_exists($thumbnail1)){
							unlink($thumbnail1);
						}
						$thumbnail2 = public_path('uploads/transportation_banners/'.$transportation_obj->id.'/'.'medium_'.$transportation_obj->transportation_banner);
						if(file_exists($thumbnail2)){
							unlink($thumbnail2);
						}$thumbnail3 = public_path('uploads/transportation_banners/'.$transportation_obj->id.'/'.'large_'.$transportation_obj->transportation_banner);
						if(file_exists($thumbnail3)){
							unlink($thumbnail3);
						}
                    }
                   $banner->move($destinationPath, $file_name);
                   $transportation_obj->transportation_banner = $file_name;                      
               }
                $transportation_obj->save();
				
				$path       = public_path('uploads/transportation_banners/'.$transportation_obj->id).'/'.$transportation_obj->transportation_banner;
				$thumbnail1 = public_path('uploads/transportation_banners/'.$transportation_obj->id).'/'.'small_'.$transportation_obj->transportation_banner;
				$thumbnail2 = public_path('uploads/transportation_banners/'.$transportation_obj->id).'/'.'medium_'.$transportation_obj->transportation_banner;
				$thumbnail3 = public_path('uploads/transportation_banners/'.$transportation_obj->id).'/'.'large_'.$transportation_obj->transportation_banner;
				
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
				
                //removed saved images
                $remove_saved_imgs = $request->input('removed_saved_img');
                $remove_saved_imgs = ( explode( ',', $remove_saved_imgs ) );
                //var_dump($remove_saved_imgs); die;
                if(count($remove_saved_imgs)>0){
                    foreach($remove_saved_imgs  as $img){
                        if(!empty($img)){
                            $old_image = public_path('uploads/transportation_gallery/'.$transportation_obj->id.'/'.$img);
                            if(file_exists($old_image)){
                                unlink($old_image);
                            }
							$thumbnail1 = public_path('uploads/transportation_gallery/'.$transportation_obj->id.'/'.'small_'.$transportation_obj->transportation_banner);
							if(file_exists($thumbnail1)){
								unlink($thumbnail1);
							}
							$thumbnail2 = public_path('uploads/transportation_gallery/'.$transportation_obj->id.'/'.'medium_'.$transportation_obj->transportation_banner);
							if(file_exists($thumbnail2)){
								unlink($thumbnail2);
							}$thumbnail3 = public_path('uploads/transportation_gallery/'.$transportation_obj->id.'/'.'large_'.$transportation_obj->transportation_banner);
							if(file_exists($thumbnail3)){
								unlink($thumbnail3);
							}
                            $delete_image = TransportationGallery::where('transportation_gallery_image', $img)->delete();
                        }
                    }

                }

                //removed uploaded on file selector but not to upload 
                $removed_image_ids = array_map('intval', explode(',', $request->input('removed_img')));
                $images =  $request->file('transportation_gallery');
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
                    $transportation_gallery = new TransportationGallery();
                       if( !empty($image) )
                       {  
                       $input['imagename'] = Auth::user()->id.'.'.$image->getClientOriginalExtension();
                       $file_name = time().rand(0,99)."_".$input['imagename'];
                       $destinationPath = public_path('uploads/transportation_gallery/'.$transportation_obj->id); 
                       if(!File::exists($destinationPath)) {
                           File::makeDirectory($destinationPath, $mode = 0777, true, true);
                       }
                       $image->move($destinationPath, $file_name);                      
                   }
                       $transportation_gallery->transportation_id = $transportation_obj->id;
                       $transportation_gallery->transportation_gallery_image = $file_name; 
                       $transportation_gallery->save();
					   
					    $path       = public_path('uploads/transportation_gallery/'.$transportation_obj->id).'/'.$transportation_gallery->transportation_gallery_image;
						$thumbnail1 = public_path('uploads/transportation_gallery/'.$transportation_obj->id).'/'.'small_'.$transportation_gallery->transportation_gallery_image;
						$thumbnail2 = public_path('uploads/transportation_gallery/'.$transportation_obj->id).'/'.'medium_'.$transportation_gallery->transportation_gallery_image;
						$thumbnail3 = public_path('uploads/transportation_gallery/'.$transportation_obj->id).'/'.'large_'.$transportation_gallery->transportation_gallery_image;
						
						$this->create_jpeg_thumbnail($path,"",160, 120,$thumbnail1);
						$this->create_jpeg_thumbnail($path,"",375, 250,$thumbnail2);
						$this->create_jpeg_thumbnail($path,"",600, 250,$thumbnail3);
						
						/* if(file_exists($path))
						{
							//$this->create_jpeg_thumbnail($path,"",150, 150,$thumbnail);
							$this->create_thumbnail_with_crop($path,"",160, 120,$thumbnail1);
							$this->create_thumbnail_with_crop($path,"",375, 250,$thumbnail2);
							$this->create_thumbnail_with_crop($path,"",600, 250,$thumbnail3);
						} */
       
                   }
               }
                return Redirect::route('transportation-list')->with('message','transportation Successfully Edited.');	
            }
            else{
                return view('admin.transportations.edit_transportation')->with(['transportation_details' => $transportation_obj,'country_details'=>$country_details,'transportation_gallery'=>$transportation_gallery_obj,'reviews'=>$reviews,'state_details'=>$state_details,'carnival_details'=>$carnival_details]);
            }
        }

    }
	
	public function approvetransportationReview(Request $request){
		$id = $request->input('id');
		$approve = TransportationReview::findOrFail($id);
		if(count($approve)>0){
			$approve->is_approved = 1;
			$approve->save();
			Mail::send('emails.comments_on_transportation',
				['review'=>$approve,'heading'=>'Congratulations.','data'=>'Your review on transportation has been approved'],
				function($message) use($approve)
				{
				   $message->to($approve->user->email)->subject('Carnivalist Transportation Comment Aprroved.');
				}
			);
		}
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
	
	public function deleteTransportationReview(Request $request){
		$id = $request->input('id');
		$review = TransportationReview::findOrFail($id);
		if(count($review)>0){
			TransportationReviewMedias::where('transportation_reviews_id',$review->id)->delete();
			$review->delete();
			return response( array('data' =>'Deleted successfuly' ,'response' => 1));
		} else {
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
	
	public function disapprovetransportationReview(Request $request){
		$id = $request->input('id');
		$approve = TransportationReview::findOrFail($id);
		if(count($approve)>0){
			$approve->is_approved = 0;
			$approve->save();
			Mail::send('emails.comments_on_transportation',
				['review'=>$approve,'heading'=>'Sorry.','data'=>'Your review on transportation has been disapproved'],
				function($message) use($approve)
				{
				   $message->to($approve->user->email)->subject('Carnivalist Transportation Comment Disaprroved.');
				}
			);
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
	
	public function editTransportationApproveStatus($id,$status){
		 $transportation = Transportation::findOrFail($id);
		 $transportation->is_active = $status;
		 $transportation->save();
		 return redirect()->back()->with('message','Transportation updated.');
	}
	
	public function postUpdateStatus(Request $request){
		$id = $request->id;
		$val = $request->value;
		$transportation  = Transportation::find($id);
		if($transportation){
			$transportation->is_active = $val;
			$transportation->save();
			return response( array('data' =>'Updated successfuly' ,'response' => 1));
		} else {
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
}

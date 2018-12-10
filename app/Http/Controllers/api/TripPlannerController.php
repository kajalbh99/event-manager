<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use Input;
use File;
use App\Models\Transportation;
use App\Models\TransportationReview;
use App\Models\Hotel;
use App\Models\HotelReview;
use App\Models\TransportationReviewMedias;
use App\Models\HotelReviewMedias;
use Mail;
use Config;
use Image;
use App\Library\ImageManipulator;

class TripPlannerController extends Controller
{

    public function __construct()
    {
        
    }
	
	/***** TRIP ADVISOR ****/
	public function index(Request $request){
		$Transportation = Transportation::with('review.user')->with('review.gallery_image')->with('gallery')->where('carnival_id',$request->carnival_id)->where('is_active','=','1')->get();
		$Hotel = Hotel::with('review.user')->with('review.gallery_image')->with('gallery')->where('carnival_id',$request->carnival_id)->where('is_active','=','1')->get();
		$data = array('transportations'=>$Transportation,'hotels'=>$Hotel);
		return response( array('data' =>$data ,'response' => 1));
		
	}
	public function hotel_comment(Request $request){
		$title      = "title";
		$comment    = $request->input('comment');
		$rating     = $request->input('rating');
		$user_email = $request->input('user');
		$hotel_id   = $request->input('hotel_id');
		$admin_email = Config::get('constants.admin_email');
		if(!empty($comment) && !empty($user_email) && !empty($hotel_id)){
			$user = User::where("email","=",$user_email)->first();
			if(count($user)>0){
				/* $hotel_review = HotelReview::where('user_id','=',$user->id)->where('hotel_id','=',$hotel_id)->first();
				
				if(count($hotel_review)>0){
					$review = $hotel_review;
				}else{
					$review = new HotelReview();
				} */
				$review = new HotelReview();
				$review->user_id            = $user->id;
				$review->hotel_id           = $hotel_id;
				$review->review_title       = $title;
				$review->review_description = $comment;
				$review->is_approved        = 0;
				if($rating > 0){
				$review->rating = $rating;	
				}else{
				$review->rating = 0;	
				}
				$review->save();
				
				if($review->id>0){
					$review_media = HotelReviewMedias::where('hotel_reviews_id',$review->id)->get();
					// delete the old images
					if(count($review_media)>0){
						foreach ($review_media as $media){ 
						   if(!empty($media->hotel_review_image)){
							    $media->delete();
								$old_image = public_path('uploads/hotel_review_gallery/'.$review->id.'/'.$media->hotel_review_image);
								if(file_exists($old_image)){
									unlink($old_image);
								} 
							}
						}
					}
			
				}
				/* Mail::send('emails.comments_on_hotel',
					['review'=>$review,'heading'=>'New commnet on hotel.','data'=>'You have new comment on hotel'],
					function($message) use($user_email)
					{
					   $message->to($user_email)->subject('Carnivalist Hotel Comment.');
					}
				); */
				Mail::send('emails.comments_on_hotel_to_admin',
					['review'=>$review,'heading'=>'Thank You.','data'=>'You have commented on hotel'],
					function($message) use($admin_email)
					{
					   $message->to($admin_email)->subject('Carnivalist Hotel Comment.');
					}
				);
				return response( array('data' => $review ,'review_id' => $review->id,'response' => 1));
			}
			    return response( array('data' => "error",'response' => 0));
		}
		
		return response( array('data' => "error",'response' => 0));
		
	}
	public function transportation_comment(Request $request){
		$title      = "title";
		$comment    = $request->input('comment');
		$rating     = $request->input('rating');
		$user_email = $request->input('user');
		$transportation_id   = $request->input('transportation_id');
		$admin_email = Config::get('constants.admin_email');
		if(!empty($comment) && !empty($user_email) && !empty($transportation_id)){
			$user = User::where("email","=",$user_email)->first();
			if(count($user)>0){
				/* $transportation_review = TransportationReview::where('user_id','=',$user->id)->where('transportation_id','=',$transportation_id)->first();
				
				if(count($transportation_review)>0){
					$review = $transportation_review;
				}else{
					$review = new TransportationReview();
				} */
				$review = new TransportationReview();
				$review->user_id            = $user->id;
				$review->transportation_id           = $transportation_id;
				$review->review_title       = $title;
				$review->review_description = $comment;
				$review->is_approved        = 0;
				if($rating > 0){
				$review->rating = $rating;	
				}else{
				$review->rating = 0;	
				}
				$review->save();
				if($review->id>0){
					$review_media = TransportationReviewMedias::where('transportation_reviews_id',$review->id)->get();
					// delete the old images
					if(count($review_media)>0){
						foreach ($review_media as $media){ 
						   if(!empty($media->transportation_review_image)){
							    $media->delete();
								$old_image = public_path('uploads/transportation_review_gallery/'.$review->id.'/'.$media->transportation_review_image);
								if(file_exists($old_image)){
									unlink($old_image);
								} 
							}
						}
					}
			
				}
				/* Mail::send('emails.comments_on_transportation',
					['review'=>$review,'heading'=>'New commnet on transportation.','data'=>'You have new comment on transportation'],
					function($message) use($user_email)
					{
					   $message->to($user_email)->subject('Carnivalist Transportation Comment.');
					}
				); */
				Mail::send('emails.comments_on_transportation_to_admin',
					['review'=>$review,'heading'=>'Thank You.','data'=>'You have commented on transportation'],
					function($message) use($admin_email)
					{
					   $message->to($admin_email)->subject('Carnivalist Transportation Comment.');
					}
				);
				return response( array('data' => $review ,'review_id' => $review->id,'response' => 1));
			}
			    return response( array('data' => "error",'response' => 0));
		}
		
		return response( array('data' => "error",'response' => 0));	
	}
	
	
	public function hotel_review_upload_image(Request $request){
		$review_id = $request->input('review_id');
		$review = new HotelReviewMedias();
		$data =  $request->file('file');
		
		if( (!empty( $request->file('file'))) && ($review_id > 0 ) )
		{  
			
		   $photo = $request->file('file');
		   $input['imagename'] = $review_id.'.'.$photo->getClientOriginalExtension();
		   $file_name = time().rand(0,99)."_".$input['imagename'];
		   $destinationPath = public_path('uploads/hotel_review_gallery/'.$review_id); 
		   if(!File::exists($destinationPath)) {
			   File::makeDirectory($destinationPath, $mode = 0777, true, true);
		   }			
		   $photo->move($destinationPath, $file_name);
		   $review->hotel_reviews_id  = $review_id;
		   $review->hotel_review_image = $file_name; 
		   $review->save();
			
		   $path = public_path('uploads/hotel_review_gallery/'.$review_id).'/'.$review->hotel_review_image;
		   $thumbnail = public_path('uploads/hotel_review_gallery/'.$review_id).'/'.'thumbnail_'.$review->hotel_review_image;
		   if(file_exists($path))
			{
				$this->create_jpeg_thumbnail($path,"",150, 150,$thumbnail);
				//$this->create_thumbnail_with_crop($path,"",150, 150,$thumbnail);
			}
		}
		return response($review->id);	
	}
	
	public function transportation_review_upload_image(Request $request){
		$review_id = $request->input('review_id');
		$review = new TransportationReviewMedias();
		$data =  $request->file('file');
		if( (!empty( $request->file('file'))) && ($review_id > 0 ) )
		{  
		   $photo = $request->file('file');
		   $input['imagename'] = $review_id.'.'.$photo->getClientOriginalExtension();
		   $file_name = time().rand(0,99)."_".$input['imagename'];
		   $destinationPath = public_path('uploads/transportation_review_gallery/'.$review_id); 
		   if(!File::exists($destinationPath)) {
			   File::makeDirectory($destinationPath, $mode = 0777, true, true);
		   }			
		   $photo->move($destinationPath, $file_name);
		   $review->transportation_reviews_id   = $review_id;
		   $review->transportation_review_image = $file_name; 
		   $review->save();  

		   $path = public_path('uploads/transportation_review_gallery/'.$review_id).'/'.$review->transportation_review_image;
		   $thumbnail = public_path('uploads/transportation_review_gallery/'.$review_id).'/'.'thumbnail_'.$review->transportation_review_image;
		   if(file_exists($path))
			{
				$this->create_jpeg_thumbnail($path,"",150, 150,$thumbnail);
				//$this->create_thumbnail_with_crop($path,"",150, 150,$thumbnail);
			}
		}
		return response('success');	
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
}


<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Band;
use App\Models\Carnival;
use App\Models\BandsGallery;
use App\Models\BandReview;
use App\Models\BandReviewMedias;
use App\Models\BandCarnival;
use Validator;
use Illuminate\Support\Facades\Input;
use Redirect;
use File;
use Auth;
use Image;
use Mail;
use Config;
use Yajra\Datatables\Facades\Datatables;
use App\Library\ImageManipulator;

class BandController extends Controller
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
        $band_details = Band::orderBy('id', 'desc')->get();
        return view('admin.bands.list_bands')->with(['band_details'=>$band_details]);
    }

    /**
     * Creting unique band slug
     */
    public function createSlug($slug){
        $next = 1;
        while(Band::where('band_slug', '=', $slug)->first()) {          
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
     *  Add New Band
     */
    public function addBand(REquest $request)
    {   

        $band_details    = Band::all();
        $carnival_details = Carnival::all();
        if($request->isMethod('post')){
            
            $band_obj = new Band();
            $band_obj->band_name       = $request->input('band_name');
            $band_obj->band_description       = $request->input('band_description');

            $slug_input =  str_slug($request->input('band_name'), '-');      
            $slug = $this->createSlug($slug_input);
            $band_obj->band_slug = $slug;

            $band_obj->save();
            if( !empty( $request->file('band_banner') ) )
            {  
                $banner = $request->file('band_banner');
                $input['imagename'] = $band_obj->id.'.'.$banner->getClientOriginalExtension();
                $file_name = time().rand(0,99)."_".$input['imagename'];
                $destinationPath = public_path('uploads/band_banners/'.$band_obj->id); 
                if(!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, $mode = 0777, true, true);
                }
                $banner->move($destinationPath, $file_name);
                $band = Band::findOrFail($band_obj->id);
                $band->band_banner = $file_name; 
                $band->save();
				
				$path      = public_path('uploads/band_banners/'.$band->id).'/'.$band->band_banner;
				$thumbnail1 = public_path('uploads/band_banners/'.$band->id).'/'.'small_'.$band->band_banner;
				$thumbnail2 = public_path('uploads/band_banners/'.$band->id).'/'.'medium_'.$band->band_banner;
				$thumbnail3 = public_path('uploads/band_banners/'.$band->id).'/'.'large_'.$band->band_banner;
				
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
            
            //Gallery Image upload
            //removed images uploaded on file selector
                $removed_image_ids = array_map('intval', explode(',', $request->input('removed_img')));
                $images =  $request->file('band_gallery');
                foreach($removed_image_ids  as $id){
                    if($id>2){
                        unset($images[$id-2]);
                    }else{
                        unset($images[$id-1]);
                    }           
                 }
        
                if(count($images)>0){
                    foreach($images as $image){
                        $band_gallery = new BandsGallery();
                        if( !empty($image) )
                        {  
                        $input['imagename'] = Auth::user()->id.'.'.$image->getClientOriginalExtension();
                        $file_name = time().rand(0,99)."_".$input['imagename'];
                        $destinationPath = public_path('uploads/band_gallery/'.$band_obj->id); 
                        if(!File::exists($destinationPath)) {
                            File::makeDirectory($destinationPath, $mode = 0777, true, true);
                        }
                        $image->move($destinationPath, $file_name);                      
                    }
                        $band_gallery->band_id = $band_obj->id;
                        $band_gallery->band_gallery_image = $file_name; 
                        $band_gallery->save();
						
						$path       = public_path('uploads/band_gallery/'.$band_obj->id).'/'.$band_gallery->band_gallery_image;
						$thumbnail1 = public_path('uploads/band_gallery/'.$band_obj->id).'/'.'small_'.$band_gallery->band_gallery_image;
						$thumbnail2 = public_path('uploads/band_gallery/'.$band_obj->id).'/'.'medium_'.$band_gallery->band_gallery_image;
						$thumbnail3 = public_path('uploads/band_gallery/'.$band_obj->id).'/'.'large_'.$band_gallery->band_gallery_image;
						$this->create_jpeg_thumbnail($path,"",160, 120,$thumbnail1);
						$this->create_jpeg_thumbnail($path,"",375, 250,$thumbnail2);
						$this->create_jpeg_thumbnail($path,"",600,250,$thumbnail3); 
						/* 
						if(file_exists($path))
						{
							//$this->create_jpeg_thumbnail($path,"",150, 150,$thumbnail);
							$this->create_thumbnail_with_crop($path,"",160, 120,$thumbnail1);
							$this->create_thumbnail_with_crop($path,"",375, 250,$thumbnail2);
							$this->create_thumbnail_with_crop($path,"",600, 250,$thumbnail3);
						}*/

                    }
                }

                //selected Carnival uploading to band_carnivals table
                $carnivals = $request->input('carnival');
                if(count($carnivals)>0){
                    foreach($carnivals as $carnival){
                        $band_carnival = new BandCarnival();
                        $band_carnival->band_id = $band_obj->id;
                        $band_carnival->carnival_id = $carnival;
                        $band_carnival->save();
                    }

                }


           return Redirect::route('band-list')->with('message','Band Successfully Added.');	
        }
        return view('admin.bands.add_bands')->with(['carnival_details' => $carnival_details ]);
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
     * Delete Band
     */
    public function deleteBand($id)
    {
        $delete_band = Band::findOrFail($id);
		$delete_band->delete();
        return Redirect::route('band-list')->with('message','Band Successfully Deleted.');	
	   
    }

    /**
     * Edit saved slug
    */
    public function editSlug($slug,$requested_slug,$id){
        $last_slug = Band::select('band_slug')->where('id', '=', $id)->first();
       // echo $last_slug->carnival_slug."<br>".$requested_slug; die;
        if($last_slug->band_slug == $requested_slug){
           $slug = $requested_slug;
        }else{
          $slug = $this->createSlug($requested_slug);  
        }

        return $slug;
        
    }

    /**
     * Edit Band
     */
    public function editBand(Request $request,$id)
    {   
        $carnival_details = Carnival::all();
        $band_gallery_obj = BandsGallery::select('band_gallery_image')->where('band_id','=',$id)->get();
        $band_gallery_obj = $band_gallery_obj->toArray();
        $band_carnival_obj = BandCarnival::select('carnival_id')->where('band_id','=',$id)->get();
        
        $carnival[] = "";
        if(count($band_carnival_obj)>0)
        foreach($band_carnival_obj as $saved_carnival_id){
            $carnival[] = $saved_carnival_id->carnival_id;
        }

        $band_obj = Band::findOrFail($id);
		$reviews   = BandReview::where('band_id','=',$id)->with('user')->get();
        if(count($band_obj)>0){
             if($request->isMethod('post')){

            //<!-- validation rules
		    $rules = [				
				'band_slug' =>'required|unique:bands,band_slug,'.$id,			
			]; 
			$validator = Validator::make(Input::all(), $rules);
			if ($validator->fails())
			{ 
                $messages = $validator->messages();
				if (!empty($messages)) {
                    if ($messages->has('band_slug')) {
                        return Redirect::route('band-edit',$id)->with('error','Entered Slug Already Exists.');
                    }			         						   
				}
			}
            //validation rules ended-->

                //echo "<pre>"; print_r($request->all()); die;
                $slug_input =  str_slug($request->input('band_slug'), '-');      
                $slug = $this->editSlug($slug_input,$request->input('band_slug'),$id);
                $band_obj->band_slug = $slug;
                $band_obj->band_name = $request->input('band_name');
                $band_obj->band_description = $request->input('band_description');
        
       
                if( !empty( $request->file('band_banner') ) )
                {  
                   $banner = $request->file('band_banner');
                   $input['imagename'] = Auth::user()->id.'.'.$banner->getClientOriginalExtension();
                   $file_name = time().rand(0,99)."_".$input['imagename'];
                   $destinationPath = public_path('uploads/band_banners/'.$band_obj->id); 
                   if(!File::exists($destinationPath)) {
                       File::makeDirectory($destinationPath, $mode = 0777, true, true);
                   }
                   // delete the old banner
                   if(!empty($band_obj->band_banner)){
                        $old_banner = public_path('uploads/band_banners/'.$band_obj->id.'/'.$band_obj->band_banner);
                        if(file_exists($old_banner)){
                            unlink($old_banner);
                        }
						$thumbnail1 = public_path('uploads/band_banners/'.$band_obj->id.'/'.'small_'.$band_obj->band_banner);
						if(file_exists($thumbnail1)){
							unlink($thumbnail1);
						}
						$thumbnail2 = public_path('uploads/band_banners/'.$band_obj->id.'/'.'medium_'.$band_obj->band_banner);
						if(file_exists($thumbnail2)){
							unlink($thumbnail2);
						}$thumbnail3 = public_path('uploads/band_banners/'.$band_obj->id.'/'.'large_'.$band_obj->band_banner);
						if(file_exists($thumbnail3)){
							unlink($thumbnail3);
						}
						
                    } 
                   $banner->move($destinationPath, $file_name);
                   $band_obj->band_banner = $file_name;                      
               }
                $band_obj->save();
				
				$path      = public_path('uploads/band_banners/'.$band_obj->id).'/'.$band_obj->band_banner;
				$thumbnail1 = public_path('uploads/band_banners/'.$band_obj->id).'/'.'small_'.$band_obj->band_banner;
				$thumbnail2 = public_path('uploads/band_banners/'.$band_obj->id).'/'.'medium_'.$band_obj->band_banner;
				$thumbnail3 = public_path('uploads/band_banners/'.$band_obj->id).'/'.'large_'.$band_obj->band_banner;
				//$img1       = Image::make($path)->resize(160, 120)->save($thumbnail1);
				//$img2       = Image::make($path)->resize(375, 250)->save($thumbnail2);
				//$img3       = Image::make($path)->resize(600, 250)->save($thumbnail3);
				
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
                            $old_image = public_path('uploads/band_gallery/'.$band_obj->id.'/'.$img);
							$thumbnail1 = public_path('uploads/band_gallery/'.$band_obj->id).'/'.'small_'.$img;
							$thumbnail2 = public_path('uploads/band_gallery/'.$band_obj->id).'/'.'medium_'.$img;
							$thumbnail3 = public_path('uploads/band_gallery/'.$band_obj->id).'/'.'large_'.$img;
                            if(file_exists($old_image)){
								unlink($old_image);
                            }
							if(file_exists($thumbnail1)){
                                unlink($thumbnail1);
                            }
							if(file_exists($thumbnail2)){
                                unlink($thumbnail2);
                            }
							if(file_exists($thumbnail3)){
                                unlink($thumbnail3);
                            }
                            $delete_image = BandsGallery::where('band_gallery_image', $img)->delete();
                        }
                    }

                }

                //removed uploaded on file selector but not to upload 
                $removed_image_ids = array_map('intval', explode(',', $request->input('removed_img')));
                $images =  $request->file('band_gallery');
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
                    $band_gallery = new BandsGallery();
                       if( !empty($image) )
                       {  
                       $input['imagename'] = Auth::user()->id.'.'.$image->getClientOriginalExtension();
                       $file_name = time().rand(0,99)."_".$input['imagename'];
                       $destinationPath = public_path('uploads/band_gallery/'.$band_obj->id); 
                       if(!File::exists($destinationPath)) {
                           File::makeDirectory($destinationPath, $mode = 0777, true, true);
                       }
                       $image->move($destinationPath, $file_name);                      
                   }
                       $band_gallery->band_id = $band_obj->id;
                       $band_gallery->band_gallery_image = $file_name; 
                       $band_gallery->save();
					   
					    $path       = public_path('uploads/band_gallery/'.$band_obj->id).'/'.$band_gallery->band_gallery_image;
						$thumbnail1 = public_path('uploads/band_gallery/'.$band_obj->id).'/'.'small_'.$band_gallery->band_gallery_image;
						$thumbnail2 = public_path('uploads/band_gallery/'.$band_obj->id).'/'.'medium_'.$band_gallery->band_gallery_image;
						$thumbnail3 = public_path('uploads/band_gallery/'.$band_obj->id).'/'.'large_'.$band_gallery->band_gallery_image;
						
						$this->create_jpeg_thumbnail($path,"",160, 120,$thumbnail1);
						$this->create_jpeg_thumbnail($path,"",375, 250,$thumbnail2);
						$this->create_jpeg_thumbnail($path,"",600,250,$thumbnail3);
						
					/* 	if(file_exists($path))
						{
							//$this->create_jpeg_thumbnail($path,"",150, 150,$thumbnail);
							$this->create_thumbnail_with_crop($path,"",160, 120,$thumbnail1);
							$this->create_thumbnail_with_crop($path,"",375, 250,$thumbnail2);
							$this->create_thumbnail_with_crop($path,"",600, 250,$thumbnail3);
						} */
                   }
                }

                //update carnivals to band_carnivals table
                $saved_carnivals = BandCarnival::where('band_id','=',$band_obj->id)->delete();
                $carnivals = $request->input('carnival');
                if(count($carnivals)>0){
                    foreach($carnivals as $carnival){
                        $band_carnival = new BandCarnival();
                        $band_carnival->band_id = $band_obj->id;
                        $band_carnival->carnival_id = $carnival;
                        $band_carnival->save();
                    }

                }

                return Redirect::route('band-list')->with('message','Band Successfully Edited.');	
            }
            else{
                return view('admin.bands.edit_bands')->with(['band_details' => $band_obj,'carnival_details'=>$carnival_details,'band_gallery'=>$band_gallery_obj,'band_carnival'=>$carnival,'reviews'=>$reviews]);
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

	public function approveBandReview(Request $request){
		$id = $request->input('id');
		$approve = BandReview::findOrFail($id);
		if(count($approve)>0){
			$approve->is_approved = 1;
			$approve->save();
			Mail::send('emails.comments',
				['review'=>$approve,'heading'=>'Congratulations.','data'=>'Your review on band has been approved'],
				function($message) use($approve)
				{
				   $message->to($approve->user->email)->subject('Carnivalist Band Comment Aprroved.');
				}
			);
		}
	}
	
	public function deleteBandReview(Request $request){
		$id = $request->input('id');
		$review = BandReview::findOrFail($id);
		if(count($review)>0){
			BandReviewMedias::where('band_reviews_id',$review->id)->delete();
			$review->delete();
			return response( array('data' =>'Deleted successfuly' ,'response' => 1));
		} else {
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
	
	public function disapproveBandReview(Request $request){
		$id = $request->input('id');
		$approve = BandReview::findOrFail($id);
		if(count($approve)>0){
			$approve->is_approved = 0;
			$approve->save();
			Mail::send('emails.comments_on_event',
				['review'=>$approve,'heading'=>'Sorry.','data'=>'Your review on band has been disapproved'],
				function($message) use($approve)
				{
				   $message->to($approve->user->email)->subject('Carnivalist Band Comment Disaprroved.');
				}
			);
		}
	}
	
	public function getAjaxBands()
    {   
        $bands  = Band::orderBy('id', 'desc')->latest('id');
		
		return Datatables::of($bands)
        /* ->add_column('action', function($bands) {
		return '<a href="'.route("band-edit",$bands->id).'" class="btn btn-sm default" ><i class="fa fa-edit"></i></a>
		        <a href="'.route("band-delete",$bands->id).'" class="btn btn-sm default" ><i class="fa fa-times"></i>';}) */
		->make(true);
	
    }

}

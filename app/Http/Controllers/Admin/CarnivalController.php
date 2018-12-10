<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Carnival;
use App\Models\Event;
use Validator;
use Illuminate\Support\Facades\Input;
use Redirect;
use Auth;
use File;
use Image;
//use Yajra\Datatables\Datatables;
use Yajra\Datatables\Facades\Datatables;
use App\Library\ImageManipulator;

class CarnivalController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin', ['except' => 'logout']);
    }

    public function index()
    {   
        return view('admin.carnivals.list_carnivals');
    }

    /**
     * Create unique slug name
     */
    public function createSlug($slug){
        $next = 1;
        while(Carnival::where('carnival_slug', '=', $slug)->first()) {          
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
     * Edit slug
     */
    public function editSlug($slug,$requested_slug,$id){
        $last_slug = Carnival::select('carnival_slug')->where('id', '=', $id)->first();
       // echo $last_slug->carnival_slug."<br>".$requested_slug; die;
        if($last_slug->carnival_slug == $requested_slug){
           $slug = $requested_slug;
        }else{
          $slug = $this->createSlug($requested_slug);  
        }

        return $slug;
        
    }

    /**
     * Add new Carnival
     */
    public function addCarnival(Request $request){
     if($request->isMethod('post')){
         //print_r($request->all());
         $slug_input =  str_slug($request->input('carnival_name'), '-');      
         $slug = $this->createSlug($slug_input);
         
         $carnival_obj = new Carnival();
         $carnival_obj->carnival_name = $request->input('carnival_name');
         $carnival_obj->carnival_slug = $slug;
         $carnival_obj->is_active = ($request->input('is_active') == 1 ) ? '1' : '0';
		 $carnival_obj->save();
         if( !empty( $request->file('carnival_banner') ) )
         { 
            $banner = $request->file('carnival_banner');
            $input['imagename'] = Auth::user()->id.'.'.$banner->getClientOriginalExtension();
            $file_name = time().rand(0,99)."_".$input['imagename'];
            $destinationPath = public_path('uploads/carnival_banners/'.$carnival_obj->id); 
            if(!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, $mode = 0777, true, true);
            }
            $banner->move($destinationPath, $file_name);           
            // set the variable
            $carnival_obj->carnival_banner = $file_name; 
            $carnival_obj->save();
			
			$path      = public_path('uploads/carnival_banners/'.$carnival_obj->id).'/'.$carnival_obj->carnival_banner;
			$thumbnail1 = public_path('uploads/carnival_banners/'.$carnival_obj->id).'/'.'small_'.$carnival_obj->carnival_banner;
			$thumbnail2 = public_path('uploads/carnival_banners/'.$carnival_obj->id).'/'.'medium_'.$carnival_obj->carnival_banner;
			$thumbnail3 = public_path('uploads/carnival_banners/'.$carnival_obj->id).'/'.'large_'.$carnival_obj->carnival_banner;
			
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
         
         return Redirect::route('carnival-list')->with('message','Carnival Successfully Added.');	
     }else
        return view('admin.carnivals.add_carnivals');
    }
    
    
    /**
     * Delete carnival
     */
    public function deleteCarnival($id)
    {
        $delete_carnival = Carnival::findOrFail($id);
        //echo $delete_carnival->id; die;
        $event_exist = Event::where('carnival_id','=',$delete_carnival->id)->first();
        if($event_exist){
            return Redirect::route('carnival-list')->with('message','Carnival Can not be Deleted.');	   
        }else{     
            $delete_carnival->delete();
            return Redirect::route('carnival-list')->with('message','Carnival Successfully Deleted.');
        }
			
	   
    }
    
    /**
     * Edit carnival
     */
    public function editCarnival(Request $request,$id)
    {   
        $carnival_obj = Carnival::findOrFail($id);
        if($request->isMethod('post')){
            
            //<!-- validation rules
		    $rules = [				
				'carnival_slug' =>'required|unique:carnivals,carnival_slug,'.$id,			
			]; 
			$validator = Validator::make(Input::all(), $rules);
			if ($validator->fails())
			{ 
                $messages = $validator->messages();
				if (!empty($messages)) {
                    if ($messages->has('carnival_slug')) {
                        return Redirect::route('carnival-edit',$id)->with('error','Entered Slug Already Exists.');
                    }			         						   
				}
			}
            //validation rules ended-->

            $slug_input =  str_slug($request->input('carnival_name'), '-');      
            $carnival_obj->carnival_name = $request->input('carnival_name');
            $carnival_obj->carnival_slug = $this->editSlug($slug_input,$request->input('carnival_slug'),$id);
            $carnival_obj->is_active = ($request->input('is_active') == 1 ) ? '1' : '0';
           // echo $carnival_obj->is_active.'<br>'.$request->input('is_active'); die;


            if( !empty( $request->file('carnival_banner') ) )
            { 
               $banner = $request->file('carnival_banner');
               $input['imagename'] = Auth::user()->id.'.'.$banner->getClientOriginalExtension();
               $file_name = time().rand(0,99)."_".$input['imagename'];
               $destinationPath = public_path('uploads/carnival_banners/'.$carnival_obj->id); 
               if(!File::exists($destinationPath)) {
                   File::makeDirectory($destinationPath, $mode = 0777, true, true);
               }

               // delete the old banner
               if(!empty($carnival_obj->carnival_banner)){
                    $old_banner = public_path('uploads/carnival_banners/'.$carnival_obj->id.'/'.$carnival_obj->carnival_banner);
                    if(file_exists($old_banner)){
                        unlink($old_banner);
                    }
					$thumbnail1 = public_path('uploads/carnival_banners/'.$carnival_obj->id.'/'.'small_'.$carnival_obj->carnival_banner);
                    if(file_exists($thumbnail1)){
                        unlink($thumbnail1);
                    }
					$thumbnail2 = public_path('uploads/carnival_banners/'.$carnival_obj->id.'/'.'medium_'.$carnival_obj->carnival_banner);
                    if(file_exists($thumbnail2)){
                        unlink($thumbnail2);
                    }$thumbnail3 = public_path('uploads/carnival_banners/'.$carnival_obj->id.'/'.'large_'.$carnival_obj->carnival_banner);
                    if(file_exists($thumbnail3)){
                        unlink($thumbnail3);
                    }
					
                }                
               $banner->move($destinationPath, $file_name);           
               // set the variable
               $carnival_obj->carnival_banner = $file_name;            
           }
            $carnival_obj->save();
			
			$path      = public_path('uploads/carnival_banners/'.$carnival_obj->id).'/'.$carnival_obj->carnival_banner;
			$thumbnail1 = public_path('uploads/carnival_banners/'.$carnival_obj->id).'/'.'small_'.$carnival_obj->carnival_banner;
			$thumbnail2 = public_path('uploads/carnival_banners/'.$carnival_obj->id).'/'.'medium_'.$carnival_obj->carnival_banner;
			$thumbnail3 = public_path('uploads/carnival_banners/'.$carnival_obj->id).'/'.'large_'.$carnival_obj->carnival_banner;
			
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
			
            return Redirect::route('carnival-list')->with('message','Carnival Changes are Saved.');
        }
        else{
            return view('admin.carnivals.edit_carnivals')->with(['carnival_details' => $carnival_obj]);
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
	
	
	/***** get carnival events *******/
	public function getAjaxCarnivalEvents(Request $request,$id)
	{
		$carnival  = Carnival::find($id);
		if(count($carnival)>0){
			$data = [];
			$data = Event::where('carnival_id',$id)->latest('id');
			if(count($data)>0){
				return Datatables::of($data)->make(true);
				
			}else{
				return Datatables::of($data)->make();
			}
			
		}
		
	}
	/*********************/
	
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
	 
	public  function getAjaxCarnival()
	{
		$carnival    = Carnival::latest('id');
		return Datatables::of($carnival)
		/* ->add_column('action', function($carnival) {
           return '<a href="'.route("carnival-edit",$carnival->id).'" class="btn btn-sm default" ><i class="fa fa-edit"></i></a>
		        <a href="'.route("carnival-delete",$carnival->id).'" class="btn btn-sm default" ><i class="fa fa-times"></i>';}) */
		->make(true);
	}
	
	public function editCarnivalApproveStatus($id,$status){
		 $carnival = Carnival::findOrFail($id);
		 $carnival->is_active = $status;
		 $carnival->save();
		 return redirect()->back()->with('message','Carnival updated.');
	}
	
	public function postUpdateStatus(Request $request){
		$id = $request->id;
		$val = $request->value;
		$carnival  = Carnival::find($id);
		if($carnival){
			$carnival->is_active = $val;
			$carnival->save();
			return response( array('data' =>'Updated successfuly' ,'response' => 1));
		} else {
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
}


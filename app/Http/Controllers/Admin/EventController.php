<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Carnival;
use App\Models\Country;
use App\Models\EventsGallery;
use App\Models\EventTicketType;
use App\Models\EventReview;
use App\Models\EventReviewMedias;
use App\Models\TicketTypePdfs;
use Validator;
use Illuminate\Support\Facades\Input;
use Redirect;
use Auth;
use File;
use Image;
use Mail;
use Config;
use Yajra\Datatables\Facades\Datatables;
use App\Library\ImageManipulator;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin', ['except' => 'logout']);
    }

    public function index()
    {   
        $logged_user_id = Auth::user()->id;
        /* $event_details = Event::where('user_id',$logged_user_id)->orderBy('id', 'desc')->get(); */
        $event_details = Event::orderBy('id', 'desc')->get();
        return view('admin.events.list_events')->with(['event_details' => $event_details]);
    }

    /**
     * Create unique slug name
     */
    public function createSlug($slug){
        $next = 1;
        while(Event::where('event_slug', '=', $slug)->first()) {          
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
        $last_slug = Event::select('event_slug')->where('id', '=', $id)->first();
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
    public function addEvent(Request $request){
      
	 
     $carnival_details = Carnival::orderBy('carnival_name','ASC')->get();
     $country_details = Country::orderBy('id','ASC')->get();
     if($request->isMethod('post')){
         
         $event_obj = new Event();
         //echo "<pre>"; print_r($request->all()); die;
         $slug_input =  str_slug($request->input('event_name'), '-');      
         $slug = $this->createSlug($slug_input);
         $event_obj->event_slug = $slug;
         $event_obj->user_id = Auth::user()->id;
         $event_obj->event_name = $request->input('event_name');
         $event_obj->event_location = $request->input('event_location');
         $event_obj->carnival_id = $request->input('carnival_id');
         $event_obj->country_id = $request->input('country_id');
         $event_obj->event_description = $request->input('event_description');
         $event_obj->event_type = $request->input('event_type');
         $event_obj->carnival_type = $request->input('carnival_type');
         $event_obj->ticketing_website = $request->input('ticketing_website');
        
		
		$ticket_type = $request->input('ticket_type');
		$ticket_price = $request->input('ticket_price');
		$ticket_seats = $request->input('ticket_seats');
		$ticket_start_date = $request->input('ticket_start_date');
		$ticket_end_date = $request->input('ticket_end_date');
		$ticket_temp_id = $request->input('temp_id');
		
         if($request->input('event_type') == 'one time'){
            $start_date = date('Y-m-d', strtotime( $request->input('one_time_event_start_date') ));
			//$end_date = date('Y-m-d', strtotime( $request->input('one_time_event_end_date') ));
            $event_obj->event_date = $start_date;
            //$event_obj->event_end_date = $end_date;
         }

         //$event_obj->event_privacy = $request->input('event_privacy');
         $event_obj->total_tickets = $request->input('total_tickets') ? $request->input('total_tickets') : 0;
         
         $event_obj->ticket_service_tax = $request->input('ticket_service_tax') ? $request->input('ticket_service_tax') : 0 ;
         $event_obj->basic_ticket_price = $request->input('basic_ticket_price') ? $request->input('basic_ticket_price') : 0 ;
         $event_obj->final_ticket_price = $request->input('basic_ticket_price') + ($request->input('basic_ticket_price')*$request->input('ticket_service_tax'))/100;
         $event_obj->is_active = ($request->input('is_active') == 1) ? '1':'0';
        // $event_obj->is_approved = ($request->input('is_approved') == 1) ? '1':'0';
         $event_obj->is_approved = '1';
         $event_obj->yearly = ($request->input('yearly') == 1) ? '1':'0';
		 $event_obj->is_refundable = ($request->input('is_refundable') == 1) ? '1':'0';
		 
		 $event_obj->save();
		 
		 
		 if(count($ticket_type)>0){
			foreach($ticket_type as $k=>$v){
				if($v):
					$EventTicketType = new EventTicketType();
					$EventTicketType->event_id = $event_obj->id;
					$EventTicketType->ticket_type = $v;
					$EventTicketType->total_tickets = $ticket_seats[$k] ?  $ticket_seats[$k]:0;
					$EventTicketType->ticket_price = $ticket_price[$k] ?  $ticket_price[$k]:0;
					$EventTicketType->ticket_start_date =$ticket_start_date[$k] ?  date('Y-m-d',strtotime($ticket_start_date[$k])) : null;
					$EventTicketType->ticket_end_date = $ticket_end_date[$k] ? date('Y-m-d',strtotime($ticket_end_date[$k])) :null;
					$EventTicketType->save();
					
					$temp_id = $ticket_temp_id[$k];
					if($temp_id!=null):
						$destinationTempPath = public_path('uploads/temp/'.$temp_id); 
						$destinationPath = public_path('uploads/ticket_pdfs/'.$EventTicketType->id); 
						
						if(File::exists($destinationTempPath)) {
							if(!File::exists($destinationPath)) {
								File::makeDirectory($destinationPath, $mode = 0777, true, true);
							}
							$filesInFolder = File::files($destinationTempPath);
							$EventTicketType->total_tickets = count($filesInFolder);
							$EventTicketType->save();
							
							foreach($filesInFolder as $path)
							{
								$filename = pathinfo($path)['basename'];
								$TicketTypePdfs = new TicketTypePdfs();
								$TicketTypePdfs->ticket_type_id = $EventTicketType->id;
								$TicketTypePdfs->file = $filename;
								$TicketTypePdfs->allocated = 0;
								$TicketTypePdfs->save();
							}
							if(File::exists($destinationTempPath) && File::exists($destinationPath)) {
								File::move($destinationTempPath,$destinationPath);
							}
						}
						
						
					endif;
				endif;
			}
		}
		
         if( !empty( $request->file('event_banner') ) )
         {  
            $banner = $request->file('event_banner');
            $input['imagename'] = Auth::user()->id.'.'.$banner->getClientOriginalExtension();
            $file_name = time().rand(0,99)."_".$input['imagename'];
            $destinationPath = public_path('uploads/event_banners/'.$event_obj->id); 
            if(!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, $mode = 0777, true, true);
            }
            $banner->move($destinationPath, $file_name);
            $event_obj->event_banner = $file_name;   
			$event_obj->save();
			
			$path      = public_path('uploads/event_banners/'.$event_obj->id).'/'.$event_obj->event_banner;
			/* $thumbnail1 = public_path('uploads/event_banners/'.$event_obj->id).'/'.'small_'.$event_obj->event_banner;
			$thumbnail2 = public_path('uploads/event_banners/'.$event_obj->id).'/'.'medium_'.$event_obj->event_banner;
			$thumbnail3 = public_path('uploads/event_banners/'.$event_obj->id).'/'.'large_'.$event_obj->event_banner; */
			
			/* $thumbnail1 = Image::make(public_path('uploads/event_banners/'.$event_obj->id.'/'.$file_name));
			$thumbnail2 = Image::make(public_path('uploads/event_banners/'.$event_obj->id.'/'.$file_name));
			$thumbnail3 = Image::make(public_path('uploads/event_banners/'.$event_obj->id.'/'.$file_name));
			
			$thumbnail1->resize(160, 120);
			$thumbnail1->save(public_path('uploads/event_banners/'.$event_obj->id.'/small_'.$file_name));
			
			$thumbnail2->resize(375, 250);
			$thumbnail2->save(public_path('uploads/event_banners/'.$event_obj->id.'/medium_'.$file_name));
			
			$thumbnail3->resize(600, 250);
			$thumbnail3->save(public_path('uploads/event_banners/'.$event_obj->id.'/large_'.$file_name)); */
			
			/* $this->create_jpeg_thumbnail($path,"",160, 120,$thumbnail1);
			$this->create_jpeg_thumbnail($path,"",375, 250,$thumbnail2);
			$this->create_jpeg_thumbnail($path,"",600,250,$thumbnail3); */
			
			$thumbnail1 = public_path('uploads/event_banners/'.$event_obj->id).'/small_'.$file_name;
			$thumbnail2 = public_path('uploads/event_banners/'.$event_obj->id).'/medium_'.$file_name;
			$thumbnail3 = public_path('uploads/event_banners/'.$event_obj->id).'/large_'.$file_name;
			
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
         $images =  $request->file('event_gallery');
         foreach($removed_image_ids  as $id){
            if($id>2){
                unset($images[$id-2]);
            }else{
                unset($images[$id-1]);
            }           
        }
        
         if(count($images)>0){
            foreach($images as $image){
                $event_gallery = new EventsGallery();
                if( !empty($image) )
                {  
					$input['imagename'] = Auth::user()->id.'.'.$image->getClientOriginalExtension();
					$file_name = time().rand(0,99)."_".$input['imagename'];
					$destinationPath = public_path('uploads/event_gallery/'.$event_obj->id); 
					if(!File::exists($destinationPath)) {
						File::makeDirectory($destinationPath, $mode = 0777, true, true);
					}
					$image->move($destinationPath, $file_name);                      
				}
                $event_gallery->event_id = $event_obj->id;
                $event_gallery->event_gallery_image = $file_name; 
                $event_gallery->save();
				
				$path       = public_path('uploads/event_gallery/'.$event_obj->id).'/'.$event_gallery->event_gallery_image;
				/* $thumbnail1 = public_path('uploads/event_gallery/'.$event_obj->id).'/'.'small_'.$event_gallery->event_gallery_image;
				$thumbnail2 = public_path('uploads/event_gallery/'.$event_obj->id).'/'.'medium_'.$event_gallery->event_gallery_image;
				$thumbnail3 = public_path('uploads/event_gallery/'.$event_obj->id).'/'.'large_'.$event_gallery->event_gallery_image;
				
				
				$this->create_jpeg_thumbnail($path,"",160, 120,$thumbnail1);
				$this->create_jpeg_thumbnail($path,"",375, 250,$thumbnail2);
				$this->create_jpeg_thumbnail($path,"",600,250,$thumbnail3); */
				
				/* $thumbnail1 = Image::make($path);
				$thumbnail2 = Image::make($path);
				$thumbnail3 = Image::make($path);
				
				$thumbnail1->resize(160, 120);
				$thumbnail1->save(public_path('uploads/event_gallery/'.$event_obj->id.'/small_'.$file_name));
				
				$thumbnail2->resize(375, 250);
				$thumbnail2->save(public_path('uploads/event_gallery/'.$event_obj->id.'/medium_'.$file_name));
				
				$thumbnail3->resize(600, 250);
				$thumbnail3->save(public_path('uploads/event_gallery/'.$event_obj->id.'/large_'.$file_name)); */
				
				$thumbnail1 = public_path('uploads/event_gallery/'.$event_obj->id).'/small_'.$file_name;
				$thumbnail2 = public_path('uploads/event_gallery/'.$event_obj->id).'/medium_'.$file_name;
				$thumbnail3 = public_path('uploads/event_gallery/'.$event_obj->id).'/large_'.$file_name;
				
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
         return Redirect::route('event-list')->with('message','Event Successfully Added.');	
      }else
        return view('admin.events.add_events')->with(['carnival_details' => $carnival_details,'country_details'=>$country_details]);
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
    public function deleteEvent($id)
    {
        $delete_event = Event::findOrFail($id);
		$delete_event->delete();
        return Redirect::route('event-list')->with('message','Event Successfully Deleted.');	
	   
    }
	
	public function editEventApproveStatus($id,$status){
		 $event = Event::findOrFail($id);
		 $event->is_approved = $status;
		 $event->save();
		 return redirect()->back()->with('message','Event updated.');
	}
    
    /**
     * Edit Event
     */
    public function editEvent(Request $request,$id)
    {   
        $carnival_details = Carnival::orderBy('carnival_name','ASC')->get();
		
		
        $event_gallery_obj = EventsGallery::select('event_gallery_image')->where('event_id','=',$id)->get();
        $event_gallery_obj = $event_gallery_obj->toArray();
       
        $country_details = Country::orderBy('id','ASC')->get();
        $event_obj = Event::findOrFail($id);
		//echo "<pre>";print_r($event_obj);die();
		$reviews   = EventReview::where('event_id','=',$id)->with('user')->get();
		$event_ticket_types   = EventTicketType::where('event_id','=',$id)->get();
		
        if(count($event_obj)>0){
             if($request->isMethod('post')){

                //<!-- validation rules
                $rules = [				
                    'event_slug' =>'required|unique:events,event_slug,'.$id,			
                ]; 
                $validator = Validator::make(Input::all(), $rules);
                if ($validator->fails())
                { 
                    $messages = $validator->messages();
                    if (!empty($messages)) {
                        if ($messages->has('event_slug')) {
                            return Redirect::route('event-edit',$id)->with('error','Entered Slug Already Exists.');
                        }			         						   
                    }
                }
                //validation rules ended-->
				$event_user_id = '';
				if($request->input('promoter_id') !=""){
					$event_user_id = $request->input('promoter_id') ;
				} else {
					$event_user_id = Auth::user()->id;
				}
                $slug_input =  str_slug($request->input('event_slug'), '-');      
                $slug = $this->editSlug($slug_input,$request->input('event_slug'),$id);
                $event_obj->event_slug = $slug;
                $event_obj->user_id =$event_user_id;
                $event_obj->event_name = $request->input('event_name');
                $event_obj->event_location = $request->input('event_location');
                $event_obj->carnival_id = $request->input('carnival_id');
                $event_obj->country_id = $request->input('country_id');
                $event_obj->event_description = $request->input('event_description');
                $event_obj->event_type = $request->input('event_type');
				$event_obj->carnival_type = $request->input('carnival_type');
				$event_obj->ticketing_website = $request->input('ticketing_website');
				
                if($request->input('event_type') == 'one time'){
                   $start_date = date('Y-m-d', strtotime( $request->input('one_time_event_start_date') ));
					//$end_date = date('Y-m-d', strtotime( $request->input('one_time_event_end_date') ));
					$event_obj->event_date = $start_date;
					//$event_obj->event_end_date = $end_date;
                }
       
                //$event_obj->event_privacy = $request->input('event_privacy');
                $event_obj->total_tickets = $request->input('total_tickets') ? $request->input('total_tickets') :0;
                $event_obj->ticket_service_tax = $request->input('ticket_service_tax') ? $request->input('ticket_service_tax') :0;
                $event_obj->basic_ticket_price = $request->input('basic_ticket_price') ? $request->input('basic_ticket_price') :0;
                $event_obj->final_ticket_price = $request->input('basic_ticket_price') + ($request->input('basic_ticket_price')*$request->input('ticket_service_tax'))/100; 
                $event_obj->is_active = ($request->input('is_active') == 1) ? '1':'0';
                //$event_obj->is_approved = ($request->input('is_approved') == 1) ? '1':'0';
				$event_obj->yearly = ($request->input('yearly') == 1) ? '1':'0';
				$event_obj->is_refundable = ($request->input('is_refundable') == 1) ? '1':'0';
				
                if( !empty( $request->file('event_banner') ) )
                {  
                   $banner = $request->file('event_banner');
                   $input['imagename'] = Auth::user()->id.'.'.$banner->getClientOriginalExtension();
                   $file_name = time().rand(0,99)."_".$input['imagename'];
                   $destinationPath = public_path('uploads/event_banners/'.$event_obj->id); 
                   if(!File::exists($destinationPath)) {
                       File::makeDirectory($destinationPath, $mode = 0777, true, true);
                   }
                   // delete the old banner
                   if(!empty($event_obj->event_banner)){
                        $old_banner = public_path('uploads/events_banners/'.$event_obj->id.'/'.$event_obj->event_banner);
                        if(file_exists($old_banner)){
                            unlink($old_banner);
                        }
						
						$thumbnail1 = public_path('uploads/event_banners/'.$event_obj->id.'/'.'small_'.$event_obj->event_banner);
						if(file_exists($thumbnail1)){
							unlink($thumbnail1);
						}
						$thumbnail2 = public_path('uploads/event_banners/'.$event_obj->id.'/'.'medium_'.$event_obj->event_banner);
						if(file_exists($thumbnail2)){
							unlink($thumbnail2);
						}$thumbnail3 = public_path('uploads/event_banners/'.$event_obj->id.'/'.'large_'.$event_obj->event_banner);
						if(file_exists($thumbnail3)){
							unlink($thumbnail3);
						}
						
					
                    }
					$banner->move($destinationPath, $file_name);
					$event_obj->event_banner = $file_name; 
					
					$path       = public_path('uploads/event_banners/'.$event_obj->id).'/'.$file_name;
					$thumbnail1 = public_path('uploads/event_banners/'.$event_obj->id).'/small_'.$file_name;
					$thumbnail2 = public_path('uploads/event_banners/'.$event_obj->id).'/medium_'.$file_name;
					$thumbnail3 = public_path('uploads/event_banners/'.$event_obj->id).'/large_'.$file_name;
					/* $thumbnail1 = Image::make($path);
					$thumbnail2 = Image::make($path);
					$thumbnail3 = Image::make($path);

					$thumbnail1->resize(160, 120);
					$thumbnail1->save(public_path('uploads/event_banners/'.$event_obj->id.'/small_'.$file_name));

					$thumbnail2->resize(375, 250);
					$thumbnail2->save(public_path('uploads/event_banners/'.$event_obj->id.'/medium_'.$file_name));

					$thumbnail3->resize(600, 250);
					$thumbnail3->save(public_path('uploads/event_banners/'.$event_obj->id.'/large_'.$file_name)); */
					
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
			   
			    $ticket_type = $request->input('ticket_type');
				$ticket_seats = $request->input('ticket_seats');
				$ticket_price = $request->input('ticket_price');
				$ticket_ids = $request->input('ticket_ids');
				$ticket_start_date = $request->input('ticket_start_date');
				$ticket_end_date = $request->input('ticket_end_date');
				$ticket_temp_id = $request->input('temp_id');
				
                $event_obj->save();
				
				/* $path      = public_path('uploads/event_banners/'.$event_obj->id).'/'.$event_obj->event_banner;
				$thumbnail1 = public_path('uploads/event_banners/'.$event_obj->id).'/'.'small_'.$event_obj->event_banner;
				$thumbnail2 = public_path('uploads/event_banners/'.$event_obj->id).'/'.'medium_'.$event_obj->event_banner;
				$thumbnail3 = public_path('uploads/event_banners/'.$event_obj->id).'/'.'large_'.$event_obj->event_banner;
				if(!empty($event_obj->event_banner)){
					$this->create_jpeg_thumbnail($path,"",160, 120,$thumbnail1);
					$this->create_jpeg_thumbnail($path,"",375, 250,$thumbnail2);
					$this->create_jpeg_thumbnail($path,"",600,250,$thumbnail3);
				} */
                //removed saved images
                $remove_saved_imgs = $request->input('removed_saved_img');
                $remove_saved_imgs = ( explode( ',', $remove_saved_imgs ) );
                //var_dump($remove_saved_imgs); die;
				
				if(count($ticket_type)>0){
					foreach($ticket_type as $k=>$v){
						if($v):
							if(isset($ticket_ids[$k])){
								$EventTicketType = EventTicketType::find($ticket_ids[$k]);
								if($EventTicketType){
									$EventTicketType->event_id = $event_obj->id;
									$EventTicketType->ticket_type = $v;
									$EventTicketType->total_tickets = $ticket_seats[$k] ?  $ticket_seats[$k]:0;
									$EventTicketType->ticket_price = $ticket_price[$k] ? $ticket_price[$k] :0;
									$EventTicketType->ticket_start_date =$ticket_start_date[$k] ?  date('Y-m-d',strtotime($ticket_start_date[$k])) : null;
									$EventTicketType->ticket_end_date = $ticket_end_date[$k] ? date('Y-m-d',strtotime($ticket_end_date[$k])) : null;
									$EventTicketType->save();
								}

							} else {
								$EventTicketType = new EventTicketType();
								$EventTicketType->event_id = $event_obj->id;
								$EventTicketType->ticket_type = $v;
								$EventTicketType->total_tickets = $ticket_seats[$k] ?  $ticket_seats[$k]:0;
								$EventTicketType->ticket_price = $ticket_price[$k] ? $ticket_price[$k] :0;
								$ticket_start_date[$k] ?  date('Y-m-d',strtotime($ticket_start_date[$k])) :null;
								$EventTicketType->ticket_end_date = $ticket_end_date[$k] ? date('Y-m-d',strtotime($ticket_end_date[$k])) : null;
								$EventTicketType->save();
								$temp_id = $ticket_temp_id[$k];
								if($temp_id!=null):
									$destinationTempPath = public_path('uploads/temp/'.$temp_id); 
									$destinationPath = public_path('uploads/ticket_pdfs/'.$EventTicketType->id); 
									
									if(File::exists($destinationTempPath)) {
										if(!File::exists($destinationPath)) {
											File::makeDirectory($destinationPath, $mode = 0777, true, true);
										}
										$filesInFolder = File::files($destinationTempPath);
										if(count($filesInFolder) > 0){
											$EventTicketType->total_tickets = count($filesInFolder);
											$EventTicketType->save();
											
											foreach($filesInFolder as $path)
											{
												$filename = pathinfo($path)['basename'];
												$TicketTypePdfs = new TicketTypePdfs();
												$TicketTypePdfs->ticket_type_id = $EventTicketType->id;
												$TicketTypePdfs->file = $filename;
												$TicketTypePdfs->allocated = 0;
												$TicketTypePdfs->save();
											}
											if(File::exists($destinationTempPath) && File::exists($destinationPath)) {
												File::move($destinationTempPath,$destinationPath);
										}
										}
									}
									
									
								endif;
							}
						endif;
					}
				}
                if(count($remove_saved_imgs)>0){
                    foreach($remove_saved_imgs  as $img){
                        if(!empty($img)){
                            $old_image = public_path('uploads/event_gallery/'.$event_obj->id.'/'.$img);
							$thumbnail1 = public_path('uploads/event_gallery/'.$event_obj->id).'/'.'small_'.$img;
							$thumbnail2 = public_path('uploads/event_gallery/'.$event_obj->id).'/'.'medium_'.$img;
							$thumbnail3 = public_path('uploads/event_gallery/'.$event_obj->id).'/'.'large_'.$img;
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
							
                            $delete_image = EventsGallery::where('event_gallery_image', $img)->delete();
                        }
                    }

                }

                //removed uploaded on file selector but not to upload 
                $removed_image_ids = array_map('intval', explode(',', $request->input('removed_img')));
                $images =  $request->file('event_gallery');
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
                    $event_gallery = new EventsGallery();
                       if( !empty($image) )
                       {  
						   $input['imagename'] = Auth::user()->id.'.'.$image->getClientOriginalExtension();
						   $file_name = time().rand(0,99)."_".$input['imagename'];
						   $destinationPath = public_path('uploads/event_gallery/'.$event_obj->id); 
						   if(!File::exists($destinationPath)) {
							   File::makeDirectory($destinationPath, $mode = 0777, true, true);
						   }
						    $image->move($destinationPath, $file_name);

							$path       = public_path('uploads/event_gallery/'.$event_obj->id).'/'.$file_name;
					
							/* $thumbnail1 = Image::make($path);
							$thumbnail2 = Image::make($path);
							$thumbnail3 = Image::make($path);

							$thumbnail1->resize(160, 120);
							$thumbnail1->save(public_path('uploads/event_gallery/'.$event_obj->id.'/small_'.$file_name));

							$thumbnail2->resize(375, 250);
							$thumbnail2->save(public_path('uploads/event_gallery/'.$event_obj->id.'/medium_'.$file_name));

							$thumbnail3->resize(600, 250);
							$thumbnail3->save(public_path('uploads/event_gallery/'.$event_obj->id.'/large_'.$file_name)); */
							
							$thumbnail1 = public_path('uploads/event_gallery/'.$event_obj->id).'/small_'.$file_name;
							$thumbnail2 = public_path('uploads/event_gallery/'.$event_obj->id).'/medium_'.$file_name;
							$thumbnail3 = public_path('uploads/event_gallery/'.$event_obj->id).'/large_'.$file_name;
							
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
                       $event_gallery->event_id = $event_obj->id;
                       $event_gallery->event_gallery_image = $file_name; 
                       $event_gallery->save();
					   
					    /* $path       = public_path('uploads/event_gallery/'.$event_obj->id).'/'.$event_gallery->event_gallery_image;
						$thumbnail1 = public_path('uploads/event_gallery/'.$event_obj->id).'/'.'small_'.$event_gallery->event_gallery_image;
						$thumbnail2 = public_path('uploads/event_gallery/'.$event_obj->id).'/'.'medium_'.$event_gallery->event_gallery_image;
						$thumbnail3 = public_path('uploads/event_gallery/'.$event_obj->id).'/'.'large_'.$event_gallery->event_gallery_image;
						$this->create_jpeg_thumbnail($path,"",160, 120,$thumbnail1);
						$this->create_jpeg_thumbnail($path,"",375, 250,$thumbnail2);
						$this->create_jpeg_thumbnail($path,"",600,250,$thumbnail3); */
       
                   }
               }
			   
			   
                return Redirect::route('event-list')->with('message','Event Successfully Edited.');	
            }
            else{
                return view('admin.events.edit_events')->with(['event_details' => $event_obj,'carnival_details'=>$carnival_details,'country_details'=>$country_details,'event_gallery'=>$event_gallery_obj,'reviews'=>$reviews,'event_ticket_types'=>$event_ticket_types]);
            }
        }

    }
	
	public function create_jpeg_thumbnail($imgSrc,$thumbDirectory,$thumbnail_width,$thumbnail_height,$image) {
		$thumb = '';
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
		      $myImage = @imagecreatefrompng("$imageDirectory/$imageName");
			 
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
				 if($myImage):
					  @imagecopyresampled($process, $myImage, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
					 
					  $thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
					  
					  @imagecopyresampled($thumb, $process, 0, 0, ($x_mid-($thumbnail_width/2)), ($y_mid-($thumbnail_height/2)), $thumbnail_width, $thumbnail_height, $thumbnail_width, $thumbnail_height);
				  endif;
		     }else{
				  if($myImage):
					   $ratio_desc = ceil(($thumbnail_height/$height_orig)*100);
					   $new_height = round(($ratio_desc/100)*$height_orig);
					   $new_width = round(($ratio_desc/100)*$width_orig);
				   
					   $thumb = @imagecreatetruecolor($thumbnail_width, $thumbnail_height) ;
					   // fill rest color with grey background
					   $grey = @imagecolorallocate($thumb, 62, 62, 62);
					   @imagefill($thumb, 0, 0, $grey);
					   
					   @imagecopyresampled($thumb, $myImage, round(($thumbnail_width-$new_width)/2), 0, 0, 0, $new_width, $thumbnail_height, $width_orig, $height_orig);
				   endif;
		     }
	     
	     $thumbImageName = $image;
	     $destination = $thumbDirectory=='' ? $thumbImageName : $thumbDirectory."/".$thumbImageName;
	     @imagejpeg($thumb, $destination, 100);
	     return $thumbImageName;
	 }
	 
	
	public function approveEventReview(Request $request){
		$id = $request->input('id');
		$approve = EventReview::findOrFail($id);
		if(count($approve)>0){
			$approve->user->email;
			$approve->is_approved = 1;
			$approve->save();
			Mail::send('emails.comments_on_event',
				['review'=>$approve,'heading'=>'Congratulations.','data'=>'Your review on comment has been approved'],
				function($message) use($approve)
				{
				   $message->to($approve->user->email)->subject('Carnivalist Event Comment Aprroved.');
				}
			);
		}
	}
	
	public function disapproveEventReview(Request $request){
		$id = $request->input('id');
		$approve = EventReview::findOrFail($id);
		if(count($approve)>0){
			$approve->is_approved = 0;
			$approve->save();
			Mail::send('emails.comments_on_event',
				['review'=>$approve,'heading'=>'Sorry.','data'=>'Your review on comment has been disapproved'],
				function($message) use($approve)
				{
				   $message->to($approve->user->email)->subject('Carnivalist Event Comment Disaprroved.');
				}
			);
		}
	}
	
	public function deleteEventReview(Request $request){
		$id = $request->input('id');
		$review = EventReview::findOrFail($id);
		if(count($review)>0){
			EventReviewMedias::where('event_reviews_id',$review->id)->delete();
			$review->delete();
			return response( array('data' =>'Deleted successfuly' ,'response' => 1));
		} else {
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
	
	public function getAjaxEvents()
	{
		$events  = Event::orderBy('id', 'desc')->latest('id');
		return Datatables::of($events)
			/* ->add_column('action', function($events) {
				return '<a href="'.route("event-edit",$events->id).'" class="btn btn-sm default" ><i class="fa fa-edit"></i></a>
		        <a href="'.route("event-delete",$events->id).'" class="btn btn-sm default" ><i class="fa fa-times"></i>';}) */
			->make(true);
	}
	public function postUpdateApproval(Request $request){
		$id = $request->id;
		$val = $request->value;
		$event  = Event::find($id);
		if($event){
			$event->is_approved = $val;
			$event->save();
			if($val==1):
				Mail::send('emails.event_approved',
					['event'=>$event,'heading'=>'Congratulations.','data'=>'Your request for event has been approved'],
					function($message) use($event)
					{
					   $message->to($event->user->email)->subject('Carnivalist Event Request Aprroved.');
					}
				);
			endif;
			return response( array('data' =>'Updated successfuly' ,'response' => 1));
		} else {
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
	public function postUpdateStatus(Request $request){
		$id = $request->id;
		$val = $request->value;
		$event  = Event::find($id);
		if($event){
			$event->is_active = $val;
			$event->save();
			return response( array('data' =>'Updated successfuly' ,'response' => 1));
		} else {
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
}

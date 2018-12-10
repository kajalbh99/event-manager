<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use App\Models\Carnival;
use App\Models\Event;
use App\Models\EventReview;
use App\Models\BandCarnival;
use App\Models\BandReview;
use App\Models\Band;
use App\Models\User;
use App\Models\Ticket;
use App\Models\CommitteeMember;
use App\Models\EventsGallery;
use App\Models\EventReviewMedias;
use App\Models\BandReviewMedias;
use App\Models\Guard;
use App\Models\CommonFunctions;
use Input;
use File;
use Illuminate\Support\Facades\Hash;
use Mail;
use Config;
use App\Models\EventTicketType;
use Image;
use App\Library\ImageManipulator;

class HomeController extends Controller
{		
	public function carnival_list(){
		$carnival_details = Carnival::where('is_active','=','1')->orderBy('carnival_name','asc')->get();
		if(count($carnival_details)>0){
			$data = $carnival_details;
			return response( array('data' =>$data ,'response' => 1));
		}else{
			return response( array('data' =>'error' ,'response' => 0));
		}
		
	}
	
	public function event_list(Request $request){
		$id = $request->input('carnival_id');
		if($id>0 && !empty($id) ){
			$carnival = Carnival::findOrFail($id);
			$carnival_event_detail = Event::where('carnival_id','=',$id)
			->where('is_active','=','1')
			->where(function($query){
				$query->whereDate('event_date', '>=', date('Y-m-d'));
				$query->orWhere('yearly', '1');
			})
			->orderBy('yearly', 'asc')
			->orderBy('event_date', 'asc')
			->get();
			
			$local_event_detail = Event::where('carnival_id','=',$id)
			->where('is_active','=','1')
			->where(function($query){
				$query->whereDate('event_date', '>=', date('Y-m-d'));
				$query->orWhere('yearly', '1');
			})
			->orderBy('yearly', 'asc')
			->orderBy('event_date', 'asc')		
			->get();
			$data = $carnival_event_detail;
			return response( array('data' =>$data ,'response' => 1));
		}else{
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
	
	public function event_list_test(Request $request){
		$id = $request->input('carnival_id');
		if($id>0 && !empty($id) ){
			$carnival = Carnival::findOrFail($id);
			$carnival_event_detail = Event::where('carnival_id','=',$id)
			->where('is_active','=','1')
			->where('is_approved','=','1')
			->where('carnival_type','=','0')
			->where(function($query){
				$query->where(function($query1){
					$query1->whereDate('event_date', '>=', date('Y-m-d'));
					$query1->whereNull('event_end_date');
				});
				//$query->orWhere('yearly', '1');
				$query->orWhere(function($query2){
					$query2->whereDate('event_date', '>=', date('Y-m-d'));
					$query2->whereNotNull('event_end_date');
				});
				
				//$query->orWhere('yearly', '1');
				$query->orWhere(function($query3){
					$query3->whereDate('event_date', '<=', date('Y-m-d'));
					$query3->whereDate('event_end_date', '>=', date('Y-m-d'));
				});
			})
			
			/* ->where(function($query){
				$query->whereDate('event_end_date', '<=', date('Y-m-d'));
				//$query->orWhere('yearly', '1');
			}) */
			//->orderBy('id', 'desc')
			->orderBy('event_date', 'asc')
			->orderBy('id', 'desc')
			->get();
			
			$local_event_detail = Event::where('carnival_id','=',$id)
			->where('is_active','=','1')
			->where('carnival_type','=','1')
			->where('is_approved','=','1')
			->where(function($query){
				$query->where(function($query1){
					$query1->whereDate('event_date', '>=', date('Y-m-d'));
					$query1->whereNull('event_end_date');
				});
				//$query->orWhere('yearly', '1');
				$query->orWhere(function($query2){
					$query2->whereDate('event_date', '>=', date('Y-m-d'));
					$query2->whereNotNull('event_end_date');
				});
				
				//$query->orWhere('yearly', '1');
				$query->orWhere(function($query3){
					$query3->whereDate('event_date', '<=', date('Y-m-d'));
					$query3->whereDate('event_end_date', '>=', date('Y-m-d'));
				});
			})
			//->orderBy('id', 'desc')
			->orderBy('event_date', 'asc')
			->orderBy('id', 'desc')			
			->get();
			$data['carnival'] = $carnival_event_detail;
			$data['local'] = $local_event_detail;
			return response( array('data' =>$data ,'response' => 1));
		}else{
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
	
	public function event_detail(Request $request){
		$id = $request->input('event_id');
		if($id>0 && !empty($id) ){
			$event_detail = Event::where('id','=',$id)->with('event_gallery')->first();
			$reviews      = EventReview::where('event_id','=',$id)->where('is_approved','=',1)->with('user')->with('gallery_image')->get();
			$data['event_detail'] = $event_detail;
			$data['reviews']      = $reviews;
			return response( array('data' =>$data ,'response' => 1));
		}else{
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
	
	public function band_list(Request $request){
		$id = $request->input('carnival_id');
		if($id>0 && !empty($id) ){
			$carnival      = Carnival::findOrFail($id);
			$band_carnival = BandCarnival::where('carnival_id','=',$id)->with('band')->get();
			$band = array();
			if(count($band_carnival)>0){
				foreach($band_carnival as $item){
					if(!empty($item['band'])){
						$band[] = $item['band'];	
					}
				}
			}			
			$data = $band;			
			return response( array('data' => $data ,'response' => 1));
		}else{
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
	
	public function band_detail(Request $request){
		$id = $request->input('band_id');
		if($id>0 && !empty($id) ){
			$gallery =  Band::where('id','=',$id)->with('band_gallery')->get();
			$reviews = BandReview::where('band_id','=',$id)->where('is_approved','=',1)->with('gallery_image')->with('user')->get();
			$data['gallery'] = $gallery;
			$data['reviews'] = $reviews;
			return response( array('data' => $data ,'response' => 1));
		}else{
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
	
	public function carnival_detail(Request $request){
		$id         = $request->input('carnival_id');
		$user_email = $request->input('user_email');
		$user       = User::where('email','=',$user_email)->first();
		if($id>0 && !empty($id) && (count($user)>0) ){
			$carnival_details = Carnival::findOrFail($id);
			if(count($carnival_details)>0){
				$data['carnival'] = $carnival_details;
				$data['user']  = $user;
				return response( array('data' =>$data ,'response' => 1));
			}else{
				return response( array('data' =>'error' ,'response' => 0));
			}
		}else{
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
	
	public function band_comment(Request $request){
		$title      = "title";
		$comment    = $request->input('comment');
		$rating     = $request->input('rating');
		$user_email = $request->input('user');
		$band_id    = $request->input('band_id');
		$admin_email = Config::get('constants.admin_email');
		if(!empty($comment) && !empty($user_email) && !empty($band_id)){
			$user = User::where("email","=",$user_email)->first();
			if(count($user)>0){
				/* $band_review = BandReview::where('user_id','=',$user->id)->where('band_id','=',$band_id)->first();
				
				if(count($band_review)>0){
					$review  = $band_review;
				}else{
					$review  = new BandReview();
				} */
				$review  = new BandReview();
				$review->user_id = $user->id;
				$review->band_id = $band_id;
				$review->review_title = $title;
				if($rating > 0){
				$review->rating  = $rating;	
				}else{
				$review->rating  = 0;	
				}
				$review->review_description = $comment;
				$review->is_approved = 0;
				$review->save();
				if($review->id>0){
					$review_media = BandReviewMedias::where('band_reviews_id',$review->id)->get();
					// delete the old images
					if(count($review_media)>0){
						foreach ($review_media as $media){ 
						   if(!empty($media->band_review_image)){
							    $media->delete();
								$old_image = public_path('uploads/band_review_gallery/'.$review->id.'/'.$media->band_review_image);
								if(file_exists($old_image)){
									unlink($old_image);
								} 
							}
						}
					}
			
				}
				/* Mail::send('emails.comments',
					['review'=>$review,'heading'=>'New commnet on band.','data'=>'You have new comment on band'],
					function($message) use($user_email)
					{
					   $message->to($user_email)->subject('Carnivalist Band Comment.');
					}
				); */
				Mail::send('emails.comments_to_admin',
					['review'=>$review,'heading'=>'Thank You.','data'=>'You have commented on band'],
					function($message) use($admin_email)
					{
					   $message->to($admin_email)->subject('Carnivalist Band Comment.');
					}
				);
				return response( array('data' => $review ,'review_id' => $review->id,'response' => 1));
			}
			    return response( array('data' => "error",'response' => 0));
		}
		
		return response( array('data' => "error",'response' => 0));
		
	}
	
	public function event_comment(Request $request){
		$title      = "title";
		$comment    = $request->input('comment');
		$rating     = $request->input('rating');
		$user_email = $request->input('user');
		$event_id   = $request->input('event_id');
		$admin_email = Config::get('constants.admin_email');
		if(!empty($comment) && !empty($user_email) && !empty($event_id)){
			$user = User::where("email","=",$user_email)->first();
			if(count($user)>0){
				/* $event_review = EventReview::where('user_id','=',$user->id)->where('event_id','=',$event_id)->first();
				
				if(count($event_review)>0){
					$review = $event_review;
				}else{
					$review = new EventReview();
				} */
				$review = new EventReview();
				$review->user_id            = $user->id;
				$review->event_id           = $event_id;
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
					$review_media = EventReviewMedias::where('event_reviews_id',$review->id)->get();
					// delete the old images
					if(count($review_media)>0){
						foreach ($review_media as $media){ 
						   if(!empty($media->event_review_image)){
							    $media->delete();
								$old_image = public_path('uploads/event_review_gallery/'.$review->id.'/'.$media->event_review_image);
								if(file_exists($old_image)){
									unlink($old_image);
								} 
							}
						}
					}
			
				}
				/* Mail::send('emails.comments_on_event',
					['review'=>$review,'heading'=>'New commnet on event.','data'=>'You have new comment on event'],
					function($message) use($user_email)
					{
					   $message->to($user_email)->subject('Carnivalist Event Comment.');
					}
				); */
				Mail::send('emails.comments_on_event_to_admin',
					['review'=>$review,'heading'=>'Thank You.','data'=>'You have commented on event'],
					function($message) use($admin_email)
					{
					   $message->to($admin_email)->subject('Carnivalist Event Comment.');
					}
				);
				return response( array('data' => $review ,'review_id' => $review->id,'response' => 1));
			}
			    return response( array('data' => "error",'response' => 0));
		}
		
		return response( array('data' => "error",'response' => 0));
		
	}
	
	public function event_review_upload_image(Request $request){
		$review_id = $request->input('review_id');
		$review = new EventReviewMedias();
		$data =  $request->file('file');
		if( (!empty( $request->file('file'))) && ($review_id > 0 ) )
		{  
		   $photo = $request->file('file');
		   $input['imagename'] = $review_id.'.'.$photo->getClientOriginalExtension();
		   $file_name = time().rand(0,99)."_".$input['imagename'];
		   $destinationPath = public_path('uploads/event_review_gallery/'.$review_id); 
		   if(!File::exists($destinationPath)) {
			   File::makeDirectory($destinationPath, $mode = 0777, true, true);
		   }			
		   $photo->move($destinationPath, $file_name);
		   $review->event_reviews_id   = $review_id;
		   $review->event_review_image = $file_name; 
		   $review->save(); 

			$path = public_path('uploads/event_review_gallery/'.$review_id).'/'.$review->event_review_image;
			$thumbnail = public_path('uploads/event_review_gallery/'.$review_id).'/'.'thumbnail_'.$review->event_review_image;
			if(file_exists($path))
			{
				$this->create_jpeg_thumbnail($path,"",150, 150,$thumbnail);
				//$this->create_thumbnail_with_crop($path,"",150, 150,$thumbnail);
			}
		}
		return response('success');	
	}
	
	public function create_thumbnail($imgSrc,$thumbDirectory,$thumbnail_width,$thumbnail_height,$image){
		$thumbnail1 = Image::make($imgSrc);
		$thumbnail1->resize($thumbnail_width, $thumbnail_height);
		$thumbnail1->save($image);
	}
	
	public function band_review_upload_image(Request $request){
		$review_id = $request->input('review_id');
		$review = new BandReviewMedias();
		$data =  $request->file('file');
		if( (!empty( $request->file('file'))) && ($review_id > 0 ) )
		{  
		   $photo = $request->file('file');
		   $input['imagename'] = $review_id.'.'.$photo->getClientOriginalExtension();
		   $file_name = time().rand(0,99)."_".$input['imagename'];
		   $destinationPath = public_path('uploads/band_review_gallery/'.$review_id); 
		   if(!File::exists($destinationPath)) {
			   File::makeDirectory($destinationPath, $mode = 0777, true, true);
		   }			
		   $photo->move($destinationPath, $file_name);
		   $review->band_reviews_id   = $review_id;
		   $review->band_review_image = $file_name; 
		   $review->save(); 
		   
		   $path = public_path('uploads/band_review_gallery/'.$review_id).'/'.$review->band_review_image;
		   $thumbnail = public_path('uploads/band_review_gallery/'.$review_id).'/'.'thumbnail_'.$review->band_review_image;
		   if(file_exists($path))
			{
				$this->create_jpeg_thumbnail($path,"",150, 150,$thumbnail);
				//$this->create_thumbnail_with_crop($path,"",150, 150,$thumbnail);
			}
		} 
		return response('success');	
	}
	
	public function save_carnival_key(Request $request){
		$id  = $request->input('carnival_id');
		$key = $request->input('carnival_key');
		if($id>0 && !empty($id)  && !empty($key)){
			$save_carnival_key = Carnival::findOrFail($id);
			if(count($save_carnival_key)>0){
				$save_carnival_key->carnival_key = $key;
				$save_carnival_key->save();				
				return response( array('data' => "success",'response' => 1));
			}else{
				return response( array('data' => "error",'response' => 0));
			}
		}else{
			return response( array('data' => "error",'response' => 0));
		}
	}
	
	public function promoter_events(Request $request){
		$email = $request->input('user_email');
		$user = User::where("email","=",$email)->first();
		if(count($user)>0){
			$event_detail = Event::where('user_id','=',$user->id)->where('is_active','=','1')/* ->whereDate('event_date', '>=', date('Y-m-d')) */->orderBy('event_date', 'asc')->get();
			if(count($event_detail)>0){
				return response( array('data' => $event_detail,'response' => 1));					
			}else{
				return response( array('data' => "error",'response' => 0));
			}
		}else{
			return response( array('data' => "error",'response' => 0));
		}
		
	}
	
	public function promoter_event_create(Request $request){
		$email   = $request->input('user_email');
		$user    = User::where("email","=",$email)->first();
		$members = $request->input('member_id');
		$member_ids = explode(',', $members);
		$admin_email = Config::get('constants.admin_email');
		//echo "<pre>"; print_r($member_ids); die; 
		if(count($user)>0){
			$event = new Event();
			
			$slug_input =  str_slug($request->input('event_name'), '-');      
            $slug = $this->createSlug($slug_input);
		 
			$event->user_id              = $user->id;
			$event->event_name           = $request->input('event_name');
			$event->event_title          = $request->input('event_name');
			$event->event_slug           = $slug;
			$event->event_description    = $request->input('event_description');
			$event->event_location       = $request->input('event_location');
			$event->event_date           = $request->input('event_date');			
			$event->event_type           = 'one time';
			$event->carnival_id          = $request->input('carnival_id');
			$event->carnival_type        = $request->input('carnival_type');
			$event->country_id           = $request->input('country_id');
			$event->total_tickets        = $request->input('total_tickets');
			$event->basic_ticket_price   = $request->input('basic_ticket_price');
			$event->ticket_service_tax   = $request->input('ticket_service_tax');
			$event->final_ticket_price   = $request->input('basic_ticket_price') + $request->input('ticket_service_tax');
			$event->is_active            = ($request->input('is_active') == 'true' ) ? '1' : '0' ;
			$event->save();
			if($event->id>0){
				if(isset($members) && count($member_ids) >0){
					foreach( $member_ids as $id ){
						$add_member = new CommitteeMember();
						$add_member->event_id  = $event->id;
						$add_member->member_id = $id;
						$add_member->save();
					}
				} 
				Mail::send('emails.promoter_event',
					['data'=>'You have successfully created event '.$event->event_name.'','heading'=>'Thank You'],
					function($message) use($email)
					{
					   $message->to($email)->subject('Carnivalist Event Created.');
					}
				);
				
				Mail::send('emails.promoter_event',
					['data'=>ucfirst($user->name) .' created event '.$event->event_name.'','heading'=>'New Event Created.'],
					function($message) use($admin_email)
					{
					   $message->to($admin_email)->subject('New event created.');
					}
				);
				return response( array('data' => $event->id,'response' => 1));
			}else{
				return response( array('data' => "error",'response' => 0));
			}
		}else{
			return response( array('data' => "error",'response' => 0));
		}
		
	}
	
	public function promoter_event_edit(Request $request){
		$event_id   = $request->input('event_id');
		$event=   Event::find($event_id);
		$email   = $request->input('user_email');
		$user    = User::where("email","=",$email)->first();
		$members = $request->input('member_id');
		$member_ids = explode(',', $members);
		$admin_email = Config::get('constants.admin_email');		
		if($event){
			if(count($user)>0){
				
				$slug_input =  str_slug($request->input('event_name'), '-');      
				$slug = $this->createSlug($slug_input);
			 
				$event->user_id              = $user->id;
				$event->event_name           = $request->input('event_name');
				$event->event_title          = $request->input('event_name');
				$event->event_slug           = $slug;
				$event->event_description    = $request->input('event_description');
				$event->event_location       = $request->input('event_location');
				$event->event_date           = $request->input('event_date');			
				$event->event_type           = 'one time';
				$event->carnival_id          = $request->input('carnival_id');
				$event->country_id           = $request->input('country_id');
				$event->total_tickets        = $request->input('total_tickets');
				$event->basic_ticket_price   = $request->input('basic_ticket_price');
				$event->ticket_service_tax   = $request->input('ticket_service_tax');
				$event->final_ticket_price   = $request->input('basic_ticket_price') + $request->input('ticket_service_tax');
				$event->is_active            = ($request->input('is_active') == 'true' ) ? '1' : '0' ;
				$event->save();
				if($event->id>0){
					if(isset($members) && count($member_ids) >0){
						foreach( $member_ids as $id ){
							$add_member = new CommitteeMember();
							$add_member->event_id  = $event->id;
							$add_member->member_id = $id;
							$add_member->save();
						}
					}
					Mail::send('emails.promoter_event',
						['data'=>'You have successfully updated event '.$event->event_name.'','heading'=>'Thank You'],
						function($message) use($email)
						{
						   $message->to($email)->subject('Carnivalist Event Updated.');
						}
					);
					
					Mail::send('emails.promoter_event',
						['data'=>ucfirst($user->name) .' updated event '.$event->event_name.'','heading'=>'Event Updated.'],
						function($message) use($admin_email)
						{
						   $message->to($admin_email)->subject('Event Updated.');
						}
					);					
					return response( array('data' => $event->id,'response' => 1));
				}else{
					return response( array('data' => "error",'response' => 0));
				}
			}
		}else{
			return response( array('data' => "error",'response' => 0));
		}
		
	}
	
	public function event_upload_banner(Request $request){
		$event_id = $request->input('event_id');
		$event= Event::findOrFail($event_id);
        echo "<pre>"; print_r($event);		
		if( !empty( $request->file('file') ) )
            {  
               $photo              = $request->file('file');
               $input['imagename'] = $event->id.'.'.$photo->getClientOriginalExtension();
               $file_name          = time().rand(0,99)."_".$input['imagename'];
               $destinationPath    = public_path('uploads/event_banners/'.$event->id); 
               if(!File::exists($destinationPath)) {
                   File::makeDirectory($destinationPath, $mode = 0777, true, true);
               }
			  			  				
				$photo->move($destinationPath, $file_name);
				$event->event_banner = $file_name; 
				$event->save();
				
				/****** upload thumbnails **********/
				$path = public_path('uploads/event_banners/'.$event->id).'/'.$file_name;
				$thumbnail1 = public_path('uploads/event_banners/'.$event->id).'/small_'.$file_name;
				$thumbnail2 = public_path('uploads/event_banners/'.$event->id).'/medium_'.$file_name;
				$thumbnail3 = public_path('uploads/event_banners/'.$event->id).'/large_'.$file_name;
				/* $thumbnail1 = Image::make(public_path('uploads/event_banners/'.$event->id.'/'.$file_name));
				$thumbnail2 = Image::make(public_path('uploads/event_banners/'.$event->id.'/'.$file_name));
				$thumbnail3 = Image::make(public_path('uploads/event_banners/'.$event->id.'/'.$file_name));

				$thumbnail1->resize(160, 120);
				$thumbnail1->save(public_path('uploads/event_banners/'.$event->id.'/small_'.$file_name));

				$thumbnail2->resize(375, 250);
				$thumbnail2->save(public_path('uploads/event_banners/'.$event->id.'/medium_'.$file_name));

				$thumbnail3->resize(600, 250);
				$thumbnail3->save(public_path('uploads/event_banners/'.$event->id.'/large_'.$file_name)); */
				/*******************************/
				
			    /* $path       = public_path('uploads/event_banners/'.$event->id).'/'.$event->event_banner;
				$thumbnail1 = public_path('uploads/event_banners/'.$event->id).'/'.'small_'.$event->event_banner;
				$thumbnail2 = public_path('uploads/event_banners/'.$event->id).'/'.'medium_'.$event->event_banner;
				$thumbnail3 = public_path('uploads/event_banners/'.$event->id).'/'.'large_'.$event->event_banner;*/
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
			   return response('success');
           }else{
			    return response('error');
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
	public function event_change_banner(Request $request){
		$event_id = $request->input('event_id');
		$event= Event::find($event_id);
        if( $event) {
			if( !empty( $request->file('file') ) )
			{  
			   $photo = $request->file('file');
			   $input['imagename'] = $event->id.'.'.$photo->getClientOriginalExtension();
			   $file_name          = time().rand(0,99)."_".$input['imagename'];
			   $destinationPath    = public_path('uploads/event_banners/'.$event->id); 
			   if(!File::exists($destinationPath)) {
				   File::makeDirectory($destinationPath, $mode = 0777, true, true);
			   }
				if(!empty($event->event_banner)){
					$old_banner = public_path('uploads/events_banners/'.$event->id.'/'.$event->event_banner);
					$thumbnail1 = public_path('uploads/event_gallery/'.$event->id).'/'.'small_'.$event->event_banner;
					$thumbnail2 = public_path('uploads/event_gallery/'.$event->id).'/'.'medium_'.$event->event_banner;
					$thumbnail3 = public_path('uploads/event_gallery/'.$event->id).'/'.'large_'.$event->event_banner;
					if(file_exists($old_banner)){
						unlink($old_banner);
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
				}						
			   $photo->move($destinationPath, $file_name);
			   $event->event_banner = $file_name; 
			   $event->save();
			   
			   /***** upload new banner ******/
			   
				/* $thumbnail1 = Image::make(public_path('uploads/event_banners/'.$event->id.'/'.$file_name));
				$thumbnail2 = Image::make(public_path('uploads/event_banners/'.$event->id.'/'.$file_name));
				$thumbnail3 = Image::make(public_path('uploads/event_banners/'.$event->id.'/'.$file_name));

				$thumbnail1->resize(160, 120);
				$thumbnail1->save(public_path('uploads/event_banners/'.$event->id.'/small_'.$file_name));

				$thumbnail2->resize(375, 250);
				$thumbnail2->save(public_path('uploads/event_banners/'.$event->id.'/medium_'.$file_name));

				$thumbnail3->resize(600, 250);
				$thumbnail3->save(public_path('uploads/event_banners/'.$event->id.'/large_'.$file_name)); */
			   
			   /*****************************/
			   /* 
				$path       = public_path('uploads/event_banners/'.$event->id).'/'.$event->event_banner;
				$thumbnail1 = public_path('uploads/event_banners/'.$event->id).'/'.'small_'.$event->event_banner;
				$thumbnail2 = public_path('uploads/event_banners/'.$event->id).'/'.'medium_'.$event->event_banner;
				$thumbnail3 = public_path('uploads/event_banners/'.$event->id).'/'.'large_'.$event->event_banner;
				$this->create_jpeg_thumbnail($path,"",160, 120,$thumbnail1);
				$this->create_jpeg_thumbnail($path,"",375, 250,$thumbnail2);
				$this->create_jpeg_thumbnail($path,"",600,250,$thumbnail3); */
				
				$path = public_path('uploads/event_banners/'.$event->id).'/'.$file_name;
				$thumbnail1 = public_path('uploads/event_banners/'.$event->id).'/small_'.$file_name;
				$thumbnail2 = public_path('uploads/event_banners/'.$event->id).'/medium_'.$file_name;
				$thumbnail3 = public_path('uploads/event_banners/'.$event->id).'/large_'.$file_name;
				
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

			   return response('success');
		   
			}
		}else{
			    return response('error');
		   }
       
		
	}
	
	/**
     * Create unique slug name
     */
    public function createSlug($slug){
        $next = 1;
        while(Event::where('event_slug', '=', $slug)->first()) {          
            $pos   = strrpos($slug, "_");
            $digit = substr($slug, -1);
            $digit = intval($digit);
            if($digit == 0){
               $i    = 1;
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
	 * getting list of users for event Committee Member
	 */
	public function get_all_users(){
		$users = User::where('type','=','user')->get();
		if(count($users)>0){
			return response(array("data" => $users,"response" => 1));
		}else{
			return response(array("data" => "error","response" => 0));
		}
	}
	
	/**
	  * Website api
	  */
	  
	public function upcoming_events(Request $request){
		$event_detail = Event::where('is_active','=','1')->whereDate('event_date', '>=', date('Y-m-d'))->orderBy('event_date', 'asc')->paginate(6);
		if(count($event_detail)>0){
			return response( array('data' =>$event_detail ,'response' => 1));
		}else{
			return response( array('data' =>'No events found' ,'response' => 0));
		}	
	}

	public function total_count(){
		$events          = Event::all()->count();
		$users           = User::where('is_active','1')->count();
		$tickets         = Ticket::where('status','1')->count();
		$data['events']  = $events;
		$data['users']   = $users;
		$data['tickets'] = $tickets;
		return response ( array('data' => $data, 'response' => 1) );
	}
	
	public function home_page_gallery(){
		$gallery = EventsGallery::take(10)->get();
		if(count($gallery)>0){
			return response ( array('data' => $gallery, 'response' => 1) );
		}else{
			return response ( array('data' => 'error', 'response' => 0) );
		}
	}
	
	public function search_events(Request $request){
		$carnival_id = $request->input('carnival_id');
		$country_id  = $request->input('country_id');
		$events      = Event::where('carnival_id',$carnival_id)->where('country_id',$country_id)->get();
		if(count($events)>0){
			return response ( array('data' => $events, 'response' => 1) );
		}else{
			return response ( array('data' => 'No Event Found for your search', 'response' => 0) );
		}
	}
	
	public function upcoming_events_by_filter(Request $request){
		$carnival_id = $request->input('carnival');
		$country_id  = $request->input('country');
	
		if( !empty($carnival_id) && !empty($country_id) ){
			$events = Event::where('carnival_id',$carnival_id)
								->where('country_id',$country_id)
								->where('is_active','=','1')
								->whereDate('event_date', '>=', date('Y-m-d'))
								->orderBy('event_date', 'asc')
								->paginate(6);
		}
		elseif( !empty($carnival_id) && empty($country_id) ){
			$events = Event::where('carnival_id',$carnival_id)
						->where('is_active','=','1')
						->whereDate('event_date', '>=', date('Y-m-d'))
						->orderBy('event_date', 'asc')
						->paginate(6);
		}
		elseif( empty($carnival_id) && !empty($country_id) ){
			$events = Event::where('country_id',$country_id)
						->where('is_active','=','1')
						->whereDate('event_date', '>=', date('Y-m-d'))
						->orderBy('event_date', 'asc')
						->paginate(6);		
		}else{
			$events = Event::where('is_active','=','1')
						->whereDate('event_date', '>=', date('Y-m-d'))
						->orderBy('event_date', 'asc')
						->paginate(6);		
		}
		if(count($events)>0){
			return response ( array('data' => $events, 'response' => 1) );
		}else{
			return response ( array('data' => 'No Event Found for your search', 'response' => 0) );
		}
	}
	
	 
	 /***** promoter guards *******/
	 public function promoter_guard_create(Request $request){
		$email   = $request->input('promoter_email');
		$user    = User::where("email","=",$email)->first();
		$admin_email = Config::get('constants.admin_email');
		if(count($user)>0){
			$data = array();
			$rules = array(
			  'user_name'     => 'required', 
			  
			  'password' => 'required'
			  
			);
			
			$validator = Validator::make($request->all(), $rules);
			if(!$validator->fails()){
				$user_name = $request->input('user_name'); 
				$user_details = Guard::check_user_exits($user_name);
				
				if(empty($user_details)){   
					
					$guard = new Guard();
					$guard->user_name  = $request->input('user_name');
					$guard->password   = Hash::make($request->input('password'));
					$guard->promoter_id  = $user->id;
					$guard->is_active  = '1';
					$guard->save();
					if(!empty($guard->id)){
						Mail::send('admin.emails.registration',
							[],
							function($message) use($email)
							{
							   $message->to($email)->subject('Carnivalist Sign up.');
							}
						);
						$data['message'] = "Gaurd registered";
						
						$guardlist = Guard::where('promoter_id',$user->id)->where('is_active','=','1')->orderBy('created_at', 'desc')->get();
						return response( array('data' =>$guardlist ,'response' => 1));
					}else{
						$data['message'] = "Gaurd registration failed";
						return response( array('data' =>$data ,'response' => 0));
					}
														 
				}else{
				  $data['message'] = "User name already exist";
				  return response( array('data' =>$data ,'response' => 0));
				}
			  
			}else{
			  $data['message'] = "Please fill all required fields";
			  return response( array('data' =>$data ,'response' => 0));
			}
			
		} else {
			return response( array('data' => "Promoter not found",'response' => 0));
		}
		
	}
	
	public function promoter_guards(Request $request){
		$email = $request->input('promoter_email');
		$promoter = User::where("email","=",$email)->first();
		if(count($promoter)>0){
			$guards = Guard::where('promoter_id','=',$promoter->id)->where('is_active','=','1')->orderBy('created_at', 'desc')->get();
			if(count($guards)>0){
				return response( array('data' => $guards,'response' => 1));					
			}else{
				return response( array('data' => "error",'response' => 0));
			}
		}else{
			return response( array('data' => "error",'response' => 0));
		}
		
	}
	
	/*********** Testing functions *************/
	
	
	public function promoter_event_create_test(Request $request){
		$email   = $request->input('user_email');
		$user    = User::where("email","=",$email)->first();
		//$members = $request->input('member_id');
		//$member_ids = explode(',', $members);
		$ticket_type = $request->input('ticket_type');
        $ticket_type_array = json_decode($ticket_type);
		
		
		$tags = $request->input('tags');
        $tags_array = json_decode($tags);
		
		$admin_email = Config::get('constants.admin_email');
		//echo "<pre>"; print_r($member_ids); die; 
		if(count($user)>0){
			$event = new Event();
			
			$slug_input =  str_slug($request->input('event_name'), '-');      
            $slug = $this->createSlug($slug_input);
		 
			$event->user_id              = $user->id;
			$event->event_name           = $request->input('event_name');
			$event->event_title          = $request->input('event_name');
			$event->event_slug           = $slug;
			$event->event_description    = $request->input('event_description');
			$event->event_location       = $request->input('event_location');
			$event->event_date           = $request->input('event_date');			
			//$event->event_end_date           = $request->input('event_end_date');			
			$event->event_type           = 'one time';
			$event->carnival_id          = $request->input('carnival_id');
			$event->carnival_type        = $request->input('carnival_type');
			$event->country_id           = $request->input('country_id');
			$event->total_tickets        = $request->input('total_tickets') ? $request->input('total_tickets') :0;
			$event->basic_ticket_price   = $request->input('basic_ticket_price') ? $request->input('basic_ticket_price') :0;
			$event->ticket_service_tax   = $request->input('ticket_service_tax') ? $request->input('ticket_service_tax') :0;
			$event->final_ticket_price   = $request->input('basic_ticket_price') + $request->input('ticket_service_tax');
			$event->is_active            = '1' ;
			$event->yearly               = ($request->input('is_active') == 'true' ) ? '1' : '0' ;
			$event->is_approved          = ($request->input('is_approved') == 'true' ) ? '1' : '0' ;
			$event->is_refundable        = ($request->input('is_refundable') == 'true' ) ? '1' : '0' ;
			$event->save();
			
			
			if($event->id>0){
				if(count($ticket_type_array)>0){
					foreach($ticket_type_array as $k=>$v){
						if($v):
							$EventTicketType = new EventTicketType();
							$EventTicketType->event_id = $event->id;
							$EventTicketType->ticket_type = $v->name;
							$EventTicketType->total_tickets =$v->seats ? $v->seats:0;
							$EventTicketType->ticket_price = $v->price ? $v->price:0;
							$EventTicketType->ticket_start_date = $v->ticket_start_date ? date('Y-m-d',strtotime($v->ticket_start_date)) : null;
							$EventTicketType->ticket_end_date = $v->ticket_end_date ? date('Y-m-d',strtotime($v->ticket_end_date)) : null ;
							$EventTicketType->save();
						endif;
					}
				}
		
				/* if(isset($members) && count($member_ids) >0){
					foreach( $member_ids as $id ){
						$add_member = new CommitteeMember();
						$add_member->event_id  = $event->id;
						$add_member->member_id = $id;
						$add_member->save();
					}
				}  */
				
				if(isset($tags) && count($tags_array) >0){
					foreach( $tags_array as $member_email ){
						$member_detail = User::where('email',$member_email)->first();
						if($member_detail):
							$add_member = new CommitteeMember();
							$add_member->event_id  = $event->id;
							$add_member->member_id = $member_detail->id;
							$add_member->save();
						endif;
					}
				} 
				Mail::send('emails.promoter_event',
					['data'=>'You have successfully created event '.$event->event_name.'','heading'=>'Thanks for creating ticket.'],
					function($message) use($email)
					{
					   $message->to($email)->subject('Carnivalist Event Created.');
					}
				);
				
				Mail::send('emails.promoter_event',
					['data'=>ucfirst($user->name) .' created event '.$event->event_name.'','heading'=>'New Event Created.'],
					function($message) use($admin_email)
					{
					   $message->to($admin_email)->subject('New event created.');
					}
				);
				return response( array('data' => $event->id,'response' => 1));
			}else{
				return response( array('data' => "error",'response' => 0));
			}
		}else{
			return response( array('data' => "error",'response' => 0));
		}
		
	}
	
	public function promoter_events_test(Request $request){
		$email = $request->input('user_email');
		$user = User::where("email","=",$email)->first();
		if(count($user)>0){
			$event_detail = Event::with('eventTicketTypes')->where('user_id','=',$user->id)->where('is_active','=','1')/* ->whereDate('event_date', '>=', date('Y-m-d')) */->orderBy('event_date', 'asc')->get();
			if(count($event_detail)>0){
				return response( array('data' => $event_detail,'response' => 1));					
			}else{
				return response( array('data' => "no event",'response' => 0));
			}
		}else{
			return response( array('data' => "error",'response' => 0));
		}
		
	}
	
	public function event_detail_test(Request $request){
		$id = $request->input('event_id');
		if($id>0 && !empty($id) ){
			$event_detail = Event::where('id','=',$id)->with('eventTicketTypes','currentEventType','past','comingSoon')->with('event_gallery')->first();
			$reviews      = EventReview::where('event_id','=',$id)->where('is_approved','=',1)->with('user')->with('gallery_image')->get();
			$members      = CommitteeMember::where('event_id','=',$id)->get();
			$members_array = array();
			//$members_string = '[';
			foreach($members as $member){
				$member_email = User::find($member->member_id)->email;
				if($member_email){
					array_push($members_array,$member_email);
					//$members_string = $members_string . "'".$member_email."'" .',';
				}
			}
			/* $members_string = rtrim($members_string,',');
			$members_string = $members_string.']'; */
			$data['event_detail'] = $event_detail;
			$data['reviews']      = $reviews;
			$data['members']      = $members_array;
			return response( array('data' =>$data ,'response' => 1));
		}else{
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
	
	public function promoter_event_edit_test(Request $request){
		$event_id   = $request->input('event_id');
		$event=   Event::find($event_id);
		$email   = $request->input('user_email');
		$user    = User::where("email","=",$email)->first();
		/* $members = $request->input('member_id');
		$member_ids = explode(',', $members); */
		$ticket_type = $request->input('ticket_type');
		$ticket_type_array = json_decode($ticket_type);
		
		$tags = $request->input('tags');
		$tags_array = json_decode($tags);
		$admin_email = Config::get('constants.admin_email');		
		if($event){
			if(count($user)>0){
				
				$slug_input =  str_slug($request->input('event_name'), '-');      
				$slug = $this->createSlug($slug_input);
			 
				$event->user_id              = $user->id;
				$event->event_name           = $request->input('event_name');
				$event->event_title          = $request->input('event_name');
				$event->event_slug           = $slug;
				$event->event_description    = $request->input('event_description');
				$event->event_location       = $request->input('event_location');
				$event->event_date           = $request->input('event_date');			
				//$event->event_end_date  =      $request->input('event_end_date');			
				$event->event_type           = 'one time';
				$event->carnival_id          = $request->input('carnival_id');
				$event->country_id           = $request->input('country_id');
				$event->total_tickets        = $request->input('total_tickets') ? $request->input('total_tickets') :0;
				$event->basic_ticket_price   = $request->input('basic_ticket_price') ?  $request->input('basic_ticket_price') : 0;
				$event->ticket_service_tax   = $request->input('ticket_service_tax') ? $request->input('ticket_service_tax') : 0;
				$event->final_ticket_price   = $request->input('basic_ticket_price') + $request->input('ticket_service_tax');
				$event->is_active            = ($request->input('is_active') == 'true' ) ? '1' : '0' ;
				$event->yearly               = ($request->input('is_active') == 'true' ) ? '1' : '0' ;
				$event->is_approved            = ($request->input('is_approved') == 'true' ) ? '1' : '0' ;
				$event->save();
				if($event->id>0){
					if(count($ticket_type_array)>0){
						foreach($ticket_type_array as $k=>$v){
							if($v):
								if($v->id){
									$EventTicketType = EventTicketType::find($v->id);
									if($EventTicketType){
										$EventTicketType->event_id = $event->id;
										$EventTicketType->ticket_type = $v->name;
										$EventTicketType->total_tickets =$v->seats ? $v->seats:0;
										$EventTicketType->ticket_price = $v->price ? $v->price:0;
										$EventTicketType->ticket_start_date = $v->ticket_start_date ? date('Y-m-d',strtotime($v->ticket_start_date)): null;
										$EventTicketType->ticket_end_date =  $v->ticket_end_date ? date('Y-m-d',strtotime($v->ticket_end_date)) : null;
										$EventTicketType->save();
									}
								} else {
									$EventTicketType = new EventTicketType();
									$EventTicketType->event_id = $event->id;
									$EventTicketType->ticket_type = $v->name;
									$EventTicketType->total_tickets =$v->seats ? $v->seats:0;
									$EventTicketType->ticket_price = $v->price ? $v->price:0;
									$EventTicketType->ticket_start_date = $v->ticket_start_date ? date('Y-m-d',strtotime($v->ticket_start_date)): null;
									$EventTicketType->ticket_end_date =  $v->ticket_end_date ? date('Y-m-d',strtotime($v->ticket_end_date)) : null;
									$EventTicketType->save();
								}
							endif;
						}
					}
					/* if(isset($members) && count($member_ids) >0){
						foreach( $member_ids as $id ){
							$add_member = new CommitteeMember();
							$add_member->event_id  = $event->id;
							$add_member->member_id = $id;
							$add_member->save();
						}
					} */
					 CommitteeMember::where('event_id','=',$event->id)->delete();
					if(isset($tags) && count($tags_array) >0){
					foreach( $tags_array as $member_email ){
						$member_detail = User::where('email',$member_email)->first();
						if($member_detail):
							$add_member = new CommitteeMember();
							$add_member->event_id  = $event->id;
							$add_member->member_id = $member_detail->id;
							$add_member->save();
						endif;
					}
				} 
					Mail::send('emails.promoter_event',
						['data'=>'You have successfully updated event '.$event->event_name.'','heading'=>'Thank You'],
						function($message) use($email)
						{
						   $message->to($email)->subject('Carnivalist Event Updated.');
						}
					);
					
					Mail::send('emails.promoter_event',
						['data'=>ucfirst($user->name) .' updated event '.$event->event_name.'','heading'=>'Event Updated.'],
						function($message) use($admin_email)
						{
						   $message->to($admin_email)->subject('Event Updated.');
						}
					);					
					return response( array('data' => $event->id,'response' => 1));
				}else{
					return response( array('data' => "error",'response' => 0));
				}
			}
		}else{
			return response( array('data' => "error",'response' => 0));
		}
		
	}
	
	public function seatsByTicketType(Request $request) {
		$ticket_type_id = $request->input('id');
		$ticket_type = EventTicketType::find($ticket_type_id);
		
		$tax = Config::get('constants.tax');
		$additional_charges = Config::get('constants.additional_charges');
		
		if($ticket_type){
			$sold_tickets = $this->totalTicketsSold($ticket_type_id);
			
			$seats_available = (int)$ticket_type->total_tickets - (int)$sold_tickets;
			return response( array('data' =>array('ticket_type'=>$ticket_type,'tax'=>(float)$tax,'additional_charges'=>(float)$additional_charges,'seats_available'=>$seats_available),'response' => 1));
		} else {
			return response( array('data' => "error",'response' => 0));
		}
	}
	
	public function totalTicketsSold($ticket_type_id){
		$sold_tickets = 0;
		$ticket_type = EventTicketType::find($ticket_type_id);
		if($ticket_type){
			/* $sold_tickets = Ticket::where('event_id',$ticket_type->event_id)
			->where('ticket_type',$ticket_type_id)
			->where(function($query){
				$query->where('status','0');
				$query->orWhere('status','1');
			})
			->sum('count'); */
			$sold_tickets = $ticket_type->tickets_sold;
			
		}
		return $sold_tickets;
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
	 
	 public function promoter_guard_create_test(Request $request){
		$email   = $request->input('promoter_email');
		$event_id   = $request->input('event_id');
		$user    = User::where("email","=",$email)->first();
		$admin_email = Config::get('constants.admin_email');
		$event = Event::find($event_id);
		if(count($user)>0){
			if($event){
				$data = array();
				$rules = array(
				  'user_name'     => 'required', 
				  'password' => 'required'
				  
				);
				
				$validator = Validator::make($request->all(), $rules);
				if(!$validator->fails()){
					$user_name = $request->input('user_name'); 
					$user_details = Guard::check_user_exits($user_name);
					
					if(empty($user_details)){   
						
						$guard = new Guard();
						$guard->user_name  = $request->input('user_name');
						$guard->password   = Hash::make($request->input('password'));
						$guard->promoter_id  = $user->id;
						$guard->event_id  = $request->input('event_id');
						$guard->is_active  = '1';
						$guard->save();
						if(!empty($guard->id)){
							Mail::send('admin.emails.registration',
								[],
								function($message) use($email)
								{
								   $message->to($email)->subject('Carnivalist Sign up.');
								}
							);
							$data['message'] = "Gaurd registered";
							
							$guardlist = Guard::where('promoter_id',$user->id)->where('is_active','=','1')->orderBy('created_at', 'desc')->get();
							return response( array('data' =>$guardlist ,'response' => 1));
						}else{
							$data['message'] = "Gaurd registration failed";
							return response( array('data' =>$data ,'response' => 0));
						}
															 
					}else{
					  $data['message'] = "User name already exist";
					  return response( array('data' =>$data ,'response' => 0));
					}
				  
				}else{
				  $data['message'] = "Please fill all required fields";
				  return response( array('data' =>$data ,'response' => 0));
				}
			}else {
				return response( array('data' => "Event not found",'response' => 0));
			}
		} else {
			return response( array('data' => "Promoter not found",'response' => 0));
		}
		
	}
	
	public function verify_tag(Request $request){
		$user_email = $request->input('user_email');
		$user = User::where('email','=',$user_email)->first();
		if(count($user)>0){
			return response( array('data' => "user found",'response' => 1));
		}else{
			$data_email = array('email'		=> $request->input('user_email'),
								'subject'	=> "Email address not found");
			$sent 		= CommonFunctions::send_larvel_default_mail('emails.email_address_not_found',$data_email);

			return response( array('data' => "user not found",'response' => 0));
		}
	}
	
	public function event_message(Request $request){
		$event   = $request->input('event');
		$user_id = $request->input('user_id');
		$requester_email = $request->input('email');
		$msg = $request->input('message');
			
		$user = User::findOrFail($user_id);
		if($user){
			$email = $user->email;
			Mail::send('admin.emails.send-message',
				['requester_email' => $requester_email, 'msg'=>$msg, 'event'=> $event ],
				function($message) use($email)
				{
				   $message->to($email)->subject('Event Query');
				}
			);
			return response( array('data' => "message sent",'response' => 1));
		}else{
			return response( array('data' => "user not found",'response' => 0));
		}
	}
}

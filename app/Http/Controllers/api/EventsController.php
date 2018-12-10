<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use App\Models\EventReview;
use App\Models\Event;
use App\Models\Country;
use App\Models\Carnival;
use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketTransfer;
use Input;
use DB;
use File;
use Illuminate\Support\Facades\Hash;
use Cartalyst\Stripe\Laravel\Facades\Stripe;
use App\Models\Invoices;
use App\Mail\PaymentDone;
use App\Mail\TicketRequestCancelled;
use Illuminate\Support\Facades\Mail;
use PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\TicketUsers;
use Config;
use App\Models\EventTicketType;
use App\Models\AllocatePdfs;

class EventsController extends Controller
{

    public function __construct()
    {
        
    }
	
	public function commitee_members_list(Request $req){
		if(isset($req->event_id))
		{
			$event_detail          = Event::find($req->event_id);
			$commitee_members_list = array();
			
			if(count($event_detail)>0){
				$commitee_members_list = $event_detail->commitee_members_list()->orderBy('id')->get();
				return response( array('data' =>$commitee_members_list ,'response' => 1));
			}else{
				return response( array('data' =>'No Member found' ,'response' => 0));
			}
		}
		
	}
	
	public function events_review_list(Request $req){
		if(isset($req->user_email))
		{	
			$user         = User::where('email',$req->user_email)->first();
			$event_review = EventReview::where([['user_id',$user->id],['is_approved',1]])->with('event')->whereHas('event')->get();
			
			if(count($event_review)>0){				
				return response( array('data' =>$event_review ,'response' => 1));
			}else{
				return response( array('data' =>'No Review found' ,'response' => 0));
			}
		}
	}
	
	public function create_ticket_request(Request $req){
		if(isset($req->event_id))
		{	
			$event      = Event::find($req->event_id);
			$user_email = $req->user_email;
			
			if($event){
				$user    = User::where('email',$req->user_email)->first();
				$tickets = $req->number_of_tickets ? $req->number_of_tickets  :0;
				
				if(isset($req->member_id) && $req->member_id != null){
					$member_id = $req->member_id;
				} else {
					$member_id = User::where('type','admin')->first()->id;
				}
				
				$member = User::where('id',$member_id)->first();
				
				if($event->total_tickets >= $tickets)
				{
					$data = array('event_id'=>$req->event_id,'member_id'=>$member_id,'user_id'=>$user->id,'count'=>$tickets,'token_id'=>$req->token_id);
					$ticket                = Ticket::create($data);
					$commitee_members_list = array();
					
					if($ticket){
						$event->total_tickets = $event->total_tickets - $tickets;
						$event->save();
						/* if(isset($req->ticket_users) && count(json_decode($req->ticket_users)) > 0)
						{
						    foreach(json_decode($req->ticket_users) as $tk => $tu) {
								TicketUsers::create(array('ticket_id'=>$ticket->id,'name'=>$tu->name,'age'=>$tu->age,'gender'=>$tu->gender));
							}
						}  */
						Mail::send('emails.ticket_request',
							['ticket'=>$ticket,'heading'=>'Thank You.','data'=>'You request has been sent to member.'],
							function($message) use($user_email)
							{
							   $message->to($user_email)->subject('Carnivalist Ticket Request.');
							}
						);
						Mail::send('emails.ticket_request',
							['ticket'=>$ticket,'heading'=>'New Ticket Request.','data'=>'You have pending ticket request.'],
							function($message) use($member)
							{
							   $message->to($member->email)->subject('Carnivalist Ticket Request.');
							}
						);
						return response( array('data' =>$ticket ,'response' => 1));
					}else{
						return response( array('data' =>'error' ,'response' => 0));
					}
				}else{
					return response( array('data' =>'Tickets sold out' ,'response' => 0));
				}
			}else{
				return response( array('data' =>'Event not found' ,'response' => 0));
			}
			
		}
		
	}
	
	public function count_ticket_request(Request $req){
		if(isset($req->user_email))
		{
			$user = User::where('email',$req->user_email)->first();
			
			if(count($user)>0)
			{   
				$is_comittee_member = Ticket::where('member_id',$user->id)->get();
				
				if(count($is_comittee_member)>0){
					$total_requests = Ticket::with('events')->has('events')->where('member_id',$user->id)->where('status',0)->get();
					return response( array('data' =>$total_requests ,'response' => 1));
				}else{
				    return response( array('data' =>'User is not member' ,'response' => 0));
				}
				
			}else{
				return response( array('data' =>'error' ,'response' => 0));
			}
		}
		else{
			return response( array('data' =>'error' ,'response' => 0));
		}
		
	}
	
	public function user_purchased_events(Request $req){
		if(isset($req->user_email))
		{
			$user = User::where('email',$req->user_email)->first();
			
			if(count($user)>0)
			{
				$present_events_with_tickets = Ticket::with('events')
												->with('invoice')
												->with('transfer','transfer.receiver')
												->has('events')->where('user_id',$user->id)
												->where('status',1)
												->orWhereHas('transfer', function($query) use($user) {
																			$query->where('receiver_id',$user->id);
																})
												->select("tickets.*",DB::raw('DATE_FORMAT(tickets.created_at, "%Y-%m-%d") as new_created_at'),DB::raw('DATE_FORMAT(tickets.updated_at, "%Y-%m-%d") as new_updated_at'))
												->orderBy('created_at','desc')
												->get();
				
				$past_events_with_tickets = Ticket::with('past_events')
													->has('past_events')
													->where('user_id',$user->id)
													->where('status',1)
													->select("tickets.*",DB::raw('DATE_FORMAT(tickets.created_at, "%Y-%m-%d") as new_created_at'),DB::raw('DATE_FORMAT(tickets.updated_at, "%Y-%m-%d") as new_updated_at'))
													->orderBy('created_at','desc')
													->get();
													
				return response( array('data' =>array('present_events_with_tickets'=>$present_events_with_tickets,'past_events_with_tickets'=>$past_events_with_tickets) ,'response' => 1));
				
			}else{
				return response( array('data' =>'error' ,'response' => 0));
			}
		}else{
			return response( array('data' =>'error' ,'response' => 0));
		}
		
	}
	
	public function transferTicket(Request $req){
		if(isset($req->ticket_id))
		{
			$ticket = Ticket::where('id',$req->ticket_id)->first();
			if(count($ticket)>0)
			{
				$user_one = User::where('email',$req->sender)->first();
				
				if(count($user_one)>0)
				{
					$user_two = User::where('email',$req->receiver)->first();
					
					if(count($user_two)>0)
					{
						$transfer_ticket              = new TicketTransfer;
						$transfer_ticket->ticket_id   = $req->ticket_id;
						$transfer_ticket->sender_id   = $user_one->id;
						$transfer_ticket->receiver_id = $user_two->id;
						$transfer_ticket->save();
						
						$basic_amount = $ticket->type->ticket_price ? (float)$ticket->type->ticket_price : 0;
						$amount = $this->calculateDetailAmount(($basic_amount)*(float)($ticket->count));
						$currency       = "USD";
						$sale_tax = $this->saleTax(($basic_amount)*(float)($ticket->count));
						
						
						Mail::send('emails.ticket_transfered',
							['ticket'=>$ticket,'heading'=>'Thank You.','data'=>'Your ticket has been transfered.','basic_amount'=>$basic_amount,'amount'=>$amount,'sale_tax'=>$sale_tax],
							function($message) use($user_one)
							{
							   $message->to($user_one->email)->subject('Carnivalist Ticket Transfered.');
							}
						);
						
						Mail::send('emails.ticket_received',
							['ticket'=>$ticket,'heading'=>'New Ticket Received.','data'=>'You have received ticket.','basic_amount'=>$basic_amount,'amount'=>$amount,'sale_tax'=>$sale_tax],
							function($message) use($user_two)
							{
							   $message->to($user_two->email)->subject('Carnivalist Ticket Received.');
							}
						);
						
						return response( array('data' =>$transfer_ticket ,'response' => 1));
						
					}else{
						return response( array('data' =>'Receiver Account not found in application.' ,'response' => 0));
					}
				}else{
					return response( array('data' =>'Sender not found' ,'response' => 0));
				}
			}else{
				return response( array('data' =>'Ticket not found' ,'response' => 0));
			}
		}else{
			return response( array('data' =>'error' ,'response' => 0));
		}
		
	}
	
	public function change_status_of_ticket(Request $req){
		if(isset($req->ticket_id))
		{
			$ticket = Ticket::find($req->ticket_id);
			$event  = $ticket->event;
			
			if($ticket)
			{
				if($req->status==1 && $req->status!=null)
				{
					if(isset($ticket->token_id) && $ticket->token_id!=null)
					{
						if($event)
						{
							$stripe = Stripe::make(env('STRIPE_SECRET'));
							try {
								$charge       =  $stripe->charges()->create([
								'card'        => $ticket->token_id,
								'currency'    => 'USD',
								'amount'      => $this->calculateAmount((float)($event->final_ticket_price)*(float)($ticket->count)),
								'description' => 'Deduct price for ticket',
								]);
								if($charge['status'] == 'succeeded') { 
								
									$ticket->status           = $req->status;
									$ticket->payment_response = json_encode($charge);
									$ticket->save();
									
									$payment_response   = json_decode($ticket->payment_response);
									$charged_amount     = (float)($event->final_ticket_price)*(float)($ticket->count);
									$charged_amount     = number_format((float)$charged_amount, 2, '.', '');
									$invoice            = new Invoices;
									$invoice->ticket_id = $req->ticket_id;
									$invoice->event_id  = $ticket->event_id;
									$invoice->user_id   = $ticket->user_id;
									$invoice->save();
									
									$destinationPath = public_path('uploads/invoices/'.$invoice->id); 
									if(!File::exists($destinationPath)) {
										File::makeDirectory($destinationPath, $mode = 0777, true, true);
									}
									
									$pdf = PDF::loadView('pdf.invoice',compact(['invoice','payment_response','charged_amount']));

									$pdf->save($destinationPath.'/invoice.pdf');
									QrCode::size(250)->generate($req->ticket_id, $destinationPath.'/qrcode.svg');
									
									$invoice->qr_code  = 'qrcode.svg';
									$invoice->pdf_file = 'invoice.pdf';
									$invoice->save();
									
									//Mail::to($ticket->requsted_user->email)->send(new PaymentDone($invoice));
									$this->PaymentMail($ticket->requsted_user->email,$invoice);
									return response( array('data' =>$charge ,'response' =>1));
									
								}else{
									return response( array('data' =>'payment error' ,'response' =>0));
								}  
							}
							catch (Exception $e) {
								$event->total_tickets = $event->total_tickets+ $ticket->count;
								$event->save();
								return response( array('data' =>$e->getMessage() ,'response' =>0));
								
							} catch(\Cartalyst\Stripe\Exception\CardErrorException $e) {
								$event->total_tickets = $event->total_tickets+ $ticket->count;
								$event->save();
								return response( array('data' =>$e->getMessage() ,'response' =>0));
								
							} catch(\Cartalyst\Stripe\Exception\MissingParameterException $e) {
								$event->total_tickets = $event->total_tickets+ $ticket->count;
								$event->save();
								return response( array('data' =>$e->getMessage() ,'response' =>0));
								
							} 
							
						}else{
							return response( array('data' =>'Event does not exist' ,'response' => 0));
						}
					}else{
						return response( array('data' =>'Token not exist' ,'response' => 0));
					}
				}else{
					$ticket->status = $req->status;
					$ticket->save();
					
					$event->total_tickets = $event->total_tickets+ $ticket->count;
					$event->save();
					
					$this->ticketCancellationMail($ticket->requsted_user->email);
					//Mail::to($ticket->requsted_user->email)->send(new TicketRequestCancelled());
					return response( array('data' =>$ticket ,'response' =>1));
					
				}
			}else{
				return response( array('data' =>'error' ,'response' => 0));
			}
		}else{
			return response( array('data' =>'error' ,'response' => 0));
		}
	
	}
	
	public function addclick(Request $req){
		if(isset($req->event_id))
		{
			$event = Event::find($req->event_id);
			
			if($event)
			{
				$number_of_click         = $event->number_of_clicks;
				$event->number_of_clicks = $number_of_click+1;
				$event->save();
				return response( array('data' =>$event,'response' => 1));
			}else{
				return response( array('data' =>'error' ,'response' => 0));
			}
		}else{
			return response( array('data' =>'error' ,'response' => 0));
		}
		
	}
	
	/* 
	 * Angular website api
	 */
	public function event_list_website(Request $request){
		$id = $request->input('carnival_id');
		
		if($id>0 && !empty($id) ){
			$carnival = Carnival::findOrFail($id);
			$event_detail = Event::where('carnival_id','=',$id)
								->where('is_active','=','1')
								->whereDate('event_date', '>=', date('Y-m-d'))
								->orderBy('event_date', 'asc')
								->paginate(6);
			$data = $event_detail;
			return response( array('data' =>$data ,'response' => 1));
		}else{
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
   
    /**
	Count country based user count according to event
	**/
	public function countryBasedUserCount(Request $request){
		$event_id  =  $request->event_id;
		$event     = Event::find($event_id);
		$data      = array();
		$count     = 0;
		
		if($event) {
		    //$countries = Country::all();
		   $countries = Country::join('users','users.country_id','=','countries.id')
									->join('tickets','users.id','=','tickets.user_id')
									->where('tickets.status',1)
									->where('tickets.event_id',$event_id)
									->select('countries.*')
									->distinct()
									->get();
									
			if(count($countries)>0) {
				foreach ( $countries as $k => $v)
				{
					$data['country'][] = $v; 
					$count             = User::leftJoin('tickets','users.id','=','tickets.user_id')
												->where('users.country_id',$v->id)
												->where('tickets.status',1)
												->where('tickets.event_id',$event_id)
												->sum('tickets.count');
					$data['userCount'][] =(int)$count; 
				}
				return response( array('data' =>$data ,'response' => 1));
			} else {
				return response( array('data' =>'error' ,'response' => 0));
			}
			
		} else {
		    return response( array('data' =>'error' ,'response' => 0));
		}
	}
	
	/**
	Gender country based user count according to event
	**/
	public function genderBasedUserCount(Request $request){
		$event_id  =  $request->event_id;
		$event     = Event::find($event_id);
		$data      = array();
		$count     = 0;
		if ($event) {
			
		    $data['female'] = $event->ticketUsers()->count() > 0  ? ($event->ticketUsers()->where('gender',2)->count()/$event->ticketUsers()->count())*100 : 0; 
			$data['female'] = number_format((float)$data['female'], 2, '.', '');
		    $data['male']   = $event->ticketUsers()->count() ? ($event->ticketUsers()->where('gender',1)->count()/$event->ticketUsers()->count())*100 : 0; 
			$data['male']   = number_format((float)$data['male'], 2, '.', '');
		    
			return response( array('data' =>$data ,'response' => 1));
			
		} else {
		    return response( array('data' =>'error' ,'response' => 0));
		}
	}
	
	/**
	Age country based user count according to event
	**/
	public function ageBasedUserCount(Request $request){
		$event_id  =  $request->event_id;
		$event     = Event::find($event_id);
		$data      = array();
		$count     = 0;
		if ($event) {
		    $data['first'] = $event->ticketUsers()->where('age','>=','18')->where('age','<=','25')->count(); 
		    $data['two']   = $event->ticketUsers()->where('age','>=','26')->where('age','<=','30')->count(); 
		    $data['three'] = $event->ticketUsers()->where('age','>=','31')->where('age','<=','35')->count(); 
		    $data['four']  = $event->ticketUsers()->where('age','>=','36')->where('age','<=','40')->count(); 
		    $data['five']  = $event->ticketUsers()->where('age','>=','41')->where('age','<=','45')->count(); 
		    $data['six']   = $event->ticketUsers()->where('age','>=','46')->where('age','<=','50')->count(); 
		    $data['seven'] = $event->ticketUsers()->where('age','>=','51')->where('age','<=','55')->count(); 
		    
			return response( array('data' =>$data ,'response' => 1));
			
		} else {
		    return response( array('data' =>'error' ,'response' => 0));
		}
	}
	
	public function ticketDetail(Request $req){
		$ticket =  Ticket::with('event')->has('event')->where('id',$req->ticket_id)->first();
		if(count($ticket) > 0 ){
			return response( array('data' =>$ticket ,'response' => 1));
		} else {
			return response( array('data' =>'error' ,'response' => 0));
		}
		
	}
	
	
	/***** test functions ******/
	
	public function create_ticket_request_test(Request $req){
		if(isset($req->event_id))
		{	
			$admin_email       = Config::get('constants.admin_email');
			$event             = Event::find($req->event_id);
			$event_ticket_type = EventTicketType::find($req->ticket_type);
			$user_email        = $req->user_email;
			if($event){
				if($event_ticket_type){
					/* if($event_ticket_type->ticket_start_date <= date('Y-m-d') && $event_ticket_type->ticket_end_date >= date('Y-m-d')){ */
						
					
						$user    = User::where('email',$req->user_email)->first();
						$tickets = $req->number_of_tickets ? $req->number_of_tickets  :0;
						
						if(isset($req->member_id) && $req->member_id!=null){
							$member_id = $req->member_id;
						}else{
							$member_id = User::where('type','admin')->first()->id;
						}
						
						$member          = User::where('id',$member_id)->first();
						$sold_tickets    = $this->totalTicketsSold($req->ticket_type);
						$seats_available = (int)$event_ticket_type->total_tickets - (int)$sold_tickets;
						
						if($seats_available >= $tickets)
						{
							$data = array('event_id'=>$req->event_id,'member_id'=>$member_id,'user_id'=>$user->id,'count'=>$tickets,'token_id'=>$req->token_id,'ticket_type'=>$event_ticket_type->id);
							
							$ticket                = Ticket::create($data);
							$commitee_members_list = array();
							
							if($ticket){
								/* $event->total_tickets = $event->total_tickets - $tickets; */
								$event_ticket_type->tickets_sold = $event_ticket_type->tickets_sold  + $tickets;
								$event_ticket_type->save();
								
								$basic_amount = $ticket->type->ticket_price ? (float)$ticket->type->ticket_price : 0;
								$amount = $this->calculateDetailAmount(($basic_amount)*(float)($ticket->count));
								$currency       = "USD";
								$sale_tax = $this->saleTax(($basic_amount)*(float)($ticket->count));
								/* if(isset($req->ticket_users) && count(json_decode($req->ticket_users)) > 0)
								{
									foreach(json_decode($req->ticket_users) as $tk => $tu) {
										TicketUsers::create(array('ticket_id'=>$ticket->id,'name'=>$tu->name,'age'=>$tu->age,'gender'=>$tu->gender));
									}
								}  */
								Mail::send('emails.ticket_request',
									['ticket'=>$ticket,'heading'=>'New Ticket Request.','data'=>'You have pending ticket request.','basic_amount'=>$basic_amount,'amount'=>$amount,'sale_tax'=>$sale_tax],
									function($message) use($user_email)
									{
									   $message->to($user_email)->subject('Carnivalist Ticket Request.');
									}
								);
								Mail::send('emails.ticket_request_to_admin',
									['ticket'=>$ticket,'heading'=>'New Ticket Request.','data'=>'You have pending ticket request.','basic_amount'=>$basic_amount,'amount'=>$amount,'sale_tax'=>$sale_tax],
									function($message) use($member)
									{
									   $message->to($member->email)->subject('Carnivalist Ticket Request.');
									}
								);
								Mail::send('emails.ticket_request_to_admin',
									['ticket'=>$ticket,'heading'=>'New Ticket Request.','data'=>'You have pending ticket request.','basic_amount'=>$basic_amount,'amount'=>$amount,'sale_tax'=>$sale_tax],
									function($message) use($admin_email)
									{
									   $message->to($admin_email)->subject('Carnivalist Ticket Request.');
									}
								);
								Mail::send('emails.ticket_request_to_admin',
									['ticket'=>$ticket,'heading'=>'New Ticket Request.','data'=>'You have pending ticket request.','basic_amount'=>$basic_amount,'amount'=>$amount,'sale_tax'=>$sale_tax],
									function($message) use($event)
									{
									   $message->to($event->user->email)->subject('Carnivalist Ticket Request.');
									}
								);
								return response( array('data' =>$ticket ,'response' => 1));
							}else{
								return response( array('data' =>'error' ,'response' => 0));
							}
						}else{
							return response( array('data' =>'Tickets sold out' ,'response' => 0));
						}
					
					/* }else{
						if($event_ticket_type->ticket_start_date > date('Y-m-d')){
							return response( array('data' =>'Coming soon' ,'response' => 0));
						} else if($event_ticket_type->ticket_end_date < date('Y-m-d')){
							return response( array('data' =>'Booking closed' ,'response' => 0));
						} else{
							return response( array('data' =>'Error' ,'response' => 0));
						}
						
					} */
				}
				else{
					return response( array('data' =>'Ticket Type not found' ,'response' => 0));
				}
			}else{
				return response( array('data' =>'Event not found' ,'response' => 0));
			}
			
		}
		
	}
	
	public function create_ticket_request_test_new(Request $req){
		if(isset($req->event_id))
		{	
			$admin_email       = Config::get('constants.admin_email');
			$event             = Event::find($req->event_id);
			$event_ticket_type = EventTicketType::find($req->ticket_type);
			$user_email        = $req->user_email;
			if($event){
				if($event_ticket_type){
					/* if($event_ticket_type->ticket_start_date <= date('Y-m-d') && $event_ticket_type->ticket_end_date >= date('Y-m-d')){ */
						
					
						$user    = User::where('email',$req->user_email)->first();
						$tickets = $req->number_of_tickets ? $req->number_of_tickets  :0;
						
						if(isset($req->member_id) && $req->member_id!=null){
							$member_id = $req->member_id;
						}else{
							$member_id = User::where('type','admin')->first()->id;
						}
						
						$member          = User::where('id',$member_id)->first();
						$sold_tickets    = $this->totalTicketsSold($req->ticket_type);
						$seats_available = (int)$event_ticket_type->total_tickets - (int)$sold_tickets;
						
						if($seats_available >= $tickets)
						{
							/* if(){
								
							}
							$data = array('event_id'=>$req->event_id,'member_id'=>$member_id,'user_id'=>$user->id,'count'=>$tickets,'token_id'=>$req->token_id,'ticket_type'=>$event_ticket_type->id);
							
							$ticket                = Ticket::create($data);
							 */$commitee_members_list = array();
							
							$ticket = $this->createCharge($req,$member_id,$user,$event_ticket_type,$tickets);
							if($ticket){
								/* $event->total_tickets = $event->total_tickets - $tickets; */
								$event_ticket_type->tickets_sold = $event_ticket_type->tickets_sold  + $tickets;
								$event_ticket_type->save();
								
								$basic_amount = $ticket->type->ticket_price ? (float)$ticket->type->ticket_price : 0;
								$amount = $this->calculateDetailAmount(($basic_amount)*(float)($ticket->count));
								$currency       = "USD";
								$sale_tax = $this->saleTax(($basic_amount)*(float)($ticket->count));
								/* if(isset($req->ticket_users) && count(json_decode($req->ticket_users)) > 0)
								{
									foreach(json_decode($req->ticket_users) as $tk => $tu) {
										TicketUsers::create(array('ticket_id'=>$ticket->id,'name'=>$tu->name,'age'=>$tu->age,'gender'=>$tu->gender));
									}
								}  */
								Mail::send('emails.ticket_request',
									['ticket'=>$ticket,'heading'=>'New Ticket Request.','data'=>'You have pending ticket request.','basic_amount'=>$basic_amount,'amount'=>$amount,'sale_tax'=>$sale_tax],
									function($message) use($user_email)
									{
									   $message->to($user_email)->subject('Carnivalist Ticket Request.');
									}
								);
								Mail::send('emails.ticket_request_to_admin',
									['ticket'=>$ticket,'heading'=>'New Ticket Request.','data'=>'You have pending ticket request.','basic_amount'=>$basic_amount,'amount'=>$amount,'sale_tax'=>$sale_tax],
									function($message) use($member)
									{
									   $message->to($member->email)->subject('Carnivalist Ticket Request.');
									}
								);
								Mail::send('emails.ticket_request_to_admin',
									['ticket'=>$ticket,'heading'=>'New Ticket Request.','data'=>'You have pending ticket request.','basic_amount'=>$basic_amount,'amount'=>$amount,'sale_tax'=>$sale_tax],
									function($message) use($admin_email)
									{
									   $message->to($admin_email)->subject('Carnivalist Ticket Request.');
									}
								);
								Mail::send('emails.ticket_request_to_admin',
									['ticket'=>$ticket,'heading'=>'New Ticket Request.','data'=>'You have pending ticket request.','basic_amount'=>$basic_amount,'amount'=>$amount,'sale_tax'=>$sale_tax],
									function($message) use($event)
									{
									   $message->to($event->user->email)->subject('Carnivalist Ticket Request.');
									}
								);
								return response( array('data' =>$ticket ,'response' => 1));
							}else{
								return response( array('data' =>'error' ,'response' => 0));
							}
						}else{
							return response( array('data' =>'Tickets sold out' ,'response' => 0));
						}
					
					/* }else{
						if($event_ticket_type->ticket_start_date > date('Y-m-d')){
							return response( array('data' =>'Coming soon' ,'response' => 0));
						} else if($event_ticket_type->ticket_end_date < date('Y-m-d')){
							return response( array('data' =>'Booking closed' ,'response' => 0));
						} else{
							return response( array('data' =>'Error' ,'response' => 0));
						}
						
					} */
				}
				else{
					return response( array('data' =>'Ticket Type not found' ,'response' => 0));
				}
			}else{
				return response( array('data' =>'Event not found' ,'response' => 0));
			}
			
		}
		
	}
	
	public function calculateDetailAmount($basic_amount){
		$tax                = Config::get('constants.tax');
		$additional_charges = Config::get('constants.additional_charges');
		$final_price        = (float)$basic_amount + (((float)$basic_amount*(float)$tax)/100) + (float)$additional_charges; 
		return $final_price;
	}
	
	public function saleTax($basic_amount){
		$tax                = Config::get('constants.tax');
		$additional_charges = Config::get('constants.additional_charges');
		$final_price        = (((float)$basic_amount*(float)$tax)/100)+(float)$additional_charges; 
		
		return ($final_price);
	}
	
	public function change_status_of_ticket_test(Request $req){
		if(isset($req->ticket_id))
		{
		$admin_email = Config::get('constants.admin_email');
		$ticket = Ticket::find($req->ticket_id);


		\Stripe\Stripe::setApiKey ( env('STRIPE_SECRET') );
		\Stripe\Stripe::setClientId( env('STRIPE_CLIENT_ID'));
		if($ticket)
		{
		$event_ticket_type = EventTicketType::find($ticket->ticket_type);
		$event = $ticket->event;
		if($event_ticket_type)
		{
			$pdfs = $event_ticket_type->pdfs->where('allocated',0);
			
			
			$basic_amount = (float)$event_ticket_type->ticket_price;
			if($req->status==1 && $req->status!=null)
			{
				if(isset($ticket->token_id) && $ticket->token_id!=null)
				{
					if($event)
					{
						if($event->user->type=='admin'){
							try {
								/* $charge = $stripe->charges()->create([
								'card' => $ticket->token_id,
								'currency' => 'USD',
								'amount'   => $this->calculateAmount(($basic_amount)*(float)($ticket->count)),
								'description' => 'Deduct price for ticket',
								]); */
								
								$charge = \Stripe\Charge::create(array(
								  "amount" => $this->calculateAmount(($basic_amount)*(float)($ticket->count)),
								  "currency" => "USD",
								  "source" => $ticket->token_id,
								  
								  "description" => "Deduct price for ticket",
								));
								
								if($charge['status'] == 'succeeded') { 
									$ticket->status=$req->status;
									$ticket->payment_response=json_encode($charge);
									$ticket->save();
									$payment_response = json_decode($ticket->payment_response);
									/* $charged_amount = (float)($event->final_ticket_price)*(float)($ticket->count); */
									$charged_amount = $this->calculateAmount(($basic_amount)*(float)($ticket->count));
									$charged_amount = number_format((float)$charged_amount, 2, '.', '');
									$invoice = new Invoices;
									$invoice->ticket_id = $req->ticket_id;
									$invoice->event_id = $ticket->event_id;
									$invoice->user_id = $ticket->user_id;
									$invoice->save();
									$final_charged_amount = 0.00;
									
									$transaction_date     =  $ticket->updated_at;
									$tax                  = Config::get('constants.tax');
									$sales_tax_amount     = ((float)$basic_amount*(float)$tax)/100;
									if($charged_amount>0){
										$final_charged_amount = (float)$charged_amount/100;
									}
									$destinationPath = public_path('uploads/invoices/'.$invoice->id); 
									if(!File::exists($destinationPath)) {
										File::makeDirectory($destinationPath, $mode = 0777, true, true);
									}
									$pdf = PDF::loadView('pdf.invoice',compact(['invoice','payment_response','final_charged_amount','transaction_date','sales_tax_amount','basic_amount']));
									
									$pdf->save($destinationPath.'/invoice.pdf');
									QrCode::size(250)->generate($req->ticket_id, $destinationPath.'/qrcode.svg');
									$invoice->qr_code = 'qrcode.svg';
									$invoice->pdf_file = 'invoice.pdf';
									$invoice->save();
									if($pdfs){
										$counted = 1;
										foreach($pdfs as $pdf){
											if($counted <= $ticket->count):
												$AllocatePdfs = new AllocatePdfs();
												$AllocatePdfs->pdf_id = $pdf->id;
												$AllocatePdfs->ticket_type_id = $pdf->ticket_type_id;
												$AllocatePdfs->user_id = $ticket->user_id;
												$AllocatePdfs->ticket_id = $ticket->id;
												$AllocatePdfs->save();
												
												$pdf->allocated = 1;
												$pdf->save();
												$counted++;
											endif;
										}
									}
									$this->PaymentMail($ticket->requsted_user->email,$invoice);
									$this->PaymentMail($admin_email,$invoice);
									$this->PaymentMail($ticket->member->email,$invoice);
									$this->PaymentMail($event->user->email,$invoice);
									//Mail::to($ticket->requsted_user->email)->send(new PaymentDone($invoice));
									//Mail::to($admin_email)->send(new PaymentDone($invoice));
									//Mail::to($ticket->member->email)->send(new PaymentDone($invoice));
									//Mail::to($event->user->email)->send(new PaymentDone($invoice));

									return response( array('data' =>$charge ,'response' =>1));
								} else {
									return response( array('data' =>'Payment error' ,'response' =>0));
								}  
							}
							catch (Exception $e) {
								/* $event->total_tickets = $event->total_tickets+ $ticket->count;
								$event->save(); */
								$event_ticket_type->tickets_sold = $event_ticket_type->tickets_sold  - $ticket->count;
								$event_ticket_type->save();

								return response( array('data' =>$e->getMessage() ,'response' =>0));
							}
						} else if($event->user->type=='promoter')
						{
							if($event->user->account){
								if($event->user->account->stripe_account_id){
									//$stripe = Stripe::make(env('STRIPE_SECRET'));
									
									try {
										$charge = \Stripe\Charge::create(array(
										  "amount" => $this->calculateAmount(($basic_amount)*(float)($ticket->count)),
										  "currency" => "USD",
										  "source" => $ticket->token_id,
										  "application_fee" => $this->calculateFees(($basic_amount)*(float)($ticket->count),$ticket->count),
										  "description" => "Deduct price for ticket",
										), array("stripe_account" => $event->user->account->stripe_account_id));
										
										if($charge['status'] == 'succeeded') { 
											$ticket->status=$req->status;
											$ticket->payment_response=json_encode($charge);
											$ticket->save();
											$payment_response = json_decode($ticket->payment_response);
											/* $charged_amount = (float)($event->final_ticket_price)*(float)($ticket->count); */
											$charged_amount = $this->calculateAmount(($basic_amount)*(float)($ticket->count));
											$charged_amount = number_format((float)$charged_amount, 2, '.', '');
											$invoice = new Invoices;
											$invoice->ticket_id = $req->ticket_id;
											$invoice->event_id = $ticket->event_id;
											$invoice->user_id = $ticket->user_id;
											$invoice->save();
											
											$final_charged_amount = 0.00;
											$transaction_date     =  $ticket->updated_at;
											$tax                  = Config::get('constants.tax');
											$sales_tax_amount     = ((float)$basic_amount*(float)$tax)/100;
											if($charged_amount>0){
												$final_charged_amount = (float)$charged_amount/100;
											}
											$destinationPath = public_path('uploads/invoices/'.$invoice->id); 
											if(!File::exists($destinationPath)) {
												File::makeDirectory($destinationPath, $mode = 0777, true, true);
											}
											$pdf = PDF::loadView('pdf.invoice',compact(['invoice','payment_response','final_charged_amount','transaction_date','sales_tax_amount','basic_amount']));

											$pdf->save($destinationPath.'/invoice.pdf');
											QrCode::size(250)->generate($req->ticket_id, $destinationPath.'/qrcode.svg');
											$invoice->qr_code = 'qrcode.svg';
											$invoice->pdf_file = 'invoice.pdf';
											$invoice->save();
											
											if($pdfs){
												$counted = 1;
												foreach($pdfs as $pdf){
													if($counted <= $ticket->count):
														$AllocatePdfs = new AllocatePdfs();
														$AllocatePdfs->pdf_id = $pdf->id;
														$AllocatePdfs->ticket_type_id = $pdf->ticket_type_id;
														$AllocatePdfs->user_id = $ticket->user_id;
														$AllocatePdfs->ticket_id = $ticket->id;
														$AllocatePdfs->save();
														
														$pdf->allocated = 1;
														$pdf->save();
														$counted++;
													endif;
												}
											}
											$this->PaymentMail($ticket->requsted_user->email,$invoice);
											$this->PaymentMail($admin_email,$invoice);
											$this->PaymentMail($ticket->member->email,$invoice);
											$this->PaymentMail($event->user->email,$invoice);
											
											//Mail::to($ticket->requsted_user->email)->send(new PaymentDone($invoice));
											//Mail::to($admin_email)->send(new PaymentDone($invoice));
											//Mail::to($ticket->member->email)->send(new PaymentDone($invoice));
											//Mail::to($event->user->email)->send(new PaymentDone($invoice));
											
											return response( array('data' =>$charge ,'response' =>1));
										} else {
											return response( array('data' =>'Payment error' ,'response' =>0));
										}  
									}
									catch (Exception $e) {
										/* $event->total_tickets = $event->total_tickets+ $ticket->count;
										$event->save(); */
										$event_ticket_type->tickets_sold = $event_ticket_type->tickets_sold  - $ticket->count;
										$event_ticket_type->save();

										return response( array('data' =>$e->getMessage() ,'response' =>0));
									}
								
								} else{
									try {
										
										$charge = \Stripe\Charge::create(array(
										  "amount" => $this->calculateAmount(($basic_amount)*(float)($ticket->count)),
										  "currency" => "USD",
										  "source" => $ticket->token_id,
										  
										  "description" => "Deduct price for ticket",
										));
										
										if($charge['status'] == 'succeeded') { 
											$ticket->status=$req->status;
											$ticket->payment_response=json_encode($charge);
											$ticket->save();
											$payment_response = json_decode($ticket->payment_response);
											/* $charged_amount = (float)($event->final_ticket_price)*(float)($ticket->count); */
											$charged_amount = $this->calculateAmount(($basic_amount)*(float)($ticket->count));
											$charged_amount = number_format((float)$charged_amount, 2, '.', '');
											$invoice = new Invoices;
											$invoice->ticket_id = $req->ticket_id;
											$invoice->event_id = $ticket->event_id;
											$invoice->user_id = $ticket->user_id;
											$invoice->save();
											
											$final_charged_amount = 0.00;
											$transaction_date     =  $ticket->updated_at;
											$tax                  = Config::get('constants.tax');
											$sales_tax_amount     = ((float)$basic_amount*(float)$tax)/100;
											if($charged_amount>0){
												$final_charged_amount = (float)$charged_amount/100;
											}
											$destinationPath = public_path('uploads/invoices/'.$invoice->id); 
											if(!File::exists($destinationPath)) {
												File::makeDirectory($destinationPath, $mode = 0777, true, true);
											}
											$pdf = PDF::loadView('pdf.invoice',compact(['invoice','payment_response','final_charged_amount','transaction_date','sales_tax_amount','basic_amount']));

											$pdf->save($destinationPath.'/invoice.pdf');
											QrCode::size(250)->generate($req->ticket_id, $destinationPath.'/qrcode.svg');
											$invoice->qr_code = 'qrcode.svg';
											$invoice->pdf_file = 'invoice.pdf';
											$invoice->save();
											
											if($pdfs){
												$counted = 1;
												foreach($pdfs as $pdf){
													if($counted <= $ticket->count):
														$AllocatePdfs = new AllocatePdfs();
														$AllocatePdfs->pdf_id = $pdf->id;
														$AllocatePdfs->ticket_type_id = $pdf->ticket_type_id;
														$AllocatePdfs->user_id = $ticket->user_id;
														$AllocatePdfs->ticket_id = $ticket->id;
														$AllocatePdfs->save();
														
														$pdf->allocated = 1;
														$pdf->save();
														$counted++;
													endif;
												}
											}
											
											$this->PaymentMail($ticket->requsted_user->email,$invoice);
											$this->PaymentMail($admin_email,$invoice);
											$this->PaymentMail($ticket->member->email,$invoice);
											$this->PaymentMail($event->user->email,$invoice);
											
											//Mail::to($ticket->requsted_user->email)->send(new PaymentDone($invoice));
											//Mail::to($admin_email)->send(new PaymentDone($invoice));
											//Mail::to($ticket->member->email)->send(new PaymentDone($invoice));
											//Mail::to($event->user->email)->send(new PaymentDone($invoice));
											
											return response( array('data' =>$charge ,'response' =>1));
										} else {
											return response( array('data' =>'Payment error' ,'response' =>0));
										}  
									}
									catch (Exception $e) {
										/* $event->total_tickets = $event->total_tickets+ $ticket->count;
										$event->save(); */
										$event_ticket_type->tickets_sold = $event_ticket_type->tickets_sold  - $ticket->count;
										$event_ticket_type->save();

										return response( array('data' =>$e->getMessage() ,'response' =>0));
									}
								}
							}else{
									try {
										$charge = \Stripe\Charge::create(array(
										  "amount" => $this->calculateAmount(($basic_amount)*(float)($ticket->count)),
										  "currency" => "USD",
										  "source" => $ticket->token_id,
										  
										  "description" => "Deduct price for ticket",
										));
										
										if($charge['status'] == 'succeeded') { 
											$ticket->status=$req->status;
											$ticket->payment_response=json_encode($charge);
											$ticket->save();
											$payment_response = json_decode($ticket->payment_response);
											/* $charged_amount = (float)($event->final_ticket_price)*(float)($ticket->count); */
											$charged_amount = $this->calculateAmount(($basic_amount)*(float)($ticket->count));
											$charged_amount = number_format((float)$charged_amount, 2, '.', '');
											$invoice = new Invoices;
											$invoice->ticket_id = $req->ticket_id;
											$invoice->event_id = $ticket->event_id;
											$invoice->user_id = $ticket->user_id;
											$invoice->save();
											
											$final_charged_amount = 0.00;
											$transaction_date     =  $ticket->updated_at;
											$tax                  = Config::get('constants.tax');
											$sales_tax_amount     = ((float)$basic_amount*(float)$tax)/100;
											if($charged_amount>0){
												$final_charged_amount = (float)$charged_amount/100;
											}
											$destinationPath = public_path('uploads/invoices/'.$invoice->id); 
											if(!File::exists($destinationPath)) {
												File::makeDirectory($destinationPath, $mode = 0777, true, true);
											}
											$pdf = PDF::loadView('pdf.invoice',compact(['invoice','payment_response','final_charged_amount','transaction_date','sales_tax_amount','basic_amount']));

											$pdf->save($destinationPath.'/invoice.pdf');
											QrCode::size(250)->generate($req->ticket_id, $destinationPath.'/qrcode.svg');
											$invoice->qr_code = 'qrcode.svg';
											$invoice->pdf_file = 'invoice.pdf';
											$invoice->save();
											
											if($pdfs){
												$counted = 1;
												foreach($pdfs as $pdf){
													if($counted <= $ticket->count):
														$AllocatePdfs = new AllocatePdfs();
														$AllocatePdfs->pdf_id = $pdf->id;
														$AllocatePdfs->ticket_type_id = $pdf->ticket_type_id;
														$AllocatePdfs->user_id = $ticket->user_id;
														$AllocatePdfs->ticket_id = $ticket->id;
														$AllocatePdfs->save();
														
														$pdf->allocated = 1;
														$pdf->save();
														$counted++;
													endif;
												}
											}
											$this->PaymentMail($ticket->requsted_user->email,$invoice);
											$this->PaymentMail($admin_email,$invoice);
											$this->PaymentMail($ticket->member->email,$invoice);
											$this->PaymentMail($event->user->email,$invoice);
											
											//Mail::to($ticket->requsted_user->email)->send(new PaymentDone($invoice));
											//Mail::to($admin_email)->send(new PaymentDone($invoice));
											//Mail::to($ticket->member->email)->send(new PaymentDone($invoice));
											//Mail::to($event->user->email)->send(new PaymentDone($invoice));
											
											return response( array('data' =>$charge ,'response' =>1));
										} else {
											return response( array('data' =>'Payment error' ,'response' =>0));
										}  
									}
									catch (Exception $e) {
										/* $event->total_tickets = $event->total_tickets+ $ticket->count;
										$event->save(); */
										$event_ticket_type->tickets_sold = $event_ticket_type->tickets_sold  - $ticket->count;
										$event_ticket_type->save();

										return response( array('data' =>$e->getMessage() ,'response' =>0));
									}
							}
						} else{
							try {
								$charge = \Stripe\Charge::create(array(
								  "amount" => $this->calculateAmount(($basic_amount)*(float)($ticket->count)),
								  "currency" => "USD",
								  "source" => $ticket->token_id,
								  
								  "description" => "Deduct price for ticket",
								));
								
								if($charge['status'] == 'succeeded') { 
									$ticket->status=$req->status;
									$ticket->payment_response=json_encode($charge);
									$ticket->save();
									$payment_response = json_decode($ticket->payment_response);
									/* $charged_amount = (float)($event->final_ticket_price)*(float)($ticket->count); */
									$charged_amount = $this->calculateAmount(($basic_amount)*(float)($ticket->count));
									$charged_amount = number_format((float)$charged_amount, 2, '.', '');
									$invoice = new Invoices;
									$invoice->ticket_id = $req->ticket_id;
									$invoice->event_id = $ticket->event_id;
									$invoice->user_id = $ticket->user_id;
									$invoice->save();
									
									$final_charged_amount = 0.00;
									$transaction_date     =  $ticket->updated_at;
									$tax                  = Config::get('constants.tax');
									$sales_tax_amount     = ((float)$basic_amount*(float)$tax)/100;
									if($charged_amount>0){
										$final_charged_amount = (float)$charged_amount/100;
									}
									$destinationPath = public_path('uploads/invoices/'.$invoice->id); 
									if(!File::exists($destinationPath)) {
										File::makeDirectory($destinationPath, $mode = 0777, true, true);
									}
									$pdf = PDF::loadView('pdf.invoice',compact(['invoice','payment_response','final_charged_amount','transaction_date','sales_tax_amount','basic_amount']));
									

									$pdf->save($destinationPath.'/invoice.pdf');
									QrCode::size(250)->generate($req->ticket_id, $destinationPath.'/qrcode.svg');
									$invoice->qr_code = 'qrcode.svg';
									$invoice->pdf_file = 'invoice.pdf';
									$invoice->save();
									
									if($pdfs){
										$counted = 1;
										foreach($pdfs as $pdf){
											if($counted <= $ticket->count):
												$AllocatePdfs = new AllocatePdfs();
												$AllocatePdfs->pdf_id = $pdf->id;
												$AllocatePdfs->ticket_type_id = $pdf->ticket_type_id;
												$AllocatePdfs->user_id = $ticket->user_id;
												$AllocatePdfs->ticket_id = $ticket->id;
												$AllocatePdfs->save();
												
												$pdf->allocated = 1;
												$pdf->save();
												$counted++;
											endif;
										}
									}
									
									$this->PaymentMail($ticket->requsted_user->email,$invoice);
									$this->PaymentMail($admin_email,$invoice);
									$this->PaymentMail($ticket->member->email,$invoice);
									$this->PaymentMail($event->user->email,$invoice);
											
									//Mail::to($ticket->requsted_user->email)->send(new PaymentDone($invoice));
									//Mail::to($admin_email)->send(new PaymentDone($invoice));
									//Mail::to($ticket->member->email)->send(new PaymentDone($invoice));
									//Mail::to($event->user->email)->send(new PaymentDone($invoice));
									
									return response( array('data' =>$charge ,'response' =>1));
								} else {
									return response( array('data' =>'Payment error' ,'response' =>0));
								}  
							}
							catch (Exception $e) {
								/* $event->total_tickets = $event->total_tickets+ $ticket->count;
								$event->save(); */
								$event_ticket_type->tickets_sold = $event_ticket_type->tickets_sold  - $ticket->count;
								$event_ticket_type->save();

								return response( array('data' =>$e->getMessage() ,'response' =>0));
							}
						}
					}else{
						return response( array('data' =>'Event does not exist' ,'response' => 0));
					}
				}else{
					//return response( array('data' =>'Token not exist' ,'response' => 0));
					$ticket->status=$req->status;
					
					$ticket->save();
					$charged_amount = 0;
					$payment_response  = '';
					$charged_amount = number_format((float)$charged_amount, 2, '.', '');
					$invoice = new Invoices;
					$invoice->ticket_id = $req->ticket_id;
					$invoice->event_id = $ticket->event_id;
					$invoice->user_id = $ticket->user_id;
					$invoice->save();
					
					$basic_amount = 0;
					$final_charged_amount = 0.00;
					$transaction_date     =  $ticket->updated_at;
					$tax                  = Config::get('constants.tax');
					$sales_tax_amount     = ((float)$basic_amount*(float)$tax)/100;
					if($charged_amount>0){
						$final_charged_amount = (float)$charged_amount/100;
					}
					$destinationPath = public_path('uploads/invoices/'.$invoice->id); 
					if(!File::exists($destinationPath)) {
						File::makeDirectory($destinationPath, $mode = 0777, true, true);
					}
					$pdf = PDF::loadView('pdf.invoice',compact(['invoice','payment_response','final_charged_amount','transaction_date','sales_tax_amount','basic_amount']));
					$pdf->save($destinationPath.'/invoice.pdf');
					QrCode::size(250)->generate($req->ticket_id, $destinationPath.'/qrcode.svg');
					$invoice->qr_code = 'qrcode.svg';
					$invoice->pdf_file = 'invoice.pdf';
					$invoice->save();
					if($pdfs){
						$counted = 1;
						foreach($pdfs as $pdf){
							if($counted <= $ticket->count):
								$AllocatePdfs = new AllocatePdfs();
								$AllocatePdfs->pdf_id = $pdf->id;
								$AllocatePdfs->ticket_type_id = $pdf->ticket_type_id;
								$AllocatePdfs->user_id = $ticket->user_id;
								$AllocatePdfs->ticket_id = $ticket->id;
								$AllocatePdfs->save();
								
								$pdf->allocated = 1;
								$pdf->save();
								$counted++;
							endif;
						}
					}
					
					$this->PaymentMail($ticket->requsted_user->email,$invoice);
					$this->PaymentMail($admin_email,$invoice);
					$this->PaymentMail($ticket->member->email,$invoice);
					$this->PaymentMail($event->user->email,$invoice);
											
					//Mail::to($ticket->requsted_user->email)->send(new PaymentDone($invoice));
					//Mail::to($admin_email)->send(new PaymentDone($invoice));
					//Mail::to($ticket->member->email)->send(new PaymentDone($invoice));
					//Mail::to($event->user->email)->send(new PaymentDone($invoice));
					
					return response( array('data' =>$ticket ,'response' => 1));
				}
			}else{
				$ticket->status=$req->status;
				$ticket->save();
				/* $event->total_tickets = $event->total_tickets+ $ticket->count;
				$event->save(); */
				
				$event_ticket_type->tickets_sold = $event_ticket_type->tickets_sold  - $ticket->count;
				$event_ticket_type->save();
				
				$this->ticketCancellationMail($ticket->requsted_user->email);
				$this->ticketCancellationMail($admin_email);
				$this->ticketCancellationMail($ticket->member->email);
				$this->ticketCancellationMail($event->user->email);
				
				//Mail::to($ticket->requsted_user->email)->send(new TicketRequestCancelled());
				//Mail::to($admin_email)->send(new TicketRequestCancelled());
				//Mail::to($ticket->member->email)->send(new TicketRequestCancelled());
				//Mail::to($event->user->email)->send(new TicketRequestCancelled());
				
				return response( array('data' =>$ticket ,'response' =>1));
			}
		} else {
			return response( array('data' =>'Ticket type not found' ,'response' => 0));
		}

		}else{
		return response( array('data' =>'Ticket not found' ,'response' => 0));
		}
		}else{
		return response( array('data' =>'Error' ,'response' => 0));
		}
	
	}
	
	public function change_status_of_ticket_test_new(Request $req){
		if(isset($req->ticket_id))
		{
			$admin_email = Config::get('constants.admin_email');
			$ticket = Ticket::find($req->ticket_id);

			$event_ticket_type = $ticket->type;
			$event = $ticket->event;
			
			\Stripe\Stripe::setApiKey ( env('STRIPE_SECRET') );
			\Stripe\Stripe::setClientId( env('STRIPE_CLIENT_ID'));
			if($ticket)
			{
				if($event_ticket_type){
					if($ticket->token_id==null || $ticket->token_id==''){
						if($req->status==1 && $req->status!=null)
						{
							$ticket->status=$req->status;
							$ticket->save();
							$invoice = $ticket->invoice;
							$this->PaymentMail($ticket->requsted_user->email,$invoice);
							/* $this->PaymentMail($admin_email,$invoice);
							$this->PaymentMail($ticket->member->email,$invoice);
							$this->PaymentMail($event->user->email,$invoice); */
							return response( array('data' =>$ticket ,'response' =>1)); 
						
						} else{
							$ticket->status=$req->status;
							$ticket->save();
							/* $event->total_tickets = $event->total_tickets+ $ticket->count;
							$event->save(); */
							
							$event_ticket_type->tickets_sold = $event_ticket_type->tickets_sold  - $ticket->count;
							$event_ticket_type->save();
							
							$this->ticketCancellationMail($ticket->requsted_user->email);
							/* $this->ticketCancellationMail($admin_email);
							$this->ticketCancellationMail($ticket->member->email);
							$this->ticketCancellationMail($event->user->email); */
							
							
							return response( array('data' =>$ticket ,'response' =>1));
						}
					}else{
						return response( array('data' =>'You can not update ticket having payment' ,'response' => 0));
					}
				}else{
					return response( array('data' =>'Ticket type not found' ,'response' => 0));
				}
			}else{
				return response( array('data' =>'Ticket not found' ,'response' => 0));
			}
		}
	}

	public function totalTicketsSold($ticket_type_id){
		$sold_tickets = 0;
		$ticket_type = EventTicketType::find($ticket_type_id);
		if($ticket_type){
			$sold_tickets = Ticket::where('event_id',$ticket_type->event_id)
			->where('ticket_type',$ticket_type_id)
			->where(function($query){
				$query->where('status','0');
				$query->orWhere('status','1');
			})
			->sum('count');
			
		}
		return $sold_tickets;
	}
	
	public function calculateAmount($basic_amount){
		$tax = Config::get('constants.tax');
		$additional_charges = Config::get('constants.additional_charges');
		$final_price = (float)$basic_amount + (((float)$basic_amount*(float)$tax)/100) + (float)$additional_charges; 
		return round($final_price*100);
	}
	
	public function user_purchased_events_test(Request $req){
		if(isset($req->user_email))
		{
			$tax = Config::get('constants.tax');
			$additional_charges = Config::get('constants.additional_charges');
			
			$user = User::where('email',$req->user_email)->first();
			if(count($user)>0)
			{
				$present_events_with_tickets = Ticket::with('type','allocatedPdfs','allocatedPdfs.pdfFile')->with('events')->with('invoice')->with('transfer','transfer.receiver')->has('events')
				->where('user_id',$user->id)
				->orWhere(function($q) use($user){
					$q->WhereHas('transfer', function($query) use($user) {
						$query->where('receiver_id',$user->id);
					});
					$q->has('events');
				})
				/* ->orWhereHas('transfer', function($query) use($user) {
					$query->where('receiver_id',$user->id);
				}) */
				->select("tickets.*",DB::raw('DATE_FORMAT(tickets.created_at, "%Y-%m-%d") as new_created_at'),DB::raw('DATE_FORMAT(tickets.updated_at, "%Y-%m-%d") as new_updated_at'))
				->orderBy('created_at','desc')->get();
				
				$past_events_with_tickets = Ticket::with('type','allocatedPdfs','allocatedPdfs.pdfFile')->with('past_events')->has('past_events')->where('user_id',$user->id)->select("tickets.*",DB::raw('DATE_FORMAT(tickets.created_at, "%Y-%m-%d") as new_created_at'),DB::raw('DATE_FORMAT(tickets.updated_at, "%Y-%m-%d") as new_updated_at'))->orderBy('created_at','desc')->get();
				return response( array('data' =>array('present_events_with_tickets'=>$present_events_with_tickets,'past_events_with_tickets'=>$past_events_with_tickets,'tax'=>$tax,'additional_charges'=>$additional_charges) ,'response' => 1));
			}
			else{
				return response( array('data' =>'error' ,'response' => 0));
			}
		}
		else{
			return response( array('data' =>'error' ,'response' => 0));
		}
		
	}
	
	public function ticketDetailTest(Request $req){
		$ticket =  Ticket::with('event','type','type.pdfs','allocatedPdfs','allocatedPdfs.pdfFile')->has('event')->where('id',$req->ticket_id)->first();
		if(count($ticket) > 0 ){
			if($ticket->scanned=='1'){
				return response( array('data' =>'Ticket already scanned' ,'ticket' => $ticket,'response' => 0));
			} else {
				return response( array('data' =>$ticket ,'response' => 1));
			}
		} else {
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
	
	public function count_ticket_request_test(Request $req){
		if(isset($req->user_email))
		{
			$tax = Config::get('constants.tax');
			$additional_charges = Config::get('constants.additional_charges');
			$user = User::where('email',$req->user_email)->first();
			if(count($user)>0)
			{   $is_comittee_member = Ticket::where('member_id',$user->id)->get();
				if(count($is_comittee_member)>0){
					$total_requests = Ticket::with('events','type','requsted_user')->has('events')->where('member_id',$user->id)->where('status',0)->get();
					return response( array('data' =>$total_requests ,'response' => 1,'tax'=>$tax,'additional_charges'=>$additional_charges));
				}else{
				    return response( array('data' =>'User is not member' ,'response' => 0));
				}
				
			}else{
				return response( array('data' =>'error' ,'response' => 0));
			}
		}
		else{
			return response( array('data' =>'error' ,'response' => 0));
		}
		
	}
	
	public function count_promoter_ticket_request_test(Request $req){
		if(isset($req->user_email))
		{
			$tax = Config::get('constants.tax');
			$additional_charges = Config::get('constants.additional_charges');
			$promoter = User::where('email',$req->user_email)->first();
			if(count($promoter)>0)
			{   
					$total_requests = Ticket::with('events','type','requsted_user')->has('events')
					->where('status',0)
					->whereHas('events', function ($query) use($promoter) {
						$query->where('user_id', $promoter->id);
					})->get();
					return response( array('data' =>$total_requests ,'response' => 1,'tax'=>$tax,'additional_charges'=>$additional_charges));
				
				
			}else{
				return response( array('data' =>'error' ,'response' => 0));
			}
		}
		else{
			return response( array('data' =>'error' ,'response' => 0));
		}
		
	}
	
	public function ticketDetailByNameOrEmail(Request $req){
		
		$user_name = $req->name;
		$email = $req->email;
		$tax = Config::get('constants.tax');
		$additional_charges = Config::get('constants.additional_charges');
		
		$tickets = Ticket::with('events')->with('invoice')->with('transfer','transfer.receiver','requsted_user','type')->has('events')->where('status',1);
		if($user_name!="" && $email==""){
			$tickets->whereHas('requsted_user',function($query) use($user_name){
				$query->where('name',$user_name);
				
			});
			 $tickets->orWhereHas('transfer.receiver', function($query) use($user_name) {
				$query->where('name',$user_name);
			}); 
		} else if($user_name=="" && $email!=""){
			$tickets->whereHas('requsted_user',function($query) use($email){
				$query->where('email',$email);
				
			});
			 $tickets->orWhereHas('transfer.receiver', function($query) use($email) {
				$query->where('email',$email);
			}); 
		}else if($user_name!="" && $email!=""){
			$tickets->whereHas('requsted_user',function($query) use($user_name,$email){
				$query->where('email',$email);
				$query->where('name',$user_name);
				
			});
			 $tickets->orWhereHas('transfer.receiver', function($query) use($user_name,$email) {
				$query->where('email',$email);
				$query->where('name',$user_name);
			}); 
		} else {
			return response( array('data' =>"No record found" ,'response' => 0));
		}
		$all_tickets = $tickets->orderBy('created_at','desc')->get();
		return response( array('data' =>$all_tickets ,'response' => 1,'tax'=>$tax,'additional_charges'=>$additional_charges));
	}
	
	public function PaymentMail($email,$invoice){
		$ticket = $invoice->ticket;
		$basic_amount = $ticket->type->ticket_price ? (float)$ticket->type->ticket_price: 0;
		$amount = $this->calculateDetailAmount(($basic_amount)*(float)($ticket->count));
		$currency       = "USD";
		$source          = $ticket->token_id;
		$sale_tax = $this->saleTax(($basic_amount)*(float)($ticket->count));
		Mail::to($email)->send(new PaymentDone($invoice,$ticket,$basic_amount,$amount,$currency,$source,$sale_tax));
	}
	
	public function ticketCancellationMail($email){
		/* $ticket = $invoice->ticket;
		$basic_amount = (float)$ticket->type->ticket_price;
		$amount = $this->calculateDetailAmount(($basic_amount)*(float)($ticket->count));
		$currency       = "USD";
		$source          = $ticket->token_id;
		$sale_tax = $this->saleTax(($basic_amount)*(float)($ticket->count)); */
		Mail::to($email)->send(new TicketRequestCancelled());
	}
	
	public function calculateFees($basic_amount,$tickets){
		/* $tax                = Config::get('constants.tax');
		$additional_charges = Config::get('constants.additional_charges');
		$fees               = Config::get('constants.admin_fees');
		$final_price        = (float)$basic_amount + (((float)$basic_amount*(float)$tax)/100) + (float)$additional_charges; 
		$admin_fees         =  ((float)$final_price*$fees)/100 ;
		return round($admin_fees*100); */
		$fees               = Config::get('constants.admin_fees');
		$tickets = $tickets ? $tickets : 0;
		$admin_fees         =  (float)($fees*$tickets) ;
		return round($admin_fees*100);
	}
	
	/************* new function create charge at time of request ******/
	public function createCharge($req,$member_id,$user,$event_ticket_type,$tickets){
		$admin_email = Config::get('constants.admin_email');
		
		\Stripe\Stripe::setApiKey ( env('STRIPE_SECRET') );
		\Stripe\Stripe::setClientId( env('STRIPE_CLIENT_ID'));
		
		$event = Event::find($req->event_id);

		$pdfs = $event_ticket_type->pdfs->where('allocated',0);

		$basic_amount = (float)$event_ticket_type->ticket_price;
		if(isset($req->token_id) && $req->token_id!=null)
		{
			if($event)
			{
				if($event->user->type=='admin'){
					try {
						$charge = \Stripe\Charge::create(array(
						  "amount" => $this->calculateAmount(($basic_amount)*(float)($tickets)),
						  "currency" => "USD",
						  "source" => $req->token_id,
						  
						  "description" => "Deduct price for ticket",
						));
						
						if($charge['status'] == 'succeeded') { 
							
							$data = array('event_id'=>$req->event_id,'member_id'=>$member_id,'user_id'=>$user->id,'count'=>$tickets,'token_id'=>$req->token_id,'ticket_type'=>$event_ticket_type->id);
							
							$ticket                = Ticket::create($data);
							
							$ticket->status=1;
							$ticket->payment_response=json_encode($charge);
							$ticket->save();
							$payment_response = json_decode($ticket->payment_response);
							/* $charged_amount = (float)($event->final_ticket_price)*(float)($ticket->count); */
							$charged_amount = $this->calculateAmount(($basic_amount)*(float)($tickets));
							$charged_amount = number_format((float)$charged_amount, 2, '.', '');
							$invoice = new Invoices;
							$invoice->ticket_id = $ticket->id;
							$invoice->event_id = $ticket->event_id;
							$invoice->user_id = $ticket->user_id;
							$invoice->save();
							$final_charged_amount = 0.00;
							
							$transaction_date     =  $ticket->updated_at;
							$tax                  = Config::get('constants.tax');
							$sales_tax_amount     = ((float)$basic_amount*(float)$tax)/100;
							if($charged_amount>0){
								$final_charged_amount = (float)$charged_amount/100;
							}
							$destinationPath = public_path('uploads/invoices/'.$invoice->id); 
							if(!File::exists($destinationPath)) {
								File::makeDirectory($destinationPath, $mode = 0777, true, true);
							}
							$pdf = PDF::loadView('pdf.invoice',compact(['invoice','payment_response','final_charged_amount','transaction_date','sales_tax_amount','basic_amount']));
							
							$pdf->save($destinationPath.'/invoice.pdf');
							QrCode::size(250)->generate($ticket->id, $destinationPath.'/qrcode.svg');
							$invoice->qr_code = 'qrcode.svg';
							$invoice->pdf_file = 'invoice.pdf';
							$invoice->save();
							if($pdfs){
								$counted = 1;
								foreach($pdfs as $pdf){
									if($counted <= $ticket->count):
										$AllocatePdfs = new AllocatePdfs();
										$AllocatePdfs->pdf_id = $pdf->id;
										$AllocatePdfs->ticket_type_id = $pdf->ticket_type_id;
										$AllocatePdfs->user_id = $ticket->user_id;
										$AllocatePdfs->ticket_id = $ticket->id;
										$AllocatePdfs->save();
										
										$pdf->allocated = 1;
										$pdf->save();
										$counted++;
									endif;
								}
							}
							$this->PaymentMail($ticket->requsted_user->email,$invoice);
							$this->PaymentMail($admin_email,$invoice);
							$this->PaymentMail($ticket->member->email,$invoice);
							$this->PaymentMail($event->user->email,$invoice);
							
							//Mail::to($ticket->requsted_user->email)->send(new PaymentDone($invoice));
							//Mail::to($admin_email)->send(new PaymentDone($invoice));
							//Mail::to($ticket->member->email)->send(new PaymentDone($invoice));
							//Mail::to($event->user->email)->send(new PaymentDone($invoice));
							return $ticket;
							
						} else {
							return '';
						}  
					}
					catch (Exception $e) {
						return '';
					}
				} else if($event->user->type=='promoter')
				{
					if($event->user->account){
						if($event->user->account->stripe_account_id){
							//$stripe = Stripe::make(env('STRIPE_SECRET'));
							
							try {
								$charge = \Stripe\Charge::create(array(
								  "amount" => $this->calculateAmount(($basic_amount)*(float)($tickets)),
								  "currency" => "USD",
								  "source" => $req->token_id,
								  "application_fee" => $this->calculateFees(($basic_amount)*(float)($tickets),$tickets),
								  "description" => "Deduct price for ticket",
								), array("stripe_account" => $event->user->account->stripe_account_id));
								
								if($charge['status'] == 'succeeded') { 
									$data = array('event_id'=>$req->event_id,'member_id'=>$member_id,'user_id'=>$user->id,'count'=>$tickets,'token_id'=>$req->token_id,'ticket_type'=>$event_ticket_type->id);
							
									$ticket= Ticket::create($data);
							
							
									$ticket->status=1;
									$ticket->payment_response=json_encode($charge);
									$ticket->save();
									$payment_response = json_decode($ticket->payment_response);
									/* $charged_amount = (float)($event->final_ticket_price)*(float)($ticket->count); */
									$charged_amount = $this->calculateAmount(($basic_amount)*(float)($tickets));
									$charged_amount = number_format((float)$charged_amount, 2, '.', '');
									$invoice = new Invoices;
									$invoice->ticket_id = $ticket->id;
									$invoice->event_id = $ticket->event_id;
									$invoice->user_id = $ticket->user_id;
									$invoice->save();
									
									$final_charged_amount = 0.00;
									$transaction_date     =  $ticket->updated_at;
									$tax                  = Config::get('constants.tax');
									$sales_tax_amount     = ((float)$basic_amount*(float)$tax)/100;
									if($charged_amount>0){
										$final_charged_amount = (float)$charged_amount/100;
									}
									$destinationPath = public_path('uploads/invoices/'.$invoice->id); 
									if(!File::exists($destinationPath)) {
										File::makeDirectory($destinationPath, $mode = 0777, true, true);
									}
									$pdf = PDF::loadView('pdf.invoice',compact(['invoice','payment_response','final_charged_amount','transaction_date','sales_tax_amount','basic_amount']));

									$pdf->save($destinationPath.'/invoice.pdf');
									QrCode::size(250)->generate($ticket->id, $destinationPath.'/qrcode.svg');
									$invoice->qr_code = 'qrcode.svg';
									$invoice->pdf_file = 'invoice.pdf';
									$invoice->save();
									
									if($pdfs){
										$counted = 1;
										foreach($pdfs as $pdf){
											if($counted <= $ticket->count):
												$AllocatePdfs = new AllocatePdfs();
												$AllocatePdfs->pdf_id = $pdf->id;
												$AllocatePdfs->ticket_type_id = $pdf->ticket_type_id;
												$AllocatePdfs->user_id = $ticket->user_id;
												$AllocatePdfs->ticket_id = $ticket->id;
												$AllocatePdfs->save();
												
												$pdf->allocated = 1;
												$pdf->save();
												$counted++;
											endif;
										}
									}
									$this->PaymentMail($ticket->requsted_user->email,$invoice);
									$this->PaymentMail($admin_email,$invoice);
									$this->PaymentMail($ticket->member->email,$invoice);
									$this->PaymentMail($event->user->email,$invoice);
									
									//Mail::to($ticket->requsted_user->email)->send(new PaymentDone($invoice));
									//Mail::to($admin_email)->send(new PaymentDone($invoice));
									//Mail::to($ticket->member->email)->send(new PaymentDone($invoice));
									//Mail::to($event->user->email)->send(new PaymentDone($invoice));
									return $ticket;
									
								} else {
									return '';
								}  
							}
							catch (Exception $e) {
								return '';
							}
						
						} else{
							try {
								
								$charge = \Stripe\Charge::create(array(
								  "amount" => $this->calculateAmount(($basic_amount)*(float)($tickets)),
								  "currency" => "USD",
								  "source" => $req->token_id,
								  
								  "description" => "Deduct price for ticket",
								));
								
								if($charge['status'] == 'succeeded') { 
									$data = array('event_id'=>$req->event_id,'member_id'=>$member_id,'user_id'=>$user->id,'count'=>$tickets,'token_id'=>$req->token_id,'ticket_type'=>$event_ticket_type->id);
							
									$ticket= Ticket::create($data);
									
									$ticket->status=1;
									$ticket->payment_response=json_encode($charge);
									$ticket->save();
									$payment_response = json_decode($ticket->payment_response);
									/* $charged_amount = (float)($event->final_ticket_price)*(float)($ticket->count); */
									$charged_amount = $this->calculateAmount(($basic_amount)*(float)($tickets));
									$charged_amount = number_format((float)$charged_amount, 2, '.', '');
									$invoice = new Invoices;
									$invoice->ticket_id = $ticket->id;
									$invoice->event_id = $ticket->event_id;
									$invoice->user_id = $ticket->user_id;
									$invoice->save();
									
									$final_charged_amount = 0.00;
									$transaction_date     =  $ticket->updated_at;
									$tax                  = Config::get('constants.tax');
									$sales_tax_amount     = ((float)$basic_amount*(float)$tax)/100;
									if($charged_amount>0){
										$final_charged_amount = (float)$charged_amount/100;
									}
									$destinationPath = public_path('uploads/invoices/'.$invoice->id); 
									if(!File::exists($destinationPath)) {
										File::makeDirectory($destinationPath, $mode = 0777, true, true);
									}
									$pdf = PDF::loadView('pdf.invoice',compact(['invoice','payment_response','final_charged_amount','transaction_date','sales_tax_amount','basic_amount']));

									$pdf->save($destinationPath.'/invoice.pdf');
									QrCode::size(250)->generate($ticket->id, $destinationPath.'/qrcode.svg');
									$invoice->qr_code = 'qrcode.svg';
									$invoice->pdf_file = 'invoice.pdf';
									$invoice->save();
									
									if($pdfs){
										$counted = 1;
										foreach($pdfs as $pdf){
											if($counted <= $ticket->count):
												$AllocatePdfs = new AllocatePdfs();
												$AllocatePdfs->pdf_id = $pdf->id;
												$AllocatePdfs->ticket_type_id = $pdf->ticket_type_id;
												$AllocatePdfs->user_id = $ticket->user_id;
												$AllocatePdfs->ticket_id = $ticket->id;
												$AllocatePdfs->save();
												
												$pdf->allocated = 1;
												$pdf->save();
												$counted++;
											endif;
										}
									}
									
									$this->PaymentMail($ticket->requsted_user->email,$invoice);
									$this->PaymentMail($admin_email,$invoice);
									$this->PaymentMail($ticket->member->email,$invoice);
									$this->PaymentMail($event->user->email,$invoice);
									
									return $ticket;
									
									//Mail::to($ticket->requsted_user->email)->send(new PaymentDone($invoice));
									//Mail::to($admin_email)->send(new PaymentDone($invoice));
									//Mail::to($ticket->member->email)->send(new PaymentDone($invoice));
									//Mail::to($event->user->email)->send(new PaymentDone($invoice));
									
									return response( array('data' =>$charge ,'response' =>1));
								} else {
									return response( array('data' =>'Payment error' ,'response' =>0));
								}  
							}
							catch (Exception $e) {
								return '';
							}
						}
					}else{
							try {
								$charge = \Stripe\Charge::create(array(
								  "amount" => $this->calculateAmount(($basic_amount)*(float)($tickets)),
								  "currency" => "USD",
								  "source" => $req->token_id,
								  
								  "description" => "Deduct price for ticket",
								));
								
								if($charge['status'] == 'succeeded') {
									$data = array('event_id'=>$req->event_id,'member_id'=>$member_id,'user_id'=>$user->id,'count'=>$tickets,'token_id'=>$req->token_id,'ticket_type'=>$event_ticket_type->id);
							
									$ticket= Ticket::create($data);									
									$ticket->status=1;
									$ticket->payment_response=json_encode($charge);
									$ticket->save();
									$payment_response = json_decode($ticket->payment_response);
									/* $charged_amount = (float)($event->final_ticket_price)*(float)($ticket->count); */
									$charged_amount = $this->calculateAmount(($basic_amount)*(float)($tickets));
									$charged_amount = number_format((float)$charged_amount, 2, '.', '');
									$invoice = new Invoices;
									$invoice->ticket_id = $ticket->id;
									$invoice->event_id = $ticket->event_id;
									$invoice->user_id = $ticket->user_id;
									$invoice->save();
									
									$final_charged_amount = 0.00;
									$transaction_date     =  $ticket->updated_at;
									$tax                  = Config::get('constants.tax');
									$sales_tax_amount     = ((float)$basic_amount*(float)$tax)/100;
									if($charged_amount>0){
										$final_charged_amount = (float)$charged_amount/100;
									}
									$destinationPath = public_path('uploads/invoices/'.$invoice->id); 
									if(!File::exists($destinationPath)) {
										File::makeDirectory($destinationPath, $mode = 0777, true, true);
									}
									$pdf = PDF::loadView('pdf.invoice',compact(['invoice','payment_response','final_charged_amount','transaction_date','sales_tax_amount','basic_amount']));

									$pdf->save($destinationPath.'/invoice.pdf');
									QrCode::size(250)->generate($ticket->id, $destinationPath.'/qrcode.svg');
									$invoice->qr_code = 'qrcode.svg';
									$invoice->pdf_file = 'invoice.pdf';
									$invoice->save();
									
									if($pdfs){
										$counted = 1;
										foreach($pdfs as $pdf){
											if($counted <= $ticket->count):
												$AllocatePdfs = new AllocatePdfs();
												$AllocatePdfs->pdf_id = $pdf->id;
												$AllocatePdfs->ticket_type_id = $pdf->ticket_type_id;
												$AllocatePdfs->user_id = $ticket->user_id;
												$AllocatePdfs->ticket_id = $ticket->id;
												$AllocatePdfs->save();
												
												$pdf->allocated = 1;
												$pdf->save();
												$counted++;
											endif;
										}
									}
									$this->PaymentMail($ticket->requsted_user->email,$invoice);
									$this->PaymentMail($admin_email,$invoice);
									$this->PaymentMail($ticket->member->email,$invoice);
									$this->PaymentMail($event->user->email,$invoice);
									
									return $ticket;
									//Mail::to($ticket->requsted_user->email)->send(new PaymentDone($invoice));
									//Mail::to($admin_email)->send(new PaymentDone($invoice));
									//Mail::to($ticket->member->email)->send(new PaymentDone($invoice));
									//Mail::to($event->user->email)->send(new PaymentDone($invoice));
									
									
								} else {
									return '';
								}  
							}
							catch (Exception $e) {
								return '';
							}
					}
				} else{
					try {
						$charge = \Stripe\Charge::create(array(
						  "amount" => $this->calculateAmount(($basic_amount)*(float)($tickets)),
						  "currency" => "USD",
						  "source" => $req->token_id,
						  
						  "description" => "Deduct price for ticket",
						));
						
						if($charge['status'] == 'succeeded') { 
							$data = array('event_id'=>$req->event_id,'member_id'=>$member_id,'user_id'=>$user->id,'count'=>$tickets,'token_id'=>$req->token_id,'ticket_type'=>$event_ticket_type->id);
							
							$ticket= Ticket::create($data);	
									
							$ticket->status=1;
							$ticket->payment_response=json_encode($charge);
							$ticket->save();
							$payment_response = json_decode($ticket->payment_response);
							/* $charged_amount = (float)($event->final_ticket_price)*(float)($ticket->count); */
							$charged_amount = $this->calculateAmount(($basic_amount)*(float)($tickets));
							$charged_amount = number_format((float)$charged_amount, 2, '.', '');
							$invoice = new Invoices;
							$invoice->ticket_id = $ticket->id;
							$invoice->event_id = $ticket->event_id;
							$invoice->user_id = $ticket->user_id;
							$invoice->save();
							
							$final_charged_amount = 0.00;
							$transaction_date     =  $ticket->updated_at;
							$tax                  = Config::get('constants.tax');
							$sales_tax_amount     = ((float)$basic_amount*(float)$tax)/100;
							if($charged_amount>0){
								$final_charged_amount = (float)$charged_amount/100;
							}
							$destinationPath = public_path('uploads/invoices/'.$invoice->id); 
							if(!File::exists($destinationPath)) {
								File::makeDirectory($destinationPath, $mode = 0777, true, true);
							}
							$pdf = PDF::loadView('pdf.invoice',compact(['invoice','payment_response','final_charged_amount','transaction_date','sales_tax_amount','basic_amount']));
							

							$pdf->save($destinationPath.'/invoice.pdf');
							QrCode::size(250)->generate($ticket->id, $destinationPath.'/qrcode.svg');
							$invoice->qr_code = 'qrcode.svg';
							$invoice->pdf_file = 'invoice.pdf';
							$invoice->save();
							
							if($pdfs){
								$counted = 1;
								foreach($pdfs as $pdf){
									if($counted <= $ticket->count):
										$AllocatePdfs = new AllocatePdfs();
										$AllocatePdfs->pdf_id = $pdf->id;
										$AllocatePdfs->ticket_type_id = $pdf->ticket_type_id;
										$AllocatePdfs->user_id = $ticket->user_id;
										$AllocatePdfs->ticket_id = $ticket->id;
										$AllocatePdfs->save();
										
										$pdf->allocated = 1;
										$pdf->save();
										$counted++;
									endif;
								}
							}
							
							$this->PaymentMail($ticket->requsted_user->email,$invoice);
							$this->PaymentMail($admin_email,$invoice);
							$this->PaymentMail($ticket->member->email,$invoice);
							$this->PaymentMail($event->user->email,$invoice);
							return $ticket;
							//Mail::to($ticket->requsted_user->email)->send(new PaymentDone($invoice));
							//Mail::to($admin_email)->send(new PaymentDone($invoice));
							//Mail::to($ticket->member->email)->send(new PaymentDone($invoice));
							//Mail::to($event->user->email)->send(new PaymentDone($invoice));
							
							return response( array('data' =>$charge ,'response' =>1));
						} else {
							return response( array('data' =>'Payment error' ,'response' =>0));
						}  
					}
					catch (Exception $e) {
						return '';
					}
				}
			}else{
				return '';
			}
		}else{
			//return response( array('data' =>'Token not exist' ,'response' => 0));
			$data = array('event_id'=>$req->event_id,'member_id'=>$member_id,'user_id'=>$user->id,'count'=>$tickets,'token_id'=>$req->token_id,'ticket_type'=>$event_ticket_type->id);
							
			$ticket= Ticket::create($data);	
			$ticket->status=0;
			
			$ticket->save();
			$charged_amount = 0;
			$payment_response  = '';
			$charged_amount = number_format((float)$charged_amount, 2, '.', '');
			$invoice = new Invoices;
			$invoice->ticket_id = $ticket->id;
			$invoice->event_id = $ticket->event_id;
			$invoice->user_id = $ticket->user_id;
			$invoice->save();
			
			$basic_amount = 0;
			$final_charged_amount = 0.00;
			$transaction_date     =  $ticket->updated_at;
			$tax                  = Config::get('constants.tax');
			$sales_tax_amount     = ((float)$basic_amount*(float)$tax)/100;
			if($charged_amount>0){
				$final_charged_amount = (float)$charged_amount/100;
			}
			$destinationPath = public_path('uploads/invoices/'.$invoice->id); 
			if(!File::exists($destinationPath)) {
				File::makeDirectory($destinationPath, $mode = 0777, true, true);
			}
			$pdf = PDF::loadView('pdf.invoice',compact(['invoice','payment_response','final_charged_amount','transaction_date','sales_tax_amount','basic_amount']));
			$pdf->save($destinationPath.'/invoice.pdf');
			QrCode::size(250)->generate($ticket->id, $destinationPath.'/qrcode.svg');
			$invoice->qr_code = 'qrcode.svg';
			$invoice->pdf_file = 'invoice.pdf';
			$invoice->save();
			if($pdfs){
				$counted = 1;
				foreach($pdfs as $pdf){
					if($counted <= $ticket->count):
						$AllocatePdfs = new AllocatePdfs();
						$AllocatePdfs->pdf_id = $pdf->id;
						$AllocatePdfs->ticket_type_id = $pdf->ticket_type_id;
						$AllocatePdfs->user_id = $ticket->user_id;
						$AllocatePdfs->ticket_id = $ticket->id;
						$AllocatePdfs->save();
						
						$pdf->allocated = 1;
						$pdf->save();
						$counted++;
					endif;
				}
			}
			
			/* $this->PaymentMail($ticket->requsted_user->email,$invoice);
			$this->PaymentMail($admin_email,$invoice);
			$this->PaymentMail($ticket->member->email,$invoice);
			$this->PaymentMail($event->user->email,$invoice); */
									
			//Mail::to($ticket->requsted_user->email)->send(new PaymentDone($invoice));
			//Mail::to($admin_email)->send(new PaymentDone($invoice));
			//Mail::to($ticket->member->email)->send(new PaymentDone($invoice));
			//Mail::to($event->user->email)->send(new PaymentDone($invoice));
			return $ticket;
			
		}
	}
}


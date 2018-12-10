<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Country;
use App\Models\State;
use App\Models\Event;
use App\Models\Ticket;
use DB;
use Redirect;
use Auth;
use File;
use Validator;
use Illuminate\Support\Facades\Input;
//use Yajra\Datatables\Datatables;
use Yajra\Datatables\Facades\Datatables;

use Cartalyst\Stripe\Laravel\Facades\Stripe;
use App\Models\Invoices;
use App\Mail\PaymentDone;
use App\Mail\TicketRequestCancelled;
use Illuminate\Support\Facades\Mail;
use PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\TicketUsers;
use App\Models\EventTicketType;
use Config;
use App\Models\AllocatePdfs;
use Carbon\Carbon;

class TicketsController extends Controller
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
         return view('admin.tickets.list');
    }

    
	
    public function getAjaxTickets()
    {   
		$admin_id = Auth::user()->id;
		$tickets    = Ticket::with('events','requsted_user','member')->with('type')->has('events')/* ->where('member_id',$admin_id) */->orderBy('id', 'desc')->latest('id');
		
		return Datatables::of($tickets)
        ->add_column('event', function($ticket) {
            return $ticket->events ? '<a href="'.route("event-edit",$ticket->events->id).'" target="_blank">'.ucfirst($ticket->events->event_name).'</a>' : '';
        })
		->add_column('user', function($ticket) {
            return $ticket->requsted_user ? ucfirst($ticket->requsted_user->name) : '';
        })
		->add_column('member_name', function($ticket) {
            return $ticket->member ? ucfirst($ticket->member->name) : '';
        })
		
		->add_column('ticket_type', function($ticket) {
            return $ticket->type ? ucfirst($ticket->type->ticket_type) : '';
        })
		/* ->editColumn('user_id', function($ticket) { return $ticket->requsted_user ? ucfirst($ticket->requsted_user->name) : ''; })
		->editColumn('member_id', function($ticket) { return $ticket->member ? ucfirst($ticket->member->name) : ''; })	
		->editColumn('ticket_type', function($ticket) {
            return $ticket->type ? ucfirst($ticket->type->ticket_type) : '';
        })		 */
		->make(true);
	
    }
	
	public function show($id){
		$ticket = Ticket::find($id);
		$basic_amount = (float)$ticket->type->ticket_price;
		$amount = $this->calculateDetailAmount(($basic_amount)*(float)($ticket->count));
		$currency       = "USD";
		$source          = $ticket->token_id;
		$sale_tax = $this->saleTax(($basic_amount)*(float)($ticket->count));
		return view('admin.tickets.detail')->with(['ticket'=>$ticket,'basic_amount'=>$basic_amount,'amount'=>$amount,'currency'=>$currency,'source'=>$source,'sale_tax'=>$sale_tax]);
	}
	
	
	
	public function postUpdateTicketStatus(Request $req)
	{
		/* $tickets_values=Input::get('value');
		$tickets    = Ticket::where('id','=',Input::get('ticket_id'))->update(array('status' => $tickets_values));
		return $tickets;
		 */
		/* if(isset($req->ticket_id))
		{
			$ticket = Ticket::find($req->ticket_id);
			$event = $ticket->event;
			if($ticket)
			{
				if($req->value==1 && $req->value!=null)
				{
					if(isset($ticket->token_id) && $ticket->token_id!=null)
					{
						if($event)
						{
							$stripe = Stripe::make(env('STRIPE_SECRET'));
							try {
								$charge = $stripe->charges()->create([
								'card' => $ticket->token_id,
								'currency' => 'USD',
								'amount'   => (float)($event->final_ticket_price)*(float)($ticket->count),
								'description' => 'Deduct price for ticket',
								]);
								if($charge['status'] == 'succeeded') { 
									$ticket->status=$req->value;
									$ticket->payment_response=json_encode($charge);
									$ticket->save();
									$payment_response = json_decode($ticket->payment_response);
									$charged_amount = (float)($event->final_ticket_price)*(float)($ticket->count);
									$charged_amount = number_format((float)$charged_amount, 2, '.', '');
									$invoice = new Invoices;
									$invoice->ticket_id = $req->ticket_id;
									$invoice->event_id = $ticket->event_id;
									$invoice->user_id = $ticket->user_id;
									$invoice->save();
									$destinationPath = public_path('uploads/invoices/'.$invoice->id); 
									if(!File::exists($destinationPath)) {
										File::makeDirectory($destinationPath, $mode = 0777, true, true);
									}
									$pdf = PDF::loadView('pdf.invoice',compact(['invoice','payment_response','charged_amount']));

									$pdf->save($destinationPath.'/invoice.pdf');
									QrCode::size(250)->generate($req->ticket_id, $destinationPath.'/qrcode.svg');
									$invoice->qr_code = 'qrcode.svg';
									$invoice->pdf_file = 'invoice.pdf';
									$invoice->save();
									Mail::to($ticket->requsted_user->email)->send(new PaymentDone($invoice));
									return response( array('data' =>$charge ,'response' =>1));
								} else {
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
					$ticket->status=$req->value;
					$ticket->save();
					$event->total_tickets = $event->total_tickets+ $ticket->count;
					$event->save();
					Mail::to($ticket->requsted_user->email)->send(new TicketRequestCancelled());
					return response( array('data' =>$ticket ,'response' =>1));
				}
				
			}else{
				return response( array('data' =>'error' ,'response' => 0));
			}
		}else{
			return response( array('data' =>'error' ,'response' => 0));
		} */
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
					if($req->value==1 && $req->value!=null)
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
											$ticket->status=$req->value;
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
													$ticket->status=$req->value;
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
													$ticket->status=$req->value;
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
													$ticket->status=$req->value;
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
											$ticket->status=$req->value;
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
							$ticket->status=$req->value;
							
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
						$ticket->status=$req->value;
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
	
	public function calculateAmount($basic_amount){
		$tax                = Config::get('constants.tax');
		$additional_charges = Config::get('constants.additional_charges');
		$final_price        = (float)$basic_amount + (((float)$basic_amount*(float)$tax)/100) + (float)$additional_charges; 
		return round($final_price*100);
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
	
	public function PaymentMail($email,$invoice){
		$ticket = $invoice->ticket;
		$basic_amount = $ticket->type->ticket_price ? (float)$ticket->type->ticket_price : 0;
		$amount = $this->calculateDetailAmount(($basic_amount)*(float)($ticket->count));
		$currency       = "USD";
		$source          = $ticket->token_id;
		$sale_tax = $this->saleTax(($basic_amount)*(float)($ticket->count));
		Mail::to($email)->send(new PaymentDone($invoice,$ticket,$basic_amount,$amount,$currency,$source,$sale_tax));
		
	}
	
	public function ticketCancellationMail($email){
		Mail::to($email)->send(new TicketRequestCancelled());
	}
	
	public function postUpdateTicketStatus_new(Request $req){
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
						if($req->value==1 && $req->value!=null)
						{
							$ticket->status=$req->value;
							$ticket->save();
							$invoice = $ticket->invoice;
							$this->PaymentMail($ticket->requsted_user->email,$invoice);
							/* $this->PaymentMail($admin_email,$invoice);
							$this->PaymentMail($ticket->member->email,$invoice);
							$this->PaymentMail($event->user->email,$invoice); */
							return response( array('data' =>$ticket ,'response' =>1)); 
						
						} else{
							$ticket->status=$req->value;
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
	
	public function refundTicketPayment(Request $req){
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
				if($ticket->scanned=='0')
				{
					if($event_ticket_type){
						if($event){
							$account_id = $event->user->account ? $event->user->account->stripe_account_id : '';
							if($event->event_date > Carbon::now()){
								if($ticket->token_id!=null || $ticket->token_id !=''){
									if($ticket->payment_response!=null || $ticket->payment_response !=''){
										$charge = json_decode($ticket->payment_response);
										$charge_id = $charge->id;
										
										if($account_id)
										{
											try{
												$refund = \Stripe\Refund::create(array(
												  "charge" =>  $charge_id,
												  "refund_application_fee" => true,
												) , array("stripe_account" => $account_id) );
												if($refund){
													$ticket->status=2;
													$ticket->refund_response=json_encode($refund);
													$ticket->save();
													
													$event_ticket_type->tickets_sold = $event_ticket_type->tickets_sold  - $ticket->count;
													$event_ticket_type->save();
													
													AllocatePdfs::where('ticket_id',$ticket->id)->delete();
													
													
													
													return response( array('data' =>$ticket ,'response' =>1));
												}
												
											}
											catch (Exception $e) {
												return response( array('data' =>$e->getMessage() ,'response' =>0));
												
											}
											 
										
										} else{
											try{
												
												$refund = \Stripe\Refund::create(array(
												  "charge" =>  $charge_id,
												 /* "refund_application_fee" => true, */
												)/* , array("stripe_account" => "acct_1Cv4WABGInTllWJp") */);
												if($refund){
													$ticket->status=2;
													$ticket->refund_response=json_encode($refund);
													$ticket->save();
													
													$event_ticket_type->tickets_sold = $event_ticket_type->tickets_sold  - $ticket->count;
													$event_ticket_type->save();
													
													AllocatePdfs::where('ticket_id',$ticket->id)->delete();
													
													return response( array('data' =>$ticket ,'response' =>1));
												}
												
											}
											catch (Exception $e) {
												
												return response( array('data' =>$e->getMessage() ,'response' =>0));
												
											}
										}
									}else{
										return response( array('data' =>'Payment not found' ,'response' => 0));
									}
								}else{
									return response( array('data' =>'Payment not found' ,'response' => 0));
								}
							}else{
								return response( array('data' =>'Event expired or event is today' ,'response' => 0));
							}
						}else{
							return response( array('data' =>'Event not found' ,'response' => 0));
						}
					}else{
						return response( array('data' =>'Ticket type not found' ,'response' => 0));
					}
				}else{
					return response( array('data' =>'Ticket scanned' ,'response' => 0));
				}
			}else{
				return response( array('data' =>'Ticket not found' ,'response' => 0));
			}
		}else{
			return response( array('data' =>'Please post ticket id' ,'response' => 0));
			
		}
	}
}

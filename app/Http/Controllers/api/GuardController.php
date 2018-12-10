<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Guard;
use App\Models\Ticket;
use App\Models\Event;
use App\Models\CommonFunctions;
use Input;
use File;
use Illuminate\Support\Facades\Hash;
use Mail;
use DB;
use Config;

class GuardController extends Controller
{
    public function deleteGuard(Request $req){
		$email   = $req->promoter_email;
		$promoter    = User::where("email","=",$email)->first();
		if(count($promoter)>0){
			$gaurd = Guard::find($req->guard_id);
			if($gaurd) {
				$gaurd->delete();
				$gaurds = Guard::where('promoter_id',$promoter->id)->get();
				return response( array('data' =>$gaurds ,'response' => 1));
			} else {
				return response( array('data' =>"Guard Not Found" ,'response' => 0));
			}
		} else {
			return response( array('data' =>"Promoter Not Found" ,'response' => 0));
		}
	}
	
	public function scanTicket(Request $req){
		$ticket_id   = $req->ticket_id;
		$ticket    = Ticket::with('transfer','transfer.receiver')->where("id","=",$ticket_id)->first();
		$receiver_email = '';
		if(count($ticket)>0){
			if($ticket->transfer){
				$receiver_email= $ticket->transfer->receiver->email;
			} else {
				$receiver_email=  $ticket->requsted_user->email;
			}
			if($ticket->scanned=='1'){
				Mail::send('emails.ticket_request',
					['ticket'=>$ticket,'heading'=>'Sorry.','data'=>'Your ticket is already scanned.'],
					function($message) use($receiver_email)
					{
					   $message->to($receiver_email)->subject('Carnivalist Ticket Scanned.');
					}
				);
				return response( array('data' =>"Ticket already scanned" ,'response' => 0));
			} else {
				$gaurd = Guard::find($req->guard_id);
				if($gaurd) {
					$ticket->scanned= '1';
					$ticket->scanned_by_guide= $req->guard_id;
					$ticket->save();
					Mail::send('emails.ticket_request',
						['ticket'=>$ticket,'heading'=>'Thank You.','data'=>'Your ticket is successfully scanned.'],
						function($message) use($receiver_email)
						{
						   $message->to($receiver_email)->subject('Carnivalist Ticket Scanned.');
						}
					);
					return response( array('data' =>$ticket ,'response' => 1));
				} else {
					return response( array('data' =>"Guard Not Found" ,'response' => 0));
				}
			}
		} else {
			return response( array('data' =>"Ticket Not Exist" ,'response' => 0));
		}
	}
	
	public function scannedTicketList(Request $req){
		$guard_id   = $req->guard_id;
		$gaurd    = Guard::find($guard_id);
		if($gaurd){
			$tickets_scanned = Ticket::with('events','invoice','transfer','transfer.receiver')->has('events')->where('scanned_by_guide',$guard_id)->select("tickets.*",DB::raw('DATE_FORMAT(tickets.created_at, "%Y-%m-%d") as new_created_at'),DB::raw('DATE_FORMAT(tickets.updated_at, "%Y-%m-%d") as new_updated_at'))->orderBy('updated_at','desc')->get();
			return response( array('data' =>$tickets_scanned ,'response' => 1));
		} else {
			return response( array('data' =>"Guard Not Exist" ,'response' => 0));
		}
	}
	
	
	public function guradEventList(Request $req){
		$guard_id   = $req->guard_id;
		$guard    = Guard::find($guard_id);
		if($guard){
			$event_detail = Event::where('user_id','=',$guard->promoter_id)->where('is_active','=','1')/* ->whereDate('event_date', '>=', date('Y-m-d')) */->orderBy('event_date', 'asc')->get();
			if(count($event_detail)>0){
				return response( array('data' => $event_detail,'response' => 1));					
			}else{
				return response( array('data' => "No record found.",'response' => 0));
			}
		} else {
			return response( array('data' =>"Guard Not Exist" ,'response' => 0));
		}
		
	}
	/******* Test functions *************/
	
	public function scanTicketTest(Request $req){
		$ticket_id   = $req->ticket_id;
		$ticket    = Ticket::with('transfer','transfer.receiver','type')->where("id","=",$ticket_id)->first();
		$receiver_email = '';
		if(count($ticket)>0){
			if($ticket->transfer){
				$receiver_email= $ticket->transfer->receiver->email;
			} else {
				$receiver_email=  $ticket->requsted_user->email;
			}

          $basic_amount = (float)$ticket->type->ticket_price;
      		$amount = CommonFunctions::calculateDetailAmount(($basic_amount)*(float)($ticket->count));
      		$currency       = "USD";
      		$source          = $ticket->token_id;
      		$sale_tax = CommonFunctions::saleTax(($basic_amount)*(float)($ticket->count));


			if($ticket->scanned=='1'){
				Mail::send('emails.ticket_request',
					['ticket'=>$ticket,'amount'=>$amount,'heading'=>'Sorry.','data'=>'Your ticket is already scanned.'],
					function($message) use($receiver_email)
					{
					   $message->to($receiver_email)->subject('Carnivalist Ticket Scanned.');
					}
				);
				return response( array('data' =>"Ticket already scanned" ,'response' => 0));
			} else {
				$gaurd = Guard::find($req->guard_id);
				if($ticket->event){
					if($gaurd->promoter_id!="" && $ticket->event->user){
						if($ticket->event->user->id==$gaurd->promoter_id){
							if($gaurd) {
								$ticket->scanned= '1';
								$ticket->scanned_by_guide= $req->guard_id;
								$ticket->save();
								Mail::send('emails.ticket_request',
									['ticket'=>$ticket,'amount'=>$amount,'heading'=>'Thank You.','data'=>'Your ticket is successfully scanned.'],
									function($message) use($receiver_email)
									{
									   $message->to($receiver_email)->subject('Carnivalist Ticket Scanned.');
									}
								);
								return response( array('data' =>$ticket ,'response' => 1));
							} else {
								return response( array('data' =>"Guard Not Found" ,'response' => 0));
							}
						} else {
							return response( array('data' =>"Promoter does not match." ,'response' => 0));
						}
					} else {
						return response( array('data' =>"error" ,'response' => 0));
					}

				}else {
					return response( array('data' =>"Event Not Found" ,'response' => 0));
				}
			}
		} else {
			return response( array('data' =>"Ticket Not Exist" ,'response' => 0));
		}
	}
	
	public function scannedTicketListTest(Request $req){
		$guard_id   = $req->guard_id;
		$event_id   = $req->event_id;
		
		$gaurd    = Guard::find($guard_id);
		$tax = Config::get('constants.tax');
		$additional_charges = Config::get('constants.additional_charges');
		if($gaurd){
			$tickets_scanned = Ticket::with('events','invoice','transfer','transfer.receiver','type')->has('events')->where('scanned_by_guide',$guard_id)->where('event_id',$event_id)->select("tickets.*",DB::raw('DATE_FORMAT(tickets.created_at, "%Y-%m-%d") as new_created_at'),DB::raw('DATE_FORMAT(tickets.updated_at, "%Y-%m-%d") as new_updated_at'))->orderBy('updated_at','desc')->get();
			return response( array('data' =>$tickets_scanned ,'response' => 1,'tax'=>$tax,'additional_charges'=>$additional_charges));
		} else {
			return response( array('data' =>"Guard Not Exist" ,'response' => 0));
		}
	}
}

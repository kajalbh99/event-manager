<?php

namespace App\Models;
use Carbon\Carbon;
use DB;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
   protected $table = 'tickets';
   protected $fillable = ['event_id', 'user_id', 'member_id', 'count', 'status','token_id','payment_response','scanned','scanned_by_guide','ticket_type','refund_response'];
   
   /* public function events() {
		return $this->hasOne("App\Models\Event", "id", "event_id")->whereDate('event_date','>=',Carbon::today()->toDateString());
	}
	public function past_events() {
		return $this->hasOne("App\Models\Event", "id", "event_id")->whereDate('event_date','<',Carbon::today()->toDateString());
	} */
	
	public function events() {
		return $this->hasOne("App\Models\Event", "id", "event_id")->whereDate('event_date','>=',Carbon::today()->toDateString())->select(['*',DB::raw('MONTH(event_date) as month'),DB::raw('YEAR(event_date) as year'),DB::raw('DAY(event_date) as day')]);
	}
	
	public function past_events() {
		return $this->hasOne("App\Models\Event", "id", "event_id")->whereDate('event_date','<',Carbon::today()->toDateString())->select(['*',DB::raw('MONTH(event_date) as month'),DB::raw('YEAR(event_date) as year'),DB::raw('DAY(event_date) as day')]);
	}
	
	public function member() {
		return $this->hasOne("App\Models\User", "id", "member_id");
	}
	
	public function requsted_user() {
		return $this->hasOne("App\Models\User", "id", "user_id");
	}
	
	public function event() {
		return $this->hasOne("App\Models\Event", "id", "event_id");
	}
	
	public function invoice() {
		return $this->hasOne("App\Models\Invoices", "ticket_id", "id")/* ->orderBy('created_at','desc')->limit(1) */;
	}
	
	public function transfer() {
		return $this->hasOne("App\Models\TicketTransfer", "ticket_id", "id")/* ->orderBy('created_at','desc')->limit(1) */;
	}
	
	public function type() {
		return $this->hasOne("App\Models\EventTicketType", "id", "ticket_type");
	}
	
	public function allocatedPdfs() {
		return $this->hasMany("App\Models\AllocatePdfs", "ticket_id", "id");
	}
	
	
}

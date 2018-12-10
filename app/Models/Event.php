<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class Event extends Model
{
    public function event_review() {
        return $this->hasMany("App\Models\EventReview", "event_id", "id");
    }
	
	public function event_gallery() {
        return $this->hasMany("App\Models\EventsGallery", "event_id", "id");
    }


	public function carnival_name() {
		return $this->hasOne("App\Models\Carnival", "id", "carnival_id");
    }
	
	public function commitee_members_list() {
		return $this->belongsToMany('App\Models\User','commitee_members','event_id','member_id');
    }
	public function tickets() {
		return $this->hasMany('App\Models\Ticket','event_id','id');
    }
	public function ticketUsers() {
		return $this->hasManyThrough("App\Models\TicketUsers","App\Models\Ticket","event_id","ticket_id","id","id");
	}
	
	/********** testing functions ********/
	
	public function eventTicketTypes() {
        return $this->hasMany("App\Models\EventTicketType", "event_id", "id");
    }
	
	public function user() {
		return $this->hasOne("App\Models\User", "id", "user_id");
    }
	public function checkCommingSoon(){
		return $this->hasMany("App\Models\EventTicketType", "event_id", "id")
		->where(function($query){
			$query->whereDate('ticket_start_date','<=',Carbon::today()->toDateString());
			$query->whereDate('ticket_end_date','>=',Carbon::today()->toDateString());
		})
		->orWhere(function($query1){
			$query1->where('ticket_start_date','=',null);
			$query1->where('ticket_end_date','=',null);
		});
		
	}
	
	public function currentEventType(){
		return $this->hasMany("App\Models\EventTicketType", "event_id", "id")
		->where(function($query){
			$query->whereDate('ticket_start_date','<=',Carbon::today()->toDateString());
			$query->whereDate('ticket_end_date','>=',Carbon::today()->toDateString());
		})
		->orWhere(function($query1){
			$query1->where('ticket_start_date','=',null);
			$query1->where('ticket_end_date','=',null);
		})
		->orWhere(function($query2){
			$query2->whereDate('ticket_start_date','<=',Carbon::today()->toDateString());
			$query2->where('ticket_end_date','=',null);
		})
		->orWhere(function($query3){
			$query3->where('ticket_start_date','=',null);
			$query3->whereDate('ticket_end_date','>=',Carbon::today()->toDateString());
		});
		
		
	}
	
	public function comingSoon(){
		
		return $coming_soon = $this->hasMany("App\Models\EventTicketType", "event_id", "id")
		->where(function($query){
			$query->whereDate('ticket_start_date','>',Carbon::today()->toDateString());
		});
		
						
	}
	
	public function past(){
		
		return $past = $this->hasMany("App\Models\EventTicketType", "event_id", "id")
		->where(function($query){
			$query->whereDate('ticket_end_date','<',Carbon::today()->toDateString());
		});
		
					
	}

}

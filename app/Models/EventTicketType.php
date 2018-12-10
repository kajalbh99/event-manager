<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventTicketType extends Model
{
    protected $casts = [
		'total_tickets' => 'integer',
		'ticket_price' => 'float',
		'tickets_sold' => 'integer',
	];
	
	public function pdfs(){
		return $this->hasMany("App\Models\TicketTypePdfs", "ticket_type_id", "id");
	}
}
